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
        Schema::create('flight_charges', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['domestic', 'international'])->unique();
            $table->decimal('ait_charge', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('segment_discount', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Insert default data
        DB::table('flight_charges')->insert([
            [
                'type' => 'domestic',
                'ait_charge' => 0,
                'service_charge' => 0,
                'segment_discount' => 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'international',
                'ait_charge' => 0,
                'service_charge' => 0,
                'segment_discount' => 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_charges');
    }
};
