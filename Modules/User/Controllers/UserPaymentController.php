<?php

namespace Modules\User\Controllers;


use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SslCommerzPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\Transaction;

class UserPaymentController
{

//    public function initiate(Request $request)
//    {
//        $request->validate([
//            'booking_id'     => 'required|integer',
//            'payment_method' => 'required|string',
//        ]);
//
//        $user          = auth()->user();
//        $booking       = Booking::where('id', $request->booking_id)
//            ->where('customer_id', $user->id)
//            ->firstOrFail();
////        return $booking;
//        $paymentMethod = $request->payment_method; // bkash, sslcommerz, wallet
//        $totalAmount   = $booking->pay_now;
//
//        // Guard: already paid
//        if ($totalAmount <= 0) {
//            return response()->json([
//                'success' => false,
//                'message' => 'No outstanding payment for this booking.',
//            ]);
//        }
//
//        // ── WALLET ──────────────────────────────────────────────
//        if ($paymentMethod === 'wallet') {
//            DB::beginTransaction();
//            try {
//                if ($user->credit_balance < $totalAmount) {
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Insufficient wallet balance!',
//                    ]);
//                }
//
//                // Deduct from wallet
//                $user->decrement('credit_balance', $totalAmount);
//
//                // Create wallet transaction
//                Transaction::create([
//                    'user_id'          => $user->id,
//                    'booking_id'       => $booking->id,
//                    'ref_id'           => $booking->id,
//                    'type'             => 'debit',
//                    'transaction_type' => 'booking_payment',
//                    'amount'           => $totalAmount,
//                    'status'           => 'payment',
//                    'reference'        => 'Flight booking - ' . $booking->code,
//                    'remarks'          => 'Payment for flight booking ' . $booking->code,
//                    'meta'             => json_encode([
//                        'balance_before' => $user->credit_balance + $totalAmount,
//                        'balance_after'  => $user->credit_balance,
//                        'booking_code'   => $booking->code,
//                        'payment_method' => 'wallet',
//                    ]),
//                    'create_user'  => $user->id,
//                    'created_at'   => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                // Mark booking as paid
//                $booking->update([
//                    'gateway'            => 'wallet',
//                    'payment_method'     => 'wallet',
//                    'wallet_credit_used' => $totalAmount,
//                    'wallet_amount'      => $totalAmount,
//                    'paid'               => $totalAmount,
//                    'pay_now'            => 0,
//                    'is_paid'            => 1,
//                    'status'            => 'issue_request',
//                    'paid_at'            => now(),
//                ]);
//
//                DB::commit();
//
//                return response()->json([
//                    'success' => true,
//                    'message' => 'Payment successful via Wallet!',
//                ]);
//
//            } catch (\Exception $e) {
//                DB::rollback();
//                \Log::error('Wallet payment failed (initiate): ' . $e->getMessage());
//
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Payment failed. Please try again.',
//                ]);
//            }
//        }
//
//        // ── SSLCOMMERZ ──────────────────────────────────────────
//        elseif ($paymentMethod === 'sslcommerz') {
//            $gatewayssl = new SslCommerzPaymentController();
//            $response   = $gatewayssl->index($request, $booking, $totalAmount);
////            dd($response);
////return $response;
//            if ($request->expectsJson()) {
//                // RedirectResponse থেকে URL বের করুন
//                return response()->json([
//                    'success'      => true,
//                    'redirect_url' => $response->getTargetUrl(),
//                ]);
//            }
//
//        }
//
//        // ── BKASH ───────────────────────────────────────────────
//        elseif ($paymentMethod === 'bkash') {
//            $bkashGateway = new PaymentController();
//            $response = $bkashGateway->createPayment($request, $booking);
//            return response()->json([
//                'success'      => true,
//                'redirect_url' => $response->getTargetUrl(),
//            ]);
//        }
//
////        if ($request->expectsJson()) {
////            // fetch() থেকে এসেছে — redirect URL টা JSON এ দাও
////            return response()->json([
////                'success'      => true,
////                'redirect_url' => $response->getTargetUrl(),
////            ]);
////        }
//
//        // ── UNKNOWN METHOD ──────────────────────────────────────
//        return response()->json([
//            'success' => false,
//            'message' => 'Invalid payment method selected.',
//        ]);
//    }

