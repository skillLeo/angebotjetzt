<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Coverage stops being implied by "an admin typed it in" and becomes an
        // explicit per-location switch, so the client can verify or unverify any
        // city (including the seeded ones) from the admin screen.
        Schema::table('map_locations', function (Blueprint $table) {
            $table->boolean('is_covered')->default(true)->after('longitude');
        });

        DB::table('map_locations')->update(['is_covered' => true]);

        // Homepage reviews get an optional portrait; the card falls back to the
        // initial-letter avatar when none is uploaded.
        Schema::table('homepage_reviews', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('map_locations', function (Blueprint $table) {
            $table->dropColumn('is_covered');
        });

        Schema::table('homepage_reviews', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
