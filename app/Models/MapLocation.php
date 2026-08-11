<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapLocation extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude', 'is_manual', 'is_covered'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_manual' => 'boolean',
        'is_covered' => 'boolean',
    ];
}
