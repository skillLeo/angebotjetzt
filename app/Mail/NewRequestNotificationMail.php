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
        $isDirectAccept = $r->serviceType->isDirectAccept();

        $summary = "<strong>{$r->serviceType->name}</strong><br>Fahrzeug: {$r->vehicle_make} {$r->vehicle_model}<br>Ort: {$r->plz} {$r->ort}<br>Anfrage-Nr.: {$r->request_number}";

        // For an accident report the two answers decide how the claim is
        // handled, so the provider gets them in the mail itself rather than
        // having to open the request to find out.
        if ($isDirectAccept) {
            $summary .= '<br>Rolle beim Unfall: <strong>'.($r->accidentRoleLabel() ?? 'Keine Angabe').'</strong>'
                .'<br>Anwalt beauftragt: <strong>'.($r->lawyerLabel() ?? 'Keine Angabe').'</strong>';
        }

        return [
            "Guten Tag {$this->inspector->name},",
            'in Ihrem Servicegebiet ist eine neue Anfrage eingegangen:',
            $summary,
            $isDirectAccept
                ? 'Für diese Leistung wird kein Angebot abgegeben. Sie können die Anfrage direkt verbindlich annehmen; Ihr Honorar richtet sich nach der tatsächlich festgestellten Schadenhöhe.'
                : 'Geben Sie jetzt Ihr individuelles Angebot ab — der Kunde vergleicht alle eingehenden Angebote und entscheidet sich für eines.',
        ];
    }

    protected function cta(): ?array
    {
        return [
            'url' => $this->signedLink,
            'label' => $this->serviceRequest->serviceType->isDirectAccept()
                ? 'Anfrage ansehen & annehmen'
                : 'Anfrage ansehen & Angebot abgeben',
        ];
    }

    protected function footnote(): ?string
    {
        return 'Der Link führt Sie ohne erneute Anmeldung direkt zur Anfrage und ist 14 Tage gültig.';
    }
}
