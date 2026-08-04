<?php

namespace App\Mail;

use App\Models\Inspector;

class InspectorApprovedMail extends BaseBrandMail
{
    public function __construct(public Inspector $inspector)
    {
    }

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
            'Ihr Anbieter-Konto bei AngebotJetzt wurde von unserem Team geprüft und freigeschaltet. Ab sofort erhalten Sie passende Anfragen aus Ihrem Servicegebiet und können eigene Angebote abgeben.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.dashboard'), 'label' => 'Zum Gutachter-Portal'];
    }
}
