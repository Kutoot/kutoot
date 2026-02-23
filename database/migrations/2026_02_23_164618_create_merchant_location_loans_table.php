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
        Schema::create('merchant_location_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('active');
            $table->unsignedInteger('streak_months_at_approval');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('streak_broken_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_location_loans');
    }
};
