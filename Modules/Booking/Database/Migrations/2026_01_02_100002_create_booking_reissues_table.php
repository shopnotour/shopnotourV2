<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingReissuesTable extends Migration
{
    public function up()
    {
        Schema::create('booking_reissues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('old_pnr')->nullable();
            $table->string('new_pnr')->nullable();
            $table->enum('reissue_type', ['date', 'route', 'passenger', 'other'])->default('date');
            $table->decimal('reissue_charges', 10, 2)->default(0);
            $table->decimal('fare_difference', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('old_flight_details')->nullable();
            $table->json('new_flight_details')->nullable();
            $table->text('reason')->nullable();
            $table->text('airline_response')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_reissues');
    }
}
