<?php

use App\Mail\BookingConfirmedInspectorMail;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('shows the booked service in both the provider confirmation email and the job detail page', function () {
    Mail::fake();

    $type = checklistServiceType();
    $inspector = checklistInspector();
    $user = User::factory()->create();

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-SVC0001', 'user_id' => $user->id, 'service_type_id' => $type->id,
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

    Mail::assertQueued(BookingConfirmedInspectorMail::class, fn ($mail) => str_contains($mail->render(), $type->name));

    $this->actingAs($inspector, 'inspector')->get("/inspector/jobs/{$booking->id}")->assertInertia(fn ($page) => $page
        ->where('job.service', $type->name));
});
