<?php

use App\Mail\BookingConfirmedCustomerMail;
use App\Mail\BookingConfirmedInspectorMail;
use App\Mail\CommissionInvoiceMail;
use App\Mail\NewRequestNotificationMail;
use App\Mail\OfferNotSelectedMail;
use App\Mail\RequestConfirmationMail;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Invoice;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('lets a guest reach the request wizard without being redirected to login', function () {
    $this->get('/request')->assertOk();
});

it('lets a guest submit the request wizard with no password at all, and creates no account', function () {
    Mail::fake();
    $type = ServiceType::where('slug', 'unfallschadengutachten')->first();
    $email = 'guest-signup-'.uniqid().'@example.de';

    $response = $this->post('/request', [
        'service_type_id' => $type->id,
        'vehicle_make' => 'Audi', 'vehicle_model' => 'A4',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Neuer Kunde', 'contact_email' => $email,
        'contact_phone' => '+49 170 1234567',
        'agb' => true, 'privacy' => true,
    ]);

    $response->assertRedirect();
    $this->assertGuest();

    $request = ServiceRequest::where('contact_email', $email)->first();
    expect($request)->not->toBeNull()
        ->and($request->user_id)->toBeNull()
        ->and(User::where('email', $email)->exists())->toBeFalse();
});

