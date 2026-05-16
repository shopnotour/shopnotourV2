<?php

namespace Modules\Dashboard\Admin;

use Illuminate\Http\Request;
use Modules\AdminController;
use Modules\Booking\Models\Booking;

class DashboardController extends AdminController
{

    public function index()
    {
        $f = strtotime('monday this week');

        // ✅ Booked Calendar Data — confirmed_at date অনুযায়ী
        $bookedCalendarData = \Modules\Booking\Models\Booking::whereNotNull('confirmed_at')
            ->where('status', 'booked')
            ->select('id', 'pnr_id', 'confirmed_at')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->confirmed_at)->format('Y-m-d');
            })
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id'     => $item->id,
                        'pnr_id' => $item->pnr_id,
                    ];
                })->values();
            });

        // ✅ Issued Calendar Data — ticket_issued_at date অনুযায়ী
        $issuedCalendarData = \Modules\Booking\Models\Booking::whereNotNull('ticket_issued_at')
            ->where('status', 'issued')
            ->select('id', 'pnr_id', 'ticket_issued_at')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->ticket_issued_at)->format('Y-m-d');
            })
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id'     => $item->id,
                        'pnr_id' => $item->pnr_id,
                    ];
                })->values();
            });

        $data = [
            'recent_bookings'     => \Modules\Booking\Models\Booking::getRecentBookings(),
            'top_cards'           => \Modules\Booking\Models\Booking::getTopCardsReport(),
            'earning_chart_data'  => \Modules\Booking\Models\Booking::getDashboardChartData($f, time()),
            'booked_calendar'     => $bookedCalendarData,
            'issued_calendar'     => $issuedCalendarData,
        ];

        return view('Dashboard::index', $data);
    }

    public function reloadChart(Request $request)
    {
        $chart = $request->input('chart');
        switch ($chart) {
            case "earning":
                $from = $request->input('from');
                $to = $request->input('to');
                return $this->sendSuccess([
                    'data' => Booking::getDashboardChartData(strtotime($from), strtotime($to))
                ]);
                break;
        }
    }
}
