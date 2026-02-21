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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('razorpay_order_id')->nullable()->after('payment_id');
            $table->string('transfer_id')->nullable()->after('razorpay_order_id');
            $table->string('refund_id')->nullable()->after('transfer_id');
            $table->string('idempotency_key')->nullable()->unique()->after('refund_id');
            $table->string('type')->default('coupon_redemption')->after('idempotency_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'razorpay_order_id',
                'transfer_id',
                'refund_id',
                'idempotency_key',
                'type',
            ]);
        });
    }
};
