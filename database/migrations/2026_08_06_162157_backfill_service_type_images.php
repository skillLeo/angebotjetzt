<?php

use App\Models\ServiceType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $images = config('media.services');

        ServiceType::whereNull('image_url')->get()->each(function (ServiceType $type) use ($images) {
            if (isset($images[$type->slug])) {
                $type->update(['image_url' => $images[$type->slug]]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