it('lets a guest optionally set a password after submission to claim a real account', function () {
    Mail::fake();
    $type = ServiceType::where('slug', 'unfallschadengutachten')->first();
    $email = 'guest-claim-'.uniqid().'@example.de';

    $this->post('/request', [
        'service_type_id' => $type->id,
        'vehicle_make' => 'Audi', 'vehicle_model' => 'A4',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Neuer Kunde', 'contact_email' => $email,
        'contact_phone' => '+49 170 1234567',
        'agb' => true, 'privacy' => true,
    ])->assertRedirect();

    $this->assertGuest();
    $request = ServiceRequest::where('contact_email', $email)->first();
    expect($request->user_id)->toBeNull();

    $claim = $this->post("/request/confirmation/{$request->request_number}/claim", [
        'password' => 'GuestPass123!', 'password_confirmation' => 'GuestPass123!',
    ]);
    $claim->assertRedirect();

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
    expect($request->fresh()->user_id)->toBe($user->id);

    // The real, user-chosen password actually works for a fresh login — never a
    // password-less/placeholder account.
    Auth::logout();
    $this->post('/login', ['email' => $email, 'password' => 'GuestPass123!'])
        ->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('lets a guest submit a request even when the email already has an account, without creating duplicates', function () {
    Mail::fake();
    $type = ServiceType::where('slug', 'unfallschadengutachten')->first();
    $existing = User::factory()->create();

    $response = $this->post('/request', [
        'service_type_id' => $type->id,
        'vehicle_make' => 'Audi', 'vehicle_model' => 'A4',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Neuer Kunde', 'contact_email' => $existing->email,
        'contact_phone' => '+49 170 1234567',
        'agb' => true, 'privacy' => true,
    ]);

    $response->assertSessionDoesntHaveErrors();
    $response->assertRedirect();
    $this->assertGuest();

    $request = ServiceRequest::where('contact_email', $existing->email)->first();
    expect($request)->not->toBeNull()
        ->and($request->user_id)->toBeNull()
        ->and(User::where('email', $existing->email)->count())->toBe(1);

    // The confirmation page must offer to log in, not to set a password
    // (that would risk creating a confusing second credential for the
    // same account, or masking that one already exists).
    $confirmation = $this->get(route('wizard.confirmation', $request->request_number));
    $confirmation->assertInertia(fn ($page) => $page
        ->where('canLogin', true)
        ->where('canClaim', false)
    );

    // Logging into the existing account retroactively claims the request.
    $this->post('/login', ['email' => $existing->email, 'password' => 'password'])->assertRedirect();
    $this->assertAuthenticatedAs($existing);
    expect($request->fresh()->user_id)->toBe($existing->id);
});

it('lets an authenticated customer submit a request, matches inspectors and notifies them', function () {
    Mail::fake();
    $type = ServiceType::where('slug', 'unfallschadengutachten')->first();
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->post('/request', [
        'service_type_id' => $type->id,
        'vehicle_make' => 'Audi', 'vehicle_model' => 'A4',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Neuer Kunde', 'contact_email' => $customer->email,
        'contact_phone' => '+49 170 1234567',
        'agb' => true, 'privacy' => true,
    ]);

    $request = ServiceRequest::latest('id')->first();

    expect($request->matched_count)->toBeGreaterThan(0)
        ->and($request->matches()->count())->toBe($request->matched_count)
        ->and($request->user_id)->toBe($customer->id);

    $response->assertRedirect(route('wizard.confirmation', $request->request_number));

    Mail::assertQueued(RequestConfirmationMail::class);
    Mail::assertQueued(NewRequestNotificationMail::class);
});

it('lets an inspector submit an offer with a correct commission split', function () {
    $inspector = Inspector::has('matches')->first();
    $request = $inspector->matches()->first()->request;
    $request->update(['status' => 'open']);
    $inspector->offers()->where('request_id', $request->id)->delete();

    $this->actingAs($inspector, 'inspector')
        ->post("/gutachter/anfragen/{$request->id}/angebot", [
            'price' => '350',
            'message' => 'Kurzfristig verfügbar.',
        ])->assertRedirect(route('inspector.offers'));

    $offer = Offer::where('request_id', $request->id)->where('inspector_id', $inspector->id)->first();

    expect($offer)->not->toBeNull()
        ->and($offer->price_cents)->toBe(35000)
        ->and($offer->commission_cents)->toBe(3500)
        ->and($offer->inspector_cents)->toBe(31500);
});

it('accepts an offer under the direct-agreement model: rejects competitors, unlocks contacts, notifies everyone and generates a downloadable commission invoice', function () {
    Mail::fake();
    Storage::fake('local');

    $customer = User::factory()->create();
    $inspector = Inspector::has('matches')->first();
    $request = $inspector->matches()->first()->request;
    $request->update(['status' => 'offers_received', 'user_id' => $customer->id]);
    $request->offers()->delete();
    $request->booking()?->delete();

    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 30000, 'commission_cents' => 3000, 'inspector_cents' => 27000,
        'status' => 'open',
    ]);
    // A competing offer that must be rejected on acceptance.
    $other = Inspector::where('id', '!=', $inspector->id)->first();
    $rejected = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $other->id,
        'price_cents' => 32000, 'commission_cents' => 3200, 'inspector_cents' => 28800,
        'status' => 'open',
    ]);

    $this->actingAs($customer)
        ->post("/account/offers/{$offer->id}/accept")
        ->assertRedirect();

    // Booking created directly (no Stripe payment record); offer accepted, competitor rejected.
    $booking = Booking::where('offer_id', $offer->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe('accepted')
        ->and($offer->fresh()->status)->toBe('accepted')
        ->and($rejected->fresh()->status)->toBe('rejected')
        ->and($request->fresh()->status)->toBe('accepted');

    // Full contact details for both sides are unlocked purely by the booking existing.
    expect($booking->user_id)->toBe($customer->id)
        ->and($booking->inspector_id)->toBe($inspector->id);

    // A real, downloadable commission-invoice PDF was generated for 10% of the offer.
    $invoice = Invoice::where('booking_id', $booking->id)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->inspector_id)->toBe($inspector->id)
        ->and($invoice->offer_amount_cents)->toBe(30000)
        ->and($invoice->commission_cents)->toBe(3000)
        ->and((float) $invoice->commission_percent)->toBe(10.0)
        ->and($invoice->due_date->toDateString())->toBe(now()->addDays(14)->toDateString())
        ->and($invoice->pdf_path)->not->toBeNull();
    Storage::disk('local')->assertExists($invoice->pdf_path);

    // All three acceptance emails plus the losing-provider notification went out.
    Mail::assertQueued(BookingConfirmedCustomerMail::class);
    Mail::assertQueued(BookingConfirmedInspectorMail::class);
    Mail::assertQueued(CommissionInvoiceMail::class);
    Mail::assertQueued(OfferNotSelectedMail::class);

    // The offer can't be accepted twice.
    $this->actingAs($customer)->post("/account/offers/{$offer->id}/accept")
        ->assertSessionHasErrors('offer');
    expect(Booking::where('offer_id', $offer->id)->count())->toBe(1);
});
