<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('countries')) {  // ← এই দুই লাইন যোগ করুন
            return;                           // ←
        }
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->comment('ISO 3166-1 alpha-2 (e.g. BD, US)');
            $table->string('code3', 3)->nullable()->comment('ISO 3166-1 alpha-3 (e.g. BGD, USA)');
            $table->string('name')->comment('Country name (e.g. Bangladesh)');
            $table->string('capital')->nullable();
            $table->string('flag_emoji', 10)->nullable()->comment('🇧🇩');
            $table->string('phone_code', 10)->nullable()->comment('e.g. +880');

            // ── Passport rules ──
            $table->unsignedTinyInteger('passport_min')->default(6)->comment('Min passport number length');
            $table->unsignedTinyInteger('passport_max')->default(12)->comment('Max passport number length');
            $table->string('passport_pattern')->default('[A-Z0-9]{6,12}')->comment('Regex pattern for validation');
            $table->string('passport_hint')->default('6–12 alphanumeric characters')->comment('User-friendly hint text');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
