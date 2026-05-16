<?php

namespace Modules\Booking\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use Modules\Flight\Models\BookingRoutes;
use Modules\User\Models\Wallet\Transaction;

class TransactionController
{

    public function index(Request $request)
    {
        $userId = $request->get('user_id');

        $query = Transaction::query()
            ->select(
                'credit_transactions.id',
                'credit_transactions.user_id',
                'credit_transactions.booking_id',
                'credit_transactions.ref_id',
                'credit_transactions.type',
                'credit_transactions.amount',
                'credit_transactions.meta',
                'credit_transactions.status',
                'credit_transactions.deleted_at',
                'credit_transactions.create_user',
                'credit_transactions.update_user',
                'credit_transactions.created_at',
                'credit_transactions.updated_at',
                'credit_transactions.reference',
                'credit_transactions.transaction_type',
                'credit_transactions.deposit_date',
                'credit_transactions.attachment_id',
                'credit_transactions.remarks'
            )
            ->leftJoin('users', 'credit_transactions.user_id', '=', 'users.id')
            ->addSelect([
                'users.name as user_name',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.phone',
                'users.avatar_id',
                'users.credit_balance',
                'users.business_name'
            ]);

        if ($userId) {
            $query->where('credit_transactions.user_id', $userId);
        }

        $transactions = $query->orderBy('credit_transactions.created_at', 'desc')->get();

        // ✅ deposit ও credit দুটোই Receive হিসেবে count করো
        $totalCredit = 0;
        $totalDebit  = 0;

        foreach ($transactions as $transaction) {
            if (in_array($transaction->type, ['credit', 'deposit'])) {
                $totalCredit += $transaction->amount;
            } elseif ($transaction->type === 'debit') {
                $totalDebit += $transaction->amount;
            }

            // ✅ meta parse করে balance_after & balance_before attach করো
            $meta = null;
            if (!empty($transaction->meta)) {
                $decoded = json_decode($transaction->meta, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }
            $transaction->balance_after  = $meta['balance_after']  ?? null;
            $transaction->balance_before = $meta['balance_before'] ?? null;
            $transaction->booking_code   = $meta['booking_code']   ?? null;
            $transaction->payment_method = $meta['payment_method'] ?? null;
        }

        $walletBalance = $totalCredit - $totalDebit;

        $userWallet = null;
        if ($userId) {
            $userWallet = User::select(
                'id', 'name', 'first_name', 'last_name', 'email',
                'phone', 'address', 'city', 'state', 'country',
                'credit_balance', 'avatar_id', 'business_name', 'status', 'created_at'
            )->where('id', $userId)->first();
        }

        $users = User::select(
            'id', 'name', 'first_name', 'last_name', 'email',
            'phone', 'business_name', 'credit_balance', 'status'
        )
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $totalUsersWallet = User::whereNull('deleted_at')->sum('credit_balance');

        return view('Booking::admin.transactions.index', compact(
            'transactions', 'totalCredit', 'totalDebit', 'walletBalance',
            'users', 'userId', 'userWallet', 'totalUsersWallet'
        ));
    }
//    public function index(Request $request)
//    {
//
//        $userId = $request->get('user_id'); // Optional user filter
//
//        // Base query
//        $query = Transaction::query()
//            ->select(
//                'credit_transactions.id',
//                'credit_transactions.user_id',
//                'credit_transactions.booking_id',
//                'credit_transactions.ref_id',
//                'credit_transactions.type',
//                'credit_transactions.amount',
//                'credit_transactions.meta',
//                'credit_transactions.status',
//                'credit_transactions.deleted_at',
//                'credit_transactions.create_user',
//                'credit_transactions.update_user',
//                'credit_transactions.created_at',
//                'credit_transactions.updated_at',
//                'credit_transactions.reference',
//                'credit_transactions.transaction_type',
//                'credit_transactions.deposit_date',
//                'credit_transactions.attachment_id',
//                'credit_transactions.remarks'
//            )
//            ->leftJoin('users', 'credit_transactions.user_id', '=', 'users.id')
//            ->addSelect([
//                'users.name as user_name',
//                'users.first_name',
//                'users.last_name',
//                'users.email',
//                'users.phone',
//                'users.avatar_id',
//                'users.credit_balance',
//                'users.business_name'
//            ]);
//
//        // Filter by user if provided
//        if ($userId) {
//            $query->where('credit_transactions.user_id', $userId);
//        }
//
//        // Get all transactions
//        $transactions = $query->orderBy('credit_transactions.created_at', 'desc')->get();
//
//        // Calculate totals
//        $totalCredit = 0;
//        $totalDebit = 0;
//
//        foreach ($transactions as $transaction) {
//            if ($transaction->type === 'credit') {
//                $totalCredit += $transaction->amount;
//            } elseif ($transaction->type === 'debit') {
//                $totalDebit += $transaction->amount;
//            }
//        }
//
//        $walletBalance = $totalCredit - $totalDebit;
//
//        // Get user wallet info if specific user
//        $userWallet = null;
//        if ($userId) {
//            $userWallet = User::select(
//                'id',
//                'name',
//                'first_name',
//                'last_name',
//                'email',
//                'phone',
//                'address',
//                'city',
//                'state',
//                'country',
//                'credit_balance',
//                'avatar_id',
//                'business_name',
//                'status',
//                'created_at'
//            )
//                ->where('id', $userId)
//                ->first();
//        }
//
//        // Get all users for dropdown with more info
//        $users = User::select(
//            'id',
//            'name',
//            'first_name',
//            'last_name',
//            'email',
//            'phone',
//            'business_name',
//            'credit_balance',
//            'status'
//        )
//            ->whereNull('deleted_at')
//            ->orderBy('name')
//            ->get();
////return $transaction;
//        $totalUsersWallet = User::whereNull('deleted_at')->sum('credit_balance');
//
//        return view('Booking::admin.transactions.index', compact(
//            'transactions',
//            'totalCredit',
//            'totalDebit',
//            'walletBalance',
//            'users',
//            'userId',
//            'userWallet',
//            'totalUsersWallet'
//        ));
//    }

    public function show($id)
    {
        $transaction = Transaction::select(
            'credit_transactions.*',
            'users.name as user_name',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.phone',
            'users.business_name',
            'users.credit_balance as user_credit_balance',
            'users.address',
            'users.city',
            'users.state',
            'users.country'
        )
            ->leftJoin('users', 'credit_transactions.user_id', '=', 'users.id')
            ->where('credit_transactions.id', $id)
            ->first();

        if (!$transaction) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaction not found');
        }

        // Get user info with full details
        $user = User::select(
            'id',
            'name',
            'first_name',
            'last_name',
            'business_name',
            'email',
            'phone',
            'address',
            'address2',
            'city',
            'state',
            'country',
            'zip_code',
            'credit_balance',
            'avatar_id',
            'status',
            'created_at'
        )
            ->where('id', $transaction->user_id)
            ->first();

        // Get user's current wallet balance from transactions
        $userTransactions = Transaction::where('user_id', $transaction->user_id)
            ->get();

        $totalCredit = 0;
        $totalDebit = 0;

        foreach ($userTransactions as $trans) {
            if ($trans->type === 'credit') {
                $totalCredit += $trans->amount;
            } elseif ($trans->type === 'debit') {
                $totalDebit += $trans->amount;
            }
        }

        $walletBalance = $totalCredit - $totalDebit;

        // Transaction count for this user
        $transactionCount = $userTransactions->count();
// Calculate total wallet balance from all users

        return view('Booking::admin.transactions.show', compact(
            'transaction',
            'user',
            'walletBalance',
            'totalCredit',
            'totalDebit',
            'transactionCount'
        ));
    }
//
//    public function itaindex(Request $request)
//    {
//        // ─── Date Range ───────────────────────────────────────────────────────
//        $dateRange = $request->input('date_range', 'this_month');
//        $startDate = $request->input('start_date');
//        $endDate   = $request->input('end_date');
//        $source = $request->input('source'); // ✅ আগে define করো
//
//        $sources = Booking::withoutGlobalScopes()
//            ->whereNotNull('source')
//            ->distinct()
//            ->pluck('source');
//
//
//
//        switch ($dateRange) {
//            case 'today':
//                $startDate = Carbon::today()->toDateString();
//                $endDate   = Carbon::today()->toDateString();
//                break;
//            case 'this_week':
//                $startDate = Carbon::now()->startOfWeek()->toDateString();
//                $endDate   = Carbon::now()->endOfWeek()->toDateString();
//                break;
//            case 'last_week':
//                $startDate = Carbon::now()->subWeek()->startOfWeek()->toDateString();
//                $endDate   = Carbon::now()->subWeek()->endOfWeek()->toDateString();
//                break;
//            case 'this_month':
//                $startDate = Carbon::now()->startOfMonth()->toDateString();
//                $endDate   = Carbon::now()->endOfMonth()->toDateString();
//                break;
//            case 'last_month':
//                $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
//                $endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
//                break;
//            case 'this_year':
//                $startDate = Carbon::now()->startOfYear()->toDateString();
//                $endDate   = Carbon::now()->endOfYear()->toDateString();
//                break;
//            case 'last_year':
//                $startDate = Carbon::now()->subYear()->startOfYear()->toDateString();
//                $endDate   = Carbon::now()->subYear()->endOfYear()->toDateString();
//                break;
//            default:
//                if (!$startDate) $startDate = Carbon::now()->startOfMonth()->toDateString();
//                if (!$endDate)   $endDate   = Carbon::today()->toDateString();
//        }
//
//        $applyFilters = function ($q) use ($startDate, $endDate, $source) { // ✅ use এ যোগ করো
//            $q->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
//                ->when($endDate,   fn($q) => $q->whereDate('created_at', '<=', $endDate))
//                ->when($source,    fn($q) => $q->where('source', $source));
//        };
//
//        // ─── Bookings — সব data একবারে, DataTable filter করবে ───────────────
//        $bookings = Booking::withoutGlobalScopes()
//            ->with('user')
//            ->select([
//                'id', 'code', 'customer_id', 'status', 'source',
//                'seat_class', 'flight_type', 'flight_from', 'flight_to',
//                'airline', 'pnr_id', 'adult_count', 'child_count', 'infant_count',
//                'ticket_number', 'ticket_number', 'ticket_issued_at',
//                'booking_date', 'payment_method',
//                'paid', 'base_fee', 'total_fee', 'supplier_fee',
//                'ticketing_fee', 'flight_discount', 'segment_discount',
//                'total', 'created_at',
//            ])
//            ->tap($applyFilters)
//            ->latest()
//            ->get(); // ← paginate বাদ, সব data
//
//        // ─── Grand Totals ─────────────────────────────────────────────────────
//        $totals = Booking::withoutGlobalScopes()
//            ->tap($applyFilters)
//            ->selectRaw("
//            COUNT(*)                             as count,
//            SUM(COALESCE(base_fee,         0))   as base_fee,
//            SUM(COALESCE(total_fee,        0))   as total_fee,
//            SUM(COALESCE(supplier_fee,     0))   as supplier_fee,
//            SUM(COALESCE(ticketing_fee,    0))   as ticketing_fee,
//            SUM(COALESCE(flight_discount,  0))   as flight_discount,
//            SUM(COALESCE(segment_discount, 0))   as segment_discount,
//            SUM(COALESCE(total,            0))   as total,
//            SUM(COALESCE(paid,             0))   as paid
//        ")
//            ->first();
//
//        return view('Booking::admin.transactions.itabooking', compact(
//            'bookings', 'totals',
//            'startDate', 'endDate', 'dateRange','sources'
//        ));
//    }

