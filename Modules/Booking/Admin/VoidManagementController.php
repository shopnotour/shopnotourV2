<?php
//
//namespace Modules\Booking\Admin;
//
//use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use Illuminate\Http\Request; // ✅ Changed from Facades\Request
//use Modules\Booking\Models\BookingPassenger;
//use Modules\Booking\Models\BookingVoid;
//use Modules\Booking\Models\Booking;
//use Modules\User\Models\Wallet\Transaction;
//
//// ✅ Added
//
//class VoidManagementController extends Controller
//{
//    public function index(Request $request)
//    {
//        $query = BookingVoid::with(['booking']) // ✅ Added with() for eager loading
//        ->orderBy('created_at', 'desc');
//
//        // ✅ Enable Filters
//        if ($request->status) {
//            $query->where('status', $request->status);
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
//        $statuses = ['pending', 'approved', 'rejected']; // ✅ Added
//
//        return view('Booking::admin.voids.index', compact('rows', 'statuses'));
//    }
//
//    public function show($id)
//    {
//        $void = BookingVoid::with(['booking'])->findOrFail($id);
//        return view('Booking::admin.voids.show', compact('void'));
//    }
//
////    public function approve(Request $request, $id)
////    {
////        Log::info('🔵 Void Approve Request', [
////            'void_id' => $id,
////            'request_data' => $request->all(),
////            'user_id' => auth()->id()
////        ]);
////
////        try {
////            // ✅ Validate request
////            $request->validate([
////                'void_charges' => 'required|numeric|min:0'
////            ]);
////
////            DB::beginTransaction();
////
////            $void = BookingVoid::findOrFail($id);
////            $booking = $void->booking;
////
////            // ✅ Check if booking exists
////            if (!$booking) {
////                throw new \Exception('Booking not found for this void request');
////            }
////
////            Log::info('📦 Void Found', [
////                'void_id' => $void->id,
////                'booking_id' => $booking->id,
////                'current_status' => $void->status
////            ]);
////
////            // Update void record
////            $void->update([
////                'status' => 'approved',
////                'void_charges' => $request->void_charges,
////                'airline_response' => $request->airline_response,
////                'processed_by' => auth()->id(),
////                'processed_at' => now()
////            ]);
////
////            Log::info('✅ Void Updated', [
////                'void_id' => $void->id,
////                'new_status' => 'approved',
////                'charges' => $request->void_charges
////            ]);
////
////            // Update booking status
////            $booking->update([
////                'status' => 'voided',
////                'void_charge' => $request->void_charges
////            ]);
////
////            Log::info('✅ Booking Updated', [
////                'booking_id' => $booking->id,
////                'new_status' => 'voided'
////            ]);
////
////            // Update all passengers
////            $updatedCount = BookingPassenger::where('booking_id', $booking->id)
////                ->update(['status' => 'voided']);
////
////            Log::info('✅ Passengers Updated', [
////                'booking_id' => $booking->id,
////                'passengers_updated' => $updatedCount
////            ]);
////
////            DB::commit();
////
////            Log::info('✅ Void Approved Successfully', [
////                'void_id' => $void->id,
////                'booking_id' => $booking->id
////            ]);
////
////            // ✅ Added success field for compatibility
////            return response()->json([
////                'status' => true,
////                'success' => true, // ✅ Added
////                'message' => 'Void approved successfully! Charge: ৳' . number_format($request->void_charges, 2),
////                'data' => [
////                    'void_id' => $void->id,
////                    'booking_id' => $booking->id,
////                    'void_charges' => $request->void_charges
////                ]
////            ], 200); // ✅ Added explicit status code
////
////        } catch (\Illuminate\Validation\ValidationException $e) {
////            DB::rollBack();
////            Log::error('❌ Validation Error', [
////                'errors' => $e->errors()
////            ]);
////            return response()->json([
////                'status' => false,
////                'success' => false, // ✅ Added
////                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
////            ], 422);
////
////        } catch (\Exception $e) {
////            DB::rollBack();
////            Log::error('❌ Void Approve Error', [
////                'error' => $e->getMessage(),
////                'line' => $e->getLine(), // ✅ Added
////                'file' => $e->getFile(), // ✅ Added
////                'trace' => $e->getTraceAsString()
////            ]);
////            return response()->json([
////                'status' => false,
////                'success' => false, // ✅ Added
////                'message' => 'Error: ' . $e->getMessage()
////            ], 500);
////        }
////    }
//
//    public function approve(Request $request, $id)
//    {
////        return $request;
//        Log::info('🔵 Void Approve Request', [
//            'void_id' => $id,
//            'request_data' => $request->all(),
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            // ✅ Validate request
//            $request->validate([
//                'void_charges' => 'required|numeric|min:0',
////                'confirm' => 'required|boolean', // ✅ Confirmation required
//                'airline_response' => 'nullable|string'
//            ]);
//
//            // ✅ Check confirmation
////            if (!$request->confirm) {
////                return response()->json([
////                    'status' => false,
////                    'success' => false,
////                    'message' => 'Please confirm to approve this void request'
////                ], 400);
////            }
//
//            DB::beginTransaction();
//
//            $void = BookingVoid::with(['booking.user'])->findOrFail($id);
//            $booking = $void->booking;
//            $user = $booking->user;
////            return $user;
//            // ✅ Check if booking exists
//            if (!$booking) {
//                throw new \Exception('Booking not found for this void request');
//            }
//
//            // ✅ Check if void is already processed
//            if (in_array($void->status, ['approved', 'rejected'])) {
//                throw new \Exception('This void request has already been processed');
//            }
//
//            Log::info('📦 Void Found', [
//                'void_id' => $void->id,
//                'booking_id' => $booking->id,
//                'user_id' => $user->id,
//                'current_credit_balance' => $user->credit_balance,
//                'paid_amount' => $booking->paid
//            ]);
//
//            $paidAmount = (float) $booking->paid;
//            $voidCharges = (float) $request->void_charges;
//
//            // ✅ Case 1: Paid amount ache - refund dite hobe
//            if ($paidAmount > 0) {
//
//                // Validate void charges
//                if ($voidCharges > $paidAmount) {
//                    throw new \Exception('Void charges (৳' . number_format($voidCharges, 2) . ') cannot be greater than paid amount (৳' . number_format($paidAmount, 2) . ')');
//                }
//
//                $refundAmount = $paidAmount - $voidCharges;
//
//                if ($refundAmount > 0) {
//                    // User er credit_balance e add koro
//                    $balanceBefore = $user->credit_balance;
//                    $user->increment('credit_balance', $refundAmount);
//                    $balanceAfter = $user->credit_balance;
//
//                    // Transaction create koro
//                    Transaction::create([
//                        'user_id' => $user->id,
//                        'booking_id' => $booking->id,
//                        'ref_id' => $void->id,
//                        'type' => 'credit', // ✅ Credit karon refund
//                        'transaction_type' => 'void_refund',
//                        'amount' => $refundAmount,
//                        'status' => 'refund',
//                        'reference' => 'Void refund - Booking #' . $booking->code,
//                        'remarks' => 'Refund for voided booking ' . $booking->code . ' (Void charges: ৳' . number_format($voidCharges, 2) . ')',
//                        'meta' => json_encode([
//                            'balance_before' => $balanceBefore,
//                            'balance_after' => $balanceAfter,
//                            'booking_code' => $booking->code,
//                            'void_id' => $void->id,
//                            'paid_amount' => $paidAmount,
//                            'void_charges' => $voidCharges,
//                            'refund_amount' => $refundAmount,
//                        ]),
//                        'create_user' => auth()->id(),
//                        'created_at' => now(),
//                        'deposit_date' => now(),
//                    ]);
//
//                    Log::info('✅ Refund Added to Credit Balance', [
//                        'user_id' => $user->id,
//                        'refund_amount' => $refundAmount,
//                        'balance_before' => $balanceBefore,
//                        'balance_after' => $balanceAfter
//                    ]);
//                }
//
//            }
//            // ✅ Case 2: Paid amount nai - charge kete nao
//            else {
//
//                // Check if user has enough balance
//                if ($user->credit_balance < $voidCharges) {
//                    throw new \Exception('Insufficient balance. Current balance: ৳' . number_format($user->credit_balance, 2) . ', Required: ৳' . number_format($voidCharges, 2));
//                }
//
//                // User er credit_balance theke minus koro
//                $balanceBefore = $user->credit_balance;
//                $user->decrement('credit_balance', $voidCharges);
//                $balanceAfter = $user->credit_balance;
//
//                // Transaction create koro
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $booking->id,
//                    'ref_id' => $void->id,
//                    'type' => 'debit', // ✅ Debit karon charge katche
//                    'transaction_type' => 'void_charge',
//                    'amount' => $voidCharges,
//                    'status' => 'payment',
//                    'reference' => 'Void charge - Booking #' . $booking->code,
//                    'remarks' => 'Void charges for booking ' . $booking->code,
//                    'meta' => json_encode([
//                        'balance_before' => $balanceBefore,
//                        'balance_after' => $balanceAfter,
//                        'booking_code' => $booking->code,
//                        'void_id' => $void->id,
//                        'paid_amount' => $paidAmount,
//                        'void_charges' => $voidCharges,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('✅ Void Charges Deducted from Credit Balance', [
//                    'user_id' => $user->id,
//                    'void_charges' => $voidCharges,
//                    'balance_before' => $balanceBefore,
//                    'balance_after' => $balanceAfter
//                ]);
//            }
//
//            // ✅ Update void record
//            $void->update([
//                'status' => 'approved',
//                'void_charges' => $voidCharges,
//                'airline_response' => $request->airline_response,
//                'processed_by' => auth()->id(),
//                'processed_at' => now()
//            ]);
//
//            // ✅ Update booking status
//            $booking->update([
//                'status' => 'voided',
//                'void_charge' => $voidCharges,
//            ]);
//
//            // ✅ Update passengers
//            $updatedCount = BookingPassenger::where('booking_id', $booking->id)
//                ->update(['status' => 'voided']);
//
//            DB::commit();
//
//            Log::info('✅ Void Approved Successfully', [
//                'void_id' => $void->id,
//                'booking_id' => $booking->id,
//                'paid_amount' => $paidAmount,
//                'void_charges' => $voidCharges,
//                'passengers_updated' => $updatedCount
//            ]);
//
//            // ✅ Build success message
//            $message = 'Void approved successfully! Void Charge: ৳' . number_format($voidCharges, 2);
//
//            if ($paidAmount > 0) {
//                $refundAmount = $paidAmount - $voidCharges;
//                if ($refundAmount > 0) {
//                    $message .= ' | Refunded: ৳' . number_format($refundAmount, 2);
//                }
//            }
//
//            return response()->json([
//                'status' => true,
//                'success' => true,
//                'message' => $message,
//                'data' => [
//                    'void_id' => $void->id,
//                    'booking_id' => $booking->id,
//                    'void_charges' => $voidCharges,
//                    'paid_amount' => $paidAmount,
//                    'passengers_updated' => $updatedCount,
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
//                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
//                'errors' => $e->errors()
//            ], 422);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('❌ Void Approve Error', [
//                'void_id' => $id,
//                'error' => $e->getMessage(),
//                'line' => $e->getLine(),
//                'file' => $e->getFile(),
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }
//
//    public function reject(Request $request, $id)
//    {
//        Log::info('🔴 Void Reject Request', [
//            'void_id' => $id,
//            'reason' => $request->rejection_reason,
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $request->validate([
//                'rejection_reason' => 'required|string|min:10' // ✅ Added min length
//            ]);
//
//            $void = BookingVoid::findOrFail($id);
//
//            $void->update([
//                'status' => 'rejected',
//                'rejection_reason' => $request->rejection_reason,
//                'processed_by' => auth()->id(),
//                'processed_at' => now()
//            ]);
//
//            Log::info('✅ Void Rejected Successfully', [
//                'void_id' => $void->id
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'success' => true, // ✅ Added
//                'message' => 'Void request rejected successfully'
//            ], 200); // ✅ Added status code
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
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
//            Log::error('❌ Void Reject Error', [
//                'error' => $e->getMessage()
//            ]);
//            return response()->json([
//                'status' => false,
//                'success' => false, // ✅ Added
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
//        Log::info('📋 Bulk Action', [
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
//                    // ✅ Set default charges for bulk approve
//                    $updated = BookingVoid::whereIn('id', $ids)
//                        ->where('status', 'pending')
//                        ->update([
//                            'status' => 'approved',
//                            'void_charges' => 500, // ✅ Default charge
//                            'processed_by' => auth()->id(),
//                            'processed_at' => now()
//                        ]);
//
//                    Log::info('✅ Bulk Approved', ['count' => $updated]);
//                    return back()->with('success', "Approved {$updated} void request(s)");
//
//                case 'reject':
//                    $updated = BookingVoid::whereIn('id', $ids)
//                        ->where('status', 'pending')
//                        ->update([
//                            'status' => 'rejected',
//                            'rejection_reason' => 'Bulk rejection by admin', // ✅ Added default reason
//                            'processed_by' => auth()->id(),
//                            'processed_at' => now()
//                        ]);
//
//                    Log::info('✅ Bulk Rejected', ['count' => $updated]);
//                    return back()->with('success', "Rejected {$updated} void request(s)");
//
//                case 'delete':
//                    $deleted = BookingVoid::whereIn('id', $ids)->delete();
//                    Log::info('✅ Bulk Deleted', ['count' => $deleted]);
//                    return back()->with('success', "Deleted {$deleted} void request(s)");
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
use Modules\Booking\Models\BookingVoid;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\Transaction;

class VoidManagementController extends Controller
{
//    public function index(Request $request)
//    {
//        $query = BookingVoid::with(['booking', 'booking.user'])
//            ->orderBy('created_at', 'desc');
//
//        if ($request->status) {
//            $query->where('status', $request->status);
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
//        return view('Booking::admin.voids.index', compact('rows'));
//    }

    public function index(Request $request)
    {
        $rows = BookingVoid::with(['booking', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Booking::admin.voids.index', compact('rows'));
    }

    public function show($id)
    {
        $void = BookingVoid::with(['booking'])->findOrFail($id);
        return view('Booking::admin.voids.show', compact('void'));
    }

    /**
     * Step 1: Admin sets void charges and sends to user for approval
     */
    public function setAmount(Request $request, $id)
    {
        Log::info('Void Set Amount Request', [
            'void_id' => $id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        try {
            $request->validate([
                'void_charges' => 'required|numeric|min:0',
                'airline_response' => 'nullable|string'
            ]);

            $void = BookingVoid::with(['booking'])->findOrFail($id);
            $booking = $void->booking;

            if ($void->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'This void request is no longer pending.'
                ], 400);
            }

            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking not found.'
                ], 400);
            }

            $paidAmount = (float)$booking->paid;
            $voidCharges = (float)$request->void_charges;

            // Validate void charges
            if ($paidAmount > 0 && $voidCharges > $paidAmount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Void charges (BDT ' . number_format($voidCharges, 2) . ') cannot be greater than paid amount (BDT ' . number_format($paidAmount, 2) . ')'
                ], 400);
            }

            // Calculate refund amount
            $refundAmount = $paidAmount > 0 ? ($paidAmount - $voidCharges) : 0;

            $void->update([
                'void_charges' => $voidCharges,
                'refund_amount' => $refundAmount,
                'airline_response' => $request->airline_response,
                'status' => 'waiting_user_approval',
                'updated_by' => auth()->id(),
            ]);

            Log::info('Void Amount Set Successfully', [
                'void_id' => $void->id,
                'void_charges' => $voidCharges,
                'paid_amount' => $paidAmount,
                'refund_amount' => $refundAmount,
                'status' => 'waiting_user_approval'
            ]);

            $message = 'Void charges set successfully. ';
            if ($paidAmount > 0) {
                $message .= 'Customer will get refund: BDT ' . number_format($refundAmount, 2) . '. ';
            } else {
                $message .= 'Customer will pay: BDT ' . number_format($voidCharges, 2) . '. ';
            }
            $message .= 'Waiting for user approval.';

            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            Log::error('Void Set Amount Error', [
                'void_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Final approve after user has approved
     */
    public function approve(Request $request, $id)
    {
        Log::info('Void Final Approve Request', [
            'void_id' => $id,
            'user_id' => auth()->id()
        ]);

        try {
            $void = BookingVoid::with(['booking.user'])->findOrFail($id);
            $booking = $void->booking;
            $user = $booking->user;

            // Check if user has approved
            if ($void->status !== 'user_approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'User has not approved this void yet.'
                ], 400);
            }

            if (!$booking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Booking not found.'
                ], 400);
            }

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 400);
            }

            $paidAmount = (float)$booking->paid;
            $voidCharges = (float)$void->void_charges;
            $currentBalance = (float)$user->credit_balance;

            Log::info('Void Calculation', [
                'void_id' => $void->id,
                'booking_id' => $booking->id,
                'paid_amount' => $paidAmount,
                'void_charges' => $voidCharges,
                'current_balance' => $currentBalance
            ]);

            DB::beginTransaction();

            // Case 1: Paid amount exists - refund needed
            if ($paidAmount > 0) {
                $refundAmount = $paidAmount - $voidCharges;

                if ($refundAmount > 0) {
                    // Add refund to wallet
                    $user->increment('credit_balance', $refundAmount);

                    Transaction::create([
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'ref_id' => $void->id,
                        'type' => 'credit',
                        'transaction_type' => 'void_refund',
                        'amount' => $refundAmount,
                        'status' => 'refund',
                        'reference' => 'Void Refund - Booking #' . $booking->code,
                        'remarks' => 'Refund for voided booking. Paid: BDT ' . number_format($paidAmount, 2) . ', Charges: BDT ' . number_format($voidCharges, 2),
                        'meta' => json_encode([
                            'balance_before' => $currentBalance,
                            'balance_after' => $currentBalance + $refundAmount,
                            'booking_code' => $booking->code,
                            'void_id' => $void->id,
                            'paid_amount' => $paidAmount,
                            'void_charges' => $voidCharges,
                            'refund_amount' => $refundAmount,
                        ]),
                        'create_user' => auth()->id(),
                        'created_at' => now(),
                        'deposit_date' => now(),
                    ]);

                    Log::info('Wallet Credited (Refund)', [
                        'user_id' => $user->id,
                        'refund_amount' => $refundAmount,
                        'balance_before' => $currentBalance,
                        'balance_after' => $currentBalance + $refundAmount
                    ]);
                }
            } // Case 2: No paid amount - deduct charges from wallet
            else {
                if ($currentBalance < $voidCharges) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient wallet balance. Required: BDT ' . number_format($voidCharges, 2) . ', Available: BDT ' . number_format($currentBalance, 2)
                    ], 400);
                }

                $user->decrement('credit_balance', $voidCharges);

                Transaction::create([
                    'user_id' => $user->id,
                    'booking_id' => $booking->id,
                    'ref_id' => $void->id,
                    'type' => 'debit',
                    'transaction_type' => 'void_charge',
                    'amount' => $voidCharges,
                    'status' => 'payment',
                    'reference' => 'Void Charge - Booking #' . $booking->code,
                    'remarks' => 'Void charges for booking',
                    'meta' => json_encode([
                        'balance_before' => $currentBalance,
                        'balance_after' => $currentBalance - $voidCharges,
                        'booking_code' => $booking->code,
                        'void_id' => $void->id,
                        'void_charges' => $voidCharges,
                    ]),
                    'create_user' => auth()->id(),
                    'created_at' => now(),
                    'deposit_date' => now(),
                ]);

                Log::info('Wallet Debited (Charge)', [
                    'user_id' => $user->id,
                    'void_charges' => $voidCharges,
                    'balance_before' => $currentBalance,
                    'balance_after' => $currentBalance - $voidCharges
                ]);
            }

            // Update void record
            $void->update([
                'status' => 'approved',
                'updated_by' => auth()->id(),
                'voided_at' => now()
            ]);

            // Update booking status
            $booking->update([
                'status' => 'voided',
                'void_charge' => $voidCharges,
            ]);

            // Update passengers
            $updatedCount = BookingPassenger::where('booking_id', $booking->id)
                ->update(['status' => 'voided']);

            DB::commit();

            Log::info('Void Approved Successfully', [
                'void_id' => $void->id,
                'booking_id' => $booking->id,
                'passengers_updated' => $updatedCount
            ]);

            // Build response message
            $message = 'Void approved successfully! ';
            if ($paidAmount > 0) {
                $refundAmount = $paidAmount - $voidCharges;
                if ($refundAmount > 0) {
                    $message .= 'Refunded to wallet: BDT ' . number_format($refundAmount, 2);
                } else {
                    $message .= 'No refund (charges equal to paid amount)';
                }
            } else {
                $message .= 'Deducted from wallet: BDT ' . number_format($voidCharges, 2);
            }

            return response()->json([
                'status' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Void Approve Error', [
                'void_id' => $id,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        Log::info('Void Reject Request', [
            'void_id' => $id,
            'reason' => $request->rejection_reason,
            'user_id' => auth()->id()
        ]);

        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:10'
            ]);

            $void = BookingVoid::findOrFail($id);

            if (!in_array($void->status, ['pending', 'waiting_user_approval'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'This void cannot be rejected at current status.'
                ], 400);
            }

            $void->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'updated_by' => auth()->id(),
                'voided_at' => now()
            ]);

            Log::info('Void Rejected Successfully', [
                'void_id' => $void->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Void request rejected successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            Log::error('Void Reject Error', [
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
                    $deleted = BookingVoid::whereIn('id', $ids)
                        ->whereIn('status', ['pending', 'rejected'])
                        ->delete();
                    return back()->with('success', "Deleted {$deleted} void request(s)");

                default:
                    return back()->with('error', 'Invalid action');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
