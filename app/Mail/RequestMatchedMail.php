<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class RequestMatchedMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest) {}

    protected function subjectLine(): string
    {
        return "Gute Neuigkeiten zu Ihrer Anfrage {$this->serviceRequest->request_number}";
    }

    protected function title(): string
    {
        return 'Wir haben einen passenden Anbieter gefunden';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;

        return [
            "Guten Tag {$r->contact_name},",
            "für Ihre Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name} für Ihren {$r->vehicle_make} {$r->vehicle_model}) haben wir soeben einen passenden Anbieter in Ihrer Region ({$r->plz} {$r->ort}) gefunden und benachrichtigt.",
            $r->serviceType->isDirectAccept()
                ? 'Sobald der Sachverständige die Anfrage annimmt, meldet er sich direkt bei Ihnen.'
                : 'Sie werden per E-Mail informiert, sobald ein Angebot eingeht.',
        ];
    }

    protected function cta(): ?array
    {
        // A direct-accept service has no offers page to send anyone to.
        return $this->serviceRequest->serviceType->isDirectAccept()
            ? ['url' => $this->serviceRequest->orderViewUrl(), 'label' => 'Auftrag ansehen']
            : ['url' => $this->serviceRequest->offersViewUrl(), 'label' => 'Anfrage ansehen'];
    }
}
