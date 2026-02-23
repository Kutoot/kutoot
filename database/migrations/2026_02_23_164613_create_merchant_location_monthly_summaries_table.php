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
        Schema::create('merchant_location_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_location_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->decimal('total_bill_amount', 15, 2)->default(0);
            $table->decimal('total_commission_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->unsignedInteger('transaction_count')->default(0);
            $table->boolean('target_met')->default(false);
            $table->timestamps();

            $table->unique(['merchant_location_id', 'year', 'month'], 'mlms_location_year_month_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_location_monthly_summaries');
    }
};
