<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;
use Modules\User\Models\Wallet\Transaction;
use Sarowar\Bkash\Facades\Bkash;


class PaymentController extends Controller
{

    public function index()
    {

        return view('bkash::bkash_payment');

    }

    public function createPayment(Request $request, $booking)
    {
        $callbackURL = config('bkash_payment.callback_url');
        $requestbody = array(
            'mode' => '0011',
            'amount' => $booking->total,
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => $booking->phone,
            'merchantInvoiceNumber' => $booking->code,
            'callbackURL' => $callbackURL
        );
//        return $requestbody;
        $requestbodyJson = json_encode($requestbody);
        $payment_options=Bkash::create_payment($requestbodyJson);
//return $payment_options;
        if (isset($payment_options['statusCode']) && $payment_options['statusCode'] === '0000') {
            return redirect()->away($payment_options['bkashURL']);
        } else {
            // Handle error
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initialization failed',
                ]);
            }
            return back()->with('error', 'Payment initialization failed');
//            return back()->with('error', 'Payment initialization failed');
        }

    }
    public function success(Request $request)
    {

        $paymentID = $request->input('paymentID');
        $status    = $request->input('status');

        // Execute the payment
        $response = Bkash::exicutepay($request);
        $response = json_decode(json_encode($response), true);

        // Find booking
        $invoice = $response['merchantInvoiceNumber'] ?? null;
        $booking = Booking::where('code', $invoice)->first();

        if (!$booking) {
            return redirect()->route('home')
                ->with('error', 'Booking not found!');
        }

        // Validate payment
        if (
            isset($response['statusCode'])         && $response['statusCode'] === '0000' &&
            isset($response['transactionStatus'])  && $response['transactionStatus'] === 'Completed'
        ) {
            DB::beginTransaction();
            try {
                $amount = $response['amount'] ?? 0;
                $trxID  = $response['trxID']  ?? null;

                // Create Transaction record
                Transaction::create([
                    'user_id'          => $booking->customer_id,
                    'booking_id'       => $booking->id,
                    'ref_id'           => $trxID,
                    'type'             => 'credit',
                    'transaction_type' => 'booking_payment',
                    'amount'           => $amount,
                    'status'           => 'confirmed',
                    'reference'        => 'bKash Payment - ' . $booking->code,
                    'remarks'          => 'Payment completed via bKash. TrxID: ' . $trxID,
                    'meta'             => json_encode([
                        'payment_id'      => $paymentID,
                        'trx_id'          => $trxID,
                        'currency'        => 'BDT',
                        'booking_code'    => $booking->code,
                        'payment_method'  => 'bkash',
                        'customer_mobile' => $response['customerMsisdn'] ?? null,
                    ]),
                    'create_user'  => $booking->customer_id,
                    'created_at'   => now(),
                    'deposit_date' => now(),
                ]);

                // Update Booking
                $booking->update([
                    'gateway'        => 'bkash',
                    'payment_method' => 'bkash',
                    'paid'           => $amount,
                    'is_paid'        => 1,
                    'pay_now'        => 0,
                    'paid_at'        => now(),
                ]);

                DB::commit();

//                Session::forget(['selected_flight', 'search_params', 'reissue_data']);

                return redirect()->route('booking.details', $booking->id)
                    ->with('success', 'Payment completed successfully!');

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('bKash success callback failed: ' . $e->getMessage(), [
                    'response'  => $response,
                    'paymentID' => $paymentID,
                ]);

                return redirect()->route('booking.details', $booking->id)
                    ->with('error', 'Payment processing failed. Please contact support.');
            }

        } else {
            // Payment validation failed
            \Log::error('bKash Payment Validation Failed', [
                'response'  => $response,
                'paymentID' => $paymentID,
            ]);

            return redirect()->route('booking.details', $booking->id)
                ->with('error', 'Payment validation failed!');
        }
    }

