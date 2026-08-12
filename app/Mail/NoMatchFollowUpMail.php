<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class NoMatchFollowUpMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest)
    {
    }

    protected function subjectLine(): string
    {
        return "Update zu Ihrer Anfrage {$this->serviceRequest->request_number}";
    }

    protected function title(): string
    {
        return 'Wir kümmern uns persönlich darum';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;

        return [
            "Guten Tag {$r->contact_name},",
            "für Ihre Anfrage <strong>{$r->request_number}</strong> ({$r->serviceType->name}) sind bisher noch keine Angebote eingegangen.",
            "Das kommt gelegentlich vor, wenn in {$r->ort} aktuell noch wenige geprüfte Anbieter aktiv sind. Unser Team schaut sich Ihre Anfrage jetzt persönlich an und sucht gezielt nach einem passenden Anbieter für Sie.",
            'Sie müssen nichts weiter tun — wir melden uns, sobald sich etwas ergibt.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->serviceRequest->myRequestsViewUrl(), 'label' => 'Meine Anfrage ansehen'];
    }
}
