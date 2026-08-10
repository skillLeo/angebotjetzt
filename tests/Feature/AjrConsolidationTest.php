<?php

use App\Mail\CommissionInvoiceMail;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * Regression test for the AJR/AJB-vs-AJ number confusion: every surface a
 * booking/invoice reference number appears on must show the exact same
 * AJ-XXXX request number, never the internal AJB- booking_number or AJR-
 * invoice_number. This is a full, real accept-offer -> real invoice PDF ->
 * real email flow, not a code-reading check, specifically because the
 * previous "fix" only covered dashboards/emails and missed the PDF
 * template and several admin views entirely.
 */
it('shows only the original AJ request number everywhere an invoice or booking is referenced, never AJR or AJB', function () {
    Mail::fake();

    $type = checklistServiceType();
    $inspector = checklistInspector();
    $user = User::factory()->create();

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-AJRTEST', 'user_id' => $user->id, 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => $user->name, 'contact_email' => $user->email, 'contact_phone' => '+49123',
        'status' => 'open',
    ]);
    RequestMatch::create(['request_id' => $request->id, 'inspector_id' => $inspector->id, 'notified_at' => now()]);

    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 30000, 'commission_cents' => 3000, 'inspector_cents' => 27000, 'status' => 'open',
    ]);

    $this->actingAs($user)->post("/account/offers/{$offer->id}/accept")->assertRedirect();

    $booking = $request->fresh()->booking;
    $invoice = Invoice::where('booking_id', $booking->id)->firstOrFail();

    expect($booking->booking_number)->toStartWith('AJB-')
        ->and($invoice->invoice_number)->toStartWith('AJR-')
        ->and($booking->booking_number)->not->toBe($request->request_number)
        ->and($invoice->invoice_number)->not->toBe($request->request_number);

    // 1) The actual rendered PDF content — this is the exact surface the
    // previous round never checked, and where AJR/AJB were still leaking.
    $pdfHtml = view('invoices.commission', [
        'invoice' => $invoice->load('booking.request', 'booking.offer', 'inspector'),
        'booking' => $booking->load('request'),
        'inspector' => $inspector,
    ])->render();
    expect($pdfHtml)->toContain($request->request_number)
        ->and($pdfHtml)->not->toContain($invoice->invoice_number)
        ->and($pdfHtml)->not->toContain($booking->booking_number);

    // 2) The commission invoice email (subject + body + attachment name).
    Mail::assertQueued(CommissionInvoiceMail::class, function ($mail) use ($request, $invoice, $booking) {
        $rendered = $mail->render();
        $subject = (fn () => $this->subjectLine())->call($mail);

        return str_contains($rendered, $request->request_number)
            && ! str_contains($rendered, $invoice->invoice_number)
            && ! str_contains($rendered, $booking->booking_number)
            && str_contains($subject, $request->request_number)
            && ! str_contains($subject, $invoice->invoice_number);
    });

    // 3) Admin: booking list, booking detail (incl. nested invoice number),
    // invoices list, customer detail's booking history.
    $admin = makeChecklistAdmin();

    $this->actingAs($admin, 'admin')->get('/admin/bookings')->assertInertia(fn ($page) => $page
        ->where('bookings.data.0.number', $request->request_number));

    $this->actingAs($admin, 'admin')->get("/admin/bookings/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('booking.number', $request->request_number)
        ->where('booking.invoice.number', $request->request_number));

    $this->actingAs($admin, 'admin')->get('/admin/invoices')->assertInertia(fn ($page) => $page
        ->where('invoices.data.0.request', $request->request_number));

    $this->actingAs($admin, 'admin')->get("/admin/customers/{$user->id}")->assertInertia(fn ($page) => $page
        ->where('customer.bookings.0.number', $request->request_number));

    // 4) Provider's own invoices list.
    $this->actingAs($inspector, 'inspector')->get('/inspector/invoices')->assertInertia(fn ($page) => $page
        ->where('invoices.data.0.number', $request->request_number));

    // 5) Customer's own booking view (already covered by the earlier round,
    // re-verified here as part of the same pass since this area was touched).
    $this->actingAs($user)->get("/account/bookings/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('booking.number', $request->request_number));
});
