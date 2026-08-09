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
        Schema::table('requests', function (Blueprint $table) {
            $table->timestamp('first_offer_at')->nullable()->after('matched_count');
            $table->timestamp('offer_reminder_sent_at')->nullable()->after('first_offer_at');
            $table->timestamp('offer_final_reminder_sent_at')->nullable()->after('offer_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['first_offer_at', 'offer_reminder_sent_at', 'offer_final_reminder_sent_at']);
        });
    }
};
