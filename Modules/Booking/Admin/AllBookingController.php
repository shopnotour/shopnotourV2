<?php

namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllBookingController extends Controller
{
    /**
     * Display a listing of bookings with filters
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->orderByRaw("
                CASE 
                    WHEN status = 'issue_request' THEN 1
                    WHEN status = 'booked' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Status filter
        $selectedStatus = $request->input('status', '');
        
        if ($request->filled('status')) {
            $query->where('status', $selectedStatus);
        } else {
            // Default: exclude cancelled and pending
            $query->whereNotIn('status', ['cancelled', 'pending', 'failed', 'refunded']);
        }

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.index', compact(
            'rows', 'selectedStatus', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    /**
     * Display bookings with status = issue_request
     */
    public function issueRequest(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'issue_request')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.issue-request', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    /**
     * Display bookings with status = booked
     */
    public function booked(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'booked')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.booked', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }


    public function cancelled(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'cancelled')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.cancelled', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    public function issued(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'issued')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.issued', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    public function pending(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'pending')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.pending', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    public function paid(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'paid')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.paid', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }
    public function ticketed(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'ticketed')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.ticketed', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }
    public function refunded(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'refunded')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.refunded', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }
    public function failed(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->where('status', 'failed')
            ->orderBy('booking_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Date column selection
        $dateColumn = in_array($request->date_column, [
            'booking_date',
            'confirmed_at',
            'ticket_issued_at',
        ]) ? $request->date_column : 'booking_date';

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('pnr_id', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->paginate(30)->withQueryString();
        
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $dateColumnSel = $dateColumn;
        $searchTerm = $request->search;

        return view('Booking::admin.all-booking.failed', compact(
            'rows', 'dateFrom', 'dateTo', 'dateColumnSel', 'searchTerm'
        ));
    }

    /**
     * Show single booking details
     */
    public function show($id)
    {
        $booking = Booking::with(['passengers', 'user', 'issuedBy', 'routes'])->findOrFail($id);
        return view('Booking::admin.all-booking.show', compact('booking'));
    }

    /**
     * Get booking modal content
     */
    public function modal(Request $request, $id)
    {
        try {
            $booking = Booking::with([
                'passengers',
                'passengers.passportMedia',
                'passengers.visaMedia',
                'routes',
            ])->findOrFail($id);

            return view('Booking::admin.all-booking.modal', compact('booking'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Booking not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Set paid for booking
     */
    public function setPaid(Request $request, $id)
    {
        Log::info('💰 Set Paid Request', [
            'booking_id' => $id,
            'amount' => $request->remain,
            'admin_id' => auth()->id()
        ]);

        try {
            $request->validate([
                'remain' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $booking = Booking::findOrFail($id);
            $user = $booking->user;
            $paidAmount = (float) $request->remain;

            if (!$user || $user->credit_balance < $paidAmount) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Insufficient credit balance!' .
                        ($user ? ' Available: ৳' . number_format($user->credit_balance, 2) : ''),
                ], 422);
            }

            $oldPaid = (float) $booking->paid;
            $newPaid = $oldPaid + $paidAmount;

            $user->decrement('credit_balance', $paidAmount);

            $booking->update([
                'paid' => $newPaid,
                'pay_now' => max(0, $booking->total - $newPaid),
            ]);

            // Transaction record
            \Modules\User\Models\Wallet\Transaction::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'ref_id' => auth()->id(),
                'type' => 'debit',
                'transaction_type' => 'credit_balance_payment',
                'amount' => $paidAmount,
                'status' => 'completed',
                'reference' => 'Credit balance payment - Booking #' . $booking->code,
                'remarks' => 'Credit balance deducted for booking #' . $booking->code,
                'meta' => json_encode([
                    'old_credit_balance' => $user->credit_balance + $paidAmount,
                    'new_credit_balance' => $user->credit_balance,
                    'old_paid' => $oldPaid,
                    'new_paid' => $newPaid,
                    'booking_code' => $booking->code,
                    'payment_method' => 'credit_balance',
                    'admin_id' => auth()->id(),
                ]),
                'create_user' => auth()->id(),
                'created_at' => now(),
                'deposit_date' => now(),
            ]);

            if ($newPaid >= $booking->total && $booking->status == 'issue_request') {
                $booking->update(['status' => 'booked']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'success' => true,
                'message' => "Payment added successfully!\n\nCredit Used: ৳" . number_format($paidAmount, 2) .
                    "\nTotal Paid: ৳" . number_format($newPaid, 2) .
                    "\nRemaining Credit: ৳" . number_format($user->credit_balance, 2),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Set Paid Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk action for bookings
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        Log::info('📋 Booking Bulk Action', [
            'action' => $action,
            'ids' => $ids,
            'admin_id' => auth()->id()
        ]);

        if (empty($ids) || empty($action)) {
            return back()->with('error', 'Please select items and action');
        }

        try {
            switch ($action) {
                case 'delete':
                    $deleted = Booking::whereIn('id', $ids)->delete();
                    return back()->with('success', "Deleted {$deleted} booking(s)");

                case 'issue_request':
                case 'booked':
                case 'paid':
                case 'ticketed':
                case 'completed':
                case 'cancelled':
                    $updated = Booking::whereIn('id', $ids)->update(['status' => $action]);
                    return back()->with('success', "Updated {$updated} booking(s) to " . ucfirst(str_replace('_', ' ', $action)));

                default:
                    return back()->with('error', 'Invalid action');
            }
        } catch (\Exception $e) {
            Log::error('❌ Bulk Action Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update PNR, Source, Status
     */
    public function updatePnr(Request $request, $id)
    {
        $request->validate([
            'pnr_id' => 'required|string|max:20',
            'source' => 'required|in:sabre,travelport,galileo,amadeus,manual',
            'status' => 'required|in:issue_request,pending,paid,booked,issued,ticketed,completed,cancelled,failed,refunded',
        ]);

        $booking = Booking::findOrFail($id);

        $booking->pnr_id = strtoupper(trim($request->pnr_id));
        $booking->source = $request->source;
        $booking->status = $request->status;

        if (in_array($request->status, ['booked', 'issued', 'ticketed', 'completed']) && !$booking->confirmed_at) {
            $booking->confirmed_at = now();
        }

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => __('PNR, Source and Status updated successfully'),
        ]);
    }

    /**
     * Duplicate booking
     */
    public function duplicate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $original = Booking::with(['passengers', 'routes'])->findOrFail($id);

            $newBookingData = $original->toArray();

            unset(
                $newBookingData['id'],
                $newBookingData['created_at'],
                $newBookingData['updated_at'],
                $newBookingData['deleted_at'],
                $newBookingData['paid'],
                $newBookingData['pay_now'],
                $newBookingData['ticket_number'],
                $newBookingData['ticket_issued_at'],
                $newBookingData['ticket_details'],
                $newBookingData['pnr_id'],
                $newBookingData['pnr_raw_data'],
                $newBookingData['confirmed_at'],
                $newBookingData['issued_by'],
                $newBookingData['payment_id'],
                $newBookingData['wallet_transaction_id'],
                $newBookingData['wallet_credit_used'],
                $newBookingData['passengers'],
                $newBookingData['routes'],
            );

            $newBookingData['code'] = 'BK' . strtoupper(substr(uniqid(), -8));
            $newBookingData['status'] = 'booked';
            $newBookingData['paid'] = 0;
            $newBookingData['pay_now'] = $original->total;
            $newBookingData['is_paid'] = 0;
            $newBookingData['create_user'] = auth()->id();
            $newBookingData['created_at'] = now();
            $newBookingData['updated_at'] = now();

            $newBooking = Booking::create($newBookingData);

            foreach ($original->passengers as $pax) {
                $paxData = $pax->toArray();
                unset(
                    $paxData['id'],
                    $paxData['created_at'],
                    $paxData['updated_at'],
                    $paxData['deleted_at'],
                    $paxData['ticket_number'],
                    $paxData['ticket_issued_at'],
                    $paxData['status'],
                    $paxData['passport_media_id'],
                    $paxData['visa_media_id'],
                );
                $paxData['booking_id'] = $newBooking->id;
                $paxData['create_user'] = auth()->id();
                $paxData['created_at'] = now();
                $paxData['updated_at'] = now();

                \Modules\Booking\Models\BookingPassenger::create($paxData);
            }

            foreach ($original->routes as $route) {
                $routeData = $route->toArray();
                unset(
                    $routeData['id'],
                    $routeData['created_at'],
                    $routeData['updated_at'],
                    $routeData['deleted_at'],
                );
                $routeData['booking_id'] = $newBooking->id;
                $routeData['create_user'] = auth()->id();
                $routeData['created_at'] = now();
                $routeData['updated_at'] = now();

                \Modules\Booking\Models\Bookingroute::create($routeData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking duplicated successfully! New booking: ' . $newBooking->code,
                'new_id' => $newBooking->id,
                'new_code' => $newBooking->code,
                'edit_url' => route('admin.bookings.edit', $newBooking->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Duplicate booking error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate: ' . $e->getMessage(),
            ], 500);
        }
    }
}