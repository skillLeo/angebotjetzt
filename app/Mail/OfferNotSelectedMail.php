<?php

namespace App\Mail;

use App\Models\Offer;

class OfferNotSelectedMail extends BaseBrandMail
{
    public function __construct(public Offer $offer)
    {
    }

    protected function subjectLine(): string
    {
        return "Anfrage {$this->offer->request->request_number} wurde vergeben";
    }

    protected function title(): string
    {
        return 'Der Kunde hat sich für einen anderen Dienstleister entschieden';
    }

    protected function lines(): array
    {
        $r = $this->offer->request;

        return [
            "Guten Tag {$this->offer->inspector->name},",
            "vielen Dank für Ihr Angebot zu <strong>{$r->serviceType->name}</strong> ({$r->vehicle_make} {$r->vehicle_model}, {$r->ort}, Anfrage-Nr. {$r->request_number}).",
            'Der Kunde hat sich für ein anderes Angebot entschieden. Dieser Auftrag ist damit für Sie abgeschlossen.',
            'Neue passende Anfragen aus Ihrem Servicegebiet erhalten Sie wie gewohnt automatisch.',
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.offers'), 'label' => 'Meine Angebote ansehen'];
    }
}
