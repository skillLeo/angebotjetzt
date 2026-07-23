<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InspectorAuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('auth/InspectorLogin');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'inspector-login:'.Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('Zu viele Anmeldeversuche. Bitte versuchen Sie es in :seconds Sekunden erneut.', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        if (! Auth::guard('inspector')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => __('Diese Zugangsdaten sind nicht korrekt.'),
            ]);
        }

        RateLimiter::clear($key);

        $inspector = Auth::guard('inspector')->user();

        if (! $inspector->is_active) {
            Auth::guard('inspector')->logout();

            throw ValidationException::withMessages([
                'email' => __('Ihr Konto ist derzeit deaktiviert. Bitte kontaktieren Sie den Support.'),
            ]);
        }

        $request->session()->regenerate();
        ActivityLog::record('inspector.login', $inspector);

        return redirect()->intended('/inspector');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('inspector')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
