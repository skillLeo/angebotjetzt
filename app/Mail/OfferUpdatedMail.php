<?php

namespace App\Mail;

use App\Models\Inspector;
use App\Models\Offer;
use App\Models\ServiceRequest;

class OfferUpdatedMail extends BaseBrandMail
{
    public function __construct(public ServiceRequest $serviceRequest, public Inspector $inspector, public Offer $offer, public string $label) {}

    protected function subjectLine(): string
    {
        return "Angebot aktualisiert für Ihre Anfrage {$this->serviceRequest->request_number}";
    }

    protected function title(): string
    {
        return 'Ein Angebot wurde aktualisiert';
    }

    protected function lines(): array
    {
        $price = number_format($this->offer->price_cents / 100, 2, ',', '.');

        return [
            "Guten Tag {$this->serviceRequest->contact_name},",
            "<strong>{$this->label}</strong> hat das Angebot für Ihre Anfrage <strong>{$this->serviceRequest->request_number}</strong> aktualisiert. Neuer Preis: <strong>{$price} €</strong>.",
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
