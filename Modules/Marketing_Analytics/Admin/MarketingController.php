<?php

namespace Modules\Marketing_Analytics\Admin;

use App\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use Modules\Flight\Models\BookingRoutes;
use Modules\User\Models\VisitorLog;

class MarketingController
{
    public function index()
    {
        // ========== Date Filter Setup ==========
        $fromDate = request('from_date')
            ? \Carbon\Carbon::parse(request('from_date'))->startOfDay()
            : now()->startOfMonth();

        $toDate = request('to_date')
            ? \Carbon\Carbon::parse(request('to_date'))->endOfDay()
            : now()->endOfDay();

        // Previous period (same duration, shifted back)
        $diffDays       = $fromDate->diffInDays($toDate) + 1;
        $prevFromDate   = $fromDate->copy()->subDays($diffDays);
        $prevToDate     = $toDate->copy()->subDays($diffDays);

        // ========== Reusable Booking Scope ==========
        // Current period base query builder (closure helper)
        $bookingQuery = fn() => Booking::whereNull('deleted_at')
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $prevBookingQuery = fn() => Booking::whereNull('deleted_at')
            ->whereBetween('created_at', [$prevFromDate, $prevToDate]);

        // ========== VISITOR TRACKING STATS ==========
        $visitorQuery     = fn() => VisitorLog::whereBetween('visited_at', [$fromDate, $toDate]);
        $prevVisitorQuery = fn() => VisitorLog::whereBetween('visited_at', [$prevFromDate, $prevToDate]);

        // Real-time online visitors (always live — no date filter)
        $onlineVisitors = VisitorLog::where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->count();

        $onlineUsers = VisitorLog::where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();

        $onlineGuests = $onlineVisitors - $onlineUsers;

        // Today's unique visitors (always today — no date filter)
        $todayUniqueVisitors = VisitorLog::whereDate('visited_at', today())
            ->distinct('ip_address')
            ->count();

        // Period visitors
        $totalWebsiteVisitors = $visitorQuery()->count();
        $lastPeriodVisitors   = $prevVisitorQuery()->count();

        $visitorGrowth = $lastPeriodVisitors > 0
            ? round((($totalWebsiteVisitors - $lastPeriodVisitors) / $lastPeriodVisitors) * 100, 1)
            : 0;

        // Visitor to booking conversion
        $visitorToBookingRate = $totalWebsiteVisitors > 0
            ? round(($bookingQuery()->count() / $totalWebsiteVisitors) * 100, 2)
            : 0;

        // Device breakdown
        $deviceBreakdown = $visitorQuery()
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->device_type => $item->count]);

        // Visitor geographic distribution (Top 10)
        $visitorsByCountry = $visitorQuery()
            ->select('country', 'country_code', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->where('country', '!=', 'Local')
            ->groupBy('country', 'country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Browser stats
        $browserStats = $visitorQuery()
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Traffic sources
        $trafficSources = $visitorQuery()
            ->select('landing_page', DB::raw('COUNT(*) as count'))
            ->groupBy('landing_page')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Avg session duration
        $avgSessionDuration = $visitorQuery()
            ->where('duration', '>', 0)
            ->avg('duration') ?? 0;

        // Bounce rate
        $singlePageVisits = $visitorQuery()->where('page_views', 1)->count();
        $bounceRate = $totalWebsiteVisitors > 0
            ? round(($singlePageVisits / $totalWebsiteVisitors) * 100, 1)
            : 0;

        // Daily visitor trend (based on selected range, max 90 days)
        $trendFrom = $fromDate->copy()->diffInDays($toDate) > 90
            ? $toDate->copy()->subDays(90)
            : $fromDate->copy();

        $dailyVisitorTrend = VisitorLog::selectRaw('
            DATE(visited_at) as date,
            COUNT(DISTINCT session_id) as total_visitors,
            COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as logged_in,
            COUNT(DISTINCT CASE WHEN user_id IS NULL THEN session_id END) as guests
        ')
            ->whereBetween('visited_at', [$trendFrom, $toDate])
            ->groupBy(DB::raw('DATE(visited_at)'))
            ->orderBy('date')
            ->get();

        // ========== BOOKING STATS ==========
        $totalBookings    = $bookingQuery()->count();
        $lastMonthBookings = $prevBookingQuery()->count();

        $bookingsGrowth = $lastMonthBookings > 0
            ? round((($totalBookings - $lastMonthBookings) / $lastMonthBookings) * 100, 1)
            : 0;

        // Revenue
        $totalRevenue = $bookingQuery()->where('status', 'ticketed')->sum('total');
        $lastMonthRevenue = $prevBookingQuery()->where('status', 'ticketed')->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Avg ticket price
        $avgTicketPrice   = $bookingQuery()->where('status', 'ticketed')->avg('total') ?? 0;
        $lastMonthAvgPrice = $prevBookingQuery()->where('status', 'ticketed')->avg('total') ?? 0;

        $avgPriceGrowth = $lastMonthAvgPrice > 0
            ? round((($avgTicketPrice - $lastMonthAvgPrice) / $lastMonthAvgPrice) * 100, 1)
            : 0;

        // Conversion rate
        $totalAttempts     = $bookingQuery()->count();
        $confirmedBookings = $bookingQuery()->where('is_paid', 1)->count();
        $conversionRate    = $totalAttempts > 0
            ? round(($confirmedBookings / $totalAttempts) * 100, 1)
            : 0;

        $prevTotalAttempts     = $prevBookingQuery()->count();
        $prevConfirmedBookings = $prevBookingQuery()->where('is_paid', 1)->count();
        $lastMonthConversionRate = $prevTotalAttempts > 0
            ? round(($prevConfirmedBookings / $prevTotalAttempts) * 100, 1)
            : 0;

        $conversionGrowth = round($conversionRate - $lastMonthConversionRate, 1);

        // ========== Revenue Trend (last 12 months — always fixed, no date filter) ==========
        $revenueTrend = Booking::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as bookings,
            SUM(total) as revenue,
            AVG(total) as avg_price
        ')
            ->whereNull('deleted_at')
            ->where('status', 'ticketed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        // ========== Top Routes (filtered) ==========
        $topRoutes = $bookingQuery()
            ->selectRaw('
                CONCAT(flight_from, " → ", flight_to) as route,
                COUNT(*) as bookings,
                SUM(total) as revenue,
                AVG(total) as avg_price,
                airline,
                seat_class
            ')
            ->where('status', 'ticketed')
            ->whereNotNull('flight_from')
            ->whereNotNull('flight_to')
            ->groupBy('flight_from', 'flight_to', 'airline', 'seat_class')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ========== Booking Sources (filtered) ==========
        $bookingSources = $bookingQuery()
            ->selectRaw('COALESCE(source, "Direct") as booking_source, COUNT(*) as bookings, SUM(total) as revenue')
            ->groupBy('source')
            ->orderByDesc('bookings')
            ->get()
            ->map(function ($source) use ($totalBookings) {
                $source->percentage = $totalBookings > 0
                    ? round(($source->bookings / $totalBookings) * 100, 2)
                    : 0;
                return $source;
            });

        // ========== Payment Methods (filtered) ==========
        $paymentMethods = $bookingQuery()
            ->selectRaw('gateway as payment_method, COUNT(*) as transactions, SUM(total) as revenue')
            ->where('status', 'ticketed')
            ->whereNotNull('gateway')
            ->groupBy('gateway')
            ->orderByDesc('transactions')
            ->get()
            ->map(function ($payment) use ($bookingQuery) {
                $totalPayments = $bookingQuery()
                    ->where('status', 'ticketed')
                    ->whereNotNull('gateway')
                    ->count();
                $payment->percentage = $totalPayments > 0
                    ? round(($payment->transactions / $totalPayments) * 100, 2)
                    : 0;
                return $payment;
            });

        // ========== Geographic Distribution (filtered) ==========
        $geographicData = $bookingQuery()
            ->selectRaw('country, COUNT(*) as bookings, SUM(total) as revenue')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get()
            ->map(function ($geo) use ($bookingQuery) {
                $totalGeoBookings = $bookingQuery()
                    ->whereNotNull('country')
                    ->where('country', '!=', '')
                    ->count();
                $geo->percentage = $totalGeoBookings > 0
                    ? round(($geo->bookings / $totalGeoBookings) * 100, 2)
                    : 0;
                return $geo;
            });

        // ========== Top Customers (filtered) ==========
        $topCustomers = $bookingQuery()
            ->selectRaw('
                customer_id,
                CONCAT(COALESCE(first_name, ""), " ", COALESCE(last_name, "")) as customer_name,
                email,
                COUNT(id) as total_bookings,
                SUM(total) as total_revenue
            ')
            ->whereNotNull('customer_id')
            ->where('status', 'ticketed')
            ->groupBy('customer_id', 'first_name', 'last_name', 'email')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // ========== Customer Segments (filtered) ==========
        $customerData = $bookingQuery()
            ->selectRaw('
                customer_id,
                COUNT(id) as total_bookings,
                SUM(total) as total_revenue,
                AVG(total) as avg_booking_value,
                CASE
                    WHEN COUNT(id) = 1 THEN "New"
                    WHEN COUNT(id) BETWEEN 2 AND 5 THEN "Returning"
                    ELSE "VIP"
                END as customer_segment
            ')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->get();

        $customerSegments = $customerData->groupBy('customer_segment')
            ->map(function ($segment, $key) {
                return (object) [
                    'customer_segment'  => $key,
                    'customers'         => $segment->count(),
                    'bookings'          => $segment->sum('total_bookings'),
                    'revenue'           => $segment->sum('total_revenue'),
                    'avg_customer_value' => $segment->avg('avg_booking_value'),
                ];
            })
            ->values();

        // ========== Airline Performance (filtered) ==========
        $airlinePerformance = $bookingQuery()
            ->selectRaw('
                airline,
                COUNT(*) as bookings,
                SUM(total) as revenue,
                AVG(total) as avg_ticket_price,
                SUM(COALESCE(adult_count, 0)) as total_adults,
                SUM(COALESCE(child_count, 0)) as total_children,
                SUM(COALESCE(infant_count, 0)) as total_infants
            ')
            ->where('status', 'ticketed')
            ->whereNotNull('airline')
            ->groupBy('airline')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ========== Flight Types (filtered) ==========
        $flightTypes = $bookingQuery()
            ->selectRaw('
                flight_type,
                seat_class,
                COUNT(*) as bookings,
                SUM(total) as revenue,
                AVG(total) as avg_price
            ')
            ->where('status', 'ticketed')
            ->whereNotNull('flight_type')
            ->groupBy('flight_type', 'seat_class')
            ->orderByDesc('revenue')
            ->get();

        // ========== Booking Patterns - Day of Week (filtered) ==========
        $bookingPatterns = $bookingQuery()
            ->selectRaw('
                DAYNAME(created_at) as day_name,
                DAYOFWEEK(created_at) as day_number,
                COUNT(*) as bookings,
                SUM(total) as revenue
            ')
            ->groupBy(DB::raw('DAYNAME(created_at)'), DB::raw('DAYOFWEEK(created_at)'))
            ->orderBy(DB::raw('DAYOFWEEK(created_at)'))
            ->get()
            ->map(function ($pattern) use ($totalBookings) {
                $pattern->percentage = $totalBookings > 0
                    ? round(($pattern->bookings / $totalBookings) * 100, 2)
                    : 0;
                return $pattern;
            });

        // ========== Booking Status (filtered) ==========
        $bookingStatus = $bookingQuery()
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(function ($status) use ($totalBookings) {
                $status->percentage = $totalBookings > 0
                    ? round(($status->count / $totalBookings) * 100, 2)
                    : 0;
                return $status;
            });

        // ========== New Users in Period ==========
        $newUsersCount = User::whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $prevNewUsers = User::whereBetween('created_at', [$prevFromDate, $prevToDate])
            ->count();

        $newUsersGrowth = $prevNewUsers > 0
            ? round((($newUsersCount - $prevNewUsers) / $prevNewUsers) * 100, 1)
            : 0;

        // ========== Revenue Breakdown (filtered) ==========
        $revenueBreakdown = $bookingQuery()
            ->selectRaw('
                SUM(total) as total_revenue,
                SUM(COALESCE(base_fee, 0)) as base_revenue,
                SUM(COALESCE(supplier_fee, 0)) as supplier_revenue,
                SUM(COALESCE(ticketing_fee, 0)) as ticketing_revenue,
                SUM(COALESCE(total_fee, 0)) as total_fees,
                SUM(COALESCE(commission, 0)) as total_commission
            ')
            ->where('status', 'ticketed')
            ->first();

        // ========== Passenger Analysis (filtered) ==========
        $passengerAnalysis = [
            'adults'   => $bookingQuery()->sum('adult_count') ?? 0,
            'children' => $bookingQuery()->sum('child_count') ?? 0,
            'infants'  => $bookingQuery()->sum('infant_count') ?? 0,
        ];

        $totalPassengers      = array_sum($passengerAnalysis);
        $customerLifetimeValue = $topCustomers->avg('total_revenue') ?? 0;
        $activeCampaigns      = 18;
        $customerSatisfaction = 4.7;

        return view('Marketing_Analytics::admin.marketing_dashboard', compact(
            'fromDate', 'toDate',
            'totalBookings', 'bookingsGrowth',
            'totalRevenue', 'revenueGrowth',
            'avgTicketPrice', 'avgPriceGrowth',
            'conversionRate', 'conversionGrowth',
            'customerLifetimeValue', 'activeCampaigns', 'customerSatisfaction',
            'totalWebsiteVisitors', 'visitorGrowth',
            'onlineVisitors', 'onlineUsers', 'onlineGuests',
            'todayUniqueVisitors', 'visitorToBookingRate',
            'deviceBreakdown', 'visitorsByCountry', 'browserStats',
            'trafficSources', 'avgSessionDuration', 'bounceRate',
            'dailyVisitorTrend', 'revenueTrend', 'topRoutes',
            'bookingSources', 'paymentMethods', 'geographicData',
            'topCustomers', 'customerSegments', 'airlinePerformance',
            'flightTypes', 'bookingPatterns', 'bookingStatus',
            'revenueBreakdown', 'passengerAnalysis', 'totalPassengers','newUsersCount', 'newUsersGrowth',
        ));
    }
}
