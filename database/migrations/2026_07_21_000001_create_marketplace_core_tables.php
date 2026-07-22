<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('inspectors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('avatar_path')->nullable();
            $table->string('city')->nullable();
            $table->text('bio')->nullable();
            $table->text('qualifications')->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_verified')->default(false);
            $table->date('member_since')->nullable();
            $table->string('imported_from')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('inspector_service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['city', 'postal_range'])->index();
            $table->string('city_name')->nullable()->index();
            $table->unsignedInteger('postal_from')->nullable()->index();
            $table->unsignedInteger('postal_to')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('category_interest_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->unique(['service_category_id', 'email']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type')->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action')->index();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('code', 10);
            $table->string('purpose')->default('email_verification');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index(['owner_type', 'owner_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('category_interest_signals');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('service_categories');
        Schema::dropIfExists('inspector_service_areas');
        Schema::dropIfExists('inspectors');
        Schema::dropIfExists('admins');
    }
};
