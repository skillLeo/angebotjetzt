<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class RequestConfirmationMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest)
    {
    }

    protected function subjectLine(): string
    {
        return "Ihre Anfrage {$this->serviceRequest->request_number} ist eingegangen";
    }

    protected function title(): string
    {
        return 'Ihre Anfrage ist unterwegs';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;

        return [
            "Guten Tag {$r->contact_name},",
            "vielen Dank für Ihre Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name} für Ihren {$r->vehicle_make} {$r->vehicle_model}).",
            "Passende Dienstleister in Ihrer Region ({$r->plz} {$r->ort}) wurden benachrichtigt. Die ersten Angebote treffen in der Regel innerhalb weniger Stunden ein.",
            'Sie werden per E-Mail benachrichtigt, sobald ein Angebot eingeht.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('konto.requests'), 'label' => 'Meine Anfragen ansehen'];
    }
}
