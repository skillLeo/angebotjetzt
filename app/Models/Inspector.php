<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Inspector extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_name', 'name', 'email', 'phone', 'password', 'avatar_path',
        'city', 'bio', 'qualifications', 'years_experience', 'is_active',
        'is_approved', 'is_verified', 'member_since', 'imported_from', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'member_since' => 'date',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'is_verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(InspectorServiceArea::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(RequestMatch::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'notifiable_id')
            ->where('notifiable_type', self::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->where('is_published', true)->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }
}
