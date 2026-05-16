<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // প্রতিটা page visit এর detail
        Schema::create('visitor_page_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_log_id'); // visitor_logs এর id
            $table->string('session_id', 191);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('page_url', 500);
            $table->string('page_title', 191)->nullable();
            $table->string('referrer_url', 500)->nullable();
            $table->timestamp('entered_at');
            $table->timestamp('left_at')->nullable();
            $table->integer('time_spent')->default(0); // seconds
            $table->integer('scroll_depth')->default(0); // percentage 0-100
            $table->integer('click_count')->default(0);
            $table->json('session_snapshot')->nullable(); // page load এর সময় session data
            $table->timestamps();

            $table->foreign('visitor_log_id')->references('id')->on('visitor_logs')->onDelete('cascade');
            $table->index(['session_id', 'entered_at']);
            $table->index('visitor_log_id');
        });

        // প্রতিটা activity/event
        Schema::create('visitor_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_log_id');
            $table->unsignedBigInteger('page_log_id')->nullable(); // visitor_page_logs এর id
            $table->string('session_id', 191);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('page_url', 500);
            $table->enum('activity_type', [
                'click',
                'search',
                'flight_search',
                'hotel_search',
                'form_input',
                'form_submit',
                'scroll',
                'page_exit',
                'custom'
            ]);
            $table->string('element_id', 191)->nullable();    // কোন element এ click
            $table->string('element_text', 500)->nullable();  // element এর text
            $table->json('activity_data')->nullable();         // search query, flight data etc
            $table->json('session_data')->nullable();          // click এর সময় full session snapshot
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('visitor_log_id')->references('id')->on('visitor_logs')->onDelete('cascade');
            $table->index(['session_id', 'occurred_at']);
            $table->index(['visitor_log_id', 'activity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_activities');
        Schema::dropIfExists('visitor_page_logs');
    }
};
