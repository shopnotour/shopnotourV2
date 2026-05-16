<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirlineCallingStructuresTable extends Migration
{
    public function up()
    {
        Schema::create('flight_calling_structures', function (Blueprint $table) {
            $table->id();
            $table->string('departure_code', 3)->nullable()->comment('NULL = any departure');
            $table->string('arrival_code', 3)->nullable()->comment('NULL = any arrival');
            $table->json('airline_codes')->comment('["BS", "BG", "G9"]');
            $table->integer('priority')->default(0)->comment('Lower number = higher priority');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable()->comment('Admin notes');
            $table->timestamps();

            // ✅ Composite index for fast lookups
            $table->index(['departure_code', 'arrival_code', 'status', 'priority'], 'route_lookup_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flight_calling_structures');
    }
}
