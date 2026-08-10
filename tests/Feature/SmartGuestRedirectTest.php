<?php

use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

function smartRedirectRequest(string $number, string $email): ServiceRequest
{
    return ServiceRequest::create([
        'request_number' => $number, 'service_type_id' => checklistServiceType()->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Guest Person', 'contact_email' => $email, 'contact_phone' => '+49123',
        'status' => 'open',
    ]);
}

// --- Part 1: "View My Requests" (RequestConfirmationMail's CTA) ---

it('sends a guest with no account from the "view my requests" link to registration, pre-filled', function () {
    $request = smartRedirectRequest('AJ-2026-SR0001', 'sr_guest1@example.de');

    $url = $request->myRequestsViewUrl();
    expect($url)->toContain('/requests/'.$request->id.'/view');

    $response = $this->get($url);
    $response->assertRedirect();
    $location = $response->headers->get('Location');
    expect($location)->toContain('/register')
        ->toContain('email='.urlencode('sr_guest1@example.de'))
        ->toContain('name=Guest');
});

it('sends a "view my requests" click to login when an account already exists for that email', function () {
    User::factory()->create(['email' => 'sr_existing@example.de']);
    $request = smartRedirectRequest('AJ-2026-SR0002', 'sr_existing@example.de');

    $this->get($request->myRequestsViewUrl())->assertRedirect(route('login'));
});

it('sends an already-authenticated user straight to their requests list', function () {
    $user = User::factory()->create();
    $request = smartRedirectRequest('AJ-2026-SR0003', 'sr_authed@example.de');

    $this->actingAs($user)->get($request->myRequestsViewUrl())->assertRedirect(route('konto.requests'));
});

it('rejects a tampered/unsigned "view my requests" link', function () {
    $request = smartRedirectRequest('AJ-2026-SR0004', 'sr_guest4@example.de');

    $this->get("/requests/{$request->id}/view")->assertForbidden();
});

// --- Part 2: "View Offer" (NewOfferMail's CTA, offers.view route) — same logic, separate code path ---

it('sends a guest with no account from the "view offer" link to registration, pre-filled', function () {
    $inspector = checklistInspector();
    $request = smartRedirectRequest('AJ-2026-SR0005', 'sr_guest5@example.de');
    RequestMatch::create(['request_id' => $request->id, 'inspector_id' => $inspector->id, 'notified_at' => now()]);
    Offer::create(['request_id' => $request->id, 'inspector_id' => $inspector->id, 'price_cents' => 10000, 'commission_cents' => 1000, 'inspector_cents' => 9000, 'status' => 'open']);

    $response = $this->get($request->offersViewUrl());
    $response->assertRedirect();
    $location = $response->headers->get('Location');
    expect($location)->toContain('/register')
        ->toContain('email='.urlencode('sr_guest5@example.de'));
});

it('sends a "view offer" click to login when an account already exists for that email', function () {
    User::factory()->create(['email' => 'sr_existing2@example.de']);
    $request = smartRedirectRequest('AJ-2026-SR0006', 'sr_existing2@example.de');

    $this->get($request->offersViewUrl())->assertRedirect(route('login'));
});

it('sends an already-authenticated user straight to compare offers', function () {
    $user = User::factory()->create();
    $request = smartRedirectRequest('AJ-2026-SR0007', 'sr_authed2@example.de');

    $this->actingAs($user)->get($request->offersViewUrl())
        ->assertRedirect(route('konto.requests.offers', $request->id));
});

// --- Registration page actually renders the pre-filled fields ---

it('renders the registration page with name and email pre-filled from the query string', function () {
    $response = $this->get('/register?'.http_build_query(['name' => 'Guest Person', 'email' => 'prefill@example.de']));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->where('prefillName', 'Guest Person')
        ->where('prefillEmail', 'prefill@example.de'));
});
