<?php

use App\Mail\LowRatingFeedbackMail;
use App\Mail\ReviewRequestMail;
use App\Models\Booking;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\Review;
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

function makeReviewTestBooking(): Booking
{
    static $seq = 0;
    $seq++;

    $category = ServiceCategory::firstOrCreate(['slug' => 'kfz-gutachten'], ['name' => 'Kfz', 'is_active' => true]);
    $type = ServiceType::firstOrCreate(
        ['slug' => 'unfallschadengutachten'],
        ['service_category_id' => $category->id, 'name' => 'Unfallschadengutachten', 'is_active' => true]
    );

    $user = User::factory()->create();
    $inspector = Inspector::create([
        'name' => 'Test Dienstleister '.$seq,
        'email' => "reviewtest{$seq}@example.de",
        'password' => Hash::make('secret12'),
        'city' => 'Köln',
        'is_active' => true,
        'is_approved' => true,
        'is_verified' => true,
        'email_verified_at' => now(),
    ]);
    Wallet::create(['inspector_id' => $inspector->id]);

    $request = ServiceRequest::create([
        'request_number' => 'AJ-2026-RT'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
        'user_id' => $user->id,
        'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
        'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => $user->name, 'contact_email' => $user->email, 'contact_phone' => '+49123',
        'status' => 'completed',
    ]);

    $offer = Offer::create([
        'request_id' => $request->id, 'inspector_id' => $inspector->id,
        'price_cents' => 30000, 'commission_cents' => 3000, 'inspector_cents' => 27000,
        'status' => 'accepted',
    ]);

    return Booking::create([
        'booking_number' => 'AJB-2026-RT'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
        'request_id' => $request->id, 'offer_id' => $offer->id,
        'user_id' => $user->id, 'inspector_id' => $inspector->id,
        'status' => 'confirmed', 'confirmed_at' => now(),
    ]);
}

it('maps every 1-10 rating band to the exact specified star value and keeps the raw value', function (int $raw, int $expectedStars) {
    $booking = makeReviewTestBooking();
    $signedLink = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $booking->id]);
    $path = parse_url($signedLink, PHP_URL_PATH).'?'.parse_url($signedLink, PHP_URL_QUERY);

    $this->post($path, ['rating' => $raw, 'comment' => 'Test-Feedback '.$raw]);

    $review = Review::where('booking_id', $booking->id)->first();

    expect($review)->not->toBeNull()
        ->and($review->raw_rating)->toBe($raw)
        ->and($review->rating)->toBe($expectedStars)
        ->and($review->is_published)->toBeFalse();
})->with([
    'raw 1 -> 1 star' => [1, 1],
    'raw 2 -> 2 stars' => [2, 2],
    'raw 3 -> 2 stars' => [3, 2],
    'raw 4 -> 3 stars' => [4, 3],
    'raw 5 -> 3 stars' => [5, 3],
    'raw 6 -> 4 stars' => [6, 4],
    'raw 7 -> 4 stars' => [7, 4],
    'raw 8 -> 5 stars' => [8, 5],
    'raw 9 -> 5 stars' => [9, 5],
    'raw 10 -> 5 stars' => [10, 5],
]);

it('redirects ratings 8-10 to Trustpilot and 1-7 to the internal thanks page', function () {
    $happy = makeReviewTestBooking();
    $link = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $happy->id]);
    $path = parse_url($link, PHP_URL_PATH).'?'.parse_url($link, PHP_URL_QUERY);
    $this->post($path, ['rating' => 9])->assertRedirect(config('services.trustpilot.review_url'));

    $unhappy = makeReviewTestBooking();
    $link2 = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $unhappy->id]);
    $path2 = parse_url($link2, PHP_URL_PATH).'?'.parse_url($link2, PHP_URL_QUERY);
    $this->post($path2, ['rating' => 5])->assertRedirect(route('reviews.survey.thanks'));
});

it('emails admin for ratings 1-7 but not for ratings 8-10', function () {
    Mail::fake();

    $unhappy = makeReviewTestBooking();
    $link = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $unhappy->id]);
    $path = parse_url($link, PHP_URL_PATH).'?'.parse_url($link, PHP_URL_QUERY);
    $this->post($path, ['rating' => 3, 'comment' => 'Es lief nicht gut.']);

    Mail::assertQueued(LowRatingFeedbackMail::class, function ($mail) use ($unhappy) {
        return $mail->booking->id === $unhappy->id && $mail->rawRating === 3 && $mail->comment === 'Es lief nicht gut.';
    });

    $happy = makeReviewTestBooking();
    $link2 = URL::temporarySignedRoute('reviews.survey.show', now()->addDays(30), ['booking' => $happy->id]);
    $path2 = parse_url($link2, PHP_URL_PATH).'?'.parse_url($link2, PHP_URL_QUERY);
    $this->post($path2, ['rating' => 10]);

    Mail::assertNotQueued(LowRatingFeedbackMail::class, fn ($mail) => $mail->booking->id === $happy->id);
});

it('lets a provider average include unpublished survey-based reviews', function () {
    $booking = makeReviewTestBooking();
    Review::create([
        'booking_id' => $booking->id, 'user_id' => $booking->user_id, 'inspector_id' => $booking->inspector_id,
        'rating' => 3, 'raw_rating' => 4, 'is_published' => false,
    ]);

    $booking->inspector->refresh();
    expect($booking->inspector->averageRating())->toBe(3.0);
});

it('lets the customer confirm completion without any rating input, and always triggers the review email', function () {
    Mail::fake();

    $booking = makeReviewTestBooking();
    $booking->update(['status' => 'completed_by_inspector']);

    $this->actingAs($booking->user)
        ->post("/account/bookings/{$booking->id}/confirm")
        ->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe('confirmed')
        ->and($booking->request->fresh()->status)->toBe('completed')
        ->and(Review::where('booking_id', $booking->id)->exists())->toBeFalse();

    Mail::assertQueued(ReviewRequestMail::class, fn ($mail) => $mail->booking->id === $booking->id);
});

it('has no quick star-rating endpoint left for customers', function () {
    $booking = makeReviewTestBooking();
    $booking->update(['status' => 'completed_by_inspector']);

    $this->actingAs($booking->user)
        ->post("/account/bookings/{$booking->id}/review", ['rating' => 5])
        ->assertNotFound();
});
