<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class OfferReminderMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest, public bool $isFinal) {}

    protected function subjectLine(): string
    {
        return $this->isFinal
            ? "Letzte Erinnerung: Angebote für Ihre Anfrage {$this->serviceRequest->request_number}"
            : "Sie haben Angebote erhalten für Anfrage {$this->serviceRequest->request_number}";
    }

    protected function title(): string
    {
        return 'Ihre Angebote warten auf eine Entscheidung';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->serviceRequest->contact_name},",
            "für Ihre Anfrage <strong>{$this->serviceRequest->request_number}</strong> liegen bereits Angebote vor, aber Sie haben noch keines angenommen.",
            $this->isFinal
                ? 'Dies ist die letzte automatische Erinnerung. Vergleichen Sie die Angebote und nehmen Sie das passende an, damit Ihr Anliegen zeitnah bearbeitet werden kann.'
                : 'Vergleichen Sie die Angebote in Ihrem Konto und nehmen Sie das passende direkt online an.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->serviceRequest->offersViewUrl(), 'label' => 'Angebote vergleichen'];
    }
}
