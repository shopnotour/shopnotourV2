<?php
//
//namespace Modules\Booking\Admin;
//
//use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Http\Request;
//use Modules\Booking\Models\BookingPassenger;
//use Modules\Booking\Models\BookingRefund;
//use Modules\Booking\Models\Booking;
//use Modules\User\Models\Wallet\Transaction;
//
//class RefundManagementController extends Controller
//{
//    public function index(Request $request)
//    {
//        $query = BookingRefund::with(['booking'])
//            ->orderBy('created_at', 'desc');
//
//        // Filters
//        if ($request->status) {
//            $query->where('status', $request->status);
//        }
//
//        if ($request->refund_type) {
//            $query->where('refund_type', $request->refund_type);
//        }
//
//        if ($request->s) {
//            $query->where(function ($q) use ($request) {
//                $q->where('pnr', 'LIKE', '%' . $request->s . '%')
//                    ->orWhereHas('booking', function ($bq) use ($request) {
//                        $bq->where('code', 'LIKE', '%' . $request->s . '%')
//                            ->orWhere('id', $request->s);
//                    });
//            });
//        }
//
//        $rows = $query->paginate(20);
//        $statuses = ['pending', 'approved', 'processing', 'completed', 'rejected'];
//        $refundTypes = ['full', 'partial', 'cancellation'];
//
//        return view('Booking::admin.refunds.index', compact('rows', 'statuses', 'refundTypes'));
//    }
//
//
//    // ✅ Add this method in your RefundController
//
//    public function getPassengerAmount($id)
//    {
//        try {
//            $refund = BookingRefund::findOrFail($id);
//
//            $passengerAmount = 0;
//
//            if (!empty($refund->passenger_id)) {
//                $passengerIds = is_array($refund->passenger_id)
//                    ? $refund->passenger_id
//                    : json_decode($refund->passenger_id, true);
//
//                if (!empty($passengerIds)) {
//                    $passengerAmount = BookingPassenger::whereIn('id', $passengerIds)
//                        ->sum('total');
//                }
//            }
//
//            return response()->json([
//                'status' => true,
//                'passenger_amount' => $passengerAmount,
//                'formatted_amount' => format_money_main($passengerAmount)
//            ]);
//
//        } catch (\Exception $e) {
//            return response()->json([
//                'status' => false,
//                'message' => $e->getMessage()
//            ], 500);
//        }
//    }
//    public function show($id)
//    {
//        $refund = BookingRefund::with(['booking', 'passengers'])->findOrFail($id);
//        return view('Booking::admin.refunds.show', compact('refund'));
//    }
//
////    public function approve(Request $request, $id)
////    {
////        Log::info('🔵 Refund Approve Request', [
////            'refund_id' => $id,
////            'request_data' => $request->all(),
////            'user_id' => auth()->id()
////        ]);
////
////        try {
////            $request->validate([
////                'refund_amount' => 'required|numeric|min:0',
////                'refund_charges' => 'required|numeric|min:0',
////            ]);
////
////            DB::beginTransaction();
////
////            $refund = BookingRefund::findOrFail($id);
////            $booking = $refund->booking;
////
////            if (!$booking) {
////                throw new \Exception('Booking not found for this refund request');
////            }
////
////            $netRefund = $request->refund_amount - $request->refund_charges;
////
////            Log::info('📦 Refund Found', [
////                'refund_id' => $refund->id,
////                'used by' => auth()->id(),
////                'booking_id' => $booking->id,
////                'current_status' => $refund->status
////            ]);
////
////            // Update refund record
////            $refund->update([
////                'status' => 'completed',
////                'refund_amount' => $request->refund_amount,
////                'refund_charges' => $request->refund_charges,
////                'net_refund_amount' => $netRefund,
////                'airline_response' => $request->airline_response,
////                'approved_by' => auth()->id(),
////                'approved_at' => now()
////            ]);
////
////            Log::info('✅ Refund Updated', [
////                'refund_id' => $refund->id,
////                'new_status' => 'approved',
////                'net_refund' => $netRefund
////            ]);
////
////            // Get passenger IDs
////            $passengerIds = $refund->passenger_ids ?? [];
////            $isAllPassengers = empty($passengerIds);
////
////            if ($isAllPassengers) {
////                // All passengers
////                $passengerIds = BookingPassenger::where('booking_id', $booking->id)
////                    ->pluck('id')->toArray();
////            }
////
////            // Update passengers status (if column exists)
////            try {
////                $updatedCount = BookingPassenger::whereIn('id', $passengerIds)
////                    ->update(['status' => 'refund_approved']);
////
////                Log::info('✅ Passengers Updated', [
////                    'count' => $updatedCount
////                ]);
////            } catch (\Exception $e) {
////                Log::warning('⚠️ Could not update passenger status', [
////                    'error' => $e->getMessage()
////                ]);
////            }
////
////            // Update booking refund status
////            $allPassengersRefunded = BookingPassenger::where('booking_id', $booking->id)
////                    ->whereNotIn('id', $passengerIds)
////                    ->count() == 0;
////
////            $booking->update([
////                'status' => $allPassengersRefunded ? 'refunded' : 'partial_refunded'
////            ]);
////
////            Log::info('✅ Booking Updated', [
////                'booking_id' => $booking->id,
////                'refund_status' => $allPassengersRefunded ? 'approved' : 'partial_approved'
////            ]);
////
////            DB::commit();
////
////            Log::info('✅ Refund Approved Successfully', [
////                'refund_id' => $refund->id,
////                'booking_id' => $booking->id,
////                'net_refund' => $netRefund
////            ]);
////
////            return response()->json([
////                'status' => true,
////                'success' => true,
////                'message' => 'Refund approved successfully! Net refund: ৳' . number_format($netRefund, 2),
////                'data' => [
////                    'refund_id' => $refund->id,
////                    'booking_id' => $booking->id,
////                    'refund_amount' => $request->refund_amount,
////                    'refund_charges' => $request->refund_charges,
////                    'net_refund_amount' => $netRefund
////                ]
////            ], 200);
////
////        } catch (\Illuminate\Validation\ValidationException $e) {
////            DB::rollBack();
////            Log::error('❌ Validation Error', [
////                'errors' => $e->errors()
////            ]);
////            return response()->json([
////                'status' => false,
////                'success' => false,
////                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
////            ], 422);
////
////        } catch (\Exception $e) {
////            DB::rollBack();
////            Log::error('❌ Refund Approve Error', [
////                'error' => $e->getMessage(),
////                'line' => $e->getLine(),
////                'file' => $e->getFile()
////            ]);
////            return response()->json([
////                'status' => false,
////                'success' => false,
////                'message' => 'Error: ' . $e->getMessage()
////            ], 500);
////        }
////    }
//
//
//    public function approve(Request $request, $id)
//    {
//        Log::info('🔵 Refund Approve Request', [
//            'refund_id' => $id,
//            'request_data' => $request->all(),
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $request->validate([
//                'refund_amount' => 'required|numeric|min:0',
//                'refund_charges' => 'required|numeric|min:0',
//            ]);
//
//            DB::beginTransaction();
//
//            $refund = BookingRefund::with(['booking.user'])->findOrFail($id);
//            $booking = $refund->booking;
//            $user = $booking->user;
//
//            if (!$booking) {
//                throw new \Exception('Booking not found for this refund request');
//            }
//
//            if (!$user) {
//                throw new \Exception('User not found for this booking');
//            }
//
//            // ✅ Calculate net refund
//            $refundAmount = (float) $request->refund_amount;
//            $refundCharges = (float) $request->refund_charges;
//            $netRefund = $refundAmount - $refundCharges;
//            $paidAmount = (float) $booking->paid;
//
//            Log::info('📦 Refund Found', [
//                'refund_id' => $refund->id,
//                'booking_id' => $booking->id,
//                'user_id' => $user->id,
//                'current_status' => $refund->status,
//                'paid_amount' => $paidAmount,
//                'net_refund' => $netRefund
//            ]);
//
//            // ✅ Validate: Net refund cannot be greater than paid amount
//            if ($netRefund > $paidAmount) {
//                throw new \Exception('Net refund amount (৳' . number_format($netRefund, 2) . ') cannot be greater than paid amount (৳' . number_format($paidAmount, 2) . ')');
//            }
//
//            // ✅ Process refund to user's credit balance (if paid amount exists)
//            if ($paidAmount > 0 && $netRefund > 0) {
//                $balanceBefore = $user->credit_balance;
//                $user->increment('credit_balance', $netRefund);
//                $balanceAfter = $user->credit_balance;
//
//                // Create transaction record
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $booking->id,
//                    'ref_id' => $refund->id,
//                    'type' => 'credit',
//                    'transaction_type' => 'refund',
//                    'amount' => $netRefund,
//                    'status' => 'refund',
//                    'reference' => 'Refund - Booking #' . $booking->code,
//                    'remarks' => 'Refund for booking ' . $booking->code . ' (Charges: ৳' . number_format($refundCharges, 2) . ')',
//                    'meta' => json_encode([
//                        'balance_before' => $balanceBefore,
//                        'balance_after' => $balanceAfter,
//                        'booking_code' => $booking->code,
//                        'refund_id' => $refund->id,
//                        'refund_amount' => $refundAmount,
//                        'refund_charges' => $refundCharges,
//                        'net_refund' => $netRefund,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('✅ Refund Added to Credit Balance', [
//                    'user_id' => $user->id,
//                    'net_refund' => $netRefund,
//                    'balance_before' => $balanceBefore,
//                    'balance_after' => $balanceAfter
//                ]);
//            }
//
//            // ✅ Update refund record
//            $refund->update([
//                'status' => 'completed',
//                'refund_amount' => $refundAmount,
//                'refund_charges' => $refundCharges,
//                'net_refund_amount' => $netRefund,
//                'airline_response' => $request->airline_response,
//                'approved_by' => auth()->id(),
//                'approved_at' => now()
//            ]);
//
//            Log::info('✅ Refund Updated', [
//                'refund_id' => $refund->id,
//                'new_status' => 'completed',
//                'net_refund' => $netRefund
//            ]);
//
//            // ✅ Get passenger IDs
//            $passengerIds = $refund->passenger_id ?? [];
//            $isAllPassengers = empty($passengerIds);
//
//            if ($isAllPassengers) {
//                $passengerIds = BookingPassenger::where('booking_id', $booking->id)
//                    ->pluck('id')->toArray();
//            }
//
//            // ✅ Update passengers status
//            try {
//                $updatedCount = BookingPassenger::whereIn('id', $passengerIds)
//                    ->update(['status' => 'refunded']);
//
//                Log::info('✅ Passengers Updated', [
//                    'count' => $updatedCount
//                ]);
//            } catch (\Exception $e) {
//                Log::warning('⚠️ Could not update passenger status', [
//                    'error' => $e->getMessage()
//                ]);
//            }
//
//            // ✅ Update booking status
//            $allPassengersRefunded = BookingPassenger::where('booking_id', $booking->id)
//                    ->whereNotIn('id', $passengerIds)
//                    ->count() == 0;
//
//            $booking->update([
//                'status' => $allPassengersRefunded ? 'refunded' : 'partial_refunded'
//            ]);
//
//            Log::info('✅ Booking Updated', [
//                'booking_id' => $booking->id,
//                'status' => $allPassengersRefunded ? 'refunded' : 'partial_refunded'
//            ]);
//
//            DB::commit();
//
//            Log::info('✅ Refund Approved Successfully', [
//                'refund_id' => $refund->id,
//                'booking_id' => $booking->id,
//                'net_refund' => $netRefund,
//                'user_balance' => $user->credit_balance
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'success' => true,
//                'message' => 'Refund approved successfully! Net refund: ৳' . number_format($netRefund, 2),
//                'data' => [
//                    'refund_id' => $refund->id,
//                    'booking_id' => $booking->id,
//                    'refund_amount' => $refundAmount,
//                    'refund_charges' => $refundCharges,
//                    'net_refund_amount' => $netRefund,
//                    'user_balance' => $user->credit_balance
//                ]
//            ], 200);
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            DB::rollBack();
//            Log::error('❌ Validation Error', [
//                'errors' => $e->errors()
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false,
//                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
//            ], 422);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('❌ Refund Approve Error', [
//                'refund_id' => $id,
//                'error' => $e->getMessage(),
//                'line' => $e->getLine(),
//                'file' => $e->getFile()
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }
//    public function reject(Request $request, $id)
//    {
//        Log::info('🔴 Refund Reject Request', [
//            'refund_id' => $id,
//            'reason' => $request->rejection_reason,
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $request->validate([
//                'rejection_reason' => 'required|string|min:10'
//            ]);
//
//            DB::beginTransaction();
//
//            $refund = BookingRefund::findOrFail($id);
//
//            $refund->update([
//                'status' => 'rejected',
//                'rejection_reason' => $request->rejection_reason,
//                'approved_by' => auth()->id(),
//                'approved_at' => now()
//            ]);
//
//            // Update passengers (if column exists)
//            $passengerIds = $refund->passenger_id ?? [];
//            if (!empty($passengerIds)) {
//                try {
//                    BookingPassenger::whereIn('id', $passengerIds)
//                        ->update(['status' => 'refund_rejected']);
//                } catch (\Exception $e) {
//                    Log::warning('⚠️ Could not update passenger status');
//                }
//            }
//
//            DB::commit();
//
//            Log::info('✅ Refund Rejected Successfully', [
//                'refund_id' => $refund->id
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'success' => true,
//                'message' => 'Refund request rejected successfully'
//            ], 200);
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            DB::rollBack();
//            Log::error('❌ Validation Error', [
//                'errors' => $e->errors()
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false,
//                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
//            ], 422);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('❌ Refund Reject Error', [
//                'error' => $e->getMessage()
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }
//
//    public function bulkAction(Request $request)
//    {
//        $ids = $request->ids;
//        $action = $request->action;
//
//        Log::info('📋 Refund Bulk Action', [
//            'action' => $action,
//            'ids' => $ids,
//            'user_id' => auth()->id()
//        ]);
//
//        if (empty($ids) || empty($action)) {
//            return back()->with('error', 'Please select items and action');
//        }
//
//        try {
//            switch ($action) {
//                case 'approve':
//                    $updated = 0;
//                    foreach ($ids as $id) {
//                        $refund = BookingRefund::find($id);
//                        if ($refund && $refund->status == 'pending') {
//                            $refundAmount = $refund->booking->total;
//                            $refundCharges = 500;
//                            $netRefund = $refundAmount - $refundCharges;
//
//                            $refund->update([
//                                'status' => 'approved',
//                                'refund_amount' => $refundAmount,
//                                'refund_charges' => $refundCharges,
//                                'net_refund_amount' => $netRefund,
//                                'approved_by' => auth()->id(),
//                                'approved_at' => now()
//                            ]);
//                            $updated++;
//                        }
//                    }
//
//                    Log::info('✅ Bulk Approved', ['count' => $updated]);
//                    return back()->with('success', "Approved {$updated} refund request(s)");
//
//                case 'reject':
//                    $updated = BookingRefund::whereIn('id', $ids)
//                        ->where('status', 'pending')
//                        ->update([
//                            'status' => 'rejected',
//                            'rejection_reason' => 'Bulk rejection by admin',
//                            'approved_by' => auth()->id(),
//                            'approved_at' => now()
//                        ]);
//
//                    Log::info('✅ Bulk Rejected', ['count' => $updated]);
//                    return back()->with('success', "Rejected {$updated} refund request(s)");
//
//                case 'delete':
//                    $deleted = BookingRefund::whereIn('id', $ids)->delete();
//                    Log::info('✅ Bulk Deleted', ['count' => $deleted]);
//                    return back()->with('success', "Deleted {$deleted} refund request(s)");
//
//                default:
//                    return back()->with('error', 'Invalid action');
//            }
//        } catch (\Exception $e) {
//            Log::error('❌ Bulk Action Error', [
//                'error' => $e->getMessage()
//            ]);
//            return back()->with('error', 'Error: ' . $e->getMessage());
//        }
//    }
//}


namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingRefund;
use Modules\Booking\Models\Booking;
use Modules\User\Models\User;
use Modules\User\Models\Wallet\Transaction;

class RefundManagementController extends Controller
{
//    public function index(Request $request)
//    {
//        $query = BookingRefund::with(['booking', 'booking.user'])
//            ->orderBy('created_at', 'desc');
//
//        // Filters
//        if ($request->status) {
//            $query->where('status', $request->status);
//        }
//
//        if ($request->refund_type) {
//            $query->where('refund_type', $request->refund_type);
//        }
//
//        if ($request->s) {
//            $query->where(function ($q) use ($request) {
//                $q->where('pnr', 'LIKE', '%' . $request->s . '%')
//                    ->orWhereHas('booking', function ($bq) use ($request) {
//                        $bq->where('code', 'LIKE', '%' . $request->s . '%')
//                            ->orWhere('id', $request->s);
//                    });
//            });
//        }
//
//        $rows = $query->paginate(20);
//
//        return view('Booking::admin.refunds.index', compact('rows'));
//    }

    public function index(Request $request)
    {
        $rows = BookingRefund::with(['booking', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Booking::admin.refunds.index', compact('rows'));
    }

    public function getPassengerAmount($id)
    {
        try {
            $refund = BookingRefund::findOrFail($id);

            $passengerAmount = 0;
//return $refund->passenger_id;


            if (!empty($refund->passenger_id)) {
                $passengerIds = is_array($refund->passenger_id)
                    ? $refund->passenger_id
                    : json_decode($refund->passenger_id, true);

                if (!empty($passengerIds)) {
                    $passengerAmount = BookingPassenger::whereIn('id', $passengerIds)
                        ->sum('user_payable');
                }
            }

            return response()->json([
                'status' => true,
                'passenger_amount' => $passengerAmount,
                'formatted_amount' => format_money_main($passengerAmount)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $refund = BookingRefund::with(['booking', 'passengers'])->findOrFail($id);
        return view('Booking::admin.refunds.show', compact('refund'));
    }

    /**
     * Admin sets refund amount and sends to user for approval
     */
//    public function setAmount(Request $request, $id)
//    {
//
//        Log::info('Refund Set Amount Request', [
//            'refund_id' => $id,
//            'request_data' => $request->all(),
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $request->validate([
//                'refund_amount' => 'required|numeric|min:0',
//                'refund_charges' => 'required|numeric|min:0',
//            ]);
//
//            $refund = BookingRefund::findOrFail($id);
//
//            if ($refund->status !== 'pending') {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'This refund request is no longer pending.'
//                ], 400);
//            }
//
//            $netRefund = $request->refund_amount - $request->refund_charges;
//
//            $refund->update([
//                'refund_amount' => $request->refund_amount,
//                'refund_charges' => $request->refund_charges,
//                'net_refund_amount' => $netRefund,
//                'airline_response' => $request->airline_response,
//                'status' => 'waiting_user_approval',
//                'approved_by' => auth()->id(),
//            ]);
//
//            // TODO: Send notification to user
//            // $this->sendUserNotification($refund, 'amount_set');
//
//            Log::info('Refund Amount Set Successfully', [
//                'refund_id' => $refund->id,
//                'net_refund' => $netRefund,
//                'status' => 'waiting_user_approval'
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'message' => 'Refund amount set successfully. Waiting for user approval.'
//            ]);
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json([
//                'status' => false,
//                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
//            ], 422);
//
//        } catch (\Exception $e) {
//            Log::error('Refund Set Amount Error', [
//                'refund_id' => $id,
//                'error' => $e->getMessage()
//            ]);
//            return response()->json([
//                'status' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }

    public function setAmount(Request $request, $id)
    {
        Log::info('Refund Set Amount Request', [
            'refund_id'    => $id,
            'request_data' => $request->all(),
            'user_id'      => auth()->id()
        ]);

        try {
            $request->validate([
                'refund_amount'  => 'required|numeric|min:0',
                'refund_charges' => 'required|numeric|min:0',
                'service_charge' => 'nullable|numeric|min:0',
            ]);

            $refund = BookingRefund::findOrFail($id);

            if ($refund->status !== 'pending') {
                return response()->json([
                    'status'  => false,
                    'message' => 'This refund request is no longer pending.'
                ], 400);
            }

            $serviceCharge = $request->service_charge ?? 0;
            $netRefund     = $request->refund_amount - $request->refund_charges - $serviceCharge;

            $refund->update([
                'refund_amount'     => $request->refund_amount,
                'refund_charges'    => $request->refund_charges,
                'service_charge'    => $serviceCharge,
                'net_refund_amount' => $netRefund,
                'airline_response'  => $request->airline_response,
                'status'            => 'waiting_user_approval',
                'approved_by'       => auth()->id(),
            ]);

            Log::info('Refund Amount Set Successfully', [
                'refund_id'     => $refund->id,
                'service_charge'=> $serviceCharge,
                'net_refund'    => $netRefund,
                'status'        => 'waiting_user_approval'
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Refund amount set successfully. Waiting for user approval.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            Log::error('Refund Set Amount Error', [
                'refund_id' => $id,
                'error'     => $e->getMessage()
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Final approve after user has approved
     */
    public function approve(Request $request, $id)
    {
//        return $id;
//        return 'sarowar';
        Log::info('Refund Final Approve Request', [
            'refund_id' => $id,
            'user_id' => auth()->id()
        ]);

//        try {
            $refund = BookingRefund::with(['booking.user'])->findOrFail($id);
            $booking = $refund->booking;
            $user = $booking->customer_id;
            $user=User::find($user);
//            return $user;
        try {
            // Check if user has approved
            if ($refund->status !== 'user_approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'User has not approved this refund yet.'
                ], 400);
            }

            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking not found for this refund request.'
                ], 400);
            }

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found for this booking.'
                ], 400);
            }

            $netRefund = $refund->net_refund_amount;
            $paidAmount = (float)$booking->paid;

            // Validate: Net refund cannot be greater than paid amount
            if ($netRefund > $paidAmount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Net refund amount (BDT ' . number_format($netRefund, 2) . ') cannot be greater than paid amount (BDT ' . number_format($paidAmount, 2) . ')'
                ], 400);
            }

            DB::beginTransaction();
            // Process refund to user's credit balance
            if ($paidAmount > 0 && $netRefund > 0) {
                $balanceBefore = $user->credit_balance;
                $user->increment('credit_balance', $netRefund);
                $balanceAfter = $user->fresh()->credit_balance;

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'booking_id' => $booking->id,
                    'ref_id' => $refund->id,
                    'type' => 'credit',
                    'transaction_type' => 'refund',
                    'amount' => $netRefund,
                    'status' => 'refund',
                    'reference' => 'Refund - Booking #' . $booking->code,
                    'remarks' => 'Refund for booking ' . $booking->code . ' (Charges: BDT ' . number_format($refund->refund_charges, 2) . ')',
                    'meta' => json_encode([
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'booking_code' => $booking->code,
                        'refund_id' => $refund->id,
                        'refund_amount' => $refund->refund_amount,
                        'refund_charges' => $refund->refund_charges,
                        'net_refund' => $netRefund,
                    ]),
                    'create_user' => auth()->id(),
                    'created_at' => now(),
                    'deposit_date' => now(),
                ]);

                Log::info('Refund Added to Credit Balance', [
                    'user_id' => $user->id,
                    'net_refund' => $netRefund,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter
                ]);
            }

            // Update refund record
            $refund->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Update passengers status
            $passengerIds = $refund->passenger_id ?? [];
            $isAllPassengers = empty($passengerIds);

            if ($isAllPassengers) {
                $passengerIds = BookingPassenger::where('booking_id', $booking->id)
                    ->pluck('id')->toArray();
            }

            try {
                BookingPassenger::whereIn('id', $passengerIds)
                    ->update(['status' => 'refunded']);
            } catch (\Exception $e) {
                Log::warning('Could not update passenger status', [
                    'error' => $e->getMessage()
                ]);
            }

            // Update booking status
            $allPassengersRefunded = BookingPassenger::where('booking_id', $booking->id)
                    ->whereNotIn('id', $passengerIds)
                    ->count() == 0;

            $booking->update([
                'status' => $allPassengersRefunded ? 'refunded' : 'partial_refunded'
            ]);

            DB::commit();

            Log::info('Refund Approved Successfully', [
                'refund_id' => $refund->id,
                'booking_id' => $booking->id,
                'net_refund' => $netRefund
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Refund approved successfully! BDT ' . number_format($netRefund, 2) . ' added to user wallet.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund Approve Error', [
                'refund_id' => $id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        Log::info('Refund Reject Request', [
            'refund_id' => $id,
            'reason' => $request->rejection_reason,
            'user_id' => auth()->id()
        ]);

        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:10'
            ]);

            $refund = BookingRefund::findOrFail($id);

            if (!in_array($refund->status, ['pending', 'waiting_user_approval'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'This refund cannot be rejected at current status.'
                ], 400);
            }

            DB::beginTransaction();

            $refund->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Update passengers status
            $passengerIds = $refund->passenger_id ?? [];
            if (!empty($passengerIds)) {
                try {
                    BookingPassenger::whereIn('id', $passengerIds)
                        ->update(['status' => 'refund_rejected']);
                } catch (\Exception $e) {
                    Log::warning('Could not update passenger status');
                }
            }

            DB::commit();

            Log::info('Refund Rejected Successfully', [
                'refund_id' => $refund->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Refund request rejected successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund Reject Error', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if (empty($ids) || empty($action)) {
            return back()->with('error', 'Please select items and action');
        }

        try {
            switch ($action) {
                case 'delete':
                    $deleted = BookingRefund::whereIn('id', $ids)->delete();
                    return back()->with('success', "Deleted {$deleted} refund request(s)");

                default:
                    return back()->with('error', 'Invalid action');
            }
        } catch (\Exception $e) {
            Log::error('Bulk Action Error', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
