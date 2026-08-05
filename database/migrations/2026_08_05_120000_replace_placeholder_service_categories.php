<?php

use App\Models\ServiceCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Swaps the 9 placeholder "coming soon" categories seeded before launch
     * planning was finalized for the client's actual launch list. Only
     * kfz-gutachten has real ServiceType children today (confirmed before
     * writing this), so every other category is safe to drop outright.
     */
    public function up(): void
    {
        ServiceCategory::where('slug', '!=', 'kfz-gutachten')->delete();

        ServiceCategory::where('slug', 'kfz-gutachten')->update(['sort_order' => 1]);

        $categories = [
            ['Transporte', 'transporte', 'truck', 'Umzüge, Lieferungen und Transporte aller Art.', 2],
            ['Handwerk', 'handwerk', 'hammer', 'Qualifizierte Handwerker für jedes Projekt.', 3],
            ['Umzug & Entrümpelungen', 'umzug-entruempelungen', 'package', 'Umzüge, Haushaltsauflösungen und Entrümpelungen.', 4],
            ['Reinigung', 'reinigung', 'sparkles', 'Professionelle Reinigungsdienste.', 5],
            ['Garten & Außenbereich', 'garten-aussenbereich', 'flower-2', 'Gartenpflege und Gestaltung des Außenbereichs.', 6],
            ['IT & Digital', 'it-digital', 'monitor', 'IT-Dienstleistungen und digitale Lösungen.', 7],
        ];

        foreach ($categories as [$name, $slug, $icon, $desc, $order]) {
            ServiceCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'description' => $desc,
                'is_active' => false, 'sort_order' => $order,
            ]);
        }

        Cache::forget('nav_categories');
        Cache::forget('homepage_data');
    }

    public function down(): void
    {
        // Placeholder-category content has no real dependents (verified before
        // writing this migration); a reverse seed isn't meaningful, so down()
        // intentionally leaves the new launch list in place.
    }
};
