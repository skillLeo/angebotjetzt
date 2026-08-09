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
        Schema::table('reviews', function (Blueprint $table) {
            // The 1-10 scale the customer actually picked in the survey —
            // "rating" itself now stores the 1-5 star equivalent used for
            // display/averages, but the raw value is still needed for the
            // Trustpilot (8-10) vs internal-survey (1-7) branching decision.
            // Nullable: historical reviews created before this column
            // existed (the old direct in-app 1-5 star flow) never had a
            // 1-10 value to begin with.
            $table->unsignedTinyInteger('raw_rating')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('raw_rating');
        });
    }
};
