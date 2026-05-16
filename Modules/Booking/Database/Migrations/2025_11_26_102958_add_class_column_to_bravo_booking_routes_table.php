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
        Schema::table('bravo_booking_routes', function (Blueprint $table) {
            // Add class column for booking class (ResBookDesigCode) - Y/N/V/W/S etc
            if (!Schema::hasColumn('bravo_booking_routes', 'class')) {
                $table->string('class', 1)->nullable()->after('duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bravo_booking_routes', function (Blueprint $table) {
            if (Schema::hasColumn('bravo_booking_routes', 'class')) {
                $table->dropColumn('class');
            }
        });
    }
};
