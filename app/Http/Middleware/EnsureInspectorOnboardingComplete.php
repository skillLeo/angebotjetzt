<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registering no longer grants full dashboard access straight away: the
 * inspector must first confirm their email, then complete their profile,
 * before the existing is_approved gate (checked separately in
 * InspectorAreaController::dashboard()) even comes into play. This routes
 * them to whichever step they haven't finished yet, on every request, so no
 * inspector-guarded route needs its own copy of this check.
 */
class EnsureInspectorOnboardingComplete
{
    private const VERIFICATION_ROUTES = [
        'inspector.verification.notice',
        'inspector.verification.resend',
    ];

    private const PROFILE_ROUTES = [
        'inspector.onboarding.profile',
        'inspector.onboarding.profile.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(self::VERIFICATION_ROUTES)) {
            return $next($request);
        }

        $inspector = Auth::guard('inspector')->user();

        if (! $inspector->email_verified_at) {
            return redirect()->route('inspector.verification.notice');
        }

        // The profile-completion page itself must stay reachable once verified
        // (else checking profile_completed_at against its own route would loop),
        // but it's still gated behind the email-verified check above — a
        // step-1-only account can never reach it directly.
        if ($request->routeIs(self::PROFILE_ROUTES)) {
            return $next($request);
        }

        if (! $inspector->profile_completed_at) {
            return redirect()->route('inspector.onboarding.profile');
        }

        return $next($request);
    }
}
