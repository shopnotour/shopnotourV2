<?php

namespace Modules\User\Admin;


    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Modules\Flight\Models\Airport;
    use Modules\User\Models\VisitorActivity;
    use Modules\User\Models\VisitorLog;
    use Modules\User\Models\VisitorPageLog;

    class VisitorController extends Controller
{
    /**
     * Display visitor dashboard
     */
        public function index(Request $request)
        {
            $stats = VisitorLog::getRealTimeStats();

            $onlineVisitors = VisitorLog::with(['user', 'pageLog', 'activities'])
                ->where('is_online', true)
                ->where('last_activity_at', '>=', now()->subMinutes(5))
                ->orderBy('last_activity_at', 'desc')
                ->get();

            $locationStats = VisitorLog::getVisitorsByLocation();
            $deviceStats   = VisitorLog::getDeviceStats();
            $hourlyStats   = $this->getHourlyStats();
            $topPages      = $this->getTopPages();

            return view('User::admin.visitor.index', compact(
                'stats', 'onlineVisitors', 'locationStats',
                'deviceStats', 'hourlyStats', 'topPages'
            ));
        }
//    public function index(Request $request)
//    {
//        // Real-time statistics
//        $stats = VisitorLog::getRealTimeStats();
//
//        // Get online visitors with details
//        $onlineVisitors = VisitorLog::with('user')
//            ->where('is_online', true)
//            ->where('last_activity_at', '>=', now()->subMinutes(5))
//            ->orderBy('last_activity_at', 'desc')
//            ->get();
//
//        // Get visitors by location
//        $locationStats = VisitorLog::getVisitorsByLocation();
//
//        // Get device statistics
//        $deviceStats = VisitorLog::getDeviceStats();
//
//        // Get hourly visitors for today
//        $hourlyStats = $this->getHourlyStats();
//
//        // Get top pages
//        $topPages = $this->getTopPages();
//
//        return view('User::admin.visitor.index', compact(
//            'stats',
//            'onlineVisitors',
//            'locationStats',
//            'deviceStats',
//            'hourlyStats',
//            'topPages'
//        ));
//    }

    /**
     * Get real-time data (AJAX endpoint)
     */
    public function getRealTimeData()
    {
        $stats = VisitorLog::getRealTimeStats();

        $onlineVisitors = VisitorLog::with('user')
            ->where('is_online', true)
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(function ($visitor) {
                return [
                    'id' => $visitor->id,
                    'user_name' => $visitor->user ? $visitor->user->name : 'Guest',
                    'ip_address' => $visitor->ip_address,
                    'country' => $visitor->country,
                    'city' => $visitor->city,
                    'device_type' => $visitor->device_type,
                    'browser' => $visitor->browser,
                    'current_page' => $visitor->current_page,
                    'last_activity' => $visitor->last_activity_at->diffForHumans(),
                    'duration' => $visitor->visited_at->diffInMinutes($visitor->last_activity_at) . ' min',
                ];
            });

        return response()->json([
            'stats' => $stats,
            'visitors' => $onlineVisitors,
        ]);
    }

    /**
     * Get visitor history
     */

        public function history(Request $request)
        {
            $query = VisitorLog::with(['user']); // activities বাদ দাও

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('visited_at', [
                    $request->from_date . ' 00:00:00',
                    $request->to_date   . ' 23:59:59',
                ]);
            }

            if ($request->has('user_type')) {
                if ($request->user_type == 'logged_in') {
                    $query->whereNotNull('user_id');
                } elseif ($request->user_type == 'guest') {
                    $query->whereNull('user_id');
                }
            }

            if ($request->has('country') && $request->country) {
                $query->where('country', $request->country);
            }

            if ($request->has('has_activity') && $request->has_activity) {
                $query->whereHas('activities', function ($q) use ($request) {
                    $q->where('activity_type', $request->has_activity);
                });
            }

            // select শুধু দরকারি columns — memory বাঁচাবে
            $visitors = $query
                ->select([
                    'id','user_id','ip_address','device_type',
                    'browser','country','city','visited_at',
                    'last_activity_at','page_views','is_online','status',
                    'landing_page'
                ])
                ->orderBy('visited_at', 'desc')
                ->paginate(5); // 50 থেকে 20 এ নামাও

            $countries = VisitorLog::select('country')
                ->distinct()
                ->whereNotNull('country')
                ->orderBy('country')
                ->pluck('country');

            return view('User::admin.visitor.history', compact('visitors', 'countries'));
        }

    /**
     * Get visitor details
     */
