<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', 'inspector_id', 'price_cents', 'commission_cents',
        'inspector_cents', 'message', 'estimated_date', 'status', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['estimated_date' => 'date', 'expires_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }
}
