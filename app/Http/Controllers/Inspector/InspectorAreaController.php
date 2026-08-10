<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Mail\InspectorVerifyEmailMail;
use App\Mail\NewOfferMail;
use App\Mail\OfferUpdatedMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\InspectorServiceArea;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Services\CommissionService;
use App\Services\RequestService;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
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

    public function verificationNotice(): Response
    {
        return Inertia::render('inspector/VerifyEmailNotice', [
            'email' => Auth::guard('inspector')->user()->email,
        ]);
    }

    public function resendVerification(): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();

        if ($inspector->email_verified_at) {
            return redirect()->route('inspector.dashboard');
        }

        $this->sendVerificationEmail($inspector);

        return back()->with('success', 'E-Mail wurde erneut gesendet.');
    }

    public function onboardingProfile(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        return Inertia::render('inspector/CompleteProfile', [
            'profile' => $inspector->only(['birthday', 'tax_id', 'street', 'plz', 'city', 'phone']),
            'pendingRequestId' => session('inspector_pending_request_id'),
            'progress' => [
                'basicsDone' => $inspector->profile_completed_at !== null,
                'serviceAreaDone' => $inspector->serviceAreas()->exists(),
                'detailsDone' => $inspector->bio !== null && $inspector->years_experience !== null,
            ],
            'serviceAreas' => $inspector->serviceAreas()->orderBy('type')->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'city' => $a->city_name,
                    'from' => $a->postal_from ? str_pad((string) $a->postal_from, 5, '0', STR_PAD_LEFT) : null,
                    'to' => $a->postal_to ? str_pad((string) $a->postal_to, 5, '0', STR_PAD_LEFT) : null,
                ]),
            'details' => $inspector->only(['bio', 'qualifications', 'years_experience', 'avatar_key']),
            'serviceTypes' => ServiceType::where('service_category_id', $inspector->service_category_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'selectedServiceTypeIds' => $inspector->serviceTypes()->pluck('service_types.id'),
            'avatarOptions' => collect(config('avatars'))->map(fn ($a, $key) => ['key' => $key, ...$a])->values(),
        ]);
    }

    public function storeOnboardingProfile(Request $httpRequest, RequestService $requestService): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();

        $data = $httpRequest->validate([
            'birthday' => ['required', 'date', 'before:'.now()->subYears(18)->toDateString()],
            'tax_id' => ['required', 'string', 'max:50'],
            'street' => ['required', 'string', 'max:190'],
            'plz' => ['required', 'digits:5'],
            'city' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
        ], [
            'birthday.before' => 'Sie müssen mindestens 18 Jahre alt sein.',
        ]);

        $inspector->update([
            'birthday' => $data['birthday'],
            'tax_id' => $data['tax_id'],
            'street' => $data['street'],
            'plz' => $data['plz'],
            'city' => $data['city'],
            'phone' => $data['phone'],
            'profile_completed_at' => now(),
        ]);

        ActivityLog::record('inspector.profile_completed', $inspector);

        // Continue the same onboarding page into its service-area and
        // profile/qualifications steps — an admin-invited registration used
        // to jump straight to the specific request right after this step
        // alone, skipping those entirely, which left the provider's profile
        // empty. session('inspector_pending_request_id') is left untouched
        // here (still peeked, not pulled) so the onboarding page can offer a
        // direct link back to that request once these steps are done too.
        return redirect()->route('inspector.onboarding.profile')
            ->with('success', 'Angaben gespeichert. Vervollständigen Sie jetzt Ihr Servicegebiet und Ihr Profil, damit Sie Anfragen erhalten können.');
    }

    private function sendVerificationEmail(Inspector $inspector): void
    {
        $signedLink = URL::temporarySignedRoute(
            'inspector.verification.verify',
            now()->addDays(14),
            ['inspector' => $inspector->id]
        );

        SafeMailer::send(fn () => Mail::to($inspector->email)->queue(new InspectorVerifyEmailMail($inspector, $signedLink)));
    }

    public function dashboard(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        // A one-time nudge (see CompleteProfile.vue) offers a direct link
        // back to the request that brought an admin-invited provider here —
        // once they reach the normal dashboard by any route, that's served
        // its purpose and shouldn't keep pointing at an increasingly stale request.
        session()->forget('inspector_pending_request_id');

        if (! $inspector->is_approved) {
            return Inertia::render('inspector/Dashboard', [
                'pendingApproval' => true,
                'stats' => null,
                'newRequests' => [],
            ]);
        }

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
            'needsServiceArea' => ! $inspector->serviceAreas()->exists(),
            'needsProfileDetails' => $inspector->bio === null || $inspector->years_experience === null,
            'stats' => [
                'openRequests' => $matchedRequestIds->diff($offeredRequestIds)->count(),
                'offers' => $totalOffers,
                'wonJobs' => $inspector->bookings()->count(),
                'responseRate' => $matchedRequestIds->count() > 0
                    ? round($totalOffers / $matchedRequestIds->count() * 100)
                    : null,
                'rating' => $inspector->averageRating(),
                'reviewsCount' => $inspector->reviews()->count(),
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
            'serviceTypes' => ServiceType::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
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
            'competingOffers' => $this->competingOffersFor($serviceRequest, $inspector),
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
                'message' => $ownOffer->message,
                'estimatedDate' => $ownOffer->estimated_date?->format('d.m.Y'),
                'status' => $ownOffer->status,
                'editedAt' => $ownOffer->edited_at?->format('d.m.Y H:i'),
            ] : null,
        ]);
    }

    public function offerForm(ServiceRequest $serviceRequest): Response|RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        if (! $inspector->is_approved) {
            return redirect()->route('inspector.dashboard')
                ->with('error', 'Ihr Konto wird noch von unserem Team geprüft. Sobald Sie freigeschaltet sind, können Sie Angebote abgeben.');
        }

        $serviceRequest->load('serviceType:id,name');

        return Inertia::render('inspector/SubmitOffer', [
            'request' => $this->requestRow($serviceRequest),
            'commissionPercent' => Setting::commissionPercent(),
            'competingOffers' => $this->competingOffersFor($serviceRequest, $inspector),
        ]);
    }

    public function storeOffer(Request $httpRequest, ServiceRequest $serviceRequest, CommissionService $commission): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        if (! $inspector->is_approved) {
            return redirect()->route('inspector.dashboard')
                ->with('error', 'Ihr Konto wird noch von unserem Team geprüft. Sobald Sie freigeschaltet sind, können Sie Angebote abgeben.');
        }

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
        $isFirstOffer = $serviceRequest->status === 'open';

        $offer = Offer::create([
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

        $label = $serviceRequest->offerLabel($offer->id);

        if ($serviceRequest->user) {
            AppNotification::notify($serviceRequest->user, 'new_offer',
                'Neues Angebot erhalten',
                "{$label} hat ein Angebot für Ihre Anfrage {$serviceRequest->request_number} abgegeben.",
                "/account/requests/{$serviceRequest->id}/offers");
        }

        SafeMailer::send(fn () => Mail::to($serviceRequest->contact_email)
            ->queue(new NewOfferMail($serviceRequest, $inspector, $label, $offer)));

        if ($isFirstOffer) {
            app(RequestService::class)->recordFirstOffer($serviceRequest);
        }

        ActivityLog::record('offer.submitted', $inspector, $serviceRequest);

        return redirect()->route('inspector.offers')->with('success', 'Ihr Angebot wurde übermittelt. Der Kunde wurde benachrichtigt.');
    }

    public function editOfferForm(ServiceRequest $serviceRequest): Response|RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        $offer = $inspector->offers()->where('request_id', $serviceRequest->id)->firstOrFail();

        if ($offer->status !== 'open') {
            return redirect()->route('inspector.requests.show', $serviceRequest)
                ->with('error', 'Dieses Angebot wurde bereits entschieden und kann nicht mehr bearbeitet werden.');
        }

        $serviceRequest->load('serviceType:id,name');

        return Inertia::render('inspector/EditOffer', [
            'request' => $this->requestRow($serviceRequest),
            'commissionPercent' => Setting::commissionPercent(),
            'competingOffers' => $this->competingOffersFor($serviceRequest, $inspector),
            'offer' => [
                'price' => number_format($offer->price_cents / 100, 2, '.', ''),
                'estimated_date' => $offer->estimated_date?->format('Y-m-d'),
                'message' => $offer->message,
            ],
        ]);
    }

    public function updateOffer(Request $httpRequest, ServiceRequest $serviceRequest, CommissionService $commission): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();
        $this->assertMatched($serviceRequest, $inspector);

        $offer = $inspector->offers()->where('request_id', $serviceRequest->id)->firstOrFail();

        if ($offer->status !== 'open') {
            return back()->withErrors(['price' => 'Dieses Angebot kann nicht mehr bearbeitet werden.']);
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

        $offer->update([
            'price_cents' => $priceCents,
            'commission_cents' => $split['commission'],
            'inspector_cents' => $split['inspector'],
            'message' => $data['message'] ?? null,
            'estimated_date' => $data['estimated_date'] ?? null,
            'edited_at' => now(),
        ]);

        ActivityLog::record('offer.updated', $inspector, $offer);

        $label = $serviceRequest->offerLabel($offer->id);

        SafeMailer::send(fn () => Mail::to($serviceRequest->contact_email)->queue(new OfferUpdatedMail($serviceRequest, $inspector, $offer, $label)));

        return redirect()->route('inspector.requests.show', $serviceRequest)->with('success', 'Ihr Angebot wurde aktualisiert.');
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

        if (! in_array($booking->status, ['accepted', 'paid', 'in_progress'], true)) {
            return back()->withErrors(['status' => 'Dieser Auftrag kann nicht abgeschlossen werden.']);
        }

        $booking->update(['status' => 'completed_by_inspector', 'completed_at' => now()]);

        ActivityLog::record('booking.completed_by_inspector', $inspector, $booking);

        return back()->with('success', 'Auftrag als abgeschlossen markiert. Der Kunde wird nun um Bestätigung gebeten.');
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

    public function storeServiceArea(Request $httpRequest, RequestService $requestService): RedirectResponse
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
            'city_name' => $data['type'] === 'city' ? trim($data['city_name']) : null,
            'postal_from' => $data['type'] === 'postal_range' ? (int) $data['postal_from'] : null,
            'postal_to' => $data['type'] === 'postal_range' ? (int) $data['postal_to'] : null,
        ]);

        // Widening coverage can mean previously-stuck "no matching inspector"
        // requests in this area now belong to this inspector too.
        $requestService->rematchUnmatchedRequestsFor($inspector);

        return back()->with('success', 'Servicegebiet hinzugefügt.');
    }

    public function deleteServiceArea(InspectorServiceArea $area): RedirectResponse
    {
        abort_unless($area->inspector_id === Auth::guard('inspector')->id(), 403);
        $area->delete();

        return back()->with('success', 'Servicegebiet entfernt.');
    }

    public function invoices(): Response
    {
        return Inertia::render('inspector/Invoices', [
            'invoices' => Auth::guard('inspector')->user()->invoices()
                ->with('booking.request:id,request_number')
                ->latest()
                ->paginate(15)
                ->through(fn ($i) => [
                    'id' => $i->id,
                    'number' => $i->booking->request->request_number,
                    'offerAmount' => $i->offer_amount_cents,
                    'commissionPercent' => $i->commission_percent,
                    'commissionAmount' => $i->commission_cents,
                    'dueDate' => $i->due_date->format('d.m.Y'),
                    'date' => $i->created_at->format('d.m.Y'),
                ]),
        ]);
    }

    public function downloadInvoice(Invoice $invoice)
    {
        abort_unless($invoice->inspector_id === Auth::guard('inspector')->id(), 403);
        abort_unless($invoice->pdf_path, 404);
        $invoice->loadMissing('booking.request');

        return Storage::disk('local')->download($invoice->pdf_path, "{$invoice->referenceNumber()}.pdf");
    }

    public function reviews(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        return Inertia::render('inspector/Reviews', [
            'averageRating' => $inspector->averageRating(),
            'reviewsCount' => $inspector->reviews()->count(),
            'reviews' => $inspector->reviews()
                ->with('booking.request:id,request_number,service_type_id', 'booking.request.serviceType:id,name')
                ->latest()
                ->paginate(15)
                ->through(fn (Review $r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'rawRating' => $r->raw_rating,
                    'comment' => $r->comment,
                    'service' => $r->booking?->request?->serviceType?->name,
                    'requestNumber' => $r->booking?->request?->request_number,
                    'date' => $r->created_at->format('d.m.Y'),
                ]),
        ]);
    }

    public function profile(): Response
    {
        $inspector = Auth::guard('inspector')->user();

        return Inertia::render('inspector/Profile', [
            'profile' => $inspector->only(['name', 'company_name', 'email', 'phone', 'city', 'bio', 'qualifications', 'years_experience', 'avatar_key']),
            'serviceTypes' => ServiceType::where('service_category_id', $inspector->service_category_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name']),
            'selectedServiceTypeIds' => $inspector->serviceTypes()->pluck('service_types.id'),
            'avatarOptions' => collect(config('avatars'))->map(fn ($a, $key) => ['key' => $key, ...$a])->values(),
        ]);
    }

    public function updateProfile(Request $httpRequest): RedirectResponse
    {
        $inspector = Auth::guard('inspector')->user();

        // "sometimes" on name/company_name/phone/city: the full profile page
        // always sends these, but the onboarding wizard's "Profile &
        // Qualifications" step reuses this same endpoint and only sends
        // bio/qualifications/experience/services/avatar — without
        // "sometimes", that partial submission would fail validation
        // (missing required name) or silently null out company/phone/city.
        $data = $httpRequest->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:190'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'avatar_key' => ['nullable', 'string', Rule::in(array_keys(config('avatars')))],
            'service_type_ids' => ['array'],
            'service_type_ids.*' => ['integer', 'exists:service_types,id'],
        ]);

        $inspector->update(collect($data)->except('service_type_ids')->all());
        $inspector->serviceTypes()->sync($data['service_type_ids'] ?? []);

        return back()->with('success', 'Profil aktualisiert.');
    }

    /**
     * Price/count transparency only for a competing inspector — never names,
     * companies, or ratings that could identify who submitted a given price.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function competingOffersFor(ServiceRequest $serviceRequest, Inspector $inspector): \Illuminate\Support\Collection
    {
        return Offer::where('request_id', $serviceRequest->id)
            ->where('inspector_id', '!=', $inspector->id)
            ->where('status', 'open')
            ->orderBy('price_cents')
            ->pluck('price_cents');
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
            // The original request number, not the internal booking_number
            // — must stay the same reference the provider already knows.
            'number' => $b->request->request_number,
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
