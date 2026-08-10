<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BalanceReleasedMail;
use App\Mail\BookingCompletedMail;
use App\Mail\InspectorApprovedMail;
use App\Mail\InspectorInvitationMail;
use App\Mail\ProviderInviteToRegisterMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\RequestMatch;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\ServiceTypeRedirect;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RequestService;
use App\Services\WalletService;
use App\Support\SafeMailer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $last30 = now()->subDays(30);

        $revenueByWeek = Payment::where('status', 'paid')
            ->where('paid_at', '>=', now()->subWeeks(12))
            ->get()
            ->groupBy(fn ($p) => $p->paid_at->startOfWeek()->format('d.m.'))
            ->map(fn ($group) => [
                'total' => $group->sum('total_cents'),
                'commission' => $group->sum('commission_cents'),
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'requests' => ServiceRequest::count(),
                'requestsNew' => ServiceRequest::where('created_at', '>=', $last30)->count(),
                'offers' => Offer::count(),
                'bookings' => Booking::count(),
                'revenue' => Payment::where('status', 'paid')->sum('total_cents'),
                'commission' => Payment::where('status', 'paid')->sum('commission_cents'),
                'outstandingInvoices' => Invoice::whereNull('paid_at')->count(),
                'outstandingInvoiceAmount' => Invoice::whereNull('paid_at')->sum('commission_cents'),
                'unmatchedRequests' => ServiceRequest::where('status', 'unmatched')->count(),
                'inspectors' => Inspector::where('is_active', true)->count(),
            ],
            'funnel' => [
                'requests' => ServiceRequest::count(),
                'withOffers' => ServiceRequest::has('offers')->count(),
                'booked' => Booking::count(),
                'completed' => Booking::whereIn('status', ['confirmed'])->count(),
            ],
            'revenueByWeek' => $revenueByWeek,
            'commissionPercent' => Setting::commissionPercent(),
            'topInspectors' => Inspector::withCount('bookings')
                ->orderByDesc('bookings_count')
                ->take(5)
                ->get(['id', 'name', 'company_name', 'city'])
                ->map(fn ($i) => [
                    'id' => $i->id, 'name' => $i->name, 'company' => $i->company_name,
                    'city' => $i->city, 'jobs' => $i->bookings_count,
                ]),
        ]);
    }

    public function requests(Request $request): Response
    {
        $query = ServiceRequest::with(['serviceType:id,name', 'user:id,name'])->withCount('offers');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('suche')) {
            $query->where(fn ($q) => $q->where('request_number', 'like', "%{$search}%")
                ->orWhere('ort', 'like', "%{$search}%")
                ->orWhere('vehicle_make', 'like', "%{$search}%"));
        }

        return Inertia::render('admin/Requests', [
            'requests' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($r) => [
                    'id' => $r->id,
                    'number' => $r->request_number,
                    'customer' => $r->user->name ?? $r->contact_name,
                    'service' => $r->serviceType->name,
                    'vehicle' => $r->vehicle_make.' '.$r->vehicle_model,
                    'ort' => $r->ort,
                    'status' => $r->status,
                    'matched' => $r->matched_count,
                    'offers' => $r->offers_count,
                    'date' => $r->created_at->format('d.m.Y H:i'),
                ]),
            'filters' => $request->only(['status', 'suche']),
        ]);
    }

    public function requestDetail(ServiceRequest $serviceRequest): Response
    {
        $serviceRequest->load(['serviceType:id,name', 'user:id,name,email,phone', 'photos',
            'matches.inspector:id,name,company_name,city',
            'offers.inspector:id,name,company_name,city', 'booking.payment']);

        return Inertia::render('admin/RequestDetail', [
            'request' => [
                'id' => $serviceRequest->id,
                'number' => $serviceRequest->request_number,
                'status' => $serviceRequest->status,
                'service' => $serviceRequest->serviceType->name,
                'customer' => $serviceRequest->user?->only(['name', 'email', 'phone']) ?? [
                    'name' => $serviceRequest->contact_name,
                    'email' => $serviceRequest->contact_email,
                    'phone' => $serviceRequest->contact_phone,
                ],
                'vehicle' => [
                    'make' => $serviceRequest->vehicle_make,
                    'model' => $serviceRequest->vehicle_model,
                    'firstRegistration' => $serviceRequest->first_registration,
                    'mileage' => $serviceRequest->mileage,
                    'vin' => $serviceRequest->vin,
                    'fuel' => $serviceRequest->fuel_type,
                    'transmission' => $serviceRequest->transmission,
                ],
                'location' => ['plz' => $serviceRequest->plz, 'ort' => $serviceRequest->ort, 'strasse' => $serviceRequest->strasse],
                'notes' => $serviceRequest->notes,
                'date' => $serviceRequest->created_at->format('d.m.Y H:i'),
                'matches' => $serviceRequest->matches->map(fn ($m) => [
                    'inspector' => $m->inspector->name,
                    'company' => $m->inspector->company_name,
                    'city' => $m->inspector->city,
                    'notified' => $m->notified_at?->format('d.m.Y H:i'),
                    'viewed' => $m->viewed_at?->format('d.m.Y H:i'),
                ]),
                'offers' => $serviceRequest->offers->map(fn ($o) => [
                    'id' => $o->id,
                    'inspector' => $o->inspector->name,
                    'price' => $o->price_cents,
                    'status' => $o->status,
                    'date' => $o->created_at->format('d.m.Y'),
                ]),
                'invitedProviders' => ActivityLog::where('action', 'request.provider_invited')
                    ->where('subject_type', $serviceRequest->getMorphClass())
                    ->where('subject_id', $serviceRequest->id)
                    ->latest()
                    ->get()
                    ->map(fn ($log) => [
                        'email' => $log->meta['email'] ?? null,
                        'date' => $log->created_at->format('d.m.Y H:i'),
                    ]),
            ],
        ]);
    }

    public function inviteProvider(Request $httpRequest, ServiceRequest $serviceRequest, RequestService $requestService): RedirectResponse
    {
        $data = $httpRequest->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        $inspector = Inspector::where('email', $data['email'])->first();

        if ($inspector) {
            RequestMatch::firstOrCreate(
                ['request_id' => $serviceRequest->id, 'inspector_id' => $inspector->id],
                ['notified_at' => now()]
            );

            $requestService->notifyInspectorOfMatch($inspector, $serviceRequest);
        } else {
            $signedLink = URL::temporarySignedRoute(
                'inspector.invite.accept',
                now()->addDays(30),
                ['serviceRequest' => $serviceRequest->id, 'email' => $data['email']]
            );

            SafeMailer::send(fn () => Mail::to($data['email'])->queue(new ProviderInviteToRegisterMail($data['email'], $serviceRequest, $signedLink)));
        }

        ActivityLog::record('request.provider_invited', Auth::guard('admin')->user(), $serviceRequest, ['email' => $data['email']]);

        return back()->with('success', $inspector
            ? 'Dienstleister wurde zur Anfrage eingeladen.'
            : 'Einladung wurde per E-Mail versendet.');
    }

    public function offers(Request $request): Response
    {
        $query = Offer::with(['inspector:id,name,company_name', 'request:id,request_number,ort']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('admin/Offers', [
            'offers' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($o) => [
                    'id' => $o->id,
                    'requestId' => $o->request_id,
                    'requestNumber' => $o->request->request_number,
                    'inspector' => $o->inspector->name,
                    'company' => $o->inspector->company_name,
                    'ort' => $o->request->ort,
                    'price' => $o->price_cents,
                    'commission' => $o->commission_cents,
                    'status' => $o->status,
                    'date' => $o->created_at->format('d.m.Y'),
                ]),
            'filters' => $request->only(['status']),
        ]);
    }

    public function bookings(Request $request): Response
    {
        $query = Booking::with(['request.serviceType:id,name', 'user:id,name', 'inspector:id,name', 'offer', 'payment']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('admin/Bookings', [
            'bookings' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($b) => [
                    'id' => $b->id,
                    'number' => $b->request->request_number,
                    'customer' => $b->user->name,
                    'inspector' => $b->inspector->name,
                    'service' => $b->request->serviceType->name,
                    'total' => $b->offer->price_cents,
                    'commission' => $b->offer->commission_cents,
                    'inspectorShare' => $b->offer->inspector_cents,
                    'status' => $b->status,
                    'date' => $b->created_at->format('d.m.Y'),
                ]),
            'filters' => $request->only(['status']),
        ]);
    }

    public function bookingDetail(Booking $booking): Response
    {
        $booking->load(['request.serviceType:id,name', 'user:id,name,email,phone', 'inspector', 'offer', 'payment', 'invoice', 'review']);

        return Inertia::render('admin/BookingDetail', [
            'booking' => [
                'id' => $booking->id,
                'number' => $booking->request->request_number,
                'status' => $booking->status,
                'customer' => $booking->user->only(['name', 'email', 'phone']),
                'inspector' => $booking->inspector->only(['id', 'name', 'company_name', 'city', 'email']),
                'service' => $booking->request->serviceType->name,
                'vehicle' => $booking->request->vehicle_make.' '.$booking->request->vehicle_model,
                'requestId' => $booking->request_id,
                'requestNumber' => $booking->request->request_number,
                'total' => $booking->offer->price_cents,
                'commission' => $booking->offer->commission_cents,
                'inspectorShare' => $booking->offer->inspector_cents,
                // Legacy Stripe-paid bookings still carry a Payment; new
                // direct-agreement bookings are invoiced for commission instead.
                'moneyTrail' => $booking->payment ? [
                    'total' => $booking->payment->total_cents,
                    'commission' => $booking->payment->commission_cents,
                    'inspectorShare' => $booking->payment->inspector_cents,
                    'stripeRef' => $booking->payment->stripe_payment_intent_id,
                    'paidAt' => $booking->payment->paid_at?->format('d.m.Y H:i'),
                ] : null,
                'invoice' => $booking->invoice ? [
                    'id' => $booking->invoice->id,
                    'number' => $booking->invoice->referenceNumber(),
                    'commissionAmount' => $booking->invoice->commission_cents,
                    'dueDate' => $booking->invoice->due_date->format('d.m.Y'),
                ] : null,
                'completedAt' => $booking->completed_at?->format('d.m.Y H:i'),
                'confirmedAt' => $booking->confirmed_at?->format('d.m.Y H:i'),
                'review' => $booking->review?->only(['rating', 'comment']),
            ],
        ]);
    }

    /**
     * Admin confirms a completed job. Under the direct-agreement model there's
     * no platform-held balance to release (the customer already paid the
     * provider directly) — that only still applies to legacy bookings that
     * went through the old Stripe/wallet flow and still carry a Payment.
     */
    public function confirmBooking(Booking $booking, WalletService $walletService, RequestService $requestService): RedirectResponse
    {
        if ($booking->status !== 'completed_by_inspector') {
            return back()->withErrors(['status' => 'Nur vom Dienstleister abgeschlossene Aufträge können bestätigt werden.']);
        }

        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $booking->request->update(['status' => 'completed']);

        if ($booking->payment) {
            $walletService->releasePending($booking);

            AppNotification::notify($booking->inspector, 'balance_released',
                'Guthaben freigegeben',
                "Ihr Anteil für Auftrag {$booking->request->request_number} ist jetzt verfügbar.",
                '/inspector');
            SafeMailer::send(fn () => Mail::to($booking->inspector->email)->queue(new BalanceReleasedMail($booking)));
        }

        AppNotification::notify($booking->user, 'booking_completed',
            'Auftrag abgeschlossen',
            "Auftrag {$booking->request->request_number} wurde als abgeschlossen bestätigt.",
            "/account/bookings/{$booking->id}");
        SafeMailer::send(fn () => Mail::to($booking->user->email)->queue(new BookingCompletedMail($booking)));

        $requestService->sendReviewRequest($booking);

        ActivityLog::record('booking.confirmed', Auth::guard('admin')->user(), $booking);

        return back()->with('success', 'Auftrag bestätigt.');
    }

    public function payments(Request $request): Response
    {
        $query = Payment::with('booking:id,request_id,user_id,inspector_id', 'booking.request:id,request_number', 'booking.user:id,name', 'booking.inspector:id,name');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('admin/Payments', [
            'payments' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'bookingId' => $p->booking->id,
                    'booking' => $p->booking->request->request_number,
                    'customer' => $p->booking->user->name,
                    'inspector' => $p->booking->inspector->name,
                    'total' => $p->total_cents,
                    'commission' => $p->commission_cents,
                    'inspectorShare' => $p->inspector_cents,
                    'stripeRef' => $p->stripe_payment_intent_id,
                    'status' => $p->status,
                    'date' => $p->paid_at?->format('d.m.Y H:i'),
                ]),
            'totals' => [
                'revenue' => Payment::where('status', 'paid')->sum('total_cents'),
                'commission' => Payment::where('status', 'paid')->sum('commission_cents'),
            ],
            'filters' => $request->only(['status']),
        ]);
    }

    public function commissions(Request $request): Response
    {
        $from = $request->query('von') ? Carbon::parse($request->query('von')) : now()->subMonths(3);
        $to = $request->query('bis') ? Carbon::parse($request->query('bis'))->endOfDay() : now();

        $payments = Payment::where('status', 'paid')->whereBetween('paid_at', [$from, $to]);

        return Inertia::render('admin/Commissions', [
            'summary' => [
                'revenue' => (clone $payments)->sum('total_cents'),
                'commission' => (clone $payments)->sum('commission_cents'),
                'count' => (clone $payments)->count(),
            ],
            'byMonth' => (clone $payments)->get()
                ->groupBy(fn ($p) => $p->paid_at->format('m/Y'))
                ->map(fn ($group) => [
                    'revenue' => $group->sum('total_cents'),
                    'commission' => $group->sum('commission_cents'),
                    'count' => $group->count(),
                ]),
            'filters' => ['von' => $from->toDateString(), 'bis' => $to->toDateString()],
            'commissionPercent' => Setting::commissionPercent(),
        ]);
    }

    /**
     * "Commissions & Invoices" — replaces the deprecated Wallet/Payout admin
     * pages entirely under the commission-invoice model: every invoice
     * AngebotJetzt has billed a provider for its commission, whether it's
     * been paid yet, and running totals of both.
     */
    public function invoices(Request $request): Response
    {
        $query = Invoice::with(['inspector:id,name,company_name', 'booking.request:id,request_number']);

        if ($search = $request->query('suche')) {
            $query->where(fn ($q) => $q->whereHas('booking.request', fn ($r) => $r->where('request_number', 'like', "%{$search}%"))
                ->orWhereHas('inspector', fn ($i) => $i->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%")));
        }

        if ($status = $request->query('status')) {
            $query->where(fn ($q) => $status === 'paid' ? $q->whereNotNull('paid_at') : $q->whereNull('paid_at'));
        }

        return Inertia::render('admin/Invoices', [
            'invoices' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($i) => [
                    'id' => $i->id,
                    'bookingId' => $i->booking_id,
                    'request' => $i->booking->request->request_number,
                    'inspector' => $i->inspector->company_name ?: $i->inspector->name,
                    'offerAmount' => $i->offer_amount_cents,
                    'commissionAmount' => $i->commission_cents,
                    'dueDate' => $i->due_date->format('d.m.Y'),
                    'date' => $i->created_at->format('d.m.Y'),
                    'paid' => $i->paid_at !== null,
                    'paidAt' => $i->paid_at?->format('d.m.Y'),
                ]),
            'totals' => [
                'outstanding' => Invoice::whereNull('paid_at')->sum('commission_cents'),
                'paid' => Invoice::whereNotNull('paid_at')->sum('commission_cents'),
            ],
            'filters' => $request->only(['suche', 'status']),
        ]);
    }

    public function markInvoicePaid(Invoice $invoice): RedirectResponse
    {
        if ($invoice->paid_at) {
            return back()->withErrors(['status' => 'Diese Rechnung ist bereits als bezahlt markiert.']);
        }

        $invoice->update(['paid_at' => now()]);

        ActivityLog::record('invoice.paid', Auth::guard('admin')->user(), $invoice);

        return back()->with('success', 'Rechnung als bezahlt markiert.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        abort_unless($invoice->pdf_path, 404);
        $invoice->loadMissing('booking.request');

        return Storage::disk('local')->download($invoice->pdf_path, "{$invoice->referenceNumber()}.pdf");
    }

    public function inspectors(Request $request): Response
    {
        $query = Inspector::withCount(['bookings', 'offers'])
            ->withSum(['invoices as outstanding_commission_cents' => fn ($q) => $q->whereNull('paid_at')], 'commission_cents');

        if ($search = $request->query('suche')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%"));
        }

        // A guest who submitted an offer via an email invite but never
        // registered has no password and hasn't completed onboarding — there
        // is nothing yet for an admin to review, so keep them out of the
        // pending-approval view until they claim the account.
        $excludeUnclaimedGuests = fn ($q) => $q->where(fn ($w) => $w->where('imported_from', '!=', 'guest_offer')->orWhereNotNull('profile_completed_at'));

        if ($request->query('status') === 'pending') {
            $query->where('is_approved', false)->tap($excludeUnclaimedGuests);
        }

        return Inertia::render('admin/Inspectors', [
            'inspectors' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'company' => $i->company_name,
                    'city' => $i->city,
                    'email' => $i->email,
                    'active' => $i->is_active,
                    'approved' => $i->is_approved,
                    'verified' => $i->is_verified,
                    'profileComplete' => $i->profile_completed_at !== null,
                    'jobs' => $i->bookings_count,
                    'offers' => $i->offers_count,
                    'outstandingCommission' => $i->outstanding_commission_cents ?? 0,
                    'imported' => $i->imported_from,
                ]),
            'pendingCount' => Inspector::where('is_approved', false)->tap($excludeUnclaimedGuests)->count(),
            'filters' => $request->only(['suche', 'status']),
        ]);
    }

    public function inspectorDetail(Inspector $inspector): Response
    {
        $inspector->load(['serviceAreas', 'serviceCategory:id,name']);
        $inspector->loadCount(['bookings', 'offers', 'reviews']);

        return Inertia::render('admin/InspectorDetail', [
            'inspector' => [
                'id' => $inspector->id,
                'name' => $inspector->name,
                'company' => $inspector->company_name,
                'email' => $inspector->email,
                'phone' => $inspector->phone,
                'city' => $inspector->city,
                'street' => $inspector->street,
                'plz' => $inspector->plz,
                'category' => $inspector->serviceCategory?->name,
                'birthday' => $inspector->birthday?->format('d.m.Y'),
                'taxId' => $inspector->tax_id,
                'bio' => $inspector->bio,
                'active' => $inspector->is_active,
                'approved' => $inspector->is_approved,
                'verified' => $inspector->is_verified,
                'emailVerified' => $inspector->email_verified_at !== null,
                'profileComplete' => $inspector->profile_completed_at !== null,
                'since' => $inspector->member_since?->format('d.m.Y'),
                'imported' => $inspector->imported_from,
                'jobs' => $inspector->bookings_count,
                'offers' => $inspector->offers_count,
                'reviews' => $inspector->reviews_count,
                'rating' => $inspector->averageRating(),
                'commissions' => [
                    'outstanding' => $inspector->invoices()->whereNull('paid_at')->sum('commission_cents'),
                    'paid' => $inspector->invoices()->whereNotNull('paid_at')->sum('commission_cents'),
                ],
                'areas' => $inspector->serviceAreas->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'city' => $a->city_name,
                    'from' => $a->postal_from,
                    'to' => $a->postal_to,
                ]),
            ],
        ]);
    }

    public function toggleInspector(Inspector $inspector, RequestService $requestService): RedirectResponse
    {
        $inspector->update(['is_active' => ! $inspector->is_active]);

        ActivityLog::record($inspector->is_active ? 'inspector.activated' : 'inspector.deactivated',
            Auth::guard('admin')->user(), $inspector);

        $message = $inspector->is_active ? 'Dienstleister aktiviert.' : 'Dienstleister deaktiviert.';

        if ($inspector->is_active) {
            $rematched = $requestService->rematchUnmatchedRequestsFor($inspector);
            if ($rematched > 0) {
                $message .= " {$rematched} zuvor unbeantwortete Anfrage(n) wurden ihm zugeordnet.";
            }
        }

        return back()->with('success', $message);
    }

    public function approveInspector(Inspector $inspector, RequestService $requestService): RedirectResponse
    {
        if ($inspector->is_approved) {
            return back()->withErrors(['status' => 'Dieser Dienstleister ist bereits freigeschaltet.']);
        }

        if (! $inspector->profile_completed_at) {
            return back()->withErrors(['status' => 'Dieser Dienstleister hat sein Profil noch nicht vervollständigt und kann noch nicht freigeschaltet werden.']);
        }

        $inspector->update(['is_approved' => true]);

        SafeMailer::send(fn () => Mail::to($inspector->email)->queue(new InspectorApprovedMail($inspector)));

        // Service area is normally set up during onboarding now, so only
        // whoever skipped that step via "Später fortsetzen" still needs the
        // "go set this up" nudge — telling someone who already did it to
        // "do it now" would read as a broken notification.
        $hasServiceArea = $inspector->serviceAreas()->exists();
        AppNotification::notify($inspector, 'account_approved',
            'Konto freigeschaltet',
            $hasServiceArea
                ? 'Sie erhalten ab sofort passende Anfragen aus Ihrem Servicegebiet.'
                : 'Legen Sie jetzt Ihr Servicegebiet fest, um passende Anfragen zu erhalten.',
            $hasServiceArea ? '/inspector' : '/inspector/service-areas');

        ActivityLog::record('inspector.approved', Auth::guard('admin')->user(), $inspector);

        $rematched = $requestService->rematchUnmatchedRequestsFor($inspector, onlyRecent: true);

        $message = 'Dienstleister freigeschaltet.';
        if ($rematched > 0) {
            $message .= " {$rematched} zuvor unbeantwortete Anfrage(n) wurden ihm zugeordnet.";
        }

        return back()->with('success', $message);
    }

    public function toggleVerified(Inspector $inspector): RedirectResponse
    {
        $inspector->update(['is_verified' => ! $inspector->is_verified]);

        ActivityLog::record($inspector->is_verified ? 'inspector.verified' : 'inspector.unverified',
            Auth::guard('admin')->user(), $inspector);

        return back()->with('success', $inspector->is_verified ? 'Dienstleister als geprüft markiert.' : 'Prüf-Status entfernt.');
    }

    public function importForm(): Response
    {
        return Inertia::render('admin/InspectorImport');
    }

    public function importPreview(Request $request): Response
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $rows = $this->parseCsv($request->file('file')->getRealPath());
        $report = [];

        foreach ($rows as $i => $row) {
            $errors = [];
            if (empty($row['name'])) {
                $errors[] = 'Name fehlt';
            }
            if (empty($row['email']) || ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ungültige E-Mail';
            } elseif (Inspector::where('email', $row['email'])->exists()) {
                $errors[] = 'E-Mail existiert bereits';
            }
            if (empty($row['city'])) {
                $errors[] = 'Stadt fehlt';
            }

            $report[] = ['row' => $i + 2, 'data' => $row, 'errors' => $errors, 'ok' => empty($errors)];
        }

        // Stash validated rows in the session for the confirm step.
        session(['inspector_import_rows' => collect($report)->where('ok', true)->pluck('data')->values()->all()]);

        return Inertia::render('admin/InspectorImport', ['report' => $report]);
    }

    public function importStore(): RedirectResponse
    {
        $rows = session('inspector_import_rows', []);

        if (empty($rows)) {
            return back()->withErrors(['file' => 'Keine gültigen Zeilen zum Import vorhanden. Bitte laden Sie zuerst eine Datei hoch.']);
        }

        $created = 0;
        foreach ($rows as $row) {
            if (Inspector::where('email', $row['email'])->exists()) {
                continue;
            }

            $password = Str::password(12);
            $inspector = Inspector::create([
                'name' => $row['name'],
                'company_name' => $row['company'] ?? null,
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'city' => $row['city'],
                'password' => $password,
                'is_active' => true,
                'is_verified' => true,
                'member_since' => now(),
                'imported_from' => 'csv-import',
                'email_verified_at' => now(),
            ]);
            $inspector->serviceAreas()->create(['type' => 'city', 'city_name' => $row['city']]);
            if (! empty($row['plz_von']) && ! empty($row['plz_bis'])) {
                $inspector->serviceAreas()->create([
                    'type' => 'postal_range',
                    'postal_from' => (int) $row['plz_von'],
                    'postal_to' => (int) $row['plz_bis'],
                ]);
            }
            Wallet::firstOrCreate(['inspector_id' => $inspector->id]);

            SafeMailer::send(fn () => Mail::to($inspector->email)->queue(new InspectorInvitationMail($inspector, $password)));
            $created++;
        }

        session()->forget('inspector_import_rows');
        ActivityLog::record('inspectors.imported', Auth::guard('admin')->user(), null, ['count' => $created]);

        return redirect()->route('admin.inspectors')->with('success', "{$created} Dienstleister importiert und eingeladen.");
    }

    public function reviews(Request $request): Response
    {
        $query = Review::with(['user:id,name', 'inspector:id,name,company_name', 'booking:id,request_id', 'booking.request:id,request_number']);

        if ($request->query('status') === 'unpublished') {
            $query->where('is_published', false);
        }

        return Inertia::render('admin/Reviews', [
            'reviews' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'rawRating' => $r->raw_rating,
                    'comment' => $r->comment,
                    'published' => $r->is_published,
                    'customer' => $r->user?->name,
                    'inspector' => $r->inspector?->name,
                    'inspectorCompany' => $r->inspector?->company_name,
                    'booking' => $r->booking?->request?->request_number,
                    'date' => $r->created_at->format('d.m.Y H:i'),
                ]),
            'unpublishedCount' => Review::where('is_published', false)->count(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function togglePublished(Review $review): RedirectResponse
    {
        $review->update(['is_published' => ! $review->is_published]);

        ActivityLog::record($review->is_published ? 'review.published' : 'review.unpublished', Auth::guard('admin')->user(), $review);

        return back()->with('success', $review->is_published ? 'Bewertung veröffentlicht.' : 'Bewertung ausgeblendet.');
    }

    public function customers(Request $request): Response
    {
        $query = User::withCount(['requests', 'bookings']);

        if ($search = $request->query('suche')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return Inertia::render('admin/Customers', [
            'customers' => $query->latest()->paginate(25)->withQueryString()
                ->through(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'requests' => $u->requests_count,
                    'bookings' => $u->bookings_count,
                    'since' => $u->created_at->format('d.m.Y'),
                ]),
            'filters' => $request->only(['suche']),
        ]);
    }

    public function customerDetail(User $customer): Response
    {
        $customer->loadCount(['requests', 'bookings']);

        return Inertia::render('admin/CustomerDetail', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'since' => $customer->created_at->format('d.m.Y'),
                'requestsCount' => $customer->requests_count,
                'bookingsCount' => $customer->bookings_count,
                'requests' => $customer->requests()->with('serviceType:id,name')->latest()->take(20)->get()
                    ->map(fn ($r) => [
                        'id' => $r->id,
                        'number' => $r->request_number,
                        'service' => $r->serviceType->name,
                        'vehicle' => $r->vehicle_make.' '.$r->vehicle_model,
                        'status' => $r->status,
                        'date' => $r->created_at->format('d.m.Y'),
                    ]),
                'bookings' => $customer->bookings()->with('inspector:id,name', 'request:id,request_number')->latest()->take(20)->get()
                    ->map(fn ($b) => [
                        'id' => $b->id,
                        'number' => $b->request->request_number,
                        'inspector' => $b->inspector->name,
                        'status' => $b->status,
                        'date' => $b->created_at->format('d.m.Y'),
                    ]),
            ],
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('admin/Services', [
            'categories' => ServiceCategory::with('serviceTypes:id,service_category_id,name,description,image_url,is_active')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'active' => $c->is_active,
                    'types' => $c->serviceTypes->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'description' => $t->description,
                        'image' => $t->image_url,
                        'active' => $t->is_active,
                    ]),
                    'interest' => $c->interestSignals()->count(),
                ]),
        ]);
    }

    public function toggleCategory(ServiceCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        Cache::forget('nav_categories');
        Cache::forget('homepage_data');

        ActivityLog::record($category->is_active ? 'category.activated' : 'category.deactivated',
            Auth::guard('admin')->user(), $category);

        return back()->with('success', $category->is_active ? 'Kategorie „'.$category->name.'“ aktiviert.' : 'Kategorie „'.$category->name.'“ deaktiviert.');
    }

    public function storeServiceType(Request $request, ServiceCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'image', 'max:4096'],
        ]);

        $slug = ServiceType::generateUniqueSlug($data['name']);

        $serviceType = $category->serviceTypes()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'image_url' => $request->hasFile('photo') ? '/storage/'.$request->file('photo')->store('service-photos', 'public') : null,
            'sort_order' => ($category->serviceTypes()->max('sort_order') ?? 0) + 1,
        ]);

        $this->forgetServiceCaches();
        ActivityLog::record('service_type.created', Auth::guard('admin')->user(), $serviceType, ['name' => $data['name']]);

        return back()->with('success', 'Leistung „'.$data['name'].'“ hinzugefügt.');
    }

    public function updateServiceType(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'image', 'max:4096'],
        ]);

        $oldSlug = $serviceType->slug;
        $nameChanged = $data['name'] !== $serviceType->name;

        $serviceType->fill([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        // Keep the URL in sync with the name automatically, and leave a
        // trail so the old URL redirects instead of 404ing — it may already
        // be indexed, linked in a sent email, or bookmarked by a customer.
        if ($nameChanged) {
            $newSlug = ServiceType::generateUniqueSlug($data['name'], $serviceType->id);
            if ($newSlug !== $oldSlug) {
                $serviceType->slug = $newSlug;
            }
        }

        if ($request->hasFile('photo')) {
            $serviceType->image_url = '/storage/'.$request->file('photo')->store('service-photos', 'public');
        }

        $serviceType->save();

        if ($nameChanged && $serviceType->slug !== $oldSlug) {
            ServiceTypeRedirect::updateOrCreate(
                ['old_slug' => $oldSlug],
                ['service_type_id' => $serviceType->id]
            );
            // If this rename happens to reclaim an old slug that used to
            // belong to a DIFFERENT rename chain, drop that stale redirect
            // rather than let it keep pointing away from its new owner.
            ServiceTypeRedirect::where('old_slug', $serviceType->slug)
                ->where('service_type_id', '!=', $serviceType->id)
                ->delete();
        }

        $this->forgetServiceCaches();
        ActivityLog::record('service_type.updated', Auth::guard('admin')->user(), $serviceType, ['name' => $data['name']]);

        return back()->with('success', 'Leistung aktualisiert.');
    }

    public function destroyServiceType(ServiceType $serviceType): RedirectResponse
    {
        if ($serviceType->requests()->exists()) {
            return back()->with('error', 'Diese Leistung kann nicht gelöscht werden, da bereits Anfragen dafür vorliegen. Deaktivieren Sie sie stattdessen.');
        }

        $name = $serviceType->name;
        $serviceType->delete();

        $this->forgetServiceCaches();
        ActivityLog::record('service_type.deleted', Auth::guard('admin')->user(), null, ['name' => $name]);

        return back()->with('success', 'Leistung „'.$name.'“ entfernt.');
    }

    private function forgetServiceCaches(): void
    {
        Cache::forget('nav_categories');
        Cache::forget('homepage_data');
    }

    public function serviceTypeFields(ServiceType $serviceType): Response
    {
        return Inertia::render('admin/ServiceTypeFields', [
            'serviceType' => ['id' => $serviceType->id, 'name' => $serviceType->name],
            'fields' => $serviceType->fields()->orderBy('sort_order')->get()->map(fn ($f) => [
                'id' => $f->id, 'label' => $f->label, 'type' => $f->type,
                'options' => $f->options, 'isRequired' => $f->is_required,
            ]),
        ]);
    }

    public function storeServiceTypeField(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:text,number,date,select,textarea,file'],
            'options' => ['required_if:type,select', 'nullable', 'string'],
            'is_required' => ['boolean'],
        ]);

        $key = Str::slug($data['label'], '_');
        $suffix = 2;
        while ($serviceType->fields()->where('key', $key)->exists()) {
            $key = Str::slug($data['label'], '_').'_'.$suffix++;
        }

        $options = $data['type'] === 'select'
            ? collect(explode(',', $data['options']))->map(fn ($o) => trim($o))->filter()->values()->all()
            : null;

        $serviceType->fields()->create([
            'label' => $data['label'],
            'key' => $key,
            'type' => $data['type'],
            'options' => $options,
            'is_required' => $data['is_required'] ?? false,
            'sort_order' => ($serviceType->fields()->max('sort_order') ?? 0) + 1,
        ]);

        ActivityLog::record('service_type_field.created', Auth::guard('admin')->user(), $serviceType, ['label' => $data['label']]);

        return back()->with('success', 'Feld hinzugefügt.');
    }

    public function destroyServiceTypeField(ServiceType $serviceType, ServiceTypeField $field): RedirectResponse
    {
        $field->delete();

        ActivityLog::record('service_type_field.deleted', Auth::guard('admin')->user(), $serviceType, ['label' => $field->label]);

        return back()->with('success', 'Feld entfernt.');
    }

    public function settings(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => [
                'commission_percent' => Setting::get('commission_percent', 10),
                'stripe_configured' => (bool) (config('cashier.secret') ?: env('STRIPE_SECRET')),
                'logoUrl' => Setting::logoUrl(),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:50'],
        ]);

        Setting::set('commission_percent', $data['commission_percent']);
        ActivityLog::record('settings.updated', Auth::guard('admin')->user(), null, $data);

        return back()->with('success', 'Einstellungen gespeichert.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'logo' => ['required', 'file', 'mimes:png,svg,webp', 'max:2048'],
        ]);

        $path = $data['logo']->store('branding', 'public');

        $previous = Setting::get('logo_path');
        Setting::set('logo_path', $path);

        if ($previous && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        ActivityLog::record('settings.logo_uploaded', Auth::guard('admin')->user());

        return back()->with('success', 'Logo gespeichert.');
    }

    public function removeLogo(): RedirectResponse
    {
        $previous = Setting::get('logo_path');

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        Setting::set('logo_path', '');
        ActivityLog::record('settings.logo_removed', Auth::guard('admin')->user());

        return back()->with('success', 'Logo auf Standard zurückgesetzt.');
    }

    public function logs(): Response
    {
        return Inertia::render('admin/ActivityLogs', [
            'logs' => ActivityLog::latest()->paginate(30)
                ->through(fn ($l) => [
                    'id' => $l->id,
                    'action' => $l->action,
                    'actor' => $l->actor_type ? class_basename($l->actor_type).' #'.$l->actor_id : 'System',
                    'subject' => $l->subject_type ? class_basename($l->subject_type).' #'.$l->subject_id : null,
                    'meta' => $l->meta,
                    'ip' => $l->ip,
                    'date' => $l->created_at->format('d.m.Y H:i:s'),
                ]),
        ]);
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 2000, ';');
            if ($header && count($header) === 1 && str_contains($header[0], ',')) {
                rewind($handle);
                $header = fgetcsv($handle, 2000, ',');
                $delimiter = ',';
            } else {
                $delimiter = ';';
            }
            $header = array_map(fn ($h) => Str::of($h)->lower()->trim()->replace(['-', ' '], '_')->toString(), $header ?: []);

            while (($line = fgetcsv($handle, 2000, $delimiter)) !== false) {
                if (count(array_filter($line)) === 0) {
                    continue;
                }
                $rows[] = array_combine($header, array_pad(array_map('trim', $line), count($header), null));
            }
            fclose($handle);
        }

        return $rows;
    }
}
