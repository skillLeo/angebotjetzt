<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Post-completion 1-10 satisfaction survey, reached via a signed emailed
 * link (no login required — the signature itself proves the recipient).
 * A high rating (8-10) routes to Trustpilot to leave a public review; a
 * lower one is kept as private internal feedback for the team, never as a
 * public testimonial (Review.rating is a live 1-5 star scale elsewhere on
 * the site, so the raw 1-10 answer is mapped down, and rows from this path
 * are always unpublished).
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

        Review::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'inspector_id' => $booking->inspector_id,
                'rating' => (int) ceil($data['rating'] / 2),
                'comment' => $data['comment'] ?? null,
                'is_published' => false,
            ]
        );

        if ($data['rating'] >= 8) {
            return redirect()->away(config('services.trustpilot.review_url'));
        }

        return redirect()->route('reviews.survey.thanks');
    }
}
