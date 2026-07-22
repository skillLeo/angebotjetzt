<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectorServiceArea extends Model
{
    use HasFactory;

    protected $fillable = ['inspector_id', 'type', 'city_name', 'postal_from', 'postal_to'];

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }
}
