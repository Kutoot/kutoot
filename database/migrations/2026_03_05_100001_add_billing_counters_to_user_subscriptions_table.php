<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('bills_used')->default(0)->after('status');
            $table->decimal('amount_redeemed', 15, 2)->default(0)->after('bills_used');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['bills_used', 'amount_redeemed']);
        });
    }
};
