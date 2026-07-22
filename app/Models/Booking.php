<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number', 'request_id', 'offer_id', 'user_id', 'inspector_id',
        'status', 'completed_at', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public static function nextBookingNumber(): string
    {
        $year = now()->year;
        $last = static::where('booking_number', 'like', "AJB-{$year}-%")
            ->orderByDesc('id')
            ->value('booking_number');
        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('AJB-%d-%06d', $year, $seq);
    }
}
