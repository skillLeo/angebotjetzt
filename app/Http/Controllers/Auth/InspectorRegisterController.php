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
        return Inertia::render('auth/InspectorRegister', [
            'categories' => ServiceCategory::orderBy('sort_order')->get(['id', 'name']),
            'prefill' => [
                'email' => $request->query('email'),
                'requestId' => $request->query('request'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:inspectors,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'agb' => ['accepted'],
            'request_id' => ['nullable', 'integer', 'exists:requests,id'],
        ], [
            'agb.accepted' => 'Bitte akzeptieren Sie die AGB.',
            'email.unique' => 'Für diese E-Mail-Adresse existiert bereits ein Gutachter-Konto.',
        ]);

        // Registering via an admin's invite link for a specific request already
        // proves inbox access (they clicked a signed link emailed to exactly
        // this address), so that path skips the separate email-verification step.
        $viaInvite = ! empty($data['request_id']);

        $inspector = Inspector::create([
            'service_category_id' => $data['service_category_id'],
            'company_name' => $data['company_name'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
            'is_approved' => false,
            'is_verified' => false,
            'member_since' => now(),
            'email_verified_at' => $viaInvite ? now() : null,
        ]);

        Wallet::firstOrCreate(['inspector_id' => $inspector->id]);
        ActivityLog::record('inspector.registered', $inspector);

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
                ->with('success', 'Ihr Gutachter-Konto wurde erstellt.');
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

        if (! $inspector) {
            return redirect()->route('gutachter.register', ['email' => $email, 'request' => $serviceRequest->id]);
        }

        Auth::guard('inspector')->login($inspector);

        RequestMatch::firstOrCreate(
            ['request_id' => $serviceRequest->id, 'inspector_id' => $inspector->id],
            ['notified_at' => now()]
        );

        return redirect()->route('inspector.requests.show', $serviceRequest->id);
    }
}
