<?php

/**
 * Creates stub world tables (countries, states, cities) for testing.
 *
 * The nnjeim/world package creates these on a separate DB connection
 * (world_mysql), but our merchant_locations table has FK constraints
 * to states/cities. When testing with SQLite :memory:, those tables
 * don't exist, causing FK failures. This migration creates minimal
 * stubs so RefreshDatabase works correctly.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create stubs if the tables don't already exist
        // (they might exist if sharing the real DB connection)
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('iso2', 2)->nullable();
                $table->string('iso3', 3)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
