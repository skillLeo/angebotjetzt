<?php

namespace App\Mail;

use App\Models\ServiceRequest;

class ProviderInviteToRegisterMail extends BaseBrandMail
{
    public function __construct(public string $email, public ServiceRequest $serviceRequest, public string $signedLink)
    {
    }

    protected function subjectLine(): string
    {
        return "Anfrage für Sie: {$this->serviceRequest->serviceType->name} in {$this->serviceRequest->ort}";
    }

    protected function title(): string
    {
        return 'Eine Anfrage wartet auf Sie';
    }

    protected function lines(): array
    {
        $r = $this->serviceRequest;

        return [
            'Guten Tag,',
            'ein Kunde von AngebotJetzt sucht einen passenden Anbieter für folgende Anfrage:',
            "<strong>{$r->serviceType->name}</strong><br>Fahrzeug: {$r->vehicle_make} {$r->vehicle_model}<br>Ort: {$r->plz} {$r->ort}<br>Anfrage-Nr.: {$r->request_number}",
            'Sie sind noch nicht bei AngebotJetzt registriert. Über den Link unten können Sie die Anfrage ansehen und sich anschließend registrieren, um ein Angebot abzugeben.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->signedLink, 'label' => 'Anfrage ansehen'];
    }

    protected function footnote(): ?string
    {
        return 'Der Link ist 30 Tage gültig.';
    }
}
