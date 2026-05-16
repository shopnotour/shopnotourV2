<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingRefundsTable extends Migration
{
    public function up()
    {
        Schema::create('booking_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('pnr')->nullable();
            $table->enum('refund_type', ['full', 'partial'])->default('full');
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->decimal('refund_charges', 10, 2)->default(0);
            $table->decimal('net_refund_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('airline_response')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_refunds');
    }
}
