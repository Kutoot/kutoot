<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * IMPORTANT: Only run this migration AFTER executing:
     *   php artisan app:migrate-sponsor-media
     *   php artisan app:migrate-merchant-category-media
     *
     * Verify the data migration was successful before proceeding.
     */
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['logo', 'banner']);
        });

        Schema::table('merchant_categories', function (Blueprint $table) {
            $table->dropColumn(['image', 'icon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('type');
            $table->string('banner')->nullable()->after('logo');
        });

        Schema::table('merchant_categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('name');
            $table->string('icon')->nullable()->after('image');
        });
    }
};
