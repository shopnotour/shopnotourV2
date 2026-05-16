<?php
//
//namespace Modules\User\Admin;
//
//use App\User;
//use Illuminate\Http\Request;
//use Illuminate\Routing\Controller;
//use Modules\User\Models\Wallet\Transaction;
//use Modules\Booking\Models\Booking;
//
//class MarketingDashboardController extends Controller
//{
//    // ✅ Main Dashboard
//    public function index(Request $request)
//    {
//        // Default today
//        $dateFrom = $request->input('date_from', date('Y-m-d'));
//        $dateTo   = $request->input('date_to',   date('Y-m-d'));
//
//        // Total B2C Users — date filter নেই (সব সময়ের)
//        // B2C Customer (role_id = 3)
//        $totalB2CUsers = User::where('role_id', 3)->whereNull('deleted_at')->count();
//        $loggedInB2CUsers = User::where('role_id', 3)->where('active_status', 1)->whereNull('deleted_at')->count();
//
//// B2B Agent (role_id = 2)
//        $totalB2BUsers = User::where('role_id', 2)->whereNull('deleted_at')->count();
//        $loggedInB2BUsers = User::where('role_id', 2)->where('active_status', 1)->whereNull('deleted_at')->count();
//        // Deposit
//        $totalDeposit = \DB::table('credit_transactions')
//            ->where('type', 'deposit')->where('status', 'confirmed')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//        $pendingDeposit = \DB::table('credit_transactions')
//            ->where('type', 'deposit')->where('status', 'pending')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//
//// Withdraw
//        $totalWithdraw = \DB::table('credit_transactions')
//            ->where('type', 'withdraw')->where('status', 'confirmed')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//        $pendingWithdraw = \DB::table('credit_transactions')
//            ->where('type', 'withdraw')->where('status', 'pending')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//
//// Payment (debit + payment status)
//        $totalPaymentAmount = Transaction::where('type', 'debit')->where('status', 'payment')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
//            ->sum('amount');
//        $totalPaymentCount = \DB::table('credit_transactions')
//            ->where('type', 'debit')->where('status', 'payment')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
//            ->count();
//        // Search Sessions
//        $searchCount = \DB::table('search_sessions')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//
//        // Price Checked
//        $priceCheckedCount = \DB::table('select_sessions')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
//
//        // Booking Activities
//        $bookingActivities = Booking::selectRaw('status, COUNT(*) as total')
//            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
//            ->groupBy('status')->orderBy('status')->get();
//
//        return view('User::admin.marketing.dashboard', compact(
//            'totalB2CUsers', 'loggedInB2CUsers', 'totalB2BUsers', 'loggedInB2BUsers',
//            'totalDeposit', 'pendingDeposit',
//            'totalWithdraw', 'pendingWithdraw',
//            'totalPaymentAmount', 'totalPaymentCount', // ← এই দুটো ছিল না
//            'searchCount', 'priceCheckedCount',
//            'bookingActivities',
//            'dateFrom', 'dateTo'
//        ));
//    }
//
//    // ✅ Search Sessions — Level 1: User list with search count
//    public function searchSessions()
//    {
//        $users = \DB::table('search_sessions')
//            ->join('users', 'search_sessions.user_id', '=', 'users.id')
//            ->selectRaw('
//                users.id as user_id,
//                users.first_name,
//                users.last_name,
//                users.email,
//                users.phone,
//                COUNT(search_sessions.id) as search_count,
//                MAX(search_sessions.created_at) as last_search_at
//            ')
//            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone')
//            ->orderByDesc('search_count')
//            ->paginate(20);
//
//        return view('User::admin.marketing.search-sessions', compact('users'));
//    }
//
//    // ✅ Search Sessions — Level 2: Single user's all search sessions
//    public function searchSessionDetail(Request $request, $userId)
//    {
//        $user = User::findOrFail($userId);
//
//        // Default: today's date যদি request এ না থাকে
//        $dateFrom = $request->input('date_from', date('Y-m-d'));
//        $dateTo   = $request->input('date_to',   date('Y-m-d'));
//
//        $query = \DB::table('search_sessions')
//            ->where('user_id', $userId)
//            ->orderByDesc('created_at');
//
//        $query->whereDate('created_at', '>=', $dateFrom);
//        $query->whereDate('created_at', '<=', $dateTo);
//
//        $sessions = $query->paginate(20)->appends([
//            'date_from' => $dateFrom,
//            'date_to'   => $dateTo,
//        ]);
//        $airportIds = collect();
//        foreach ($sessions as $session) {
//            $data = json_decode($session->data, true);
//            if (!empty($data['segments'])) {
//                foreach ($data['segments'] as $segment) {
//                    if (!empty($segment['from'])) $airportIds->push($segment['from']);
//                    if (!empty($segment['to']))   $airportIds->push($segment['to']);
//                }
//            }
//        }
//
//        $airports = \DB::table('bravo_airport')
//            ->whereIn('id', $airportIds->unique()->values())
//            ->select('id', 'name', 'code')
//            ->get()
//            ->keyBy('id');
//
//        return view('User::admin.marketing.search-session-detail', compact('user', 'sessions', 'airports'));
//    }
//    // ✅ Select Sessions — Level 1: User list with price check count
//    public function selectSessions()
//    {
//        $users = \DB::table('select_sessions')
//            ->join('users', 'select_sessions.user_id', '=', 'users.id')
//            ->selectRaw('
//                users.id as user_id,
//                users.first_name,
//                users.last_name,
//                users.email,
//                users.phone,
//                COUNT(select_sessions.id) as select_count,
//                MAX(select_sessions.created_at) as last_select_at
//            ')
//            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone')
//            ->orderByDesc('select_count')
//            ->paginate(20);
//
//        return view('User::admin.marketing.select-sessions', compact('users'));
//    }
//
//    // ✅ Select Sessions — Level 2: Single user's all select sessions
//    public function selectSessionDetail(Request $request, $userId)
//    {
//        $user = User::findOrFail($userId);
//
//        $dateFrom = $request->input('date_from', date('Y-m-d'));
//        $dateTo   = $request->input('date_to',   date('Y-m-d'));
//
//        $sessions = \DB::table('select_sessions')
//            ->where('user_id', $userId)
//            ->whereDate('created_at', '>=', $dateFrom)
//            ->whereDate('created_at', '<=', $dateTo)
//            ->orderByDesc('created_at')
//            ->paginate(20)
//            ->appends(['date_from' => $dateFrom, 'date_to' => $dateTo]);
//
//        return view('User::admin.marketing.select-session-detail', compact('user', 'sessions'));
//    }
//}


