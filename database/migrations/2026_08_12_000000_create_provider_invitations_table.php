<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invitations to join the platform as a provider, uploaded in bulk from a
     * CSV. Rows are created queued and sent in capped batches by a scheduled
     * command rather than all at once, so a large list cannot flood the mail
     * server or trip spam filters on the sending domain.
     *
     * The row itself is what makes "already invited" answerable, so a repeat
     * upload of the same list never mails anyone twice.
     */
    public function up(): void
    {
        Schema::create('provider_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->foreignId('invited_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('source')->default('csv-bulk');
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_invitations');
    }
};
