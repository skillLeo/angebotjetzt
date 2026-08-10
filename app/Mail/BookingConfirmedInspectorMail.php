<?php

namespace App\Mail;

use App\Models\Booking;

class BookingConfirmedInspectorMail extends BaseBrandMail
{
    public function __construct(public Booking $booking)
    {
    }

    protected function subjectLine(): string
    {
        return "Ihr Angebot wurde angenommen — Auftrag {$this->booking->request->request_number}";
    }

    protected function title(): string
    {
        return 'Glückwunsch — Sie haben den Auftrag!';
    }

    protected function lines(): array
    {
        $b = $this->booking;
        $price = number_format($b->offer->price_cents / 100, 2, ',', '.');
        $request = $b->request;

        return [
            "Guten Tag {$b->inspector->name},",
            "der Kunde hat Ihr Angebot über <strong>{$price} €</strong> angenommen. Der Auftrag <strong>{$request->request_number}</strong> ist Ihnen verbindlich zugeteilt. Bitte kontaktieren Sie den Kunden, um den Termin zu vereinbaren.",
            "Leistung: <strong>{$request->serviceType->name}</strong><br>Fahrzeug: {$request->vehicle_make} {$request->vehicle_model}",
            "Kunde: <strong>{$request->contact_name}</strong><br>Telefon: {$request->contact_phone}<br>E-Mail: {$request->contact_email}",
            "Der vereinbarte Auftragswert von <strong>{$price} €</strong> ist direkt zwischen Ihnen und dem Kunden zu begleichen, außerhalb der Plattform. Die Rechnung über unsere Vermittlungsprovision erhalten Sie separat per E-Mail.",
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.jobs.show', $this->booking->id), 'label' => 'Auftrag öffnen'];
    }
}
