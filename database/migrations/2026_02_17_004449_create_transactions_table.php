<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('discount_coupons')->nullOnDelete();
            $table->foreignId('merchant_location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('original_bill_amount', 15, 2)->comment('Original bill before any discount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount applied to the bill');
            $table->decimal('amount', 15, 2)->comment('Bill amount after discount (original_bill_amount - discount_amount)');
            $table->decimal('platform_fee', 15, 2)->default(0)->comment('Platform service fee');
            $table->decimal('gst_amount', 15, 2)->default(0)->comment('GST on platform fee');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Final amount user paid (amount + platform_fee + gst_amount)');
            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('transfer_id')->nullable();
            $table->string('refund_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('type')->default('coupon_redemption');
            $table->string('payment_status')->default('pending');
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
