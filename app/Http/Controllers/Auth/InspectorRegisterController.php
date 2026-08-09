<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InspectorVerifyEmailMail;
use App\Models\ActivityLog;
use App\Models\Inspector;
use App\Models\RequestMatch;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\Wallet;
use App\Support\SafeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class InspectorRegisterController extends Controller
{
    public function show(Request $request): Response
    {
        $requestId = $request->query('request');
        $email = $request->query('email');
        $serviceCategoryId = $requestId
            ? ServiceRequest::find($requestId)?->serviceType?->service_category_id
            : null;

        // Only for this specific path: someone who already submitted a guest
        // offer via an admin's invite link, and is now completing a full
        // account under the same email, shouldn't have to retype what they
        // already gave us. Any other visit to this form (no request_id, or a
        // different/unclaimed email) gets no such lookup.
        $placeholder = $requestId && $email
            ? Inspector::where('email', $email)
                ->where('imported_from', 'guest_offer')
                ->whereNull('profile_completed_at')
                ->first()
            : null;

        return Inertia::render('auth/InspectorRegister', [
            'categories' => ServiceCategory::orderBy('sort_order')->get(['id', 'name']),
            'prefill' => [
                'email' => $email,
                'requestId' => $requestId,
                'serviceCategoryId' => $serviceCategoryId,
                'name' => $placeholder?->name,
                'companyName' => $placeholder?->company_name,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', 'min:8'],
            'agb' => ['accepted'],
            'request_id' => ['nullable', 'integer', 'exists:requests,id'],
        ], [
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
        ]);

        // A guest who already submitted an offer via an email invite (without
        // registering) has an unclaimed placeholder account waiting under this
        // email — claim and complete it instead of erroring on a duplicate.
        $placeholder = Inspector::where('email', $data['email'])
            ->where('imported_from', 'guest_offer')
            ->whereNull('profile_completed_at')
            ->first();

        if (! $placeholder && Inspector::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'Für diese E-Mail-Adresse existiert bereits ein Dienstleister-Konto.'])->withInput();
        }

        // Registering via an admin's invite link for a specific request already
        // proves inbox access (they clicked a signed link emailed to exactly
        // this address), so that path skips the separate email-verification step.
        $viaInvite = ! empty($data['request_id']);

        $attributes = [
            'service_category_id' => $data['service_category_id'],
            'company_name' => $data['company_name'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
            // Claiming a placeholder created via the admin-invited guest-offer
            // flow keeps whatever approval status it already had (bypassed
            // approval, per that flow's own vetting rule) instead of
            // resetting it — a brand-new registration still starts false.
            'is_approved' => $placeholder?->is_approved ?? false,
            'is_verified' => false,
            'member_since' => $placeholder?->member_since ?? now(),
            'email_verified_at' => $viaInvite ? now() : null,
        ];

        $inspector = $placeholder
            ? tap($placeholder)->update($attributes)
            : Inspector::create($attributes);

        Wallet::firstOrCreate(['inspector_id' => $inspector->id]);
        ActivityLog::record($placeholder ? 'inspector.claimed' : 'inspector.registered', $inspector);

        Auth::guard('inspector')->login($inspector);

        if ($viaInvite) {
            $serviceRequest = ServiceRequest::find($data['request_id']);

            if ($serviceRequest) {
                RequestMatch::firstOrCreate(
                    ['request_id' => $serviceRequest->id, 'inspector_id' => $inspector->id],
                    ['notified_at' => now()]
                );

                // Profile completion still applies — remember the request so
                // they land on it once that's done instead of the dashboard.
                session(['inspector_pending_request_id' => $serviceRequest->id]);
            }

            return redirect()->route('inspector.onboarding.profile')
                ->with('success', 'Ihr Dienstleister-Konto wurde erstellt.');
        }

        $signedLink = URL::temporarySignedRoute(
            'inspector.verification.verify',
            now()->addDays(14),
            ['inspector' => $inspector->id]
        );
        SafeMailer::send(fn () => Mail::to($inspector->email)->queue(new InspectorVerifyEmailMail($inspector, $signedLink)));

        return redirect()->route('inspector.verification.notice');
    }

    public function confirmEmail(Inspector $inspector): RedirectResponse
    {
        if (! $inspector->email_verified_at) {
            $inspector->update(['email_verified_at' => now()]);
        }

        Auth::guard('inspector')->login($inspector);

        return redirect()->route('inspector.onboarding.profile')->with('success', 'E-Mail-Adresse bestätigt.');
    }

    public function acceptInvite(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $email = (string) $request->query('email');
        $inspector = Inspector::where('email', $email)->first();
        $isUnclaimedPlaceholder = $inspector && $inspector->imported_from === 'guest_offer' && ! $inspector->profile_completed_at;

        if (! $inspector || $isUnclaimedPlaceholder) {
            // No account at all yet — let them submit a real offer straight
            // away without registering first; they can turn this into a full
            // account afterward. An unclaimed guest-offer placeholder (if this
            // email already submitted a guest offer before) also lands here.
            $request->session()->put("guest_offer_access_{$serviceRequest->id}", $email);

            return redirect()->route('inspector.guest-offer.create', $serviceRequest->id);
        }

        Auth::guard('inspector')->login($inspector);

        RequestMatch::firstOrCreate(
            ['request_id' => $serviceRequest->id, 'inspector_id' => $inspector->id],
            ['notified_at' => now()]
        );

        return redirect()->route('inspector.requests.show', $serviceRequest->id);
    }
}
