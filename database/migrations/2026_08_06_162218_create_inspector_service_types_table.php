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
        Schema::create('inspector_service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['inspector_id', 'service_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspector_service_types');
    }
};
