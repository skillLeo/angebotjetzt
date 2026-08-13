<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use App\Support\SiteContentRegistry;
use Illuminate\Database\Seeder;

/**
 * Writes every registry default into site_contents so the admin screens open
 * with the site's real current wording instead of empty fields.
 *
 * Idempotent, and deliberately non-destructive: a key the client has already
 * edited keeps its value, so re-running this after adding new fields only
 * fills in the new ones.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $groups = SiteContentRegistry::groups();
        $existing = SiteContent::pluck('key')->all();

        $rows = [];
        $now = now();

        foreach (SiteContentRegistry::defaults() as $key => $default) {
            if (in_array($key, $existing, true)) {
                continue;
            }

            $rows[] = [
                'key' => $key,
                'value' => $default,
                'group' => $groups[$key] ?? 'home',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            SiteContent::insert($rows);
        }

        SiteContent::flush();
    }
}
