<?php
namespace Modules\Report\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Booking\Emails\NewBookingEmail;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\Transaction;

class StatisticController extends AdminController
{
    public function __construct()
    {

    }

    public function index()
    {
        $f = strtotime('monday this week');
        $status = config('booking.statuses');
        $data = [
            'earning_chart_data'  => Booking::getStatisticChartData($f, time(), $status)['chart'],
            'earning_detail_data' => Booking::getStatisticChartData($f, time(), $status)['detail']
        ];
        return view('Report::admin.statistic.index', $data);
    }

    public function wallet_statistic(Request $request)
    {
        // ─── Date Range ───────────────────────────────────────────────────────
        $dateRange = $request->input('date_range', 'this_month');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today()->toDateString();
                $endDate   = Carbon::today()->toDateString();
                break;
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek()->toDateString();
                $endDate   = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'last_week':
                $startDate = Carbon::now()->subWeek()->startOfWeek()->toDateString();
                $endDate   = Carbon::now()->subWeek()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate   = Carbon::now()->endOfMonth()->toDateString();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->toDateString();
                $endDate   = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'last_year':
                $startDate = Carbon::now()->subYear()->startOfYear()->toDateString();
                $endDate   = Carbon::now()->subYear()->endOfYear()->toDateString();
                break;
            default:
                if (!$startDate) $startDate = Carbon::now()->startOfMonth()->toDateString();
                if (!$endDate)   $endDate   = Carbon::today()->toDateString();
        }

        // ─── Filters ─────────────────────────────────────────────────────────
        $userId = $request->input('user_id');
        $status = $request->input('status');

        // ─── Date filter closure ──────────────────────────────────────────────
        $dateFilter = function ($q) use ($startDate, $endDate, $userId, $status) {
            $q->when($startDate, fn($q) => $q->whereDate('deposit_date', '>=', $startDate))
                ->when($endDate,   fn($q) => $q->whereDate('deposit_date', '<=', $endDate))
                ->when($userId,    fn($q) => $q->where('user_id', $userId))
                ->when($status,    fn($q) => $q->where('status', $status));
        };

        // ─── Data for DataTables ──────────────────────────────────────────────
        $deposits  = Transaction::with('author')->where('type', 'deposit')
            ->tap($dateFilter)->orderBy('id', 'desc')->get();

        $withdraws = Transaction::with('author')->where('type', 'withdraw')
            ->tap($dateFilter)->orderBy('id', 'desc')->get();

        // ─── Summary stats ────────────────────────────────────────────────────
        $alls = Transaction::tap($dateFilter)->get();

        $stats = [
            'total_count'      => $alls->count(),
            'total_amount'     => $alls->sum('amount'),
            'pending_count'    => $alls->where('status', 'pending')->count(),
            'pending_amount'   => $alls->where('status', 'pending')->sum('amount'),
            'confirmed_count'  => $alls->where('status', 'confirmed')->count(),
            'confirmed_amount' => $alls->where('status', 'confirmed')->sum('amount'),
            'deposit_count'    => $deposits->count(),
            'deposit_amount'   => $deposits->sum('amount'),
            'withdraw_count'   => $withdraws->count(),
            'withdraw_amount'  => $withdraws->sum('amount'),
        ];

        // ─── Filter lists ─────────────────────────────────────────────────────
        $users    = \App\User::orderBy('name')->get(['id', 'name', 'email']);
        $statuses = Transaction::distinct()->whereNotNull('status')->pluck('status');

        return view('Report::admin.statistic.wallet_statistic', compact(
            'deposits', 'withdraws', 'stats',
            'startDate', 'endDate', 'dateRange',
            'userId', 'status', 'users', 'statuses'
        ));
    }


//public function wallet_statistic(Request $request)
//    {
//        // Get date range values
//        $from = request()->from;
//        $to   = request()->to;
//
//        // Base queries
//        $depositQuery  = Transaction::where('type', 'deposit')->orderBy('id', 'desc');
//        $withdrawQuery = Transaction::where('type', 'withdraw')->orderBy('id', 'desc');
//        $allQuery      = Transaction::query();
//
//        // Apply date filters only if selected
//        if ($from && $to) {
//            $depositQuery->whereBetween('deposit_date', [$from, $to]);
//            $withdrawQuery->whereBetween('deposit_date', [$from, $to]);
//            $allQuery->whereBetween('deposit_date', [$from, $to]);
//        }
//
//        // Pagination results
//        $deposits  = $depositQuery->paginate(10);
//        $withdraws = $withdrawQuery->paginate(10);
//
//        // Summary (no pagination)
//        $depositAll  = $depositQuery->get();
//        $withdrawAll = $withdrawQuery->get();
//        $alls        = $allQuery->get();
//
//        return view('Report::admin.statistic.wallet_statistic', compact(
//            'deposits',
//            'withdraws',
//            'depositAll',
//            'withdrawAll',
//            'alls'
//        ));
//
//    }

    public function reloadChart(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $status = config('booking.statuses');
        $customer_id = false;
        $vendor_id = false;
        $user_type = $request->input('user_type');
        if ($user_type == 'customer') {
            $customer_id = $request->input('user_id');
        }
        if ($user_type == 'vendor') {
            $vendor_id = $request->input('user_id');
        }
        return $this->sendSuccess([
            'chart_data'  => Booking::getStatisticChartData(strtotime($from), strtotime($to), $status, $customer_id, $vendor_id)['chart'],
            'detail_data' => Booking::getStatisticChartData(strtotime($from), strtotime($to), $status, $customer_id, $vendor_id)['detail']
        ]);
    }
}
