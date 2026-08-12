<?php

namespace App\Mail;

use App\Models\Booking;

/**
 * Asks the customer to confirm the provider really finished the job. The
 * review request is deliberately not sent yet: rating only opens once the
 * customer has confirmed, so a provider cannot trigger a review by marking
 * their own work complete.
 */
class JobCompletionConfirmationMail extends BaseBrandMail
{
    public function __construct(public Booking $booking, public string $signedLink) {}

    protected function subjectLine(): string
    {
        return "Bitte bestätigen: Auftrag {$this->booking->request->request_number} abgeschlossen?";
    }

    protected function title(): string
    {
        return 'Wurde Ihr Auftrag abgeschlossen?';
    }

    protected function lines(): array
    {
        $provider = $this->booking->inspector?->company_name
            ?: $this->booking->inspector?->name
            ?: 'Ihr Dienstleister';

        return [
            "Guten Tag {$this->booking->customerName()},",
            "<strong>{$provider}</strong> hat Ihren Auftrag <strong>{$this->booking->request->request_number}</strong> als abgeschlossen markiert.",
            'Bitte bestätigen Sie kurz, dass die Leistung tatsächlich erbracht wurde. Danach können Sie den Auftrag direkt bewerten.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->signedLink, 'label' => 'Abschluss bestätigen'];
    }

    protected function footnote(): ?string
    {
        return 'Sollte der Auftrag noch nicht abgeschlossen sein, antworten Sie einfach auf diese E-Mail.';
    }
}
