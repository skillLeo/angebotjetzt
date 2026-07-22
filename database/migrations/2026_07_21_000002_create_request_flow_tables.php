<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->string('vehicle_make');
            $table->string('vehicle_model');
            $table->string('first_registration', 20)->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->string('vin', 17)->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('plz', 5)->index();
            $table->string('ort')->index();
            $table->string('strasse')->nullable();
            $table->date('preferred_date')->nullable();
            $table->date('alternative_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->enum('status', ['open', 'offers_received', 'accepted', 'completed', 'cancelled', 'expired', 'unmatched'])->default('open')->index();
            $table->unsignedInteger('matched_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('request_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('request_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
            $table->unique(['request_id', 'inspector_id']);
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price_cents');
            $table->unsignedBigInteger('commission_cents');
            $table->unsignedBigInteger('inspector_cents');
            $table->text('message')->nullable();
            $table->date('estimated_date')->nullable();
            $table->enum('status', ['open', 'accepted', 'rejected', 'expired', 'withdrawn'])->default('open')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['request_id', 'inspector_id']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('request_id')->constrained()->restrictOnDelete();
            $table->foreignId('offer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('inspector_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['awaiting_payment', 'paid', 'in_progress', 'completed_by_inspector', 'confirmed', 'cancelled', 'refunded'])->default('awaiting_payment')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->string('stripe_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->unsignedBigInteger('total_cents');
            $table->unsignedBigInteger('commission_cents');
            $table->unsignedBigInteger('inspector_cents');
            $table->string('currency', 3)->default('eur');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('available_cents')->default(0);
            $table->bigInteger('pending_cents')->default(0);
            $table->bigInteger('lifetime_cents')->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit_pending', 'release', 'credit', 'debit_payout', 'adjustment']);
            $table->bigInteger('amount_cents');
            $table->bigInteger('balance_after_cents');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description');
            $table->timestamps();
            $table->index(['wallet_id', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents');
            $table->string('iban', 34);
            $table->string('bic', 11)->nullable();
            $table->string('account_holder');
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending')->index();
            $table->timestamp('requested_at');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
            $table->index(['inspector_id', 'is_published']);
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('request_matches');
        Schema::dropIfExists('request_photos');
        Schema::dropIfExists('requests');
    }
};
