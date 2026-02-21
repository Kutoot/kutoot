<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('id');
            $table->unsignedTinyInteger('stamp_slots')->nullable()->after('stamp_target');
            $table->unsignedInteger('stamp_slot_min')->nullable()->after('stamp_slots');
            $table->unsignedInteger('stamp_slot_max')->nullable()->after('stamp_slot_min');
            $table->boolean('stamp_editable_on_plan_purchase')->default(false)->after('stamp_slot_max');
            $table->boolean('stamp_editable_on_coupon_redemption')->default(false)->after('stamp_editable_on_plan_purchase');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'stamp_slots',
                'stamp_slot_min',
                'stamp_slot_max',
                'stamp_editable_on_plan_purchase',
                'stamp_editable_on_coupon_redemption',
            ]);
        });
    }
};
