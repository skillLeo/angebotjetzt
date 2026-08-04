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
    private const EXEMPT_ROUTES = [
        'inspector.verification.notice',
        'inspector.verification.resend',
        'inspector.onboarding.profile',
        'inspector.onboarding.profile.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(self::EXEMPT_ROUTES)) {
            return $next($request);
        }

        $inspector = Auth::guard('inspector')->user();

        if (! $inspector->email_verified_at) {
            return redirect()->route('inspector.verification.notice');
        }

        if (! $inspector->profile_completed_at) {
            return redirect()->route('inspector.onboarding.profile');
        }

        return $next($request);
    }
}
