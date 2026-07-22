<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Inspector;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function walletFor(Inspector $inspector): Wallet
    {
        return Wallet::firstOrCreate(['inspector_id' => $inspector->id]);
    }

    /**
     * Credit the inspector share as PENDING when a booking is paid.
     */
    public function creditPending(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $payment = $booking->payment;
            $wallet = Wallet::where('inspector_id', $booking->inspector_id)->lockForUpdate()->first()
                ?? $this->walletFor($booking->inspector);
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            $wallet->pending_cents += $payment->inspector_cents;
            $wallet->save();

            $wallet->transactions()->create([
                'type' => 'credit_pending',
                'amount_cents' => $payment->inspector_cents,
                'balance_after_cents' => $wallet->available_cents,
                'source_type' => Booking::class,
                'source_id' => $booking->id,
                'description' => "Gutschrift (ausstehend) für Auftrag {$booking->booking_number}",
            ]);
        });
    }

    /**
     * Move the pending share to available once the job is confirmed complete.
     */
    public function releasePending(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $payment = $booking->payment;
            $wallet = Wallet::where('inspector_id', $booking->inspector_id)->lockForUpdate()->firstOrFail();

            if ($wallet->pending_cents < $payment->inspector_cents) {
                throw new RuntimeException('Pending balance insufficient for release.');
            }

            $wallet->pending_cents -= $payment->inspector_cents;
            $wallet->available_cents += $payment->inspector_cents;
            $wallet->lifetime_cents += $payment->inspector_cents;
            $wallet->save();

            $wallet->transactions()->create([
                'type' => 'release',
                'amount_cents' => $payment->inspector_cents,
                'balance_after_cents' => $wallet->available_cents,
                'source_type' => Booking::class,
                'source_id' => $booking->id,
                'description' => "Freigabe für Auftrag {$booking->booking_number}",
            ]);
        });
    }

    /**
     * Debit the wallet when an admin marks a payout as paid.
     */
    public function debitPayout(PayoutRequest $payout): void
    {
        DB::transaction(function () use ($payout) {
            $wallet = Wallet::where('inspector_id', $payout->inspector_id)->lockForUpdate()->firstOrFail();

            if ($wallet->available_cents < $payout->amount_cents) {
                throw new RuntimeException('Available balance insufficient for payout.');
            }

            $wallet->available_cents -= $payout->amount_cents;
            $wallet->save();

            $wallet->transactions()->create([
                'type' => 'debit_payout',
                'amount_cents' => -$payout->amount_cents,
                'balance_after_cents' => $wallet->available_cents,
                'source_type' => PayoutRequest::class,
                'source_id' => $payout->id,
                'description' => "Auszahlung #{$payout->id} an {$payout->account_holder}",
            ]);
        });
    }
}
