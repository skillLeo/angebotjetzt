<?php

namespace App\Actions\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Replaces Fortify's default RedirectIfTwoFactorAuthenticatable + AttemptToAuthenticate
 * pipeline steps for the single, unified /login endpoint used by both customers and
 * inspectors. Registered via Fortify::authenticateThrough() in FortifyServiceProvider.
 *
 * Customer accounts (users table) keep their exact existing behaviour, including the
 * 2FA challenge redirect. If the email does not belong to a customer, the same
 * credentials are attempted against the inspector guard. Admin accounts are
 * intentionally excluded — the admin login stays at its own separate, unlisted route.
 */
class AttemptToAuthenticateAnyGuard
{
    public function handle($request, $next)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $customer = User::where('email', $email)->first();

        if ($customer) {
            if (! Hash::check((string) $password, $customer->password)) {
                event(new Failed('web', $customer, ['email' => $email, 'password' => $password]));
                $this->throwFailed();
            }

            if (
                $customer->two_factor_secret
                && ! is_null($customer->two_factor_confirmed_at)
                && in_array(TwoFactorAuthenticatable::class, class_uses_recursive($customer))
            ) {
                $request->session()->put([
                    'login.id' => $customer->getKey(),
                    'login.remember' => $request->boolean('remember'),
                ]);

                TwoFactorAuthenticationChallenged::dispatch($customer);

                return $request->wantsJson()
                    ? response()->json(['two_factor' => true])
                    : redirect()->route('two-factor.login');
            }

            Auth::guard('web')->login($customer, $request->boolean('remember'));

            return $next($request);
        }

        if (Auth::guard('inspector')->attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
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

        $this->throwFailed();
    }

    /**
     * @return never
     */
    protected function throwFailed(): void
    {
        throw ValidationException::withMessages([
            'email' => __('Diese Zugangsdaten sind nicht korrekt.'),
        ]);
    }
}
