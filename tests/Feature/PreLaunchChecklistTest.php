<?php

use App\Mail\BookingConfirmedCustomerMail;
use App\Mail\BookingConfirmedInspectorMail;
use App\Models\Inspector;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

function checklistServiceType(): ServiceType
{
    $category = ServiceCategory::firstOrCreate(['slug' => 'kfz-gutachten'], ['name' => 'Kfz', 'is_active' => true]);

    return ServiceType::firstOrCreate(
        ['slug' => 'unfallschadengutachten'],
        ['service_category_id' => $category->id, 'name' => 'Unfallschadengutachten', 'is_active' => true]
    );
}

function makeChecklistAdmin(): App\Models\Admin
{
    static $seq = 0;
    $seq++;

    return App\Models\Admin::create([
        'name' => 'Test Admin '.$seq,
        'email' => "checklist-admin{$seq}@example.de",
        'password' => Hash::make('secret12'),
    ]);
}

function checklistInspector(int $plzFrom = 50000, int $plzTo = 51999): Inspector
{
    static $seq = 0;
    $seq++;

    $inspector = Inspector::create([
        'name' => 'Test Dienstleister '.$seq,
        'email' => "checklist{$seq}@example.de",
        'password' => Hash::make('secret12'),
        'city' => 'Köln',
        'is_active' => true,
        'is_approved' => true,
        'is_verified' => true,
        'email_verified_at' => now(),
        'profile_completed_at' => now(),
    ]);
    $inspector->serviceAreas()->create(['type' => 'postal_range', 'postal_from' => $plzFrom, 'postal_to' => $plzTo]);
    Wallet::create(['inspector_id' => $inspector->id]);

    return $inspector;
}

// --- Part 1: guest "View Offers" link pre-fills name/email, only asks a password ---

it('rejects an unsigned or tampered offers.view link', function () {
    $type = checklistServiceType();
    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-CL0001', 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Guest Customer', 'contact_email' => 'guest1@example.de', 'contact_phone' => '+49123',
        'status' => 'open',
    ]);

    $this->get("/offers/{$request->id}/view")->assertForbidden();
});

it('lets a guest with a validly signed offers.view link land on pre-filled registration', function () {
    // Superseded by the smart-redirect round: guests with no account now go
    // straight to /register (pre-filled), not the wizard's claim screen —
    // see SmartGuestRedirectTest.php for the full branching coverage.
    $type = checklistServiceType();
    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-CL0002', 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Guest Customer', 'contact_email' => 'guest2@example.de', 'contact_phone' => '+49123',
        'status' => 'open',
    ]);

    $signed = URL::temporarySignedRoute('offers.view', now()->addDays(30), ['serviceRequest' => $request->id]);
    $response = $this->get($signed);
    $response->assertRedirect(route('register', ['name' => 'Guest Customer', 'email' => 'guest2@example.de']));
});

// --- Part 2: request number must never change format after acceptance ---

it('keeps showing the original request number to customer and provider after acceptance, never the internal booking number', function () {
    Mail::fake();

    $type = checklistServiceType();
    $inspector = checklistInspector();
    $user = User::factory()->create();

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-CL0003', 'user_id' => $user->id, 'service_type_id' => $type->id,
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
    expect($booking)->not->toBeNull()
        ->and($booking->booking_number)->not->toBe($request->request_number); // genuinely different internal refs

    // Customer's own booking list/detail must show the ORIGINAL request number.
    $this->actingAs($user)->get('/account/bookings')->assertInertia(
        fn ($page) => $page->where('bookings.data.0.number', $request->request_number)
    );
    $this->actingAs($user)->get("/account/bookings/{$booking->id}")->assertInertia(
        fn ($page) => $page->where('booking.number', $request->request_number)
    );

    // Provider's own job list/detail too.
    $this->actingAs($inspector, 'inspector')->get('/inspector/jobs')->assertInertia(
        fn ($page) => $page->where('jobs.data.0.number', $request->request_number)
    );
    $this->actingAs($inspector, 'inspector')->get("/inspector/jobs/{$booking->id}")->assertInertia(
        fn ($page) => $page->where('job.number', $request->request_number)
    );

    // Emails reference the same original number too.
    Mail::assertQueued(BookingConfirmedCustomerMail::class, function ($mail) use ($request) {
        return str_contains($mail->render(), $request->request_number);
    });
    Mail::assertQueued(BookingConfirmedInspectorMail::class, function ($mail) use ($request) {
        return str_contains($mail->render(), $request->request_number);
    });
});

// --- Part 4: customer Payments section is gone ---

it('no longer has a customer payments route', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/account/payments')->assertNotFound();
});

// --- Part 5: admin wallet/payout pages removed, commissions & invoices works ---

it('has removed the admin wallet and payout pages entirely', function () {
    $admin = makeChecklistAdmin();
    $this->actingAs($admin, 'admin')->get('/admin/wallets')->assertNotFound();
    $this->actingAs($admin, 'admin')->get('/admin/payouts')->assertNotFound();
});

it('lets admin mark a commission invoice as paid and updates the outstanding/paid totals', function () {
    $inspector = checklistInspector();
    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-CL0004', 'service_type_id' => checklistServiceType()->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Test', 'contact_email' => 't@example.de', 'contact_phone' => '+49123',
        'status' => 'accepted',
    ]);
    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id, 'price_cents' => 30000,
        'commission_cents' => 3000, 'inspector_cents' => 27000, 'status' => 'accepted',
    ]);
    $booking = App\Models\Booking::create([
        'booking_number' => 'AJB-2026-CL0001', 'request_id' => $request->id, 'offer_id' => $offer->id,
        'user_id' => User::factory()->create()->id, 'inspector_id' => $inspector->id, 'status' => 'accepted',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'AJR-2026-CL0001', 'booking_id' => $booking->id, 'inspector_id' => $inspector->id,
        'offer_amount_cents' => 30000, 'commission_percent' => 10, 'commission_cents' => 3000,
        'due_date' => now()->addDays(14),
    ]);

    expect($invoice->paid_at)->toBeNull();

    $admin = makeChecklistAdmin();
    $this->actingAs($admin, 'admin')->get('/admin/invoices')->assertInertia(
        fn ($page) => $page->where('totals.outstanding', 3000)->where('totals.paid', 0)
    );

    $this->actingAs($admin, 'admin')->post("/admin/invoices/{$invoice->id}/paid")->assertRedirect();

    $invoice->refresh();
    expect($invoice->paid_at)->not->toBeNull();

    $this->actingAs($admin, 'admin')->get('/admin/invoices')->assertInertia(
        fn ($page) => $page->where('totals.outstanding', 0)->where('totals.paid', 3000)
    );
});
