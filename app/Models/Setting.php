<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('app_settings', 300, fn () => static::pluck('value', 'key')->all());

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('app_settings');
    }

    public static function commissionPercent(): float
    {
        return (float) static::get('commission_percent', 10);
    }

    /**
     * The admin-uploaded logo's public URL, or null if none has been
     * uploaded yet — every place the logo appears (header, auth pages,
     * dashboards, wizard, emails) falls back to the built-in default file
     * when this is null, so a fresh install always has a working logo.
     */
    public static function logoUrl(): ?string
    {
        $path = static::get('logo_path');

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
