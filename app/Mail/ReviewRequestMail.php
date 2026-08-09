<?php

namespace App\Mail;

use App\Models\Booking;

class ReviewRequestMail extends BaseBrandMail
{
    public function __construct(public Booking $booking, public string $signedLink) {}

    protected function subjectLine(): string
    {
        return "Wie war Ihr Auftrag {$this->booking->request->request_number}?";
    }

    protected function title(): string
    {
        return 'Wie zufrieden waren Sie?';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->booking->user->name},",
            "Ihr Auftrag <strong>{$this->booking->request->request_number}</strong> wurde abgeschlossen. Wir würden uns sehr über eine kurze Rückmeldung freuen — es dauert nur eine Minute.",
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->signedLink, 'label' => 'Jetzt bewerten'];
    }
}
