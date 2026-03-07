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
        Schema::create('subscription_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // Unique constraint: track latest acceptance per user per plan
            $table->unique(['user_id', 'plan_id']);

            // Indexes for audit and reporting queries
            $table->index('user_id');
            $table->index('plan_id');
            $table->index('accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_consents');
    }
};
