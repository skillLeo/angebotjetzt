<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['owner_type', 'owner_id', 'code', 'purpose', 'expires_at', 'consumed_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime'];
    }

    public static function issue(Model $owner, string $purpose = 'email_verification'): self
    {
        static::where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->delete();

        return static::create([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public static function verify(Model $owner, string $code, string $purpose = 'email_verification'): bool
    {
        $otp = static::where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp || ! hash_equals($otp->code, $code)) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
