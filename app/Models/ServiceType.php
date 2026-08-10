<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = ['service_category_id', 'name', 'slug', 'description', 'image_url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceTypeField::class)->orderBy('sort_order');
    }

    public function redirects(): HasMany
    {
        return $this->hasMany(ServiceTypeRedirect::class);
    }

    /**
     * A slug derived from $name that's guaranteed unique among service
     * types (excluding $excludeId, so renaming a row back toward its own
     * current slug doesn't collide with itself) and among every retired
     * old slug still on file for a redirect — a freed-up old slug could
     * otherwise collide with a redirect record pointing somewhere else.
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        $taken = fn (string $candidate) => static::where('slug', $candidate)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()
            || ServiceTypeRedirect::where('old_slug', $candidate)->exists();

        while ($taken($slug)) {
            $slug = "{$base}-".$suffix++;
        }

        return $slug;
    }
}
