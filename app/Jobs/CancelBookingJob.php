<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Booking\Controllers\BookingController;
use Modules\Booking\Models\Booking;

class CancelBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // failed হলে 3 বার retry করবে

    protected $booking;

    public function __construct(    Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle()
    {
        DB::transaction(function () {
            // আবার check করো — এর মধ্যে paid হয়ে গেছে কিনা
            $booking = Booking::where('id', $this->booking->id)
                ->where('status', 'booked')
                ->first();

            if (!$booking) return;

            $controller = app(\Modules\Booking\Controllers\BookingController::class);
            $controller->cancelBooking($booking->id, 'System (Auto Cancel)');

            $booking->update([
                'status'      => 'cancelled',
                'updated_at' => now(),
            ]);

            // Email notification (optional)
            // Mail::to($booking->user->email)->send(new BookingCancelledMail($booking));
        });
    }

    public function failed(\Throwable $exception)
    {
        \Log::error("CancelBookingJob failed for booking ID: {$this->booking->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}