//        public function show($id)
//        {
//            // pageLog আর activities সহ load করো
//            $visitor = VisitorLog::with(['user', 'pageLog', 'activities'])
//                ->findOrFail($id);
//
//            // Same IP এর অন্য visits
//            $relatedVisits = VisitorLog::where('ip_address', $visitor->ip_address)
//                ->where('id', '!=', $id)
//                ->orderBy('visited_at', 'desc')
//                ->limit(10)
//                ->get();
//
//            return view('User::admin.visitor.show', compact('visitor', 'relatedVisits'));
//        }

        public function show($id, Request $request)
        {

            $userId  = $request->query('user_id');
            $visitor = VisitorLog::with(['user', 'activities'])->findOrFail($id);

//            return $visitor;
            // Airport ID → name map তৈরি করো
            $airportMap = \Modules\Flight\Models\Airport::whereNotNull('code')
                ->get(['id', 'name', 'code', 'address'])
                ->keyBy('id')
                ->map(fn($a) => ['name' => $a->name, 'code' => $a->code, 'address' => $a->address])
                ->toArray();

            if ($userId) {
                $relatedVisits = VisitorLog::where('user_id', $userId)
                    ->where('id', '!=', $id)
                    ->orderBy('visited_at', 'desc')
                    ->limit(20)->get();
                $filterType = 'user';
            } else {
                $relatedVisits = VisitorLog::whereNull('user_id')
                    ->where('ip_address', $visitor->ip_address)
                    ->where('id', '!=', $id)
                    ->orderBy('visited_at', 'desc')
                    ->limit(20)->get();
                $filterType = 'ip';
            }
//return $visitor;
            return view('User::admin.visitor.show', compact('visitor', 'relatedVisits', 'filterType', 'airportMap'));
        }

        // Date অনুযায়ী activities দেখাও
        public function showByDate($id, Request $request)
        {
            $date = $request->query('date');

            $activities = VisitorActivity::where('visitor_log_id', $id)
                ->whereDate('created_at', $date)
                ->orderBy('occurred_at')
                ->get();

            return response()->json([
                'activities' => $activities,
                'page_logs'  => [],  // খালি পাঠাও
            ]);
        }

// Date অনুযায়ী delete করো
        public function deleteByDate($id, Request $request)
        {
            $date = $request->input('date');

            $deleted = VisitorActivity::where('visitor_log_id', $id)
                ->whereDate('created_at', $date)
                ->delete();

            return response()->json(['success' => true, 'deleted' => $deleted]);
        }

// Date অনুযায়ী CSV download
        public function downloadByDate($id, Request $request)
        {
            $date    = $request->query('date');
            $visitor = VisitorLog::findOrFail($id);

            $activities = VisitorActivity::where('visitor_log_id', $id)
                ->whereDate('created_at', $date)
                ->orderBy('occurred_at')
                ->get();

            $filename = "visitor_{$id}_{$date}.csv";

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ];

            $callback = function () use ($activities, $visitor, $date) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Visitor ID', 'Date', 'Activity Type', 'Page URL', 'Element Text', 'Activity Data', 'Occurred At']);
                foreach ($activities as $act) {
                    fputcsv($file, [
                        $visitor->id,
                        $date,
                        $act->activity_type,
                        $act->page_url,
                        $act->element_text,
                        json_encode($act->activity_data),
                        $act->occurred_at,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
    /**
     * Get statistics
     */
    public function statistics()
    {
        // Daily visitors for last 30 days
        $dailyVisitors = VisitorLog::select(
            DB::raw('DATE(visited_at) as date'),
            DB::raw('COUNT(DISTINCT session_id) as total'),
            DB::raw('COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as logged_in'),
            DB::raw('COUNT(DISTINCT CASE WHEN user_id IS NULL THEN session_id END) as guests')
        )
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top countries
        $topCountries = VisitorLog::select('country', 'country_code', DB::raw('COUNT(*) as total'))
            ->whereNotNull('country')
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('country', 'country_code')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Browser statistics
        $browserStats = VisitorLog::select('browser', DB::raw('COUNT(*) as total'))
            ->whereNotNull('browser')
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('browser')
            ->orderBy('total', 'desc')
            ->get();

        // Device statistics
        $deviceStats = VisitorLog::select('device_type', DB::raw('COUNT(*) as total'))
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('device_type')
            ->get();

        // Platform statistics
        $platformStats = VisitorLog::select('platform', DB::raw('COUNT(*) as total'))
            ->whereNotNull('platform')
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('platform')
            ->orderBy('total', 'desc')
            ->get();

        return view('User::admin.visitor.statictics', compact(
            'dailyVisitors',
            'topCountries',
            'browserStats',
            'deviceStats',
            'platformStats'
        ));
    }

    /**
     * Get hourly statistics for today
     */
    protected function getHourlyStats()
    {
        return VisitorLog::select(
            DB::raw('HOUR(visited_at) as hour'),
            DB::raw('COUNT(DISTINCT session_id) as total')
        )
            ->whereDate('visited_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get top visited pages
     */
    protected function getTopPages()
    {
        return VisitorLog::select('current_page', DB::raw('COUNT(*) as visits'))
            ->whereNotNull('current_page')
            ->whereDate('visited_at', today())
            ->groupBy('current_page')
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Clean old records
     */
        public function cleanup(Request $request)
        {
            $days = $request->input('days', 30);

            // VisitorLog delete করলে সাথে VisitorPageLog ও যাবে (cascade)
            $deleted = VisitorLog::cleanOldRecords($days);

            // VisitorPageLog এ এমন records থাকতে পারে যেগুলো VisitorLog এ নেই
            VisitorPageLog::cleanOldRecords($days);

            return redirect()->back()->with(
                $deleted > 0 ? 'success' : 'info',
                $deleted > 0 ? "Deleted {$deleted} old records." : 'No old records found.'
            );
        }
}
