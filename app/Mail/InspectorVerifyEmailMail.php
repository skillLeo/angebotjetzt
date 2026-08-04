<?php

namespace App\Mail;

use App\Models\Inspector;

class InspectorVerifyEmailMail extends BaseBrandMail
{
    public function __construct(public Inspector $inspector, public string $signedLink)
    {
    }

    protected function subjectLine(): string
    {
        return 'Bitte bestätigen Sie Ihre E-Mail-Adresse';
    }

    protected function title(): string
    {
        return 'Fast geschafft';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->inspector->name},",
            'vielen Dank für Ihre Registrierung als Anbieter bei AngebotJetzt. Bitte bestätigen Sie Ihre E-Mail-Adresse, um mit der Einrichtung Ihres Kontos fortzufahren.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => $this->signedLink, 'label' => 'E-Mail-Adresse bestätigen'];
    }

    protected function footnote(): ?string
    {
        return 'Der Link ist 14 Tage gültig.';
    }
}
