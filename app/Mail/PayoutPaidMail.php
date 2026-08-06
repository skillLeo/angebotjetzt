<?php

namespace App\Mail;

use App\Models\PayoutRequest;

class PayoutPaidMail extends BaseBrandMail
{
    public function __construct(public PayoutRequest $payout) {}

    protected function subjectLine(): string
    {
        return 'Ihre Auszahlung wurde überwiesen';
    }

    protected function title(): string
    {
        return 'Auszahlung überwiesen';
    }

    protected function lines(): array
    {
        $amount = number_format($this->payout->amount_cents / 100, 2, ',', '.');
        $iban = substr($this->payout->iban, 0, 4).' •••• '.substr($this->payout->iban, -4);

        return [
            "Guten Tag {$this->payout->inspector->name},",
            "Ihre Auszahlung über <strong>{$amount} €</strong> wurde auf Ihr Konto ({$iban}) überwiesen.",
            'Je nach Bank kann die Gutschrift 1–2 Werktage dauern.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.dashboard'), 'label' => 'Zum Gutachter-Portal'];
    }
}
