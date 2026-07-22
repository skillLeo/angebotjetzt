<?php

namespace App\Mail;

use App\Models\User;

class AccountCreatedMail extends BaseBrandMail
{
    public function __construct(public User $user, public string $password)
    {
    }

    protected function subjectLine(): string
    {
        return 'Ihr AngebotJetzt-Konto wurde erstellt';
    }

    protected function title(): string
    {
        return 'Willkommen bei AngebotJetzt';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->user->name},",
            'damit Sie Ihre Anfrage verfolgen und Angebote vergleichen können, haben wir automatisch ein Konto für Sie angelegt:',
            "E-Mail: <strong>{$this->user->email}</strong><br>Passwort: <strong>{$this->password}</strong>",
            'Bitte ändern Sie das Passwort nach der ersten Anmeldung in Ihren Kontoeinstellungen.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('login'), 'label' => 'Jetzt anmelden'];
    }
}
