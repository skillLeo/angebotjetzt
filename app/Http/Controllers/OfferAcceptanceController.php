<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmedCustomerMail;
use App\Mail\BookingConfirmedInspectorMail;
use App\Mail\CommissionInvoiceMail;
use App\Mail\OfferNotSelectedMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Offer;
use App\Services\InvoiceService;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Direct-agreement model (no platform payment): accepting an offer creates a
 * binding agreement directly between customer and provider. Payment for the
 * job itself is arranged between them outside the platform; AngebotJetzt only
 * invoices the winning provider for its commission.
 */
class OfferAcceptanceController extends Controller
{
    public function accept(Request $request, Offer $offer, InvoiceService $invoices): RedirectResponse
    {
        $serviceRequest = $offer->request;

        abort_unless($serviceRequest->user_id === Auth::id(), 403);

        if ($offer->status !== 'open' || in_array($serviceRequest->status, ['accepted', 'completed'], true)) {
            return back()->withErrors(['offer' => 'Dieses Angebot kann nicht mehr angenommen werden.']);
        }

        if (! $offer->inspector->is_approved) {
            return back()->withErrors(['offer' => 'Dieser Anbieter wird noch von unserem Team überprüft. Sie können das Angebot erst annehmen, sobald die Prüfung abgeschlossen ist.']);
        }

        $losingOffers = Offer::where('request_id', $offer->request_id)
            ->where('id', '!=', $offer->id)
            ->where('status', 'open')
            ->with(['inspector', 'request.serviceType'])
            ->get();

        // Every data-level effect of accepting — offer/request status,
        // rejecting the rest, creating the booking, and generating the
        // invoice (including its PDF) — happens in one transaction. If any of
        // it fails, none of it is left half-done. Emails/notifications are
        // necessarily best-effort afterward (SafeMailer): external mail
        // delivery isn't something a database transaction can roll back.
        [$booking, $invoice] = DB::transaction(function () use ($offer, $serviceRequest, $losingOffers, $invoices) {
            $offer->update(['status' => 'accepted']);

            Offer::whereIn('id', $losingOffers->pluck('id'))->update(['status' => 'rejected']);

            $serviceRequest->update(['status' => 'accepted']);

            $booking = Booking::create([
                'booking_number' => Booking::nextBookingNumber(),
                'request_id' => $serviceRequest->id,
                'offer_id' => $offer->id,
                'user_id' => $serviceRequest->user_id,
                'inspector_id' => $offer->inspector_id,
                'status' => 'accepted',
            ]);

            $booking->setRelation('offer', $offer);
            $invoice = $invoices->generateFor($booking);

            return [$booking, $invoice];
        });

        $booking->load(['offer', 'inspector', 'user', 'request.serviceType']);

        AppNotification::notify($offer->inspector, 'offer_accepted',
            'Ihr Angebot wurde angenommen',
            "Auftrag {$serviceRequest->request_number} · {$serviceRequest->vehicle_make} {$serviceRequest->vehicle_model} in {$serviceRequest->ort}",
            "/inspector/jobs/{$booking->id}");

        AppNotification::notify($booking->user, 'booking_confirmed',
            'Auftrag bestätigt',
            "Auftrag {$serviceRequest->request_number} bei {$offer->inspector->name}",
            "/account/bookings/{$booking->id}");

        SafeMailer::send(fn () => Mail::to($booking->user->email)->queue(new BookingConfirmedCustomerMail($booking)));
        SafeMailer::send(fn () => Mail::to($offer->inspector->email)->queue(new BookingConfirmedInspectorMail($booking)));
        SafeMailer::send(fn () => Mail::to($offer->inspector->email)->queue(new CommissionInvoiceMail($invoice)));

        foreach ($losingOffers as $losingOffer) {
            AppNotification::notify($losingOffer->inspector, 'offer_not_selected',
                'Anfrage vergeben',
                "Auftrag {$serviceRequest->request_number} · {$serviceRequest->vehicle_make} {$serviceRequest->vehicle_model} wurde an einen anderen Dienstleister vergeben.",
                '/inspector/offers');

            SafeMailer::send(fn () => Mail::to($losingOffer->inspector->email)->queue(new OfferNotSelectedMail($losingOffer)));
        }

        ActivityLog::record('booking.accepted', Auth::user(), $booking, [
            'total_cents' => $offer->price_cents,
            'commission_cents' => $offer->commission_cents,
        ]);

        return redirect()->route('konto.bookings.show', $booking->id)
            ->with('success', 'Auftrag bestätigt! Der Dienstleister wird sich in Kürze bei Ihnen melden. Die Kontaktdaten finden Sie unten.');
    }
}
