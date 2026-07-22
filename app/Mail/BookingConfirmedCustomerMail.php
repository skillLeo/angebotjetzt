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
        return "Zahlung erfolgreich — Auftrag {$this->booking->booking_number} bestätigt";
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
            "Guten Tag {$b->user->name},",
            "Ihre Zahlung über <strong>{$price} €</strong> ist eingegangen. Ihr Auftrag <strong>{$b->booking_number}</strong> ist damit verbindlich bestätigt.",
            "Ihr Gutachter: <strong>{$b->inspector->name}</strong> ({$b->inspector->company_name})<br>Telefon: {$b->inspector->phone}",
            'Der Gutachter wird sich zeitnah bei Ihnen melden, um den Termin abzustimmen.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('konto.bookings.show', $this->booking->id), 'label' => 'Auftrag ansehen'];
    }
}
