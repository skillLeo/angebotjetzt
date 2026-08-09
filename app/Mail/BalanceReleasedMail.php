<?php

namespace App\Mail;

use App\Models\Booking;

class BalanceReleasedMail extends BaseBrandMail
{
    public function __construct(public Booking $booking) {}

    protected function subjectLine(): string
    {
        return "Guthaben freigegeben für Auftrag {$this->booking->request->request_number}";
    }

    protected function title(): string
    {
        return 'Ihr Guthaben ist verfügbar';
    }

    protected function lines(): array
    {
        $b = $this->booking;
        $amount = number_format($b->offer->inspector_cents / 100, 2, ',', '.');

        return [
            "Guten Tag {$b->inspector->name},",
            "Ihr Anteil über <strong>{$amount} €</strong> für Auftrag <strong>{$b->request->request_number}</strong> wurde freigegeben und steht Ihnen jetzt zur Auszahlung zur Verfügung.",
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.dashboard'), 'label' => 'Zum Dienstleister-Portal'];
    }
}
