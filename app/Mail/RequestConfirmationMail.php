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

        // No offers are collected for a direct-accept service, so the customer
        // must not be told to expect any.
        if ($r->serviceType->isDirectAccept()) {
            return [
                "Guten Tag {$r->contact_name},",
                "vielen Dank für Ihre Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name} für Ihren {$r->vehicle_make} {$r->vehicle_model}).",
                "Passende Sachverständige in Ihrer Region ({$r->plz} {$r->ort}) wurden benachrichtigt. Sobald einer Ihre Anfrage annimmt, meldet er sich direkt bei Ihnen.",
                'Für diese Leistung gibt es vorab keinen Festpreis: Das Honorar richtet sich nach der tatsächlich festgestellten Schadenhöhe.',
            ];
        }

        return [
            "Guten Tag {$r->contact_name},",
            "vielen Dank für Ihre Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name} für Ihren {$r->vehicle_make} {$r->vehicle_model}).",
            "Passende Dienstleister in Ihrer Region ({$r->plz} {$r->ort}) wurden benachrichtigt. Die ersten Angebote treffen in der Regel innerhalb weniger Stunden ein.",
            'Sie werden per E-Mail benachrichtigt, sobald ein Angebot eingeht.',
        ];
    }

    protected function cta(): ?array
    {
        return $this->serviceRequest->serviceType->isDirectAccept()
            ? ['url' => $this->serviceRequest->orderViewUrl(), 'label' => 'Auftrag ansehen']
            : ['url' => $this->serviceRequest->myRequestsViewUrl(), 'label' => 'Meine Anfragen ansehen'];
    }
}
