<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Service types with custom fields defined (Part 9) won't populate these
     * two columns at all, so they can no longer be a hard NOT NULL — mirrors
     * the same sqlite-vs-MySQL branch already used for making user_id nullable.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->string('vehicle_make')->nullable()->change();
                $table->string('vehicle_model')->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE requests MODIFY vehicle_make VARCHAR(255) NULL');
        DB::statement('ALTER TABLE requests MODIFY vehicle_model VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->string('vehicle_make')->nullable(false)->change();
                $table->string('vehicle_model')->nullable(false)->change();
            });

            return;
        }

        DB::statement('ALTER TABLE requests MODIFY vehicle_make VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE requests MODIFY vehicle_model VARCHAR(255) NOT NULL');
    }
};
