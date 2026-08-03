<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bookings are now created directly on offer acceptance (no payment
        // processed through the platform), so the status list needs a neutral
        // "accepted" state instead of the old awaiting_payment/paid pair.
        // SQLite's enum() column is backed by a real CHECK constraint too, so
        // it needs the same widening as MySQL — Schema::table()->change()
        // handles both drivers (SQLite via Laravel's native table rebuild).
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['accepted', 'awaiting_payment', 'paid', 'in_progress', 'completed_by_inspector', 'confirmed', 'cancelled', 'refunded'])
                ->default('accepted')
                ->change();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('inspector_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('offer_amount_cents');
            $table->decimal('commission_percent', 5, 2);
            $table->unsignedBigInteger('commission_cents');
            $table->date('due_date');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['awaiting_payment', 'paid', 'in_progress', 'completed_by_inspector', 'confirmed', 'cancelled', 'refunded'])
                ->default('awaiting_payment')
                ->change();
        });
    }
};
