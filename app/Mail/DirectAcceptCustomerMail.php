<?php

namespace App\Mail;

use App\Models\Inspector;
use App\Models\ServiceRequest;

/**
 * Sent when a provider accepts a direct-accept request (Unfallschadengutachten).
 * Deliberately quotes no price: for this service the fee follows from the
 * damage amount established on inspection, so there is nothing to compare and
 * no acceptance step left for the customer.
 */
class DirectAcceptCustomerMail extends BaseBrandMail
{
    public function __construct(
        public ServiceRequest $serviceRequest,
        public Inspector $inspector,
    ) {}

    protected function subjectLine(): string
    {
        return "Ihre Anfrage {$this->serviceRequest->request_number} wurde angenommen";
    }

    protected function title(): string
    {
        return 'Ein Sachverständiger übernimmt Ihren Fall';
    }

    protected function lines(): array
    {
        $name = $this->inspector->company_name ?: $this->inspector->name;

        return [
            "Guten Tag {$this->serviceRequest->contact_name},",
            "<strong>{$name}</strong> hat Ihre Anfrage <strong>{$this->serviceRequest->request_number}</strong> verbindlich angenommen und meldet sich in Kürze direkt bei Ihnen, um die Begutachtung abzustimmen.",
            'Für das Unfallschadengutachten gibt es vorab keinen Festpreis: Das Honorar des Sachverständigen richtet sich nach der tatsächlich festgestellten Schadenhöhe. Bei einem unverschuldeten Unfall trägt in der Regel die gegnerische Versicherung diese Kosten.',
            'Sie müssen nichts weiter tun. Ihr Sachverständiger kommt auf Sie zu.',
        ];
    }

    protected function cta(): ?array
    {
        return null;
    }
}
