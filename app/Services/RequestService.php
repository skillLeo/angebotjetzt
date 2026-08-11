<?php

namespace App\Services;

use App\Mail\NewRequestNotificationMail;
use App\Mail\RequestConfirmationMail;
use App\Mail\RequestMatchedMail;
use App\Mail\ReviewRequestMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\SafeMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RequestService
{
    public function __construct(private MatchingService $matching) {}

    /**
     * Called when an inspector is newly approved, reactivated, or adds/changes
     * a service area. Matching only ever runs once at submission time, so a
     * request this inspector's area now covers but didn't back then would
     * otherwise never reach them — whether it found zero inspectors ("unmatched")
     * or already found others ("open"/"offers_received"). This re-checks every
     * still-live request the inspector isn't already matched to.
     *
     * @param  bool  $onlyRecent  When true (used for brand-new approvals), only
     *                            requests submitted in the last 48 hours are
     *                            considered — a freshly-approved provider
     *                            shouldn't be handed a backlog of old requests.
     * @return int number of requests newly matched to this inspector
     */
    public function rematchUnmatchedRequestsFor(Inspector $inspector, bool $onlyRecent = false): int
    {
        if (! $inspector->is_active || ! $inspector->is_approved) {
            return 0;
        }

        $candidates = ServiceRequest::whereIn('status', ['unmatched', 'open', 'offers_received'])
            ->where('expires_at', '>', now())
            ->when($onlyRecent, fn ($q) => $q->where('created_at', '>=', now()->subHours(48)))
            ->whereDoesntHave('matches', fn ($q) => $q->where('inspector_id', $inspector->id))
            ->get();

        $matchedCount = 0;

        foreach ($candidates as $request) {
            $covers = $this->matching->match($request)->contains('id', $inspector->id);

            if (! $covers) {
                continue;
            }

            $wasUnmatched = $request->status === 'unmatched';

            DB::transaction(function () use ($request, $inspector) {
                $request->matches()->create([
                    'inspector_id' => $inspector->id,
                    'notified_at' => now(),
                ]);

                $request->update([
                    'matched_count' => $request->matched_count + 1,
                    'status' => $request->status === 'unmatched' ? 'open' : $request->status,
                ]);
            });

            $this->notifyInspectorOfMatch($inspector, $request);

            if ($wasUnmatched) {
                $this->notifyCustomerOfMatch($request);
            }

            $matchedCount++;
        }

        return $matchedCount;
    }

    private function notifyCustomerOfMatch(ServiceRequest $request): void
    {
        if ($request->user) {
            AppNotification::notify(
                $request->user,
                'request_matched',
                "Anbieter gefunden für Anfrage {$request->request_number}",
                "{$request->serviceType->name} · {$request->ort}",
                "/account/requests/{$request->id}"
            );
        }

        SafeMailer::send(fn () => Mail::to($request->contact_email)->queue(new RequestMatchedMail($request)));
    }

    /**
     * Also called directly by AdminController::inviteProvider() when an admin
     * manually matches an already-registered inspector to a request.
     */
    public function notifyInspectorOfMatch(Inspector $inspector, ServiceRequest $request): void
    {
        AppNotification::notify(
            $inspector,
            'new_request',
            "Neue Anfrage in {$request->ort}",
            "{$request->serviceType->name} · {$request->vehicle_make} {$request->vehicle_model}",
            "/inspector/requests/{$request->id}"
        );

        $signedLink = URL::temporarySignedRoute(
            'inspector.requests.signed',
            now()->addDays(14),
            ['request' => $request->id, 'inspector' => $inspector->id]
        );
        SafeMailer::send(fn () => Mail::to($inspector->email)->queue(new NewRequestNotificationMail($request, $inspector, $signedLink)));
    }

    /**
     * Send the post-completion review survey — called from both places a
     * booking can be marked complete (admin confirmation and the customer's
     * own completion confirmation) so neither path can miss it. Only the
     * emailed 1-10 survey ever creates a Review now, so the guard below is
     * just protection against sending this twice for the same booking.
     */
    public function sendReviewRequest(Booking $booking): void
    {
        if ($booking->review) {
            return;
        }

        $booking->loadMissing(['user', 'request']);

        if (! $booking->user) {
            return;
        }

        $signedLink = URL::temporarySignedRoute(
            'reviews.survey.show',
            now()->addDays(30),
            ['booking' => $booking->id]
        );

        SafeMailer::send(fn () => Mail::to($booking->customerEmail())->queue(new ReviewRequestMail($booking, $signedLink)));
    }

    /**
     * Record when a request's very first offer arrived, so the scheduled
     * `offers:send-reminders` command (see app/Console/Commands) knows when
     * its 24h/48h reminder windows open. Call this only at the moment a
     * request's first offer arrives (its status is still "open" right
     * before that offer flips it to "offers_received") so this is set once
     * per request, not once per offer.
     *
     * This deliberately does NOT use a delayed queued job: this app's queue
     * connection is "sync" in production, which runs jobs immediately and
     * silently ignores ->delay() — a delayed dispatch here would send both
     * reminders instantly instead of 24h/48h later. A polled command driven
     * by Laravel's scheduler works the same regardless of queue driver.
     */
    public function recordFirstOffer(ServiceRequest $request): void
    {
        $request->update(['first_offer_at' => now()]);
    }

    /**
     * Persist a submitted request, match inspectors, and queue all notifications.
     */
    public function submit(?User $user, array $data, array $photoPaths = []): ServiceRequest
    {
        $request = DB::transaction(function () use ($user, $data, $photoPaths) {
            $request = ServiceRequest::createWithUniqueNumber([
                ...$data,
                'user_id' => $user?->id,
                'status' => 'open',
                'expires_at' => now()->addDays(14),
            ]);

            foreach ($photoPaths as $photo) {
                $request->photos()->create($photo);
            }

            return $request;
        });

        $inspectors = $this->matching->match($request);

        DB::transaction(function () use ($request, $inspectors) {
            foreach ($inspectors as $inspector) {
                $request->matches()->create([
                    'inspector_id' => $inspector->id,
                    'notified_at' => now(),
                ]);
            }

            $request->update([
                'matched_count' => $inspectors->count(),
                'status' => $inspectors->isEmpty() ? 'unmatched' : 'open',
            ]);
        });

        foreach ($inspectors as $inspector) {
            $this->notifyInspectorOfMatch($inspector, $request);
        }

        SafeMailer::send(fn () => Mail::to($request->contact_email)->queue(new RequestConfirmationMail($request)));

        ActivityLog::record('request.submitted', $user, $request, [
            'matched' => $inspectors->count(),
        ]);

        return $request;
    }
}
