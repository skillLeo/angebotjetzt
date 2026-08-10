<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'booking_id', 'inspector_id', 'offer_amount_cents',
        'commission_percent', 'commission_cents', 'due_date', 'pdf_path', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'commission_percent' => 'float', 'paid_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    /**
     * The number to show a human anywhere this invoice is referenced —
     * always the original request number, never `invoice_number`. That
     * column only exists for internal sequential record-keeping; use this
     * method (not the column) in every view, email, and PDF so the visible
     * reference can never silently diverge again.
     */
    public function referenceNumber(): string
    {
        return $this->booking->request->request_number;
    }

    public static function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $last = static::where('invoice_number', 'like', "AJR-{$year}-%")
            ->orderByDesc('id')
            ->value('invoice_number');
        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('AJR-%d-%06d', $year, $seq);
    }
}