    public function initiate(Request $request)
    {
//        return $request;
        $request->validate([
            'booking_id'     => 'required|integer',
            'payment_method' => 'required|string',
        ]);

        $user    = auth()->user();
        $booking = Booking::where('id', $request->booking_id)
            ->where('customer_id', $user->id)
            ->firstOrFail();

        $paymentMethod = $request->payment_method;
        $originalTotal = (float) $booking->pay_now;

        // Guard: already paid
        if ($originalTotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No outstanding payment for this booking.',
            ]);
        }

        // ══════════════════════════════════════════════════
        // WALLET ONLY
        // ══════════════════════════════════════════════════
        if ($paymentMethod !== 'wallet') {
            return response()->json([
                'success' => false,
                'message' => 'Only wallet payment is supported at this time.',
            ]);
        }

        // ══════════════════════════════════════════════════
        // BONUS CHECK
        // ══════════════════════════════════════════════════
        $bonusDeducted  = 0;
        $bonusEnabled   = (bool) setting_item('bonus_enabled');
        $bonusPerDeduct = (float) (setting_item('bonus_per_deduct') ?? 0);
        $userBonusBal   = (float) ($user->bonus_balance ?? 0);

        if (
            $bonusEnabled       &&
            $bonusPerDeduct > 0 &&
            $userBonusBal >= $bonusPerDeduct   // user এর bonus আছে কিনা শুধু এটুকুই
        ) {
            $bonusDeducted = min($bonusPerDeduct, $originalTotal);
        }

        // Wallet থেকে কত কাটবে
        $walletAmount = $originalTotal - $bonusDeducted;

        // ══════════════════════════════════════════════════
        // WALLET BALANCE CHECK
        // ══════════════════════════════════════════════════
        $walletBalance = (float) ($user->credit_balance ?? 0);

        if ($walletBalance < $walletAmount) {
            $shortfall = $walletAmount - $walletBalance;
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance! আপনার wallet এ ৳'
                    . number_format($walletBalance, 2)
                    . ' আছে, প্রয়োজন ৳'
                    . number_format($walletAmount, 2)
                    . ' (Shortfall: ৳' . number_format($shortfall, 2) . ')',
            ]);
        }

        // ══════════════════════════════════════════════════
        // DB TRANSACTION — সব একসাথে, fail হলে rollback
        // ══════════════════════════════════════════════════
        DB::beginTransaction();
        try {

            // ── ১. Bonus কাটো ──────────────────────────
            if ($bonusDeducted > 0) {
                $user->decrement('bonus_balance', $bonusDeducted);

                Transaction::create([
                    'user_id'          => $user->id,
                    'booking_id'       => $booking->id,
                    'ref_id'           => $booking->id,
                    'type'             => 'debit',
                    'transaction_type' => 'bonus_deduction',
                    'amount'           => $bonusDeducted,
                    'status'           => 'payment',
                    'reference'        => 'Bonus deduction - ' . $booking->code,
                    'remarks'          => 'Bonus ৳' . number_format($bonusDeducted, 2) . ' কাটা হয়েছে — ' . $booking->code,
                    'meta'             => json_encode([
                        'bonus_before'   => $userBonusBal,
                        'bonus_after'    => $userBonusBal - $bonusDeducted,
                        'booking_code'   => $booking->code,
                        'payment_method' => 'bonus',
                    ]),
                    'create_user'  => $user->id,
                    'created_at'   => now(),
                    'deposit_date' => now(),
                ]);
            }

            // ── ২. Wallet কাটো ─────────────────────────
            if ($walletAmount > 0) {
                $user->decrement('credit_balance', $walletAmount);

                Transaction::create([
                    'user_id'          => $user->id,
                    'booking_id'       => $booking->id,
                    'ref_id'           => $booking->id,
                    'type'             => 'debit',
                    'transaction_type' => 'booking_payment',
                    'amount'           => $walletAmount,
                    'status'           => 'payment',
                    'reference'        => 'Flight booking - ' . $booking->code,
                    'remarks'          => 'Wallet ৳' . number_format($walletAmount, 2) . ' payment — ' . $booking->code,
                    'meta'             => json_encode([
                        'balance_before' => $walletBalance,
                        'balance_after'  => $walletBalance - $walletAmount,
                        'bonus_deducted' => $bonusDeducted,
                        'total_original' => $originalTotal,
                        'booking_code'   => $booking->code,
                        'payment_method' => 'wallet',
                    ]),
                    'create_user'  => $user->id,
                    'created_at'   => now(),
                    'deposit_date' => now(),
                ]);
            }

            // ── ৩. Booking update ───────────────────────
            $booking->update([
                'gateway'            => 'wallet',
                'payment_method'     => 'wallet',
                'wallet_credit_used' => $walletAmount,
                'wallet_amount'      => $walletAmount,
                'paid'               => $originalTotal,
                'pay_now'            => 0,
//                'bonus_pending'      => 0,
                'is_paid'            => 1,
                'status'             => 'issue_request',
                'paid_at'            => now(),
            ]);

            DB::commit();

            // Success message
            $msg = 'Payment successful!';
            if ($bonusDeducted > 0 && $walletAmount > 0) {
                $msg = 'Payment successful! (Bonus: ৳' . number_format($bonusDeducted, 2)
                    . ' + Wallet: ৳' . number_format($walletAmount, 2) . ')';
            } elseif ($bonusDeducted > 0 && $walletAmount <= 0) {
                $msg = 'Payment successful via Bonus! (৳' . number_format($bonusDeducted, 2) . ')';
            } else {
                $msg = 'Payment successful via Wallet! (৳' . number_format($walletAmount, 2) . ')';
            }

            return response()->json(['success' => true, 'message' => $msg]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Wallet payment failed [' . $booking->code . ']: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
            ]);
        }
    }
}
