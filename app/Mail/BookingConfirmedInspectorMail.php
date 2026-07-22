<?php

namespace App\Mail;

use App\Models\Booking;

class BookingConfirmedInspectorMail extends BaseBrandMail
{
    public function __construct(public Booking $booking)
    {
    }

    protected function subjectLine(): string
    {
        return "Ihr Angebot wurde angenommen — Auftrag {$this->booking->booking_number}";
    }

    protected function title(): string
    {
        return 'Glückwunsch — Sie haben den Auftrag!';
    }

    protected function lines(): array
    {
        $b = $this->booking;
        $net = number_format($b->offer->inspector_cents / 100, 2, ',', '.');

        return [
            "Guten Tag {$b->inspector->name},",
            "der Kunde hat Ihr Angebot angenommen und bereits online bezahlt. Der Auftrag <strong>{$b->booking_number}</strong> ist Ihnen verbindlich zugeteilt.",
            "Ihr Anteil nach Abzug der Plattformprovision: <strong>{$net} €</strong>. Er wird Ihrem Wallet nach Abschluss und Bestätigung der Begutachtung gutgeschrieben.",
            'Die vollständigen Kontaktdaten des Kunden finden Sie in Ihrem Portal.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.jobs.show', $this->booking->id), 'label' => 'Auftrag öffnen'];
    }
}
