<?php

namespace App\Mail;

use App\Models\Inspector;
use App\Models\ServiceRequest;

class NewOfferMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest, public Inspector $inspector)
    {
    }

    protected function subjectLine(): string
    {
        return "Neues Angebot für Ihre Anfrage {$this->serviceRequest->request_number}";
    }

    protected function title(): string
    {
        return 'Sie haben ein neues Angebot erhalten';
    }

    protected function lines(): array
    {
        return [
            "Guten Tag {$this->serviceRequest->contact_name},",
            "<strong>{$this->inspector->name}</strong> ({$this->inspector->company_name}) hat ein Angebot für Ihre Anfrage <strong>{$this->serviceRequest->request_number}</strong> abgegeben.",
            'Vergleichen Sie alle eingegangenen Angebote in Ihrem Konto und nehmen Sie das passende direkt online an.',
        ];
    }

    protected function cta(): ?array
    {
        return [
            'url' => route('konto.requests.offers', $this->serviceRequest->id),
            'label' => 'Angebote vergleichen',
        ];
    }
}
