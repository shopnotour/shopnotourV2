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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // NULL = guest user
            $table->string('session_id')->index();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // Windows, Mac, Linux, etc.

            // Location data
            $table->string('country')->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();

            // Visit tracking
            $table->timestamp('visited_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration')->default(0); // in seconds

            // Page tracking
            $table->string('landing_page')->nullable();
            $table->string('current_page')->nullable();
            $table->integer('page_views')->default(1);

            // Status
            $table->boolean('is_online')->default(true);
            $table->string('status')->default('active'); // active, inactive

            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('is_online');
            $table->index('visited_at');
            $table->index(['session_id', 'is_online']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
