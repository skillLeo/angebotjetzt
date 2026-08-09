<?php

namespace App\Console\Commands;

use App\Mail\OfferReminderMail;
use App\Models\ServiceRequest;
use App\Support\SafeMailer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Runs on Laravel's scheduler (see bootstrap/app.php) rather than as a
 * delayed queued job: this app's queue connection is "sync" in production,
 * which executes jobs immediately and ignores ->delay() entirely, so a
 * delayed dispatch would have sent both the 24h and 48h reminders instantly
 * instead of on schedule. Polling on a cron-driven schedule works the same
 * regardless of queue driver.
 */
#[Signature('app:send-offer-reminders')]
#[Description('Send the 24h/48h no-decision-yet offer reminder emails')]
class SendOfferReminders extends Command
{
    public function handle(): void
    {
        $this->sendBatch(
            column: 'offer_reminder_sent_at',
            cutoff: now()->subHours(24),
            isFinal: false,
        );

        $this->sendBatch(
            column: 'offer_final_reminder_sent_at',
            cutoff: now()->subHours(48),
            isFinal: true,
        );
    }

    private function sendBatch(string $column, \Carbon\CarbonInterface $cutoff, bool $isFinal): void
    {
        $requests = ServiceRequest::where('status', 'offers_received')
            ->whereNotNull('first_offer_at')
            ->where('first_offer_at', '<=', $cutoff)
            ->whereNull($column)
            ->get();

        foreach ($requests as $request) {
            SafeMailer::send(fn () => Mail::to($request->contact_email)->send(new OfferReminderMail($request, $isFinal)));

            $request->update([$column => now()]);
        }

        if ($requests->isNotEmpty()) {
            $this->info(($isFinal ? 'Final' : 'First')." reminder sent for {$requests->count()} request(s).");
        }
    }
}
