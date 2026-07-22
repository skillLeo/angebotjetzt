<?php

use App\Mail\NewRequestNotificationMail;
use App\Mail\RequestConfirmationMail;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('lets a guest submit a request, creates an account, matches inspectors and notifies them', function () {
    Mail::fake();
    $type = ServiceType::where('slug', 'unfallschadengutachten')->first();

    $response = $this->post('/anfrage', [
        'service_type_id' => $type->id,
        'vehicle_make' => 'Audi', 'vehicle_model' => 'A4',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Neuer Kunde', 'contact_email' => 'neu-'.uniqid().'@example.de',
        'contact_phone' => '+49 170 1234567',
        'agb' => true, 'privacy' => true,
    ]);

    $request = ServiceRequest::latest('id')->first();

    expect($request->matched_count)->toBeGreaterThan(0)
        ->and($request->matches()->count())->toBe($request->matched_count);

    $response->assertRedirect(route('wizard.confirmation', $request->request_number));

    // A guest account was created and the confirmation + inspector emails were queued.
    expect(User::where('email', $request->contact_email)->exists())->toBeTrue();
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

it('processes a Stripe checkout.session.completed webhook idempotently', function () {
    $inspector = Inspector::has('matches')->first();
    $request = $inspector->matches()->first()->request;
    $request->update(['status' => 'offers_received']);
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

    $payload = [
        'id' => 'evt_test_'.uniqid(),
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_test_'.uniqid(),
            'payment_intent' => 'pi_test_123',
            'metadata' => ['offer_id' => (string) $offer->id, 'request_id' => (string) $request->id, 'user_id' => (string) $request->user_id],
        ]],
    ];

    $this->postJson('/stripe/webhook', $payload)->assertOk();

    $booking = Booking::where('offer_id', $offer->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe('paid')
        ->and($offer->fresh()->status)->toBe('accepted')
        ->and($rejected->fresh()->status)->toBe('rejected');

    $payment = Payment::where('booking_id', $booking->id)->first();
    expect($payment->commission_cents + $payment->inspector_cents)->toBe($payment->total_cents);

    // Inspector share is credited as pending.
    expect($inspector->wallet->fresh()->pending_cents)->toBe(27000);

    // Replaying the same event must not create a second booking.
    $this->postJson('/stripe/webhook', $payload)->assertOk();
    expect(Booking::where('offer_id', $offer->id)->count())->toBe(1);
});