//    public function success(Request $request)
//    {
////        return $request;
//        $paymentID = $request->input('paymentID');
//        $status = $request->input('status');
//
//        // Check if payment was successful
////        if ($status !== 'success') {
////            return redirect()->route('home')->with('error', 'Payment was ' . $status);
////        }
//
////        DB::beginTransaction();
////        try {
//            // Execute the payment
//            $response = Bkash::exicutepay($request);
////            return $response;
//            $response = json_decode(json_encode($response), true);
////            return $response;
//            // Find booking
//            $invoice = $response['merchantInvoiceNumber'] ?? null;
//            $booking = Booking::where('code', $invoice)->first();
////dd($booking);
//            if (!$booking) {
//                DB::rollback();
//                return redirect()->route('home')->with('error', 'Booking not found!');
//            }
//
//            // Validate payment
//            if (isset($response['statusCode']) && $response['statusCode'] === '0000' &&
//                isset($response['transactionStatus']) && $response['transactionStatus'] === 'Completed') {
//
//                $amount = $response['amount'] ?? 0;
//                $trxID = $response['trxID'] ?? null;
//
//                // Create Transaction record
//                Transaction::create([
//                    'user_id' => $booking->customer_id,
//                    'booking_id' => $booking->id,
//                    'ref_id' => $trxID,
//                    'type' => 'credit',
//                    'transaction_type' => 'booking_payment',
//                    'amount' => $amount,
//                    'status' => 'confirmed',
//                    'reference' => 'bKash Payment - ' . $booking->code,
//                    'remarks' => 'Payment completed via bKash. TrxID: ' . $trxID,
//                    'meta' => json_encode([
//                        'payment_id' => $paymentID,
//                        'trx_id' => $trxID,
//                        'currency' => 'BDT',
//                        'booking_code' => $booking->code,
//                        'payment_method' => 'bkash',
//                        'customer_mobile' => $response['customerMsisdn'] ?? null,
//                    ]),
//                    'create_user' => $booking->customer_id,
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                // Update Booking
//                $booking->update([
//                    'gateway' => 'bkash',
//                    'payment_method' => 'bkash',
//                    'paid' => $amount,
//                    'is_paid' => 1,
//                    'pay_now' => 0,
//                    'paid_at' => now(),
//                ]);
//
//                DB::commit();
//
//                // Clear session
//                Session::forget(['selected_flight', 'search_params', 'reissue_data']);
//
//                return redirect()->route('booking.confirmation', $booking->code)
//                    ->with('success', 'Payment completed successfully!');
//
//            } else {
//                // Payment validation failed
//                DB::rollback();
//
//                \Log::error('bKash Payment Validation Failed', [
//                    'response' => $response,
//                    'paymentID' => $paymentID
//                ]);
//
//                return redirect()->route('booking.confirmation', $booking->code)
//                    ->with('error', 'Payment validation failed!');
//            }
//
//    }

//    public function success(Request $request)
//    {
//
//        try {
//            $response = Bkash::exicutepay($request);
////            return $response;
//            $response = json_decode(json_encode($response), true);
//
//            // Check status code (now use array notation)
//            if (isset($response['statusCode']) && $response['statusCode'] === '0000') {
//
//                // Extract response data
//
//                $paymentData = [
//                    'payment_id' => $response['paymentID'] ?? null,
//                    'trx_id' => $response['trxID'] ?? null,
//                    'amount' => $response['amount'] ?? 0,
//                    'invoice' => $response['merchantInvoiceNumber'] ?? null,
//                    'customer_mobile' => $response['customerMsisdn'] ?? null,
//                    'status' => $response['transactionStatus'] ?? 'Unknown'
//                ];
//
//                // Save to database
//                // Your database save logic here
//                $booking = Booking::where('code', $paymentData['invoice'])->first();
//                $payment = $booking->payment;
//                // Check if payment exists
//                if (!$payment) {
//                    // Payment record nai, notun create kora lagbe
//                    $payment = new Payment();
//                }
//                if ($payment) {
//                    $payment->code = $paymentData['invoice'];
//                    $payment->booking_id=$booking->id;
//                    $payment->payment_gateway='bkash';
//                    $payment->amount = $paymentData['amount'];
//                    $payment->currency='BDT';
//                    $payment->status = 'completed';
//                    if (auth()->user()){
//                        $payment->create_user = auth()->user()->id;
//                    }
//
//                    $payment->save();
//                }
//                try{
////                    $oldPaynow = (float)$booking->pay_now;
//                    $booking->paid += (float)$booking->pay_now;
////                    $booking->pay_now = (float)($oldPaynow - $data['originalAmount'] < 0 ? 0 : $oldPaynow - $data['originalAmount']);
//                    $booking->markAsPaid();
//
//                } catch(\Swift_TransportException $e){
//                    Log::warning($e->getMessage());
//                }
//                return redirect($booking->getDetailUrl())->with("success", __("You payment has been processed successfully"));
//
//
//
//            } else {
//
//                return redirect()->back()
//                    ->with('error', 'Payment failed: ' . ($response['statusMessage'] ?? 'Unknown error'));
//
//            }
//
//        } catch (\Exception $e) {
//            // Log the error
//            \Log::error('bKash Payment Error: ' . $e->getMessage());
//
//            return redirect()->route('payment.failed')
//                ->with('error', 'Payment processing failed. Please try again.');
//        }
//    }

    public function failed()
    {
        return redirect()->back()
            ->with('error','unsuccessfull');
    }
}
