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
        'commission_percent', 'commission_cents', 'due_date', 'pdf_path',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'commission_percent' => 'float'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
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
