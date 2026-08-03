<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A mail-send failure (SMTP down, credentials revoked, etc.) must never break
 * the user-facing action it was triggered by — especially with QUEUE_CONNECTION
 * set to sync, where Mail::queue() runs inline and would otherwise throw
 * straight through the controller. Swallow and log instead.
 */
class SafeMailer
{
    public static function send(callable $dispatch): bool
    {
        try {
            $dispatch();

            return true;
        } catch (Throwable $e) {
            Log::error('Mail dispatch failed, continuing without it', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
