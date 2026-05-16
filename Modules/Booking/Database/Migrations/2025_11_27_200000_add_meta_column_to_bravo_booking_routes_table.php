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
            // Add meta column for storing PNR segment information and other metadata
            if (!Schema::hasColumn('bravo_booking_routes', 'meta')) {
                $table->json('meta')->nullable()->after('class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bravo_booking_routes', function (Blueprint $table) {
            if (Schema::hasColumn('bravo_booking_routes', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};

