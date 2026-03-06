<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->string('approval_status')->default('approved')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('approval_status');
        });

        // Merchant-created coupons that are already active remain approved
        // Platform coupons (no merchant_location_id) are auto-approved
    }

    public function down(): void
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'rejection_reason']);
        });
    }
};
