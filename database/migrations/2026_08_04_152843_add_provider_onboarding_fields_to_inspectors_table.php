<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspectors', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->after('company_name')->constrained()->nullOnDelete();
            $table->date('birthday')->nullable()->after('years_experience');
            $table->string('tax_id')->nullable()->after('birthday');
            $table->string('street')->nullable()->after('city');
            $table->string('plz', 5)->nullable()->after('street');
            $table->timestamp('profile_completed_at')->nullable()->after('is_verified');
        });

        // Registration used to be a single step with no real email verification
        // or profile-completion gate. Every inspector that already exists today
        // already went through the old flow — backfill them as "complete" so
        // this new gate only applies going forward, not retroactively.
        DB::table('inspectors')->update(['profile_completed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('inspectors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_category_id');
            $table->dropColumn(['birthday', 'tax_id', 'street', 'plz', 'profile_completed_at']);
        });
    }
};
