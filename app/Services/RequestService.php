<?php

namespace App\Services;

use App\Mail\NewRequestNotificationMail;
use App\Mail\RequestConfirmationMail;
use App\Mail\RequestMatchedMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Inspector;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\SafeMailer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RequestService
{
    public function __construct(private MatchingService $matching)
    {
    }

    /**
     * Called when an inspector is newly approved, reactivated, or adds/changes
     * a service area. Matching only ever runs once at submission time, so a
     * request this inspector's area now covers but didn't back then would
     * otherwise never reach them — whether it found zero inspectors ("unmatched")
     * or already found others ("open"/"offers_received"). This re-checks every
     * still-live request the inspector isn't already matched to.
     *
     * @return int number of requests newly matched to this inspector
     */
    public function rematchUnmatchedRequestsFor(Inspector $inspector): int
    {
        if (! $inspector->is_active || ! $inspector->is_approved) {
            return 0;
        }

        $candidates = ServiceRequest::whereIn('status', ['unmatched', 'open', 'offers_received'])
            ->where('expires_at', '>', now())
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
     * Persist a submitted request, match inspectors, and queue all notifications.
     */
    public function submit(?User $user, array $data, array $photoPaths = []): ServiceRequest
    {
        $request = DB::transaction(function () use ($user, $data, $photoPaths) {
            $request = ServiceRequest::create([
                ...$data,
                'user_id' => $user?->id,
                'request_number' => ServiceRequest::nextRequestNumber(),
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
