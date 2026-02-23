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
        Schema::create('loan_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_streak_months');
            $table->decimal('max_loan_amount', 15, 2);
            $table->decimal('interest_rate_percentage', 5, 2)->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_tiers');
    }
};
