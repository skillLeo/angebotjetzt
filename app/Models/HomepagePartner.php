<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepagePartner extends Model
{
    protected $fillable = [
        'name', 'city', 'reviews_count', 'rating', 'jobs_count',
        'member_since', 'photo_path', 'sort_order',
    ];

    protected $casts = [
        'reviews_count' => 'integer',
        'jobs_count' => 'integer',
        'rating' => 'float',
        'sort_order' => 'integer',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
