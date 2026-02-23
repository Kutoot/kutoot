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
        Schema::table('merchant_locations', function (Blueprint $table) {
            $table->string('monthly_target_type')->nullable()->after('is_active');
            $table->decimal('monthly_target_value', 15, 2)->nullable()->after('monthly_target_type');
            $table->boolean('deduct_commission_from_target')->default(true)->after('monthly_target_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_locations', function (Blueprint $table) {
            $table->dropColumn(['monthly_target_type', 'monthly_target_value', 'deduct_commission_from_target']);
        });
    }
};
