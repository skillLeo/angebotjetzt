<?php

namespace App\Services;

use App\Mail\NewRequestNotificationMail;
use App\Mail\RequestConfirmationMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RequestService
{
    public function __construct(private MatchingService $matching)
    {
    }

    /**
     * Persist a submitted request, match inspectors, and queue all notifications.
     */
    public function submit(User $user, array $data, array $photoPaths = []): ServiceRequest
    {
        $request = DB::transaction(function () use ($user, $data, $photoPaths) {
            $request = ServiceRequest::create([
                ...$data,
                'user_id' => $user->id,
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

                AppNotification::notify(
                    $inspector,
                    'new_request',
                    "Neue Anfrage in {$request->ort}",
                    "{$request->serviceType->name} · {$request->vehicle_make} {$request->vehicle_model}",
                    "/inspector/requests/{$request->id}"
                );
            }

            $request->update([
                'matched_count' => $inspectors->count(),
                'status' => $inspectors->isEmpty() ? 'unmatched' : 'open',
            ]);
        });

        foreach ($inspectors as $inspector) {
            $signedLink = URL::temporarySignedRoute(
                'inspector.requests.signed',
                now()->addDays(14),
                ['request' => $request->id, 'inspector' => $inspector->id]
            );
            Mail::to($inspector->email)->queue(new NewRequestNotificationMail($request, $inspector, $signedLink));
        }

        Mail::to($request->contact_email)->queue(new RequestConfirmationMail($request));

        ActivityLog::record('request.submitted', $user, $request, [
            'matched' => $inspectors->count(),
        ]);

        return $request;
    }
}
