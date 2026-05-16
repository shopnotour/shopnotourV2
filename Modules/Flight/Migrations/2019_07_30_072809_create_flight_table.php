<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    use Modules\Flight\Models\Airline;
    use Modules\Flight\Models\Airport;
    use Modules\Flight\Models\BookingPassengers;
    use Modules\Flight\Models\Flight;
    use Modules\Flight\Models\FlightSeat;
    use Modules\Flight\Models\FlightTerm;
    use Modules\Flight\Models\SeatType;

    class CreateFlightTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
//            $this->down();
            Schema::create(FlightTerm::getTableName(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('term_id')->nullable();
                $table->integer('target_id')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();

                $table->softDeletes();
                $table->timestamps();
            });
            Schema::create(Airport::getTableName(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->string('code')->unique();
                $table->string('address')->nullable();
                $table->string('country',20)->nullable();
                $table->integer('location_id')->nullable();
                $table->text('description')->nullable();
                $table->string('map_lat', 20)->nullable();
                $table->string('map_lng', 20)->nullable();
                $table->integer('map_zoom')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->string('status',30)->nullable()->default('publish');
                $table->timestamps();
            });
            Schema::create(Airline::getTableName(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->integer('image_id')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
            Schema::create(SeatType::getTableName(), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code')->unique();
                $table->string('name')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });

            Schema::create(Flight::getTableName(), function (Blueprint $blueprint) {

                $blueprint->bigIncrements('id');
                $blueprint->string('title')->nullable();
                $blueprint->string('code')->nullable();
                $blueprint->decimal('review_score',2,1)->nullable();
                $blueprint->dateTime('departure_time')->nullable();
                $blueprint->dateTime('arrival_time')->nullable();
                $blueprint->float('duration')->nullable();
                $blueprint->decimal('min_price', 12, 2)->nullable();
                $blueprint->integer('airport_to')->nullable();
                $blueprint->integer('airport_from')->nullable();
                $blueprint->integer('airline_id')->nullable();
                $blueprint->string('status', 50)->nullable();

                $blueprint->bigInteger('create_user')->nullable();
                $blueprint->bigInteger('update_user')->nullable();
                $blueprint->timestamps();
                $blueprint->softDeletes();
            });
            Schema::create(FlightSeat::getTableName(), function (Blueprint $blueprint) {

                $blueprint->bigIncrements('id');
                $blueprint->decimal('price', 12, 2)->nullable();
                $blueprint->integer('max_passengers')->nullable();
                $blueprint->integer('flight_id')->nullable();
                $blueprint->string('seat_type')->nullable();
                $blueprint->string('person')->nullable();
                $blueprint->integer('baggage_check_in')->nullable();
                $blueprint->integer('baggage_cabin')->nullable();


                $blueprint->bigInteger('create_user')->nullable();
                $blueprint->bigInteger('update_user')->nullable();
                $blueprint->timestamps();
                $blueprint->softDeletes();
            });

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
                $table->timestamp('used_at');
                $table->timestamps();

                $table->index(['discount_id', 'user_id']);
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists(FlightTerm::getTableName());
            Schema::dropIfExists(Airport::getTableName());
            Schema::dropIfExists(Airline::getTableName());
            Schema::dropIfExists(SeatType::getTableName());
            Schema::dropIfExists(Flight::getTableName());
            Schema::dropIfExists(FlightSeat::getTableName());
            Schema::dropIfExists(BookingPassengers::getTableName());
            Schema::dropIfExists('flight_discount_usages');
            Schema::dropIfExists('flight_discounts');
        }
    }
