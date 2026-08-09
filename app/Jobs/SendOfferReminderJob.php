<?php

namespace App\Jobs;

use App\Mail\OfferReminderMail;
use App\Models\ServiceRequest;
use App\Support\SafeMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Dispatched exactly once per request — at the moment its first offer
 * arrives (see RequestService::scheduleOfferReminders()) — with a 24h delay
 * for the first reminder and a 48h delay for the second, never per-offer.
 * Runs a fresh status check right before sending: if the customer already
 * accepted an offer (or the request otherwise left "offers_received") by the
 * time this fires, it's a silent no-op — that's the cancel-on-accept
 * behavior, since a delayed queue job already in the queue can't be pulled
 * back out, but it can decline to act.
 */
class SendOfferReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $requestId, public bool $isFinal) {}

    public function handle(): void
    {
        $request = ServiceRequest::find($this->requestId);

        if (! $request || $request->status !== 'offers_received') {
            return;
        }

        SafeMailer::send(fn () => Mail::to($request->contact_email)->queue(new OfferReminderMail($request, $this->isFinal)));
    }
}
