<?php

namespace App\Http\Controllers;

use App\Mail\LowRatingFeedbackMail;
use App\Models\Booking;
use App\Models\Review;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Post-completion 1-10 satisfaction survey, reached via a signed emailed
 * link (no login required — the signature itself proves the recipient).
 * A high rating (8-10) routes to Trustpilot to leave a public review; a
 * lower one is kept as private internal feedback for the team, never as a
 * public testimonial. The raw 1-10 value is what drives that branching and
 * is kept in `raw_rating`; `rating` stores the 1-5 star equivalent used for
 * display and the provider's average (see mapToStars()) — rows from this
 * path are always unpublished, so they never appear as a public testimonial
 * quote, but still count toward the average (see Inspector::averageRating()).
 */
class ReviewSurveyController extends Controller
{
    public function show(Booking $booking): Response|RedirectResponse
    {
        if ($booking->review) {
            return redirect('/')->with('success', 'Sie haben für diesen Auftrag bereits eine Bewertung abgegeben. Vielen Dank!');
        }

        $booking->load('request.serviceType:id,name');

        return Inertia::render('public/ReviewSurvey', [
            'booking' => [
                'id' => $booking->id,
                'number' => $booking->booking_number,
                'service' => $booking->request->serviceType->name,
            ],
        ]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ]);

        $review = Review::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'inspector_id' => $booking->inspector_id,
                'rating' => $this->mapToStars($data['rating']),
                'raw_rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_published' => false,
            ]
        );

        // Only for a genuinely new submission — firstOrCreate() means a
        // revisit of an already-answered survey (the show() guard above
        // normally prevents this, but the POST itself has no such guard)
        // would otherwise re-notify admin about feedback they've already seen.
        if ($review->wasRecentlyCreated && $data['rating'] <= 7) {
            $booking->loadMissing(['inspector', 'user']);
            SafeMailer::send(fn () => Mail::to(config('mail.from.address'))
                ->queue(new LowRatingFeedbackMail($booking, $data['rating'], $data['comment'] ?? null)));
        }

        if ($data['rating'] >= 8) {
            return redirect()->away(config('services.trustpilot.review_url'));
        }

        return redirect()->route('reviews.survey.thanks');
    }

    /**
     * 1-10 customer rating -> 1-5 star equivalent used for display/averages.
     * Exact bands as specified: 8-10=5, 6-7=4, 4-5=3, 2-3=2, 0-1=1.
     */
    private function mapToStars(int $raw): int
    {
        return match (true) {
            $raw >= 8 => 5,
            $raw >= 6 => 4,
            $raw >= 4 => 3,
            $raw >= 2 => 2,
            default => 1,
        };
    }
}
