<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\RequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

/**
 * The customer's "yes, the job really was done" step, reached from a signed
 * link in the completion email.
 *
 * It exists alongside the account-area confirm action rather than replacing
 * it: bookings may belong to a guest with no login, and those customers have
 * no other way to confirm.
 */
class BookingConfirmationController extends Controller
{
    public function confirm(Booking $booking, RequestService $requestService): RedirectResponse
    {
        // Already confirmed: don't error, just carry them on to the rating.
        // Re-opening the mail after confirming is the common case.
        if (in_array($booking->status, ['confirmed'], true)) {
            return redirect()->to($this->surveyUrl($booking));
        }

        if ($booking->status !== 'completed_by_inspector') {
            return redirect()->route('home')->with(
                'error',
                'Dieser Auftrag wartet derzeit nicht auf Ihre Bestätigung.'
            );
        }

        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $booking->request->update(['status' => 'completed']);

        // Still send the review request mail: if they abandon the rating
        // screen now, the link in their inbox is the way back to it.
        $requestService->sendReviewRequest($booking);

        return redirect()->to($this->surveyUrl($booking));
    }

    /**
     * Signed, and carries the flag that makes the survey page open with the
     * thank-you acknowledgement, so confirmation and rating read as one flow.
     */
    private function surveyUrl(Booking $booking): string
    {
        return URL::temporarySignedRoute(
            'reviews.survey.show',
            now()->addDays(30),
            ['booking' => $booking->id, 'confirmed' => 1]
        );
    }
}
