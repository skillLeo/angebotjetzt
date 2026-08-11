<?php

namespace App\Mail;

use App\Models\Booking;

class BookingConfirmedCustomerMail extends BaseBrandMail
{
    public function __construct(public Booking $booking)
    {
    }

    protected function subjectLine(): string
    {
        return "Auftrag {$this->booking->request->request_number} bestätigt — Kontaktdaten Ihres Dienstleisters";
    }

    protected function title(): string
    {
        return 'Ihr Auftrag ist bestätigt';
    }

    protected function lines(): array
    {
        $b = $this->booking;
        $price = number_format($b->offer->price_cents / 100, 2, ',', '.');

        return [
            "Guten Tag {$b->customerName()},",
            "Sie haben das Angebot über <strong>{$price} €</strong> angenommen. Ihr Auftrag <strong>{$b->request->request_number}</strong> ist damit verbindlich bestätigt. Der Dienstleister wird sich in Kürze bei Ihnen melden.",
            "Ihr Dienstleister: <strong>{$b->inspector->name}</strong> ({$b->inspector->company_name})<br>Telefon: {$b->inspector->phone}<br>E-Mail: {$b->inspector->email}",
            'Bitte stimmen Sie den Termin und die Bezahlung direkt mit dem Dienstleister ab — beides erfolgt außerhalb der Plattform.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('konto.bookings.show', $this->booking->id), 'label' => 'Auftrag ansehen'];
    }
}
