<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightApisTable extends Migration
{
    public function up()
    {
        Schema::create('flight_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // API Name
            $table->string('provider'); // Sabre, Amadeus, etc
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->text('api_url')->nullable();
            $table->text('endpoint')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('configuration')->nullable(); // JSON format
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);
            $table->integer('is_enabled')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flight_apis');
    }
}
