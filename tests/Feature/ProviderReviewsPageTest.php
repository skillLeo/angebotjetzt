<?php

use App\Models\Booking;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('shows a real customer-submitted review on the provider reviews page, correctly star-mapped', function () {
    Mail::fake();
    $type = checklistServiceType();
    $inspector = checklistInspector();
    $user = User::factory()->create();

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-REV0001', 'user_id' => $user->id, 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => $user->name, 'contact_email' => $user->email, 'contact_phone' => '+49123',
        'status' => 'accepted',
    ]);
    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 30000, 'commission_cents' => 3000, 'inspector_cents' => 27000, 'status' => 'accepted',
    ]);
    $booking = Booking::create([
        'booking_number' => Booking::nextBookingNumber(), 'request_id' => $request->id, 'offer_id' => $offer->id,
        'user_id' => $user->id, 'inspector_id' => $inspector->id, 'status' => 'completed_by_inspector',
    ]);

    $surveyUrl = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $booking->id]);
    $this->get($surveyUrl)->assertOk();

    // A real submission through the real endpoint — rating 6 -> maps to 4 stars.
    $this->post("/reviews/{$booking->id}/survey", [
        'rating' => 6,
        'comment' => 'Insgesamt gut, aber die Terminfindung hat etwas gedauert.',
    ])->assertRedirect();

    $this->actingAs($inspector, 'inspector')->get('/inspector/reviews')->assertInertia(fn ($page) => $page
        ->where('reviewsCount', 1)
        ->where('averageRating', 4)
        ->where('reviews.data.0.rating', 4)
        ->where('reviews.data.0.rawRating', 6)
        ->where('reviews.data.0.comment', 'Insgesamt gut, aber die Terminfindung hat etwas gedauert.')
        ->where('reviews.data.0.service', $type->name)
        ->where('reviews.data.0.requestNumber', 'AJ-2026-REV0001'));
});
