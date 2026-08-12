<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class AdminNoMatchAlertMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest)
    {
    }

    protected function subjectLine(): string
    {
        return "Keine Angebote seit 24h — Anfrage {$this->serviceRequest->request_number} benötigt Aufmerksamkeit";
    }

    protected function title(): string
    {
        return 'Anfrage ohne Angebote — bitte prüfen';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;
        $status = $r->status === 'unmatched' ? 'Kein Anbieter im System deckt diese Region/Kategorie ab.' : 'Anbieter wurden benachrichtigt, aber niemand hat bisher ein Angebot abgegeben.';

        return [
            "Die Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name}) in <strong>{$r->plz} {$r->ort}</strong> hat seit mehr als 24 Stunden kein Angebot erhalten.",
            $status,
            "Der Kunde wurde bereits automatisch informiert, dass sich das Team persönlich darum kümmert. Bitte prüfen Sie, ob ein passender Anbieter direkt eingeladen werden kann.",
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('admin.requests.show', $this->serviceRequest->id), 'label' => 'Anfrage im Admin-Bereich öffnen'];
    }
}
