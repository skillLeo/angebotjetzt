<?php

namespace App\Mail;

use App\Models\Inspector;

class InspectorInvitationMail extends BaseBrandMail
{
    public function __construct(public Inspector $inspector, public string $password)
    {
    }

    protected function subjectLine(): string
    {
        return 'Ihr Gutachter-Zugang bei AngebotJetzt';
    }

    protected function title(): string
    {
        return 'Willkommen bei AngebotJetzt';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->inspector->name},",
            'für Sie wurde ein Gutachter-Konto auf AngebotJetzt angelegt. Ab sofort erhalten Sie automatisch Anfragen aus Ihrem Servicegebiet und können eigene Preisangebote abgeben.',
            "E-Mail: <strong>{$this->inspector->email}</strong><br>Passwort: <strong>{$this->password}</strong>",
            'Bitte ändern Sie das Passwort nach der ersten Anmeldung.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('gutachter.login'), 'label' => 'Zum Gutachter-Portal'];
    }
}
