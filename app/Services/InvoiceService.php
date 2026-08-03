<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate the commission invoice for a just-accepted booking: build the
     * PDF, store it, and persist the invoice record. The offer's own
     * commission_cents (computed at submission time from the platform's
     * commission percentage) is reused directly, so the invoiced amount can
     * never drift from what the provider was quoted.
     */
    public function generateFor(Booking $booking): Invoice
    {
        $offer = $booking->offer;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::nextInvoiceNumber(),
            'booking_id' => $booking->id,
            'inspector_id' => $booking->inspector_id,
            'offer_amount_cents' => $offer->price_cents,
            'commission_percent' => \App\Models\Setting::commissionPercent(),
            'commission_cents' => $offer->commission_cents,
            'due_date' => now()->addDays(14),
        ]);

        $invoice->load(['booking.offer', 'inspector']);

        $pdf = Pdf::loadView('invoices.commission', [
            'invoice' => $invoice,
            'booking' => $invoice->booking,
            'inspector' => $invoice->inspector,
        ]);

        $path = "invoices/{$invoice->invoice_number}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);

        return $invoice;
    }
}
