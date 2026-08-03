<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property bool $agb_accepted
 * @property Carbon|null $privacy_accepted_at
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'phone', 'avatar_path', 'agb_accepted', 'privacy_accepted_at', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'agb_accepted' => 'boolean',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Guests can submit a request before ever creating an account — or using
        // an email that already has one. As soon as this account is provably
        // theirs (created just now, or a real login later), link any orphaned
        // guest requests matching this email so they're never unreachable.
        static::created(fn (User $user) => $user->claimOrphanedRequests());
    }

    /**
     * Link any guest-submitted, still-unclaimed requests for this account's
     * email address. Only ever called once account ownership is proven
     * (account creation or a successful login) — never at submission time,
     * since typing an email address doesn't prove you control it.
     */
    public function claimOrphanedRequests(): void
    {
        ServiceRequest::whereNull('user_id')
            ->where('contact_email', $this->email)
            ->update(['user_id' => $this->id]);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
