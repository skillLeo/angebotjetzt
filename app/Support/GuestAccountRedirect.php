<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Shared destination for guest-facing email links that lead into an
 * authenticated area: already logged in -> straight through; an account
 * already exists for this email -> login; otherwise -> registration with
 * name/email pre-filled (the new account is automatically linked back to
 * any of their existing requests via User::claimOrphanedRequests()). Every
 * such link should go through this one method so the branching logic can't
 * silently diverge between them again.
 */
class GuestAccountRedirect
{
    public static function to(string $name, string $email, string $authenticatedUrl): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($authenticatedUrl);
        }

        if (User::where('email', $email)->exists()) {
            return redirect()->route('login');
        }

        return redirect()->route('register', ['name' => $name, 'email' => $email]);
    }
}
