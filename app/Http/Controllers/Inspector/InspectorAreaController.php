<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\InspectorServiceArea;
use App\Models\Offer;
use App\Models\PayoutRequest;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Services\CommissionService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InspectorAreaController extends Controller
{
    public function signedRequest(ServiceRequest $request, Inspector $inspector): RedirectResponse
    {
        // Signed email link: log the inspector in and take them to the request.
        Auth::guard('inspector')->login($inspector);

        return redirect()->route('inspector.requests.show', $request->id);
    }

    public function dashboard(WalletService $walletService): Response
    {
        $inspector = Auth::guard('inspector')->user();
        $wallet = $walletService->walletFor($inspector);

        $matchedRequestIds = $inspector->matches()->pluck('request_id');
        $offeredRequestIds = $inspector->offers()->pluck('request_id');

        $openMatches = ServiceRequest::whereIn('id', $matchedRequestIds->diff($offeredRequestIds))
            ->whereIn('status', ['open', 'offers_received'])
            ->with('serviceType:id,name')
            ->latest()
            ->take(6)
            ->get();

        $totalOffers = $inspector->offers()->count();

        return Inertia::render('inspector/Dashboard', [
            'stats' => [
                'openRequests' => $matchedRequestIds->diff($offeredRequestIds)->count(),
                'offers' => $totalOffers,
                'wonJobs' => $inspector->bookings()->count(),
                'walletAvailable' => $wallet->available_cents,
                'walletPending' => $wallet->pending_cents,
                'responseRate' => $matchedRequestIds->count() > 0
                    ? round($totalOffers / $matchedRequestIds->count() * 100)
                    : null,
            ],
            'newRequests' => $openMatches->map(fn ($r) => $this->requestRow($r)),
        ]);
    }

    public function requests(Request $httpRequest): Response
    {
        $inspector = Auth::guard('inspector')->user();

        $query = ServiceRequest::whereIn('id', $inspector->matches()->pluck('request_id'))
            ->whereIn('status', ['open', 'offers_received'])
            ->with('serviceType:id,name')
            ->withCount('offers');

        if ($type = $httpRequest->query('service')) {
            $query->whereHas('serviceType', fn ($q) => $q->where('slug', $type));
        }

        return Inertia::render('inspector/Requests', [
            'requests' => $query->latest()->paginate(12)->withQueryString()
                ->through(fn ($r) => [
                    ...$this->requestRow($r),
                    'totalOffers' => $r->offers_count,
                    'hasOwnOffer' => $inspector->offers()->where('request_id', $r->id)->exists(),
                ]),
            'serviceTypes' => \App\Models\ServiceType::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'filter' => $httpRequest->query('service'),
        ]);
    }

    public function requestDetail(ServiceRequest $serviceRequest): Response
    {
        $inspector = Auth::guard('inspector')->user();
        $match = $this->assertMatched($serviceRequest, $inspector);

        $match->update(['viewed_at' => $match->viewed_at ?? now()]);

        $ownOffer = $inspector->offers()->where('request_id', $serviceRequest->id)->first();
        $serviceRequest->load(['serviceType:id,name', 'photos']);

        return Inertia::render('inspector/RequestDetail', [
            'request' => [
                ...$this->requestRow($serviceRequest),
                'vehicle' => [
                    'make' => $serviceRequest->vehicle_make,
                    'model' => $serviceRequest->vehicle_model,
                    'firstRegistration' => $serviceRequest->first_registration,
                    'mileage' => $serviceRequest->mileage,
                    'vin' => $serviceRequest->vin,
                    'fuel' => $serviceRequest->fuel_type,
                    'transmission' => $serviceRequest->transmission,
                ],
                'plz' => $serviceRequest->plz,
                'preferredDate' => $serviceRequest->preferred_date?->format('d.m.Y'),
                'alternativeDate' => $serviceRequest->alternative_date?->format('d.m.Y'),
                'notes' => $serviceRequest->notes,
                'photos' => $serviceRequest->photos->map(fn ($p) => '/storage/'.$p->path),
            ],
            'ownOffer' => $ownOffer ? [
                'price' => $ownOffer->price_cents,
                'status' => $ownOffer->status,
            ] : null,
        ]);
    }

    public function offerForm(ServiceRequest $serviceRequest): Response
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        $serviceRequest->load('serviceType:id,name');

        return Inertia::render('inspector/SubmitOffer', [
            'request' => $this->requestRow($serviceRequest),
            'commissionPercent' => \App\Models\Setting::commissionPercent(),
        ]);
    }

    public function storeOffer(Request $httpRequest, ServiceRequest $serviceRequest, CommissionService $commission): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        if (! in_array($serviceRequest->status, ['open', 'offers_received'], true)) {
            return back()->withErrors(['price' => 'Für diese Anfrage können keine Angebote mehr abgegeben werden.']);
        }

        if ($inspector->offers()->where('request_id', $serviceRequest->id)->exists()) {
            return back()->withErrors(['price' => 'Sie haben bereits ein Angebot für diese Anfrage abgegeben.']);
        }

        $data = $httpRequest->validate([
            'price' => ['required', 'numeric', 'min:10', 'max:100000'],
            'estimated_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [
            'price.min' => 'Der Angebotspreis muss mindestens 10 € betragen.',
        ]);

        $priceCents = (int) round($data['price'] * 100);
        $split = $commission->split($priceCents);

        Offer::create([
            'request_id' => $serviceRequest->id,
            'inspector_id' => $inspector->id,
            'price_cents' => $priceCents,
            'commission_cents' => $split['commission'],
            'inspector_cents' => $split['inspector'],
            'message' => $data['message'] ?? null,
            'estimated_date' => $data['estimated_date'] ?? null,
            'status' => 'open',
            'expires_at' => $serviceRequest->expires_at,
        ]);

        $serviceRequest->update(['status' => 'offers_received']);

        AppNotification::notify($serviceRequest->user, 'new_offer',
            'Neues Angebot erhalten',
            "{$inspector->name} hat ein Angebot für Ihre Anfrage {$serviceRequest->request_number} abgegeben.",
            "/account/requests/{$serviceRequest->id}/offers");

        \Illuminate\Support\Facades\Mail::to($serviceRequest->contact_email)
            ->queue(new \App\Mail\NewOfferMail($serviceRequest, $inspector));

        ActivityLog::record('offer.submitted', $inspector, $serviceRequest);

        return redirect()->route('inspector.offers')->with('success', 'Ihr Angebot wurde übermittelt. Der Kunde wurde benachrichtigt.');
    }

    public function offers(): Response
    {
        return Inertia::render('inspector/Offers', [
            'offers' => Auth::guard('inspector')->user()->offers()
                ->with('request.serviceType:id,name')
                ->latest()
                ->paginate(12)
                ->through(fn ($o) => [
                    'id' => $o->id,
                    'requestId' => $o->request_id,
                    'requestNumber' => $o->request->request_number,
                    'service' => $o->request->serviceType->name,
                    'vehicle' => $o->request->vehicle_make.' '.$o->request->vehicle_model,
                    'ort' => $o->request->ort,
                    'price' => $o->price_cents,
                    'net' => $o->inspector_cents,
                    'status' => $o->status,
                    'date' => $o->created_at->format('d.m.Y'),
                ]),
        ]);
    }

    public function jobs(): Response
    {
        return Inertia::render('inspector/Jobs', [
            'jobs' => Auth::guard('inspector')->user()->bookings()
                ->with(['request.serviceType:id,name', 'offer', 'payment'])
                ->latest()
                ->paginate(12)
                ->through(fn ($b) => $this->jobRow($b)),
        ]);
    }

    public function jobDetail(Booking $booking): Response
    {
        $inspector = Auth::guard('inspector')->user();
        abort_unless($booking->inspector_id === $inspector->id, 403);

        $booking->load(['request.serviceType:id,name', 'request.photos', 'offer', 'payment', 'user:id,name,email,phone']);

        return Inertia::render('inspector/JobDetail', [
            'job' => [
                ...$this->jobRow($booking),
                // Full customer contact data is revealed only after the job is awarded.
                'customer' => [
                    'name' => $booking->request->contact_name,
                    'email' => $booking->request->contact_email,
                    'phone' => $booking->request->contact_phone,
                    'strasse' => $booking->request->strasse,
                    'plz' => $booking->request->plz,
                    'ort' => $booking->request->ort,
                ],
                'vehicle' => [
                    'make' => $booking->request->vehicle_make,
                    'model' => $booking->request->vehicle_model,
                    'firstRegistration' => $booking->request->first_registration,
                    'mileage' => $booking->request->mileage,
                    'vin' => $booking->request->vin,
                ],
                'notes' => $booking->request->notes,
                'photos' => $booking->request->photos->map(fn ($p) => '/storage/'.$p->path),
            ],
        ]);
    }

    public function completeJob(Booking $booking): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        abort_unless($booking->inspector_id === $inspector->id, 403);

        if (! in_array($booking->status, ['paid', 'in_progress'], true)) {
            return back()->withErrors(['status' => 'Dieser Auftrag kann nicht abgeschlossen werden.']);
        }

        $booking->update(['status' => 'completed_by_inspector', 'completed_at' => now()]);

        ActivityLog::record('booking.completed_by_inspector', $inspector, $booking);

        return back()->with('success', 'Auftrag als abgeschlossen markiert. Nach Bestätigung durch die Plattform wird Ihr Guthaben freigegeben.');
    }

    public function serviceAreas(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        return Inertia::render('inspector/ServiceAreas', [
            'areas' => $inspector->serviceAreas()->orderBy('type')->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'city' => $a->city_name,
                    'from' => $a->postal_from ? str_pad((string) $a->postal_from, 5, '0', STR_PAD_LEFT) : null,
                    'to' => $a->postal_to ? str_pad((string) $a->postal_to, 5, '0', STR_PAD_LEFT) : null,
                ]),
        ]);
    }

    public function storeServiceArea(Request $httpRequest): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();

        $data = $httpRequest->validate([
            'type' => ['required', 'in:city,postal_range'],
            'city_name' => ['required_if:type,city', 'nullable', 'string', 'max:120'],
            'postal_from' => ['required_if:type,postal_range', 'nullable', 'digits:5'],
            'postal_to' => ['required_if:type,postal_range', 'nullable', 'digits:5', 'gte:postal_from'],
        ], [
            'postal_to.gte' => 'Die End-PLZ muss größer oder gleich der Start-PLZ sein.',
        ]);

        $inspector->serviceAreas()->create([
            'type' => $data['type'],
            'city_name' => $data['type'] === 'city' ? $data['city_name'] : null,
            'postal_from' => $data['type'] === 'postal_range' ? (int) $data['postal_from'] : null,
            'postal_to' => $data['type'] === 'postal_range' ? (int) $data['postal_to'] : null,
        ]);

        return back()->with('success', 'Servicegebiet hinzugefügt.');
    }

    public function deleteServiceArea(InspectorServiceArea $area): RedirectResponse
    {
        abort_unless($area->inspector_id === Auth::guard('inspector')->id(), 403);
        $area->delete();

        return back()->with('success', 'Servicegebiet entfernt.');
    }

    public function wallet(WalletService $walletService): Response
    {
        $inspector = Auth::guard('inspector')->user();
        $wallet = $walletService->walletFor($inspector);

        return Inertia::render('inspector/Wallet', [
            'wallet' => [
                'available' => $wallet->available_cents,
                'pending' => $wallet->pending_cents,
                'lifetime' => $wallet->lifetime_cents,
            ],
            'transactions' => $wallet->transactions()->latest()->paginate(15)
                ->through(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'amount' => $t->amount_cents,
                    'balanceAfter' => $t->balance_after_cents,
                    'description' => $t->description,
                    'date' => $t->created_at->format('d.m.Y H:i'),
                ]),
        ]);
    }

    public function payoutForm(WalletService $walletService): Response
    {
        $inspector = Auth::guard('inspector')->user();
        $wallet = $walletService->walletFor($inspector);

        return Inertia::render('inspector/PayoutRequest', [
            'available' => $wallet->available_cents,
            'pendingPayout' => $inspector->payoutRequests()->where('status', 'pending')->sum('amount_cents'),
            'history' => $inspector->payoutRequests()->latest('requested_at')->take(10)->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'amount' => $p->amount_cents,
                    'status' => $p->status,
                    'requested' => $p->requested_at->format('d.m.Y'),
                    'paid' => $p->paid_at?->format('d.m.Y'),
                ]),
        ]);
    }

    public function storePayout(Request $httpRequest, WalletService $walletService): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $wallet = $walletService->walletFor($inspector);

        $pendingPayouts = (int) $inspector->payoutRequests()->where('status', 'pending')->sum('amount_cents');
        $maxAmount = ($wallet->available_cents - $pendingPayouts) / 100;

        $data = $httpRequest->validate([
            'amount' => ['required', 'numeric', 'min:1', "max:{$maxAmount}"],
            'iban' => ['required', 'string', 'regex:/^DE\d{20}$/i'],
            'bic' => ['nullable', 'string', 'max:11'],
            'account_holder' => ['required', 'string', 'max:120'],
        ], [
            'amount.max' => 'Der Betrag übersteigt Ihr verfügbares Guthaben.',
            'iban.regex' => 'Bitte geben Sie eine gültige deutsche IBAN ein (DE + 20 Ziffern).',
        ]);

        $payout = PayoutRequest::create([
            'inspector_id' => $inspector->id,
            'amount_cents' => (int) round($data['amount'] * 100),
            'iban' => strtoupper(str_replace(' ', '', $data['iban'])),
            'bic' => $data['bic'] ?? null,
            'account_holder' => $data['account_holder'],
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        ActivityLog::record('payout.requested', $inspector, $payout, ['amount_cents' => $payout->amount_cents]);

        return redirect()->route('inspector.wallet')->with('success', 'Ihre Auszahlungsanfrage wurde übermittelt und wird in Kürze bearbeitet.');
    }

    public function profile(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        return Inertia::render('inspector/Profile', [
            'profile' => $inspector->only(['name', 'company_name', 'email', 'phone', 'city', 'bio', 'qualifications', 'years_experience']),
        ]);
    }

    public function updateProfile(Request $httpRequest): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();

        $data = $httpRequest->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
        ]);

        $inspector->update($data);

        return back()->with('success', 'Profil aktualisiert.');
    }

    private function assertMatched(ServiceRequest $serviceRequest, Inspector $inspector): RequestMatch
    {
        $match = RequestMatch::where('request_id', $serviceRequest->id)
            ->where('inspector_id', $inspector->id)
            ->first();

        abort_unless($match !== null, 403);

        return $match;
    }

    private function requestRow(ServiceRequest $r): array
    {
        return [
            'id' => $r->id,
            'number' => $r->request_number,
            'service' => $r->serviceType->name,
            'vehicle' => $r->vehicle_make.' '.$r->vehicle_model,
            'ort' => $r->ort,
            'status' => $r->status,
            'date' => $r->created_at->format('d.m.Y'),
        ];
    }

    private function jobRow(Booking $b): array
    {
        return [
            'id' => $b->id,
            'number' => $b->booking_number,
            'service' => $b->request->serviceType->name,
            'vehicle' => $b->request->vehicle_make.' '.$b->request->vehicle_model,
            'ort' => $b->request->ort,
            'price' => $b->offer->price_cents,
            'net' => $b->offer->inspector_cents,
            'status' => $b->status,
            'date' => $b->created_at->format('d.m.Y'),
        ];
    }
}
