<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Default true so every inspector that already existed before this
        // change stays exactly as they were (active and matchable) — only
        // inspectors created from now on start out pending approval.
        Schema::table('inspectors', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspectors', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
