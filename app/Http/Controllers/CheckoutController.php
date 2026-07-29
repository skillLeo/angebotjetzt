<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmedCustomerMail;
use App\Mail\BookingConfirmedInspectorMail;
use App\Mail\OfferNotSelectedMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Offer;
use App\Models\Payment;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Stripe\Webhook;

class CheckoutController extends Controller
{
    /**
     * Accepting an offer immediately creates a Stripe Checkout session —
     * payment happens in the same step, never afterwards.
     */
    public function accept(Request $request, Offer $offer): RedirectResponse|HttpResponse
    {
        $serviceRequest = $offer->request;

        abort_unless($serviceRequest->user_id === Auth::id(), 403);

        if ($offer->status !== 'open' || in_array($serviceRequest->status, ['accepted', 'completed'], true)) {
            return back()->withErrors(['offer' => 'Dieses Angebot kann nicht mehr angenommen werden.']);
        }

        $secret = config('cashier.secret') ?: env('STRIPE_SECRET');

        if (! $secret) {
            return back()->withErrors(['offer' => 'Die Zahlungsabwicklung ist noch nicht konfiguriert. Bitte kontaktieren Sie den Support.']);
        }

        Stripe::setApiKey($secret);

        $session = StripeSession::create([
            'mode' => 'payment',
            'customer_email' => Auth::user()->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $offer->price_cents,
                    'product_data' => [
                        'name' => $serviceRequest->serviceType->name.' — '.$serviceRequest->vehicle_make.' '.$serviceRequest->vehicle_model,
                        'description' => 'Anfrage '.$serviceRequest->request_number.' · Gutachter: '.$offer->inspector->name,
                    ],
                ],
            ]],
            'metadata' => [
                'offer_id' => (string) $offer->id,
                'request_id' => (string) $serviceRequest->id,
                'user_id' => (string) Auth::id(),
            ],
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancelled'),
        ]);

        return Inertia::location($session->url);
    }

    public function success(Request $request): Response
    {
        return Inertia::render('customer/CheckoutSuccess', [
            'sessionId' => $request->query('session_id'),
        ]);
    }

    public function cancelled(): Response
    {
        return Inertia::render('customer/CheckoutCancelled');
    }

    /**
     * Stripe webhook: signature-verified and idempotent per event ID.
     */
    public function webhook(Request $request, WalletService $walletService): HttpResponse
    {
        $secret = config('cashier.webhook.secret') ?: env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = $secret
                ? Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), $secret)
                : \Stripe\Event::constructFrom(json_decode($request->getContent(), true));
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature rejected', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        // Idempotency: process each Stripe event exactly once.
        if (! Cache::add('stripe_event_'.$event->id, true, now()->addDays(3))) {
            return response('Already processed', 200);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object, $walletService);
        }

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(object $session, WalletService $walletService): void
    {
        $offer = Offer::with(['request.serviceType', 'inspector'])->find($session->metadata->offer_id ?? null);

        if (! $offer) {
            Log::error('Stripe webhook: offer not found', ['session' => $session->id]);

            return;
        }

        if (Payment::where('stripe_session_id', $session->id)->exists()) {
            return;
        }

        $losingOffers = Offer::where('request_id', $offer->request_id)
            ->where('id', '!=', $offer->id)
            ->where('status', 'open')
            ->with(['inspector', 'request.serviceType'])
            ->get();

        $booking = DB::transaction(function () use ($offer, $session, $losingOffers) {
            $offer->update(['status' => 'accepted']);

            Offer::whereIn('id', $losingOffers->pluck('id'))->update(['status' => 'rejected']);

            $offer->request->update(['status' => 'accepted']);

            $booking = Booking::create([
                'booking_number' => Booking::nextBookingNumber(),
                'request_id' => $offer->request_id,
                'offer_id' => $offer->id,
                'user_id' => $offer->request->user_id,
                'inspector_id' => $offer->inspector_id,
                'status' => 'paid',
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'total_cents' => $offer->price_cents,
                'commission_cents' => $offer->commission_cents,
                'inspector_cents' => $offer->inspector_cents,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $booking;
        });

        $walletService->creditPending($booking);

        AppNotification::notify($offer->inspector, 'offer_accepted',
            'Ihr Angebot wurde angenommen',
            "Auftrag {$booking->booking_number} · {$offer->request->vehicle_make} {$offer->request->vehicle_model} in {$offer->request->ort}",
            "/inspector/jobs/{$booking->id}");

        AppNotification::notify($booking->user, 'booking_paid',
            'Zahlung erfolgreich — Auftrag bestätigt',
            "Auftrag {$booking->booking_number} bei {$offer->inspector->name}",
            "/account/bookings/{$booking->id}");

        Mail::to($booking->user->email)->queue(new BookingConfirmedCustomerMail($booking));
        Mail::to($offer->inspector->email)->queue(new BookingConfirmedInspectorMail($booking));

        foreach ($losingOffers as $losingOffer) {
            AppNotification::notify($losingOffer->inspector, 'offer_not_selected',
                'Anfrage vergeben',
                "Auftrag {$offer->request->request_number} · {$offer->request->vehicle_make} {$offer->request->vehicle_model} wurde an einen anderen Gutachter vergeben.",
                '/inspector/offers');

            Mail::to($losingOffer->inspector->email)->queue(new OfferNotSelectedMail($losingOffer));
        }

        ActivityLog::record('booking.paid', null, $booking, [
            'total_cents' => $offer->price_cents,
            'commission_cents' => $offer->commission_cents,
        ]);
    }
}
