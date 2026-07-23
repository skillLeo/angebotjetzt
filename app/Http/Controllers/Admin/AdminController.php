<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PayoutPaidMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
                'pendingPayouts' => PayoutRequest::where('status', 'pending')->count(),
                'pendingPayoutAmount' => PayoutRequest::where('status', 'pending')->sum('amount_cents'),
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
                    'customer' => $r->user->name,
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
                'customer' => $serviceRequest->user->only(['name', 'email', 'phone']),
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
            ],
        ]);
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

    public function bookings(): Response
    {
        return Inertia::render('admin/Bookings', [
            'bookings' => Booking::with(['request.serviceType:id,name', 'user:id,name', 'inspector:id,name', 'offer', 'payment'])
                ->latest()->paginate(20)
                ->through(fn ($b) => [
                    'id' => $b->id,
                    'number' => $b->booking_number,
                    'customer' => $b->user->name,
                    'inspector' => $b->inspector->name,
                    'service' => $b->request->serviceType->name,
                    'total' => $b->offer->price_cents,
                    'commission' => $b->payment?->commission_cents,
                    'inspectorShare' => $b->payment?->inspector_cents,
                    'status' => $b->status,
                    'date' => $b->created_at->format('d.m.Y'),
                ]),
        ]);
    }

    public function bookingDetail(Booking $booking): Response
    {
        $booking->load(['request.serviceType:id,name', 'user:id,name,email,phone', 'inspector', 'offer', 'payment', 'review']);

        return Inertia::render('admin/BookingDetail', [
            'booking' => [
                'id' => $booking->id,
                'number' => $booking->booking_number,
                'status' => $booking->status,
                'customer' => $booking->user->only(['name', 'email', 'phone']),
                'inspector' => $booking->inspector->only(['id', 'name', 'company_name', 'city', 'email']),
                'service' => $booking->request->serviceType->name,
                'vehicle' => $booking->request->vehicle_make.' '.$booking->request->vehicle_model,
                'requestId' => $booking->request_id,
                'requestNumber' => $booking->request->request_number,
                'moneyTrail' => $booking->payment ? [
                    'total' => $booking->payment->total_cents,
                    'commission' => $booking->payment->commission_cents,
                    'inspectorShare' => $booking->payment->inspector_cents,
                    'stripeRef' => $booking->payment->stripe_payment_intent_id,
                    'paidAt' => $booking->payment->paid_at?->format('d.m.Y H:i'),
                ] : null,
                'completedAt' => $booking->completed_at?->format('d.m.Y H:i'),
                'confirmedAt' => $booking->confirmed_at?->format('d.m.Y H:i'),
                'review' => $booking->review?->only(['rating', 'comment']),
            ],
        ]);
    }

    /**
     * Admin confirms a completed job — this releases the inspector's pending balance.
     */
    public function confirmBooking(Booking $booking, WalletService $walletService): RedirectResponse
    {
        if ($booking->status !== 'completed_by_inspector') {
            return back()->withErrors(['status' => 'Nur vom Gutachter abgeschlossene Aufträge können bestätigt werden.']);
        }

        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $booking->request->update(['status' => 'completed']);
        $walletService->releasePending($booking);

        AppNotification::notify($booking->inspector, 'balance_released',
            'Guthaben freigegeben',
            "Ihr Anteil für Auftrag {$booking->booking_number} ist jetzt verfügbar.",
            '/inspector/wallet');

        ActivityLog::record('booking.confirmed', Auth::guard('admin')->user(), $booking);

        return back()->with('success', 'Auftrag bestätigt — Guthaben wurde freigegeben.');
    }

    public function payments(): Response
    {
        return Inertia::render('admin/Payments', [
            'payments' => Payment::with('booking:id,booking_number,user_id,inspector_id', 'booking.user:id,name', 'booking.inspector:id,name')
                ->latest()->paginate(20)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'booking' => $p->booking->booking_number,
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
        ]);
    }

    public function commissions(Request $request): Response
    {
        $from = $request->query('von') ? \Carbon\Carbon::parse($request->query('von')) : now()->subMonths(3);
        $to = $request->query('bis') ? \Carbon\Carbon::parse($request->query('bis'))->endOfDay() : now();

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

    public function inspectors(Request $request): Response
    {
        $query = Inspector::withCount(['bookings', 'offers'])->with('wallet');

        if ($search = $request->query('suche')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%"));
        }

        return Inertia::render('admin/Inspectors', [
            'inspectors' => $query->orderBy('name')->paginate(20)->withQueryString()
                ->through(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'company' => $i->company_name,
                    'city' => $i->city,
                    'email' => $i->email,
                    'active' => $i->is_active,
                    'verified' => $i->is_verified,
                    'jobs' => $i->bookings_count,
                    'offers' => $i->offers_count,
                    'balance' => $i->wallet?->available_cents ?? 0,
                    'imported' => $i->imported_from,
                ]),
            'filters' => $request->only(['suche']),
        ]);
    }

    public function inspectorDetail(Inspector $inspector): Response
    {
        $inspector->load(['serviceAreas', 'wallet']);
        $inspector->loadCount(['bookings', 'offers', 'reviews']);

        return Inertia::render('admin/InspectorDetail', [
            'inspector' => [
                'id' => $inspector->id,
                'name' => $inspector->name,
                'company' => $inspector->company_name,
                'email' => $inspector->email,
                'phone' => $inspector->phone,
                'city' => $inspector->city,
                'bio' => $inspector->bio,
                'active' => $inspector->is_active,
                'verified' => $inspector->is_verified,
                'since' => $inspector->member_since?->format('d.m.Y'),
                'imported' => $inspector->imported_from,
                'jobs' => $inspector->bookings_count,
                'offers' => $inspector->offers_count,
                'reviews' => $inspector->reviews_count,
                'rating' => $inspector->averageRating(),
                'wallet' => [
                    'available' => $inspector->wallet?->available_cents ?? 0,
                    'pending' => $inspector->wallet?->pending_cents ?? 0,
                    'lifetime' => $inspector->wallet?->lifetime_cents ?? 0,
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

    public function toggleInspector(Inspector $inspector): RedirectResponse
    {
        $inspector->update(['is_active' => ! $inspector->is_active]);

        ActivityLog::record($inspector->is_active ? 'inspector.activated' : 'inspector.deactivated',
            Auth::guard('admin')->user(), $inspector);

        return back()->with('success', $inspector->is_active ? 'Gutachter aktiviert.' : 'Gutachter deaktiviert.');
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

            Mail::to($inspector->email)->queue(new \App\Mail\InspectorInvitationMail($inspector, $password));
            $created++;
        }

        session()->forget('inspector_import_rows');
        ActivityLog::record('inspectors.imported', Auth::guard('admin')->user(), null, ['count' => $created]);

        return redirect()->route('admin.inspectors')->with('success', "{$created} Gutachter importiert und eingeladen.");
    }

    public function wallets(): Response
    {
        return Inertia::render('admin/Wallets', [
            'wallets' => Wallet::with('inspector:id,name,company_name,city')
                ->orderByDesc('available_cents')
                ->paginate(25)
                ->through(fn ($w) => [
                    'inspectorId' => $w->inspector_id,
                    'name' => $w->inspector->name,
                    'company' => $w->inspector->company_name,
                    'city' => $w->inspector->city,
                    'available' => $w->available_cents,
                    'pending' => $w->pending_cents,
                    'lifetime' => $w->lifetime_cents,
                ]),
            'totals' => [
                'available' => Wallet::sum('available_cents'),
                'pending' => Wallet::sum('pending_cents'),
            ],
        ]);
    }

    public function payouts(): Response
    {
        return Inertia::render('admin/Payouts', [
            'payouts' => PayoutRequest::with(['inspector.wallet', 'paidByAdmin:id,name'])
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->latest('requested_at')
                ->paginate(20)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'inspector' => $p->inspector->name,
                    'inspectorId' => $p->inspector_id,
                    'company' => $p->inspector->company_name,
                    'amount' => $p->amount_cents,
                    'iban' => $p->iban,
                    'bic' => $p->bic,
                    'accountHolder' => $p->account_holder,
                    'balance' => $p->inspector->wallet?->available_cents ?? 0,
                    'status' => $p->status,
                    'requested' => $p->requested_at->format('d.m.Y H:i'),
                    'paid' => $p->paid_at?->format('d.m.Y H:i'),
                    'paidBy' => $p->paidByAdmin?->name,
                ]),
        ]);
    }

    public function markPayoutPaid(PayoutRequest $payout, WalletService $walletService): RedirectResponse
    {
        if ($payout->status !== 'pending') {
            return back()->withErrors(['status' => 'Diese Auszahlung wurde bereits bearbeitet.']);
        }

        $walletService->debitPayout($payout);

        $payout->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by_admin_id' => Auth::guard('admin')->id(),
        ]);

        AppNotification::notify($payout->inspector, 'payout_paid',
            'Auszahlung überwiesen',
            'Ihre Auszahlung über '.number_format($payout->amount_cents / 100, 2, ',', '.').' € wurde überwiesen.',
            '/inspector/wallet');

        Mail::to($payout->inspector->email)->queue(new PayoutPaidMail($payout));

        ActivityLog::record('payout.paid', Auth::guard('admin')->user(), $payout, ['amount_cents' => $payout->amount_cents]);

        return back()->with('success', 'Auszahlung als bezahlt markiert und Wallet belastet.');
    }

    public function customers(Request $request): Response
    {
        $query = \App\Models\User::withCount(['requests', 'bookings']);

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

    public function services(): Response
    {
        return Inertia::render('admin/Services', [
            'categories' => ServiceCategory::with('serviceTypes:id,service_category_id,name,is_active')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'active' => $c->is_active,
                    'types' => $c->serviceTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'active' => $t->is_active]),
                    'interest' => $c->interestSignals()->count(),
                ]),
        ]);
    }

    public function toggleCategory(ServiceCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        \Illuminate\Support\Facades\Cache::forget('nav_categories');
        \Illuminate\Support\Facades\Cache::forget('homepage_data');

        ActivityLog::record($category->is_active ? 'category.activated' : 'category.deactivated',
            Auth::guard('admin')->user(), $category);

        return back()->with('success', $category->is_active ? 'Kategorie „'.$category->name.'“ aktiviert.' : 'Kategorie „'.$category->name.'“ deaktiviert.');
    }

    public function settings(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => [
                'commission_percent' => Setting::get('commission_percent', 10),
                'stripe_configured' => (bool) (config('cashier.secret') ?: env('STRIPE_SECRET')),
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
