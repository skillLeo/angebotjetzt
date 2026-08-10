<?php

use App\Mail\NewOfferMail;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('shows the real offer price directly in the new-offer notification email body', function () {
    Mail::fake();

    $inspector = checklistInspector();
    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-PRICE01', 'service_type_id' => checklistServiceType()->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'Preis Kunde', 'contact_email' => 'price_test@example.de', 'contact_phone' => '+49123',
        'status' => 'open',
    ]);
    RequestMatch::create(['request_id' => $request->id, 'inspector_id' => $inspector->id, 'notified_at' => now()]);

    $this->actingAs($inspector, 'inspector')->post("/inspector/requests/{$request->id}/offer", [
        'price' => 349.50,
        'message' => 'Testangebot',
    ])->assertRedirect();

    Mail::assertQueued(NewOfferMail::class, function ($mail) {
        $rendered = $mail->render();

        return str_contains($rendered, '349,50') && str_contains($rendered, '€');
    });
});
