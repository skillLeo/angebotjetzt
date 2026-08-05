<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAreaController extends Controller
{
    public function dashboard(): Response
    {
        $user = Auth::user();

        return Inertia::render('customer/Dashboard', [
            'stats' => [
                'requests' => $user->requests()->count(),
                'openOffers' => $user->requests()->whereIn('status', ['open', 'offers_received'])->withCount('offers')->get()->sum('offers_count'),
                'bookings' => $user->bookings()->count(),
            ],
            'recentRequests' => $user->requests()
                ->with('serviceType:id,name')
                ->withCount(['offers' => fn ($q) => $q->where('status', 'open')])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($r) => $this->requestSummary($r)),
        ]);
    }

    public function requests(): Response
    {
        return Inertia::render('customer/Requests', [
            'requests' => Auth::user()->requests()
                ->with('serviceType:id,name')
                ->withCount(['offers' => fn ($q) => $q->where('status', 'open')])
                ->latest()
                ->paginate(10)
                ->through(fn ($r) => $this->requestSummary($r)),
        ]);
    }

    public function requestDetail(ServiceRequest $serviceRequest): Response
    {
        abort_unless($serviceRequest->user_id === Auth::id(), 403);

        $serviceRequest->load(['serviceType:id,name', 'photos', 'offers' => fn ($q) => $q->with('inspector')->latest()]);

        return Inertia::render('customer/RequestDetail', [
            'request' => [
                ...$this->requestSummary($serviceRequest),
                'vehicle' => [
                    'make' => $serviceRequest->vehicle_make,
                    'model' => $serviceRequest->vehicle_model,
                    'firstRegistration' => $serviceRequest->first_registration,
                    'mileage' => $serviceRequest->mileage,
                    'fuel' => $serviceRequest->fuel_type,
                    'transmission' => $serviceRequest->transmission,
                ],
                'plz' => $serviceRequest->plz,
                'notes' => $serviceRequest->notes,
                'matched' => $serviceRequest->matched_count,
                'photos' => $serviceRequest->photos->map(fn ($p) => '/storage/'.$p->path),
            ],
            'offers' => $serviceRequest->offers->map(fn ($o) => $this->offerSummary($o)),
        ]);
    }

    public function compareOffers(ServiceRequest $serviceRequest): Response
    {
        abort_unless($serviceRequest->user_id === Auth::id(), 403);

        $serviceRequest->load(['serviceType:id,name', 'offers' => fn ($q) => $q->with(['inspector' => fn ($i) => $i->withCount(['reviews' => fn ($r) => $r->where('is_published', true)])->withAvg(['reviews' => fn ($r) => $r->where('is_published', true)], 'rating')])->orderBy('price_cents')]);

        return Inertia::render('customer/CompareOffers', [
            'request' => $this->requestSummary($serviceRequest),
            'offers' => $serviceRequest->offers->map(fn ($o) => $this->offerSummary($o, detailed: true)),
        ]);
    }

    public function bookings(): Response
    {
        return Inertia::render('customer/Bookings', [
            'bookings' => Auth::user()->bookings()
                ->with(['inspector:id,name,company_name,city', 'offer', 'request.serviceType:id,name', 'review:id,booking_id'])
                ->latest()
                ->paginate(10)
                ->through(fn ($b) => $this->bookingSummary($b)),
        ]);
    }

    public function bookingDetail(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $booking->load(['inspector', 'offer', 'request.serviceType:id,name', 'review']);

        return Inertia::render('customer/BookingDetail', [
            'booking' => [
                ...$this->bookingSummary($booking),
                'inspectorPhone' => $booking->inspector->phone,
                'inspectorEmail' => $booking->inspector->email,
                'message' => $booking->offer->message,
                'hasReview' => $booking->review !== null,
            ],
        ]);
    }

    public function storeReview(Request $request, Booking $booking, \App\Services\WalletService $walletService): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless(in_array($booking->status, ['completed_by_inspector', 'confirmed'], true), 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ]);

        Review::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => Auth::id(),
                'inspector_id' => $booking->inspector_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        // A customer rating is their confirmation the job was done — this is
        // what finalizes the booking. Under the direct-agreement model there's
        // no platform-held balance to release (the customer paid the provider
        // directly); that only still applies to legacy bookings carrying a
        // Payment from the old Stripe/wallet flow.
        if ($booking->status === 'completed_by_inspector') {
            $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            $booking->request->update(['status' => 'completed']);

            if ($booking->payment) {
                $walletService->releasePending($booking);

                \App\Models\AppNotification::notify($booking->inspector, 'balance_released',
                    'Guthaben freigegeben',
                    "Ihr Anteil für Auftrag {$booking->booking_number} ist jetzt verfügbar.",
                    '/inspector/wallet');
            }

            \App\Models\ActivityLog::record('booking.confirmed', Auth::user(), $booking);
        }

        return back()->with('success', 'Vielen Dank für Ihre Bewertung!');
    }

    public function payments(): Response
    {
        return Inertia::render('customer/Payments', [
            'payments' => \App\Models\Payment::whereHas('booking', fn ($q) => $q->where('user_id', Auth::id()))
                ->with('booking.request.serviceType:id,name')
                ->latest()
                ->paginate(15)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'booking' => $p->booking->booking_number,
                    'service' => $p->booking->request->serviceType->name,
                    'total' => $p->total_cents,
                    'status' => $p->status,
                    'date' => $p->paid_at?->format('d.m.Y H:i'),
                ]),
        ]);
    }

    private function requestSummary(ServiceRequest $r): array
    {
        return [
            'id' => $r->id,
            'number' => $r->request_number,
            'service' => $r->serviceType->name,
            'vehicle' => $r->vehicle_make.' '.$r->vehicle_model,
            'ort' => $r->ort,
            'status' => $r->status,
            'offers' => $r->offers_count ?? $r->offers->count(),
            'date' => $r->created_at->format('d.m.Y'),
        ];
    }

    private function offerSummary($o, bool $detailed = false): array
    {
        $inspector = $o->inspector;

        return [
            'id' => $o->id,
            'price' => $o->price_cents,
            'message' => $o->message,
            'estimatedDate' => $o->estimated_date?->format('d.m.Y'),
            'status' => $o->status,
            'editedAt' => $o->edited_at?->format('d.m.Y H:i'),
            'inspector' => [
                'name' => $inspector->name,
                'company' => $inspector->company_name,
                'city' => $inspector->city,
                'verified' => $inspector->is_verified,
                'experience' => $inspector->years_experience,
                'rating' => $detailed && $inspector->reviews_avg_rating ? round((float) $inspector->reviews_avg_rating, 1) : null,
                'reviews' => $detailed ? ($inspector->reviews_count ?? 0) : null,
            ],
        ];
    }

    private function bookingSummary(Booking $b): array
    {
        return [
            'id' => $b->id,
            'number' => $b->booking_number,
            'service' => $b->request->serviceType->name,
            'vehicle' => $b->request->vehicle_make.' '.$b->request->vehicle_model,
            'inspector' => $b->inspector->name,
            'inspectorCompany' => $b->inspector->company_name,
            'city' => $b->inspector->city,
            'price' => $b->offer->price_cents,
            'status' => $b->status,
            'date' => $b->created_at->format('d.m.Y'),
            'hasReview' => isset($b->review),
        ];
    }
}
