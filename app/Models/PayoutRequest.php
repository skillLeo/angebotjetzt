<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspector_id', 'amount_cents', 'iban', 'bic', 'account_holder',
        'status', 'requested_at', 'paid_at', 'paid_by_admin_id', 'note',
    ];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function paidByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'paid_by_admin_id');
    }
}
