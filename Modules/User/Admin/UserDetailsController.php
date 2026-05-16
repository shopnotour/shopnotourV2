<?php

namespace Modules\User\Admin;

use App\User;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\Transaction;

class UserDetailsController
{
    public function index(Request $request)
    {
        $query = User::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereIn('role_id', [2, 3]) // শুধু agent + customer
            ->orderBy('created_at', 'desc');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->get([
            'id', 'first_name', 'last_name', 'business_name',
            'email', 'phone', 'credit_balance',
            'status', 'role_id', 'created_at'
        ]);

        return view('User::admin.users.index', compact('users'));
    }

    public function bookings(Request $request, $id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo   = $request->input('date_to',   date('Y-m-d'));

        $bookings = Booking::withoutGlobalScopes()
            ->where('customer_id', $id)
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at', 'desc')
            ->get();

        // Status counts
        $totalBookings   = $bookings->count();
        $issuedCount     = $bookings->where('status', 'issued')->count();
        $bookedCount     = $bookings->where('status', 'booked')->count();
        $cancelledCount  = $bookings->whereIn('status', ['cancelled', 'canceled'])->count();
        $pendingCount    = $bookings->whereIn('status', ['pending', 'pnr_pending'])->count();

        // Financial calculation — শুধু active statuses এর উপর
        $activeStatuses  = ['issued', 'reissued', 'refunded', 'void', 'voided'];
        $activeBookings  = $bookings->whereIn('status', $activeStatuses);
        $totalPayNow     = $activeBookings->sum('pay_now');
        $totalPaid       = $activeBookings->sum('paid');
        $totalDue        = max(0, $totalPayNow - $totalPaid);

        // Credit balance
        $creditBalance   = $user->credit_balance ?? 0;
//return $creditBalance;
        $pageTitle = 'All Bookings';

        return view('User::admin.users.bookings', compact(
            'user', 'bookings', 'pageTitle',
            'totalBookings', 'issuedCount', 'bookedCount',
            'cancelledCount', 'pendingCount',
            'totalPayNow', 'totalPaid', 'totalDue',
            'creditBalance'
        ));
    }

