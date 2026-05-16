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
        // Flight Discounts Table
        Schema::create('flight_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Discount name for admin reference');
            $table->string('code')->nullable()->unique()->comment('Promo code (if applicable)');

            // Discount Type
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2)->comment('Discount value (percentage or amount)');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('Maximum discount amount');
            $table->decimal('min_purchase', 10, 2)->default(0)->comment('Minimum purchase amount');

            // Applicable To
            $table->string('airline_code')->nullable()->comment('Specific airline (null = all airlines)');
            $table->string('departure_code')->nullable()->comment('Specific departure airport (null = all)');
            $table->string('arrival_code')->nullable()->comment('Specific arrival airport (null = all)');

            // Validity
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();

            // Status & Priority
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->integer('priority')->default(0)->comment('Higher priority applies first');

            // Usage Limits
            $table->integer('usage_limit')->nullable()->comment('Total usage limit (null = unlimited)');
            $table->integer('usage_count')->default(0)->comment('Current usage count');
            $table->integer('per_user_limit')->nullable()->comment('Per user usage limit');

            // Additional Info
            $table->text('description')->nullable();
            $table->json('conditions')->nullable()->comment('Additional conditions (JSON)');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'valid_from', 'valid_to']);
            $table->index('airline_code');
            $table->index(['departure_code', 'arrival_code']);
            $table->index('priority');
        });

        // Usage tracking table
        Schema::create('flight_discount_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('flight_discounts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained('bravo_bookings')->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->string('airline_code')->nullable();
            $table->string('route')->nullable();

            $table->decimal('ait_charge', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('segment_discount', 10, 2)->default(0);

            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['discount_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('flight_discount_usages');
        Schema::dropIfExists('flight_discounts');
    }
};
