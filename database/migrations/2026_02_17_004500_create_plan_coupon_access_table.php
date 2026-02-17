<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_coupon_category_access', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('coupon_category_id')->constrained('coupon_categories')->cascadeOnDelete();
            $table->primary(['plan_id', 'coupon_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_coupon_category_access');
    }
};
