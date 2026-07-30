<?php

namespace App\Mail;

use App\Models\Booking;

class BookingCompletedMail extends BaseBrandMail
{
    public function __construct(public Booking $booking)
    {
    }

    protected function subjectLine(): string
    {
        return "Auftrag {$this->booking->booking_number} abgeschlossen";
    }

    protected function title(): string
    {
        return 'Ihr Auftrag ist abgeschlossen';
    }

    protected function lines(): array
    {
        $b = $this->booking;

        return [
            "Guten Tag {$b->user->name},",
            "Ihr Auftrag <strong>{$b->booking_number}</strong> bei <strong>{$b->inspector->name}</strong> ({$b->inspector->company_name}) wurde als abgeschlossen bestätigt.",
            'Wir würden uns freuen, wenn Sie Ihre Erfahrung mit einer kurzen Bewertung teilen.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('konto.bookings.show', $this->booking->id), 'label' => 'Auftrag ansehen & bewerten'];
    }
}
