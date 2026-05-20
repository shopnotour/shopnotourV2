<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSegmentToBookingRefundsTable extends Migration
{
    public function up()
    {
        Schema::table('booking_refunds', function (Blueprint $table) {
            $table->json('segment')->nullable()->after('pnr');
        });
    }

    public function down()
    {
        Schema::table('booking_refunds', function (Blueprint $table) {
            $table->dropColumn('segment');
        });
    }
}
