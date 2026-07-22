<?php

namespace App\Mail;

use App\Models\Inspector;
use App\Models\ServiceRequest;

class NewRequestNotificationMail extends BaseBrandMail
{
    public function __construct(
        public ServiceRequest $serviceRequest,
        public Inspector $inspector,
        public string $signedLink,
    ) {
    }

    protected function subjectLine(): string
    {
        return "Neue Anfrage in {$this->serviceRequest->ort}: {$this->serviceRequest->serviceType->name}";
    }

    protected function title(): string
    {
        return 'Neue Anfrage in Ihrem Servicegebiet';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;

        return [
            "Guten Tag {$this->inspector->name},",
            "in Ihrem Servicegebiet ist eine neue Anfrage eingegangen:",
            "<strong>{$r->serviceType->name}</strong><br>Fahrzeug: {$r->vehicle_make} {$r->vehicle_model}<br>Ort: {$r->plz} {$r->ort}<br>Anfrage-Nr.: {$r->request_number}",
            'Geben Sie jetzt Ihr individuelles Angebot ab — der Kunde vergleicht alle eingehenden Angebote und entscheidet sich für eines.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->signedLink, 'label' => 'Anfrage ansehen & Angebot abgeben'];
    }

    protected function footnote(): ?string
    {
        return 'Der Link führt Sie ohne erneute Anmeldung direkt zur Anfrage und ist 14 Tage gültig.';
    }
}
