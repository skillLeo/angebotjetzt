<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class CommissionInvoiceMail extends BaseBrandMail
{
    public function __construct(public Invoice $invoice)
    {
    }

    protected function subjectLine(): string
    {
        return "Rechnung {$this->invoice->referenceNumber()} — Vermittlungsprovision";
    }

    protected function title(): string
    {
        return 'Ihre Provisionsrechnung';
    }

    protected function lines(): array
    {
        $i = $this->invoice;
        $amount = number_format($i->commission_cents / 100, 2, ',', '.');

        return [
            "Guten Tag {$i->inspector->name},",
            "herzlichen Glückwunsch — Sie haben den Auftrag {$i->booking->request->request_number} erhalten. Anbei finden Sie die Rechnung über unsere Vermittlungsprovision in Höhe von <strong>{$amount} €</strong>.",
            "Bitte begleichen Sie den Betrag bis zum <strong>{$i->due_date->format('d.m.Y')}</strong> unter Angabe der Rechnungsnummer {$i->referenceNumber()}.",
        ];
    }

    protected function cta(): ?array
    {
        return ['url' => route('inspector.invoices'), 'label' => 'Rechnungen ansehen'];
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->invoice->pdf_path)
                ->as("{$this->invoice->referenceNumber()}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
