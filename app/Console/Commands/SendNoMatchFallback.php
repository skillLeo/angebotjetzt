<?php

namespace App\Console\Commands;

use App\Mail\AdminNoMatchAlertMail;
use App\Mail\NoMatchFollowUpMail;
use App\Models\ServiceRequest;
use App\Support\SafeMailer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Closes a real gap: a request that never gets matched to any provider (or
 * gets matched but nobody offers) previously left the customer with no
 * follow-up at all, ever, despite the confirmation page unconditionally
 * telling them providers were notified. Runs on the scheduler for the same
 * reason as SendOfferReminders — production's sync queue driver ignores
 * ->delay(), so a real cron-driven poll is what actually fires this on time.
 */
#[Signature('app:send-no-match-fallback')]
#[Description('Notify the customer and admin when a request has had zero offers for 24h+')]
class SendNoMatchFallback extends Command
{
    public function handle(): void
    {
        $requests = ServiceRequest::whereIn('status', ['unmatched', 'open'])
            ->where('created_at', '<=', now()->subHours(24))
            ->whereNull('no_offer_fallback_sent_at')
            ->get();

        foreach ($requests as $request) {
            SafeMailer::send(fn () => Mail::to($request->contact_email)->send(new NoMatchFollowUpMail($request)));
            SafeMailer::send(fn () => Mail::to(config('mail.from.address'))->send(new AdminNoMatchAlertMail($request)));

            $request->update(['no_offer_fallback_sent_at' => now()]);
        }

        if ($requests->isNotEmpty()) {
            $this->info("No-match fallback sent for {$requests->count()} request(s).");
        }
    }
}
