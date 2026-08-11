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

    /*
     * A booking created by a provider accepting a direct-accept request can
     * belong to a guest who never opened an account. These read the customer
     * from the account when there is one and fall back to the contact details
     * captured on the request when there isn't, so callers never touch a null
     * user.
     */

    public function customerName(): string
    {
        return $this->user?->name ?? $this->request->contact_name;
    }

    public function customerEmail(): string
    {
        return $this->user?->email ?? $this->request->contact_email;
    }

    public function customerPhone(): ?string
    {
        return $this->user?->phone ?? $this->request->contact_phone;
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * The number to show a human anywhere this booking is referenced —
     * always the original request number, never `booking_number`. That
     * column only exists as an internal record identifier; use this method
     * (not the column) in every view and email so the visible reference
     * can never silently diverge again.
     */
    public function referenceNumber(): string
    {
        return $this->request->request_number;
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
