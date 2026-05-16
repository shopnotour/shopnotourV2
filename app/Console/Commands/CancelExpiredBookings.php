<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CancelBookingJob;
use Carbon\Carbon;
use Modules\Booking\Models\Booking;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Cancel bookings whose ticket deadline is within 30 minutes';

    public function handle()
    {
        $now = Carbon::now();
        $threshold = Carbon::now()->addMinutes(30);

        $bookings = Booking::where('status', 'booked')
            ->whereBetween('booking_date', [$now, $threshold])
            ->get();

        foreach ($bookings as $booking) {
            CancelBookingJob::dispatch($booking);
            $this->info("Dispatched cancel job for booking ID: {$booking->id}");
        }

        $this->info("Total dispatched: {$bookings->count()}");
    }
}
