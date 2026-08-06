<?php

namespace App\Mail;

use App\Models\Inspector;

class InspectorApprovedMail extends BaseBrandMail
{
    public function __construct(public Inspector $inspector) {}

    protected function subjectLine(): string
    {
        return 'Ihr Konto wurde freigeschaltet';
    }

    protected function title(): string
    {
        return 'Willkommen an Bord';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->inspector->name},",
            'Ihr Anbieter-Konto bei AngebotJetzt wurde von unserem Team geprüft und freigeschaltet.',
            'Ein letzter Schritt fehlt noch: Legen Sie Ihr Servicegebiet fest, damit wir Ihnen passende Anfragen aus Ihrer Region zusenden können. Ohne Servicegebiet erhalten Sie noch keine Anfragen.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.service-areas'), 'label' => 'Servicegebiet festlegen'];
    }
}
