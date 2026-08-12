<?php

namespace App\Mail;

/**
 * Bulk invitation for CarSpector's existing contacts to register as providers
 * on AngebotJetzt. Extends the same BaseBrandMail template every other mail
 * uses, so branding, layout and footer stay identical; only the copy differs.
 */
class ProviderPlatformInviteMail extends BaseBrandMail
{
    public function __construct(public string $email) {}

    protected function subjectLine(): string
    {
        return 'AngebotJetzt ist die neue Plattform von CarSpector';
    }

    protected function title(): string
    {
        return 'AngebotJetzt ist die neue Plattform von CarSpector';
    }

    protected function lines(): array
    {
        return [
            'Guten Tag,',
            'AngebotJetzt ist die neue Plattform von CarSpector. Registrieren Sie sich jetzt und erhalten Sie Anfragen für weitere Dienstleistungen.',
            'Über AngebotJetzt finden Kunden aus Ihrer Region gezielt passende Dienstleister. Sie legen Ihr Servicegebiet selbst fest, erhalten dazu passende Anfragen automatisch per E-Mail und entscheiden bei jeder Anfrage frei, ob Sie sie annehmen möchten. Die Registrierung ist kostenlos und unverbindlich.',
            'Klicken Sie einfach auf die Schaltfläche unten, um Ihr Dienstleister-Konto anzulegen.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('gutachter.register'), 'label' => 'Jetzt kostenlos registrieren'];
    }

    protected function footnote(): ?string
    {
        return 'Sie erhalten diese E-Mail, weil Sie als Dienstleister bei CarSpector hinterlegt sind.';
    }
}
