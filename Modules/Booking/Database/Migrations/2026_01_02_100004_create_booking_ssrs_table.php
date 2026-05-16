<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingSsrsTable extends Migration
{
    public function up()
    {
        Schema::create('booking_ssrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('passenger_id')->nullable();
            $table->enum('ssr_type', ['baggage', 'meal', 'seat', 'insurance', 'wheelchair', 'other'])->default('baggage');
            $table->string('ssr_code')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'failed', 'cancelled'])->default('pending');
            $table->string('airline_reference')->nullable();
            $table->json('ssr_details')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_ssrs');
    }
}