   public function itaindex(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $source    = $request->input('source');
        $userId    = $request->input('user_id');

        // Default: এই মাসের শুরু থেকে আজ পর্যন্ত
        if (!$startDate) $startDate = Carbon::now()->startOfMonth()->toDateString();
        if (!$endDate)   $endDate   = Carbon::today()->toDateString();

        $sources = Booking::withoutGlobalScopes()
            ->whereNotNull('source')
            ->distinct()
            ->pluck('source');

        $users = User::orderBy('first_name')->get();

        $query = BookingPassenger::with(['booking', 'booking.user', 'updatedByUser']);

        $query->whereHas('booking', fn($q) => $q->whereNotIn('status', [
            'cancelled', 'pnr_pending', 'booked', 'pending',
        ]));

        $query->whereHas('booking', fn($q) => $q->whereDate('created_at', '>=', $startDate));
        $query->whereHas('booking', fn($q) => $q->whereDate('created_at', '<=', $endDate));

        if ($source) {
            $query->whereHas('booking', fn($q) => $q->where('source', $source));
        }
        if ($userId) {
            $query->whereHas('booking', fn($q) => $q->where('customer_id', $userId));
        }

        $passengers = $query->orderBy('ticket_issued_at', 'desc')->get()->map(function ($pax) {
            return (object) [
                'passenger_id'        => $pax->id,
                'first_name'          => $pax->first_name,
                'last_name'           => $pax->last_name,
                'traveler_type'       => $pax->traveler_type,
                'passenger_type_code' => $pax->passenger_type_code,
                'pnr'                 => $pax->pnr,
                'ticket_number'       => $pax->ticket_number,
                'ticket_issued_at'    => $pax->ticket_issued_at,
                'base'                => $pax->base,
                'tax'                 => $pax->tax,
                'total'               => $pax->total,
                'passenger_status'    => $pax->status,
                'gross_fare'          => $pax->gross_fare,
                'ait_amount'          => $pax->ait_amount,
                'service_charge'      => $pax->service_charge,
                'user_discount'       => $pax->user_discount,
                'user_seg_discount'   => $pax->user_seg_discount,
                'user_payable'        => $pax->user_payable,
                'own_discount'        => $pax->own_discount,
                'own_seg_discount'    => $pax->own_seg_discount,
                'commission'          => $pax->commission,
                'own_cost'            => $pax->own_cost,
                'profit'              => $pax->profit,
                'currency'            => $pax->currency,
                'booking_id'          => $pax->booking_id,
                'booking_code'        => $pax->booking->code ?? '',
                'source'              => $pax->booking->source ?? '',
                'airline'             => $pax->booking->airline ?? '',
                'departure_date'      => $pax->booking->start_date ?? null,
                'flight_from'         => $pax->booking->flight_from ?? '',
                'flight_to'           => $pax->booking->flight_to ?? '',
                'booking_status'      => $pax->booking->status ?? '',
                'user_first_name'     => $pax->booking->user->first_name ?? '',
                'user_last_name'      => $pax->booking->user->last_name ?? '',
                'user_email'          => $pax->booking->user->email ?? '',
                'updated_by_name'     => $pax->updatedByUser
                    ? trim($pax->updatedByUser->first_name . ' ' . $pax->updatedByUser->last_name)
                    : '-',
            ];
        });

        $bookingIds  = $passengers->pluck('booking_id')->unique();
        $routeCounts = BookingRoutes::selectRaw('booking_id, COUNT(*) as route_count')
            ->whereIn('booking_id', $bookingIds)
            ->groupBy('booking_id')
            ->pluck('route_count', 'booking_id');

        return view('Booking::admin.transactions.itabooking', compact(
            'passengers', 'routeCounts',
            'startDate', 'endDate',
            'sources', 'users', 'source', 'userId'
        ));
    }
}
