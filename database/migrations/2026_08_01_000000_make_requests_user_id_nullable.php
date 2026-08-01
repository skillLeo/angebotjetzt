<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->change();
            });

            return;
        }

        DB::statement('ALTER TABLE requests MODIFY user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable(false)->change();
            });

            return;
        }

        DB::statement('ALTER TABLE requests MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};
