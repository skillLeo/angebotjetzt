<?php

namespace App\Console\Commands;

use App\Services\ProviderInviteService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Drains the queued bulk provider invitations a batch at a time. Mail is sent
 * synchronously on this host, so throttling has to happen here rather than via
 * delayed jobs, which fire immediately under the sync driver.
 */
#[Signature('app:send-provider-invites')]
#[Description('Send the next batch of queued provider invitations')]
class SendProviderInvites extends Command
{
    public function handle(ProviderInviteService $invites): void
    {
        $sent = $invites->sendBatch();

        if ($sent > 0) {
            $this->info("Sent {$sent} provider invitation(s).");
        }
    }
}
