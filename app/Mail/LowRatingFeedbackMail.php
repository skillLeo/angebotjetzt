<?php

namespace App\Mail;

use App\Models\Booking;

class LowRatingFeedbackMail extends BaseBrandMail
{
    public function __construct(public Booking $booking, public int $rawRating, public ?string $comment) {}

    protected function subjectLine(): string
    {
        return "Interne Rückmeldung ({$this->rawRating}/10) – Auftrag {$this->booking->booking_number}";
    }

    protected function title(): string
    {
        return 'Neue interne Kundenrückmeldung';
    }

    protected function lines(): array
    {
        $inspector = $this->booking->inspector;

        return [
            "Ein Kunde hat für Auftrag <strong>{$this->booking->booking_number}</strong> eine Bewertung von <strong>{$this->rawRating}/10</strong> abgegeben und wurde zur internen Rückmeldung statt zu Trustpilot weitergeleitet.",
            "Kunde: <strong>{$this->booking->user->name}</strong> ({$this->booking->user->email})",
            "Dienstleister: <strong>{$inspector->name}</strong>".($inspector->company_name ? " ({$inspector->company_name})" : ''),
            'Rückmeldung des Kunden:<br>'.($this->comment ? nl2br(e($this->comment)) : '<em>Kein zusätzlicher Kommentar.</em>'),
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('admin.reviews', ['status' => 'unpublished']), 'label' => 'Im Admin-Bereich ansehen'];
    }
}
