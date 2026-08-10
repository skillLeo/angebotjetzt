<?php

use App\Mail\CommissionInvoiceMail;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

/**
 * Re-verification of every Part 5 surface, but this time the request number
 * is never hand-written — it comes from the real, now-changed
 * createWithUniqueNumber() generator, so this proves the new AJYYMMXXXX
 * format actually flows through the same referenceNumber() consolidation,
 * not just that consolidation works for whatever string happens to be in
 * the column.
 */
it('shows the new AJYYMMXXXX format, never the old pattern, across every Part 5 surface', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::create(2026, 8, 15));

    $type = checklistServiceType();
    $inspector = checklistInspector();
    $user = User::factory()->create();

    $request = ServiceRequest::createWithUniqueNumber([
        'user_id' => $user->id, 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => $user->name, 'contact_email' => $user->email, 'contact_phone' => '+49123',
        'status' => 'open',
    ]);
    $number = $request->request_number;

    // Prove it's genuinely the new format before checking anything else.
    expect($number)->toMatch('/^AJ2608\d{4}$/')
        ->and($number)->not->toContain('-');

    RequestMatch::create(['request_id' => $request->id, 'inspector_id' => $inspector->id, 'notified_at' => now()]);
    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 30000, 'commission_cents' => 3000, 'inspector_cents' => 27000, 'status' => 'open',
    ]);

    $this->actingAs($user)->post("/account/offers/{$offer->id}/accept")->assertRedirect();

    $booking = $request->fresh()->booking;
    $invoice = Invoice::where('booking_id', $booking->id)->firstOrFail();

    // 1) Real rendered PDF.
    $pdfHtml = view('invoices.commission', [
        'invoice' => $invoice->load('booking.request', 'booking.offer', 'inspector'),
        'booking' => $booking->load('request'),
        'inspector' => $inspector,
    ])->render();
    expect($pdfHtml)->toContain($number)
        ->and($pdfHtml)->not->toContain($invoice->invoice_number)
        ->and($pdfHtml)->not->toContain($booking->booking_number)
        ->and($pdfHtml)->not->toContain('AJ-2026'); // old-format shape must never appear

    // 2) Commission invoice email.
    Mail::assertQueued(CommissionInvoiceMail::class, function ($mail) use ($number) {
        $rendered = $mail->render();
        $subject = (fn () => $this->subjectLine())->call($mail);

        return str_contains($rendered, $number) && str_contains($subject, $number)
            && ! str_contains($rendered, 'AJ-2026') && ! str_contains($subject, 'AJ-2026');
    });

    // 3) Admin: bookings list, booking detail, invoices list, customer detail.
    $admin = makeChecklistAdmin();

    $this->actingAs($admin, 'admin')->get('/admin/bookings')->assertInertia(fn ($page) => $page
        ->where('bookings.data.0.number', $number));

    $this->actingAs($admin, 'admin')->get("/admin/bookings/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('booking.number', $number)
        ->where('booking.invoice.number', $number));

    $this->actingAs($admin, 'admin')->get('/admin/invoices')->assertInertia(fn ($page) => $page
        ->where('invoices.data.0.request', $number));

    $this->actingAs($admin, 'admin')->get("/admin/customers/{$user->id}")->assertInertia(fn ($page) => $page
        ->where('customer.bookings.0.number', $number));

    // 4) Provider's own invoices list and job detail.
    $this->actingAs($inspector, 'inspector')->get('/inspector/invoices')->assertInertia(fn ($page) => $page
        ->where('invoices.data.0.number', $number));

    $this->actingAs($inspector, 'inspector')->get("/inspector/jobs/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('job.number', $number));

    // 5) Customer's own booking view.
    $this->actingAs($user)->get("/account/bookings/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('booking.number', $number));
});