    public function bookingDetail($userId, $bookingId)
    {
        $user    = User::withoutGlobalScopes()->findOrFail($userId);
        $booking = Booking::withoutGlobalScopes()
            ->where('customer_id', $userId)
            ->findOrFail($bookingId);

        // ── Raw data decode
        $fd = [];
        if (!empty($booking->pnr_raw_data)) {
            $fd = is_string($booking->pnr_raw_data)
                ? json_decode($booking->pnr_raw_data, true)
                : (array)$booking->pnr_raw_data;
        }

        // ── Segments & Journeys
        $segments = $fd['segments']        ?? [];
        $journeys = $fd['journeys']        ?? [];
        $pricing  = $fd['pricing']         ?? [];
        $fareRules   = $fd['fare_rules']      ?? [];
        $specialSvc  = $fd['special_services'] ?? [];
        $passengers  = $fd['passengers']      ?? ($fd['passenger'] ? [$fd['passenger']] : []);
        $provRes     = $fd['provider_reservation'] ?? [];
        $supplierLoc = $fd['supplier_locator']     ?? [];
        $actionSt    = $fd['action_status']        ?? [];

        // ── Leg grouping (group field দিয়ে)
        $legGroups = [];
        foreach ($segments as $seg) {
            $legGroups[$seg['group'] ?? 0][] = $seg;
        }

        // ── Layover calculation
        foreach ($legGroups as $gIdx => $legSegs) {
            foreach ($legSegs as $sIdx => $seg) {
                if ($sIdx > 0) {
                    $legGroups[$gIdx][$sIdx]['layover_minutes'] =
                        (int)($legSegs[$sIdx - 1]['connection']['duration'] ?? 0);
                }
            }
        }

        // ── PNR info
        $pnrGDS     = $fd['air_reservation']['locator_code']
            ?? ($fd['universal_record']['locator_code'] ?? null);
        $pnrAirline = $supplierLoc['locator_code'] ?? null;
        $airCode    = $supplierLoc['supplier_code'] ?? null;
        $provCode   = $provRes['provider_code'] ?? '1G';

        // ── TAU deadline
        $tauDate = null;
        if (!empty($actionSt['ticket_date'])) {
            try {
                $tauDate = \Carbon\Carbon::parse($actionSt['ticket_date'])
                    ->setTimezone('Asia/Dhaka')
                    ->format('d M Y, h:i A');
            } catch (\Exception $e) {}
        }

        // ── Ticket map: traveler_index → ticket number
        $tktMap = [];
        foreach ($fd['flight_tickets'] ?? [] as $t) {
            $tktMap[(int)($t['traveler_index'] ?? 0)] = $t['number'] ?? '';
        }

        // ── Ticket numbers array (from booking table)
        $ticketNumbers = [];
        if (!empty($booking->ticket_number)) {
            $ticketNumbers = is_string($booking->ticket_number)
                ? (json_decode($booking->ticket_number, true) ?? [])
                : (array)$booking->ticket_number;
        }

        // ── Fare breakdowns
        $fareBreakdowns    = $pricing['fare_breakdowns']         ?? [];
        $checkedBag        = $pricing['checked_bag_kg']          ?? null;
        $cabinBag          = $pricing['cabin_bag_kg']            ?? null;
        $grandTotal        = $pricing['grand_total']             ?? [];
        $checkedBagCharges = $pricing['checked_baggage_charges'] ?? [];

        // ── Booking fee fields
        $baseFee    = (float)($booking->base_fee       ?? 0);
        $taxFee     = (float)($booking->total_fee      ?? 0);
        $serviceFee = (float)($booking->ticketing_fee  ?? 0);
        $aitFee     = (float)($booking->supplier_fee   ?? 0);
        $flightDisc = (float)($booking->flight_discount  ?? 0);
        $segDisc    = (float)($booking->segment_discount ?? 0);
        $totalDisc  = $flightDisc + $segDisc;
        $bookTotal  = (float)($booking->total          ?? 0);
        $walletUsed = (float)($booking->wallet_credit_used ?? 0);
        $penaltyAmt = (float)($booking->penalty_amount ?? 0);
        $penaltyRmk = $booking->penalty_remark ?? null;
        $payable    = $bookTotal - $walletUsed;

        // ── Pax counts
        $adultCount  = (int)($booking->adult_count  ?? 0);
        $childCount  = (int)($booking->child_count  ?? 0);
        $infantCount = (int)($booking->infant_count ?? 0);

        // ── Tax labels
        $taxLabels = [
            'BD' => 'Airport Development Fee',
            'UT' => 'Travel Tax',
            'OW' => 'Security Charge',
            'E5' => 'Embarkation Fee',
            'BH' => 'Fuel Surcharge',
            'HM' => 'IATA Misc Charge',
            'ZR' => 'Security Service Fee',
            'P7' => 'Pax Service Charge (Dep)',
            'P8' => 'Pax Service Charge (Arr)',
            'YQ' => 'Fuel Surcharge (YQ)',
            'YR' => 'Carrier Surcharge (YR)',
            'AE' => 'Passenger Service Charge',
            'TP' => 'Security Fee',
            'F6' => 'User Development Fee',
        ];

        // ── Pax type labels
        $paxLabel = [
            'ADT' => 'Adult',
            'CNN' => 'Child',
            'CHD' => 'Child',
            'C07' => 'Child',
            'INF' => 'Infant',
        ];

        // ── Status config
        $statusConfig = match($booking->status ?? '') {
            'issued'      => ['success', 'Issued'],
            'booked'      => ['primary', 'Booked'],
            'pnr_pending' => ['warning', 'PNR Pending'],
            'pending'     => ['warning', 'Pending'],
            'cancelled'   => ['danger',  'Cancelled'],
            'refunded'    => ['info',    'Refunded'],
            default       => ['secondary', ucfirst($booking->status ?? '—')],
        };

        // ── Remarks
        $remarks = $fd['remarks'] ?? [];

        return view('User::admin.users.booking-detail', compact(
            'user',
            'booking',
            // PNR
            'pnrGDS', 'pnrAirline', 'airCode', 'provCode', 'tauDate',
            // Flight
            'segments', 'journeys', 'legGroups',
            'checkedBag', 'cabinBag',
            // Passengers
            'passengers', 'tktMap', 'ticketNumbers', 'paxLabel',
            'adultCount', 'childCount', 'infantCount',
            // Fare
            'fareBreakdowns', 'fareRules', 'grandTotal',
            'checkedBagCharges', 'taxLabels',
            // Special services
            'specialSvc',
            // Booking fees
            'baseFee', 'taxFee', 'serviceFee', 'aitFee',
            'totalDisc', 'bookTotal', 'walletUsed', 'payable',
            'penaltyAmt', 'penaltyRmk',
            // Meta
            'statusConfig', 'remarks', 'provRes', 'actionSt',
        ));
    }

    public function transactions($id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        $transactions = Transaction::withoutGlobalScopes()
            ->where('user_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('User::admin.users.transactions', compact('user', 'transactions'));
    }

    public function tickets($id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        $bookings = Booking::withoutGlobalScopes()
            ->where('customer_id', $id)
            ->whereIn('status', ['issued', 'ticketed'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $pageTitle = 'Issued Tickets';

        return view('User::admin.users.bookings', compact('user', 'bookings', 'pageTitle'));
    }
}