namespace Modules\User\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\Wallet\Transaction;
use Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;

class MarketingDashboardController extends Controller
{
    // ✅ Main Dashboard
    public function index(Request $request)
    {
        // Default today
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Total B2C Users — date filter নেই (সব সময়ের)
        // B2C Customer (role_id = 3)
        $totalB2CUsers = User::where('role_id', 3)->whereNull('deleted_at')->count();
        $loggedInB2CUsers = User::where('role_id', 3)->where('active_status', 1)->whereNull('deleted_at')->count();

// B2B Agent (role_id = 2)
        $totalB2BUsers = User::where('role_id', 2)->whereNull('deleted_at')->count();
        $loggedInB2BUsers = User::where('role_id', 2)->where('active_status', 1)->whereNull('deleted_at')->count();
        // Deposit
        $totalDeposit = DB::table('credit_transactions')
            ->where('type', 'deposit')->where('status', 'confirmed')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
        $pendingDeposit = DB::table('credit_transactions')
            ->where('type', 'deposit')->where('status', 'pending')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();

// Withdraw
        $totalWithdraw = DB::table('credit_transactions')
            ->where('type', 'withdraw')->where('status', 'confirmed')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();
        $pendingWithdraw = DB::table('credit_transactions')
            ->where('type', 'withdraw')->where('status', 'pending')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();

// Payment (debit + payment status)
        $totalPaymentAmount = Transaction::where('type', 'debit')->where('status', 'payment')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
            ->sum('amount');
        $totalPaymentCount = DB::table('credit_transactions')
            ->where('type', 'debit')->where('status', 'payment')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
            ->count();
        // Search Sessions
        $searchCount = DB::table('search_sessions')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();

        // Price Checked
        $priceCheckedCount = DB::table('select_sessions')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)->count();

        // Booking Activities
        $bookingActivities = Booking::selectRaw('status, COUNT(*) as total')
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
            ->groupBy('status')->orderBy('status')->get();

