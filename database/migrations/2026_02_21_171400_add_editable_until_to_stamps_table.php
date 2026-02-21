<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stamps', function (Blueprint $table) {
            $table->dateTime('editable_until')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('stamps', function (Blueprint $table) {
            $table->dropColumn('editable_until');
        });
    }
};
