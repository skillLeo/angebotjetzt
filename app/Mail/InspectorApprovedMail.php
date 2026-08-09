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
        $lines = [
            "Guten Tag {$this->inspector->name},",
            'Ihr Anbieter-Konto bei AngebotJetzt wurde von unserem Team geprüft und freigeschaltet.',
        ];

        // Service area is now set up as part of onboarding for most
        // providers, so this "one last step" nudge only applies to whoever
        // skipped it via "Später fortsetzen" — telling someone who already
        // configured it to "set it up now" would read as broken/confused.
        $lines[] = $this->inspector->serviceAreas()->exists()
            ? 'Sie erhalten ab sofort passende Anfragen aus Ihrem Servicegebiet.'
            : 'Ein letzter Schritt fehlt noch: Legen Sie Ihr Servicegebiet fest, damit wir Ihnen passende Anfragen aus Ihrer Region zusenden können. Ohne Servicegebiet erhalten Sie noch keine Anfragen.';

        return $lines;
    }

    protected function cta(): ?array
    {
        return $this->inspector->serviceAreas()->exists()
            ? ['url' => route('inspector.dashboard'), 'label' => 'Zum Dashboard']
            : ['url' => route('inspector.service-areas'), 'label' => 'Servicegebiet festlegen'];
    }
}
