<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\ServiceRequest;
use App\Services\RequestService;
use App\Services\WalletService;
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
        $labels = $serviceRequest->offerLabels();

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
            'offers' => $serviceRequest->offers->map(fn ($o) => $this->offerSummary($o, $labels[$o->id])),
        ]);
    }

    public function compareOffers(ServiceRequest $serviceRequest): Response
    {
        abort_unless($serviceRequest->user_id === Auth::id(), 403);

        // Not filtered by is_published: that flag only gates whether a
        // review's text is shown publicly as a testimonial, not whether the
        // rating counts — see Inspector::averageRating().
        $serviceRequest->load(['serviceType:id,name', 'offers' => fn ($q) => $q->with(['inspector' => fn ($i) => $i->withCount('reviews')->withAvg('reviews', 'rating')])->orderBy('price_cents')]);
        $labels = $serviceRequest->offerLabels();

        return Inertia::render('customer/CompareOffers', [
            'request' => $this->requestSummary($serviceRequest),
            'offers' => $serviceRequest->offers->map(fn ($o) => $this->offerSummary($o, $labels[$o->id], detailed: true)),
        ]);
    }

    public function bookings(): Response
    {
        return Inertia::render('customer/Bookings', [
            'bookings' => Auth::user()->bookings()
                ->with(['inspector:id,name,company_name,city', 'offer', 'request.serviceType:id,name'])
                ->latest()
                ->paginate(10)
                ->through(fn ($b) => $this->bookingSummary($b)),
        ]);
    }

    public function bookingDetail(Booking $booking): Response
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        $booking->load(['inspector', 'offer', 'request.serviceType:id,name']);

        return Inertia::render('customer/BookingDetail', [
            'booking' => [
                ...$this->bookingSummary($booking),
                'inspectorPhone' => $booking->inspector->phone,
                'inspectorEmail' => $booking->inspector->email,
                'message' => $booking->offer->message,
            ],
        ]);
    }

    /**
     * The customer's own way of confirming a job is done — no rating is
     * collected here anymore. Every rating now comes exclusively through
     * the emailed 1-10 survey (sendReviewRequest() below), regardless of
     * whether admin or the customer is the one who confirms completion, so
     * there's exactly one review path instead of two.
     */
    public function confirmCompletion(Booking $booking, WalletService $walletService, RequestService $requestService): RedirectResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        abort_unless($booking->status === 'completed_by_inspector', 403);

        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $booking->request->update(['status' => 'completed']);

        // Under the direct-agreement model there's no platform-held balance
        // to release (the customer paid the provider directly); that only
        // still applies to legacy bookings carrying a Payment from the old
        // Stripe/wallet flow.
        if ($booking->payment) {
            $walletService->releasePending($booking);

            AppNotification::notify($booking->inspector, 'balance_released',
                'Guthaben freigegeben',
                "Ihr Anteil für Auftrag {$booking->request->request_number} ist jetzt verfügbar.",
                '/inspector');
        }

        ActivityLog::record('booking.confirmed', Auth::user(), $booking);

        $requestService->sendReviewRequest($booking);

        return back()->with('success', 'Auftrag bestätigt! Wir haben Ihnen eine E-Mail zur Bewertung Ihrer Erfahrung gesendet.');
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

    private function offerSummary($o, string $label, bool $detailed = false): array
    {
        $inspector = $o->inspector;
        $revealed = $o->status !== 'open';

        return [
            'id' => $o->id,
            'price' => $o->price_cents,
            'message' => $o->message,
            'estimatedDate' => $o->estimated_date?->format('d.m.Y'),
            'status' => $o->status,
            'editedAt' => $o->edited_at?->format('d.m.Y H:i'),
            'inspector' => [
                'label' => $label,
                'name' => $revealed ? $inspector->name : null,
                'company' => $revealed ? $inspector->company_name : null,
                'city' => $inspector->city,
                'bio' => $inspector->bio,
                'qualifications' => $inspector->qualifications,
                'verified' => $inspector->is_verified,
                'experience' => $inspector->years_experience,
                'rating' => $detailed && $inspector->reviews_avg_rating ? round((float) $inspector->reviews_avg_rating, 1) : null,
                'reviews' => $detailed ? ($inspector->reviews_count ?? 0) : null,
                'pendingVerification' => ! $inspector->is_approved,
                'avatar' => $inspector->avatar_key ? ['key' => $inspector->avatar_key, ...config('avatars.'.$inspector->avatar_key, [])] : null,
            ],
        ];
    }

    private function bookingSummary(Booking $b): array
    {
        return [
            'id' => $b->id,
            // The customer's own request number, not the internal
            // booking_number — that reference must never change or take
            // over from the AJ-... number the customer already knows.
            'number' => $b->request->request_number,
            'service' => $b->request->serviceType->name,
            'vehicle' => $b->request->vehicle_make.' '.$b->request->vehicle_model,
            'inspector' => $b->inspector->name,
            'inspectorCompany' => $b->inspector->company_name,
            'city' => $b->inspector->city,
            'price' => $b->offer->price_cents,
            'status' => $b->status,
            'date' => $b->created_at->format('d.m.Y'),
        ];
    }
}
