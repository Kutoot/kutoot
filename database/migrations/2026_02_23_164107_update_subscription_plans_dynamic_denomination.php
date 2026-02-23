<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('stamp_denomination', 15, 2)->default(100)->after('stamps_on_purchase');
            $table->integer('stamps_per_denomination')->default(1)->after('stamp_denomination');
        });

        // Backfill existing rows: map stamps_per_100 → stamps_per_denomination, keep denomination at 100
        DB::table('subscription_plans')->update([
            'stamp_denomination' => 100,
        ]);
        DB::statement('UPDATE subscription_plans SET stamps_per_denomination = stamps_per_100');

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('stamps_per_100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('stamps_per_100')->default(1)->after('stamps_on_purchase');
        });

        DB::statement('UPDATE subscription_plans SET stamps_per_100 = stamps_per_denomination');

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['stamp_denomination', 'stamps_per_denomination']);
        });
    }
};
