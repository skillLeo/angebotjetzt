<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Mail\NewOfferMail;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Inspector;
use App\Models\Offer;
use App\Models\RequestMatch;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Services\CommissionService;
use App\Services\RequestService;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Lets someone who received an admin's request-invite email, but has no
 * account yet, submit one real offer without registering first. A
 * placeholder Inspector row is created (unapproved, marked
 * imported_from=guest_offer) so the offer satisfies the normal offers.
 * inspector_id constraint; they can turn it into a full account afterward
 * via the ordinary registration form, which recognizes and claims it.
 */
class GuestOfferController extends Controller
{
    public function create(Request $request, ServiceRequest $serviceRequest): Response
    {
        $email = $this->guestEmail($request, $serviceRequest);
        abort_unless(in_array($serviceRequest->status, ['open', 'offers_received'], true), 404);

        $serviceRequest->load('serviceType:id,name');

        return Inertia::render('inspector/GuestOffer', [
            'email' => $email,
            'commissionPercent' => Setting::commissionPercent(),
            'request' => [
                'id' => $serviceRequest->id,
                'number' => $serviceRequest->request_number,
                'service' => $serviceRequest->serviceType->name,
                'vehicle' => trim("{$serviceRequest->vehicle_make} {$serviceRequest->vehicle_model}"),
                'ort' => $serviceRequest->ort,
            ],
        ]);
    }

    public function store(Request $httpRequest, ServiceRequest $serviceRequest, CommissionService $commission): RedirectResponse
    {
        $email = $this->guestEmail($httpRequest, $serviceRequest);
        abort_unless(in_array($serviceRequest->status, ['open', 'offers_received'], true), 404);

        $data = $httpRequest->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'price' => ['required', 'numeric', 'min:10', 'max:100000'],
            'estimated_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:2000'],
            'agb' => ['accepted'],
        ], [
            'price.min' => 'Der Angebotspreis muss mindestens 10 € betragen.',
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
        ]);

        // This inspector only exists because an admin personally chose to
        // invite this exact email address for this exact request (the only
        // way to reach this controller — see guestEmail() below) — that
        // manual, per-request invitation is treated as the vetting step, so
        // unlike a self-registered or CSV-imported inspector, their offer
        // doesn't wait on a separate admin approval to become acceptable.
        $inspector = Inspector::firstOrCreate(
            ['email' => $email],
            [
                'name' => $data['name'],
                'company_name' => $data['company_name'] ?? null,
                'password' => Str::random(60),
                'service_category_id' => $serviceRequest->serviceType->service_category_id,
                'is_active' => true,
                'is_approved' => true,
                'is_verified' => false,
                'imported_from' => 'guest_offer',
                'member_since' => now(),
            ]
        );

        if (Offer::where('request_id', $serviceRequest->id)->where('inspector_id', $inspector->id)->exists()) {
            return back()->withErrors(['price' => 'Sie haben für diese Anfrage bereits ein Angebot abgegeben.']);
        }

        RequestMatch::firstOrCreate(
            ['request_id' => $serviceRequest->id, 'inspector_id' => $inspector->id],
            ['notified_at' => now()]
        );

        $priceCents = (int) round($data['price'] * 100);
        $split = $commission->split($priceCents);
        $isFirstOffer = $serviceRequest->status === 'open';

        $offer = Offer::create([
            'request_id' => $serviceRequest->id,
            'inspector_id' => $inspector->id,
            'price_cents' => $priceCents,
            'commission_cents' => $split['commission'],
            'inspector_cents' => $split['inspector'],
            'message' => $data['message'] ?? null,
            'estimated_date' => $data['estimated_date'] ?? null,
            'status' => 'open',
            'expires_at' => $serviceRequest->expires_at,
        ]);

        $serviceRequest->update(['status' => 'offers_received']);

        $label = $serviceRequest->offerLabel($offer->id);

        if ($serviceRequest->user) {
            AppNotification::notify($serviceRequest->user, 'new_offer',
                'Neues Angebot erhalten',
                "{$label} hat ein Angebot für Ihre Anfrage {$serviceRequest->request_number} abgegeben.",
                "/account/requests/{$serviceRequest->id}/offers");
        }

        SafeMailer::send(fn () => Mail::to($serviceRequest->contact_email)->queue(new NewOfferMail($serviceRequest, $inspector, $label)));

        if ($isFirstOffer) {
            app(RequestService::class)->recordFirstOffer($serviceRequest);
        }

        ActivityLog::record('offer.submitted_guest', null, $serviceRequest, ['inspector_id' => $inspector->id]);

        $httpRequest->session()->forget("guest_offer_access_{$serviceRequest->id}");

        return redirect()->route('gutachter.register', ['email' => $email, 'request' => $serviceRequest->id])
            ->with('success', 'Ihr Angebot wurde übermittelt. Der Kunde wurde benachrichtigt. Vervollständigen Sie jetzt Ihr Konto, um weitere Anfragen zu erhalten.');
    }

    private function guestEmail(Request $request, ServiceRequest $serviceRequest): string
    {
        $email = $request->session()->get("guest_offer_access_{$serviceRequest->id}");
        abort_unless(is_string($email) && $email !== '', 403);

        return $email;
    }
}
