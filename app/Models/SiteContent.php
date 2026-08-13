<?php

namespace App\Models;

use App\Support\SiteContentRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable marketing copy, stored one string per row.
 *
 * Every read falls back to the string that used to be hardcoded in the Vue
 * component (see SiteContentRegistry), so a missing row — or a database that
 * has never been seeded — renders exactly what the site rendered before this
 * table existed.
 */
class SiteContent extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    private const CACHE_KEY = 'site_contents';

    /** One string, with the registry default when nothing is stored. */
    public static function get(string $key, ?string $default = null): string
    {
        $stored = self::stored()[$key] ?? null;

        return $stored ?? $default ?? (SiteContentRegistry::defaults()[$key] ?? '');
    }

    /**
     * Every key in a group, resolved against the registry defaults.
     *
     * This is what gets handed to Inertia: the frontend receives a complete,
     * ready-to-render map and never has to know which values came from the
     * database and which fell back.
     *
     * @return array<string, string>
     */
    public static function group(string $group): array
    {
        $stored = self::stored();
        $defaults = SiteContentRegistry::defaults();
        $resolved = [];

        foreach (SiteContentRegistry::groups() as $key => $keyGroup) {
            if ($keyGroup !== $group) {
                continue;
            }

            $resolved[$key] = $stored[$key] ?? $defaults[$key] ?? '';
        }

        return $resolved;
    }

    /**
     * The resolved values for one legal page, keyed by their short name
     * (title, updated, body) rather than the full dotted key.
     *
     * @return array{title: string, updated: string, body: string}
     */
    public static function legalPage(string $page): array
    {
        return [
            'title' => self::get("legal.{$page}.title"),
            'updated' => self::get("legal.{$page}.updated"),
            'body' => self::get("legal.{$page}.body"),
        ];
    }

    public static function set(string $key, ?string $value): void
    {
        $group = SiteContentRegistry::groups()[$key] ?? 'home';

        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);

        self::flush();
    }

    /**
     * Write many keys in one go, so saving an admin form is a single flush
     * rather than one cache bust per field.
     *
     * @param  array<string, string|null>  $values
     */
    public static function setMany(array $values): void
    {
        $groups = SiteContentRegistry::groups();

        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $groups[$key] ?? 'home'],
            );
        }

        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * The raw stored rows. Cached, because the homepage reads ~80 keys on
     * every request.
     *
     * @return array<string, string|null>
     */
    private static function stored(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => static::pluck('value', 'key')->all());
    }
}
