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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // Required - announcement text
            $table->string('icon', 10)->nullable()->default('🌟'); // Optional - emoji icon
            $table->tinyInteger('is_active')->default(1)->comment('1=active, 0=inactive'); // Boolean as tinyint
            $table->integer('scroll_speed')->default(40)->comment('Speed in seconds (10-100)');
            $table->string('bg_color', 20)->default('blue')->comment('blue, green, purple, orange, dark');
            $table->integer('display_order')->default(0)->comment('Lower numbers appear first');
            $table->timestamp('start_date')->nullable()->comment('When to start showing');
            $table->timestamp('end_date')->nullable()->comment('When to stop showing');
            $table->timestamps();

            // Indexes for performance
            $table->index('is_active');
            $table->index('display_order');
            $table->index(['is_active', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
