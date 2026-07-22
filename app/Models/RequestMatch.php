<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestMatch extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'inspector_id', 'notified_at', 'viewed_at'];

    protected function casts(): array
    {
        return ['notified_at' => 'datetime', 'viewed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }
}
