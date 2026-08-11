<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pins on the public Germany map. Rows seeded here mirror the list
        // that used to be hardcoded in GermanyMap.vue, so the map keeps its
        // existing behaviour (green only where an approved provider sits)
        // until an admin adds locations of their own.
        Schema::create('map_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->boolean('is_manual')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->string('service')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('homepage_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('jobs_count')->default(0);
            $table->string('member_since')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $cities = [
            ['Berlin', 52.520000, 13.405000],
            ['Hamburg', 53.551100, 9.993700],
            ['München', 48.135100, 11.582000],
            ['Köln', 50.937500, 6.960300],
            ['Frankfurt am Main', 50.110900, 8.682100],
            ['Stuttgart', 48.775800, 9.182900],
            ['Düsseldorf', 51.227700, 6.773500],
            ['Dortmund', 51.513600, 7.465300],
            ['Leipzig', 51.339700, 12.373100],
            ['Bremen', 53.079300, 8.801700],
            ['Hannover', 52.375900, 9.732000],
            ['Nürnberg', 49.452100, 11.076700],
            ['Essen', 51.455600, 7.011600],
            ['Dresden', 51.050400, 13.737300],
            ['Bonn', 50.737400, 7.098200],
            ['Wiesbaden', 50.078200, 8.239800],
            ['Saarbrücken', 49.235400, 6.996900],
            ['Kiel', 54.323300, 10.122800],
            ['Rostock', 54.088700, 12.140100],
            ['Magdeburg', 52.120500, 11.627600],
            ['Erfurt', 50.984800, 11.029900],
            ['Potsdam', 52.390600, 13.064500],
            ['Schwerin', 53.635500, 11.401200],
            ['Mainz', 49.992900, 8.247300],
            ['Kassel', 51.312700, 9.479700],
            ['Karlsruhe', 49.006900, 8.403700],
            ['Mannheim', 49.487500, 8.466000],
            ['Augsburg', 48.370500, 10.897800],
            ['Münster', 51.960700, 7.626100],
            ['Bielefeld', 52.030200, 8.532500],
            ['Wuppertal', 51.256200, 7.150800],
            ['Bochum', 51.481800, 7.216200],
            ['Freiburg', 47.999000, 7.842100],
            ['Regensburg', 49.013400, 12.101600],
            ['Chemnitz', 50.827800, 12.921400],
            ['Halle', 51.482500, 11.969900],
            ['Braunschweig', 52.268900, 10.526800],
            ['Aachen', 50.775300, 6.083900],
            ['Lübeck', 53.865500, 10.686600],
            ['Trier', 49.759600, 6.644100],
            ['Flensburg', 54.793700, 9.431800],
        ];

        $now = now();

        DB::table('map_locations')->insert(
            array_map(fn ($c) => [
                'name' => $c[0],
                'latitude' => $c[1],
                'longitude' => $c[2],
                'is_manual' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ], $cities)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_partners');
        Schema::dropIfExists('homepage_reviews');
        Schema::dropIfExists('map_locations');
    }
};