        return view('User::admin.marketing.dashboard', compact(
            'totalB2CUsers', 'loggedInB2CUsers', 'totalB2BUsers', 'loggedInB2BUsers',
            'totalDeposit', 'pendingDeposit',
            'totalWithdraw', 'pendingWithdraw',
            'totalPaymentAmount', 'totalPaymentCount', // ← এই দুটো ছিল না
            'searchCount', 'priceCheckedCount',
            'bookingActivities',
            'dateFrom', 'dateTo'
        ));
    }

    // ✅ Search Sessions — Level 1: User list with search count
    public function searchSessions(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $users = DB::table('search_sessions')
            ->join('users', 'search_sessions.user_id', '=', 'users.id')
            ->selectRaw('
                users.id as user_id,
                users.first_name,
                users.last_name,
                users.email,
                users.phone,
                COUNT(search_sessions.id) as search_count,
                MAX(search_sessions.created_at) as last_search_at
            ')
            ->whereDate('search_sessions.created_at', '>=', $dateFrom)
            ->whereDate('search_sessions.created_at', '<=', $dateTo)
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone')
            ->orderByDesc('search_count')
            ->paginate(20)
            ->appends(['date_from' => $dateFrom, 'date_to' => $dateTo]);

        return view('User::admin.marketing.search-sessions', compact('users', 'dateFrom', 'dateTo'));
    }

    // ✅ Search Sessions — Level 2: Single user's all search sessions
    public function searchSessionDetail(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Default: today's date যদি request এ না থাকে
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $query = DB::table('search_sessions')
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        $query->whereDate('created_at', '>=', $dateFrom);
        $query->whereDate('created_at', '<=', $dateTo);

        $sessions = $query->paginate(20)->appends([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        $airportIds = collect();
        foreach ($sessions as $session) {
            $data = json_decode($session->data, true);
            if (!empty($data['segments'])) {
                foreach ($data['segments'] as $segment) {
                    if (!empty($segment['from'])) $airportIds->push($segment['from']);
                    if (!empty($segment['to'])) $airportIds->push($segment['to']);
                }
            }
        }

        $airports = DB::table('bravo_airport')
            ->whereIn('id', $airportIds->unique()->values())
            ->select('id', 'name', 'code')
            ->get()
            ->keyBy('id');

        return view('User::admin.marketing.search-session-detail', compact('user', 'sessions', 'airports'));
    }

    // ✅ Search Sessions — Bulk Delete
    public function deleteSearchSessions(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', __('No sessions selected.'));
        }

        DB::table('search_sessions')->whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', __('Selected sessions deleted successfully.'));
    }

    // ✅ Select Sessions — Level 1: User list with price check count
    public function selectSessions(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $query = DB::table('select_sessions')
            ->join('users', 'select_sessions.user_id', '=', 'users.id')
            ->selectRaw('
                users.id as user_id,
                users.first_name,
                users.last_name,
                users.email,
                users.phone,
                COUNT(select_sessions.id) as select_count,
                MAX(select_sessions.created_at) as last_select_at
            ')
            ->whereDate('select_sessions.created_at', '>=', $dateFrom)
            ->whereDate('select_sessions.created_at', '<=', $dateTo)
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone')
            ->orderByDesc('select_count');

        if ($s = $request->input('s')) {
            $query->where(function ($q) use ($s) {
                $q->where('users.first_name', 'like', '%' . $s . '%')
                    ->orWhere('users.last_name', 'like', '%' . $s . '%')
                    ->orWhere('users.email', 'like', '%' . $s . '%')
                    ->orWhere('users.phone', 'like', '%' . $s . '%');
            });
        }

        $users = $query->paginate(20)->appends([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            's' => $request->input('s'),
        ]);

        if ($request->ajax()) {
            return view('User::admin.marketing.select_session_cards', compact('users'))->render();
        }

        return view('User::admin.marketing.select-sessions', compact('users', 'dateFrom', 'dateTo'));
    }

    // ✅ Select Sessions — Level 2: Single user's all select sessions
    public function selectSessionDetail(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $sessions = DB::table('select_sessions')
            ->where('user_id', $userId)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(['date_from' => $dateFrom, 'date_to' => $dateTo]);

        return view('User::admin.marketing.select-session-detail', compact('user', 'sessions'));
    }

    // ✅ Select Sessions — Bulk Delete
    public function deleteSelectSessions(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', __('No sessions selected.'));
        }

        DB::table('select_sessions')->whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', __('Selected sessions deleted successfully.'));
    }
}
