<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How a service type is fulfilled. Everything defaults to 'offer', so
        // every existing service keeps the standard price-comparison booking
        // flow untouched; only the two rows updated at the bottom opt out.
        Schema::table('service_types', function (Blueprint $table) {
            $table->enum('flow_mode', ['offer', 'direct_accept', 'external'])
                ->default('offer')
                ->after('slug');
            $table->string('external_url')->nullable()->after('flow_mode');
        });

        // Unfallschadengutachten asks two extra questions up front. Kept as
        // real columns rather than the generic answers JSON so they can be
        // labelled and shown reliably, and so an admin editing the dynamic
        // field set can never remove them.
        Schema::table('requests', function (Blueprint $table) {
            $table->enum('accident_role', ['geschaedigter', 'verursacher', 'unklar'])
                ->nullable()
                ->after('notes');
            $table->boolean('has_lawyer')->nullable()->after('accident_role');
        });

        // A direct-accept service has no price at acceptance time: the fee
        // follows from the damage amount established after the inspection.
        // NULL states that honestly, and SQL aggregates (MIN/AVG over offer
        // prices) skip NULLs on their own, so no reporting query has to care.
        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedBigInteger('price_cents')->nullable()->change();
            $table->unsignedBigInteger('commission_cents')->nullable()->change();
            $table->unsignedBigInteger('inspector_cents')->nullable()->change();
        });

        // The accident-report and used-car-check services have been renamed
        // more than once, so match every spelling that has been live rather
        // than a single slug that may not exist in a given environment.
        DB::table('service_types')
            ->whereIn('slug', ['unfallgutachten', 'unfallschadengutachten'])
            ->update(['flow_mode' => 'direct_accept']);

        DB::table('service_types')
            ->whereIn('slug', ['gebrauchtwagen-check', 'gebrauchtwagencheck'])
            ->update(['flow_mode' => 'external']);
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['flow_mode', 'external_url']);
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['accident_role', 'has_lawyer']);
        });

        // Rows created by the direct-accept flow hold NULL prices and would
        // block the column going back to NOT NULL, so clear them first.
        DB::table('offers')->whereNull('price_cents')->delete();

        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedBigInteger('price_cents')->nullable(false)->change();
            $table->unsignedBigInteger('commission_cents')->nullable(false)->change();
            $table->unsignedBigInteger('inspector_cents')->nullable(false)->change();
        });
    }
};
