<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'request_number', 'user_id', 'service_type_id', 'vehicle_make', 'vehicle_model',
        'first_registration', 'mileage', 'vin', 'fuel_type', 'transmission',
        'plz', 'ort', 'strasse', 'preferred_date', 'alternative_date', 'notes', 'answers',
        'contact_name', 'contact_email', 'contact_phone', 'status', 'matched_count', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'alternative_date' => 'date',
            'expires_at' => 'datetime',
            'answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RequestPhoto::class, 'request_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(RequestMatch::class, 'request_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'request_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'request_id');
    }

    public static function nextRequestNumber(): string
    {
        $year = now()->year;
        $last = static::where('request_number', 'like', "AJ-{$year}-%")
            ->orderByDesc('id')
            ->value('request_number');
        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('AJ-%d-%06d', $year, $seq);
    }
}
