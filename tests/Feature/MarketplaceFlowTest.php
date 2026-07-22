<?php

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\PayoutRequest;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CommissionService;
use App\Services\MatchingService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeAutomotiveType(): ServiceType
{
    $cat = ServiceCategory::create(['name' => 'Kfz', 'slug' => 'kfz-gutachten', 'is_active' => true]);

    return ServiceType::create([
        'service_category_id' => $cat->id,
        'name' => 'Unfallschadengutachten',
        'slug' => 'unfallschadengutachten',
        'is_active' => true,
    ]);
}

function makeInspector(string $city, int $from, int $to): Inspector
{
    $inspector = Inspector::create([
        'name' => 'Test Gutachter '.$city,
        'email' => strtolower($city).uniqid().'@example.de',
        'password' => Hash::make('secret12'),
        'city' => $city,
        'is_active' => true,
        'is_verified' => true,
        'email_verified_at' => now(),
    ]);
    $inspector->serviceAreas()->create(['type' => 'city', 'city_name' => $city]);
    $inspector->serviceAreas()->create(['type' => 'postal_range', 'postal_from' => $from, 'postal_to' => $to]);
    Wallet::create(['inspector_id' => $inspector->id]);

    return $inspector;
}

it('splits commission exactly to the cent', function () {
    Setting::set('commission_percent', 10);
    $split = app(CommissionService::class)->split(29900);

    expect($split['commission'])->toBe(2990)
        ->and($split['inspector'])->toBe(26910)
        ->and($split['commission'] + $split['inspector'])->toBe(29900);
});

it('matches inspectors by city and postal range', function () {
    $type = makeAutomotiveType();
    $koeln = makeInspector('Köln', 50000, 51999);
    $berlin = makeInspector('Berlin', 10000, 14999);

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-000001',
        'user_id' => User::factory()->create()->id,
        'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Test', 'contact_email' => 't@e.de', 'contact_phone' => '+49123',
        'status' => 'open',
    ]);

    $matched = app(MatchingService::class)->match($request);

    expect($matched->pluck('id'))->toContain($koeln->id)
        ->not->toContain($berlin->id);
});

it('runs the full money cycle: offer, payment, wallet credit, release, payout', function () {
    Setting::set('commission_percent', 10);
    $type = makeAutomotiveType();
    $inspector = makeInspector('Köln', 50000, 51999);
    $user = User::factory()->create();

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-000002',
        'user_id' => $user->id, 'service_type_id' => $type->id,
        'vehicle_make' => 'BMW', 'vehicle_model' => '320d',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Test', 'contact_email' => 't@e.de', 'contact_phone' => '+49123',
        'status' => 'open',
    ]);
    $request->matches()->create(['inspector_id' => $inspector->id, 'notified_at' => now()]);

    $split = app(CommissionService::class)->split(40000);
    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 40000, 'commission_cents' => $split['commission'],
        'inspector_cents' => $split['inspector'], 'status' => 'open',
    ]);

    // Simulate a paid booking (what the webhook produces).
    $booking = Booking::create([
        'booking_number' => 'AJB-2026-000001',
        'request_id' => $request->id, 'offer_id' => $offer->id,
        'user_id' => $user->id, 'inspector_id' => $inspector->id, 'status' => 'paid',
    ]);
    Payment::create([
        'booking_id' => $booking->id, 'total_cents' => 40000,
        'commission_cents' => $split['commission'], 'inspector_cents' => $split['inspector'],
        'status' => 'paid', 'paid_at' => now(),
    ]);

    $wallet = app(WalletService::class);
    $wallet->creditPending($booking);

    $inspector->wallet->refresh();
    expect($inspector->wallet->pending_cents)->toBe(36000)
        ->and($inspector->wallet->available_cents)->toBe(0);

    // Inspector completes, admin confirms -> release.
    $booking->update(['status' => 'completed_by_inspector']);
    $wallet->releasePending($booking);

    $inspector->wallet->refresh();
    expect($inspector->wallet->pending_cents)->toBe(0)
        ->and($inspector->wallet->available_cents)->toBe(36000)
        ->and($inspector->wallet->lifetime_cents)->toBe(36000);

    // Payout request -> admin marks paid -> wallet debited + ledger entry.
    $payout = PayoutRequest::create([
        'inspector_id' => $inspector->id, 'amount_cents' => 30000,
        'iban' => 'DE00000000000000000000', 'account_holder' => 'Test',
        'status' => 'pending', 'requested_at' => now(),
    ]);
    $wallet->debitPayout($payout);

    $inspector->wallet->refresh();
    expect($inspector->wallet->available_cents)->toBe(6000);

    // Every movement is logged.
    expect($inspector->wallet->transactions()->count())->toBe(3);
});

it('prevents payout exceeding available balance', function () {
    $inspector = makeInspector('Köln', 50000, 51999);
    $inspector->wallet->update(['available_cents' => 5000]);

    $payout = PayoutRequest::create([
        'inspector_id' => $inspector->id, 'amount_cents' => 10000,
        'iban' => 'DE00000000000000000000', 'account_holder' => 'Test',
        'status' => 'pending', 'requested_at' => now(),
    ]);

    expect(fn () => app(WalletService::class)->debitPayout($payout))
        ->toThrow(RuntimeException::class);
});

it('rejects other guards from admin area', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/gutachter')->assertRedirect('/gutachter/login');
});

it('renders the homepage', function () {
    makeAutomotiveType();
    $this->get('/')->assertOk();
});
