<?php

namespace App\Services;

use App\Mail\ProviderPlatformInviteMail;
use App\Models\Admin;
use App\Models\Inspector;
use App\Models\ProviderInvitation;
use App\Support\SafeMailer;
use Illuminate\Support\Facades\Mail;

class ProviderInviteService
{
    /**
     * How many invitations go out per send. The mail transport here is
     * synchronous, so an unbounded list would hold a request open for minutes
     * and hand the sending domain a burst large enough to look like spam. A
     * scheduled command drains the rest a batch at a time.
     */
    public const BATCH_SIZE = 25;

    /**
     * Record invitations without sending. Anything that already has an account
     * or an earlier invitation is skipped, so re-uploading the same list is
     * harmless.
     *
     * @param  array<int, string>  $emails
     * @return int number actually queued
     */
    public function queue(array $emails, ?Admin $admin = null): int
    {
        $queued = 0;

        foreach ($emails as $email) {
            $email = trim($email);

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if (Inspector::where('email', $email)->exists() || ProviderInvitation::where('email', $email)->exists()) {
                continue;
            }

            ProviderInvitation::create([
                'email' => $email,
                'invited_by_admin_id' => $admin?->id,
                'source' => 'csv-bulk',
            ]);

            $queued++;
        }

        return $queued;
    }

    /**
     * Send the next batch of queued invitations.
     *
     * @return int number sent
     */
    public function sendBatch(?int $limit = null): int
    {
        $sent = 0;

        foreach (ProviderInvitation::queued()->orderBy('id')->take($limit ?? self::BATCH_SIZE)->get() as $invitation) {
            // Someone may have registered between upload and send.
            if (Inspector::where('email', $invitation->email)->exists()) {
                $invitation->update(['sent_at' => now(), 'registered_at' => now()]);

                continue;
            }

            SafeMailer::send(fn () => Mail::to($invitation->email)->send(new ProviderPlatformInviteMail($invitation->email)));

            $invitation->update(['sent_at' => now()]);
            $sent++;
        }

        return $sent;
    }
}
