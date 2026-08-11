<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageReview extends Model
{
    protected $fillable = ['name', 'rating', 'comment', 'service', 'city', 'photo_path', 'sort_order'];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
