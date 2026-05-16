<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Mockery\Exception;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;
use Modules\User\Models\Wallet\Transaction;

class SslCommerzPaymentController extends Controller
{

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request, $booking, $totalAmount)
    {
//        return $request;
//        if (in_array($booking->status, [
//            $booking::PAID,
//            $booking::COMPLETED,
//            $booking::CANCELLED
//        ])) {
//
//            throw new Exception(__("Booking status does need to be paid"));
//        }
        if (!$totalAmount) {
            throw new Exception(__("Booking total is zero. Can not process payment gateway!"));
        }

        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] =  (float)$totalAmount; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = $booking->code ; // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $booking->first_name . ' ' . $booking->last_name;
        $post_data['cus_email'] = $booking->email;
        $post_data['cus_add1'] = 'Dhaka';
        $post_data['cus_add2'] = $request->input('address_line_2');
        $post_data['cus_city'] = 'Dhaka';
        $post_data['cus_state'] = 'Dhaka';
        $post_data['cus_postcode'] = 1000;
        $post_data['cus_country'] = 'Bangladesh';
        $post_data['cus_phone'] = $booking->phone ;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = $request->input('address_line_1');
        $post_data['ship_add2'] = $request->input('address_line_2');
        $post_data['ship_city'] = $request->input('city');
        $post_data['ship_state'] = $request->input('state');
        $post_data['ship_postcode'] = $request->input('zip_code');
        $post_data['ship_phone'] =  $request->input('phone');
        $post_data['ship_country'] = $request->input('country');

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";




//        $payment = new Payment();
//        $payment->booking_id = $booking->id;
//        $payment->payment_gateway = "ssl";
//        $payment->status = 'processing';
//        return 'sarowar';
//        return $post_data;
        $sslc = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'hosted');
//return $payment_options;
        // Check if redirect URL is returned
        if (isset($payment_options['GatewayPageURL'])) {
            return redirect($payment_options['GatewayPageURL']);
        } else {
            // Handle error
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initialization failed',
                ]);
            }
            // Handle error
            return back()->with('error', 'Payment initialization failed');
        }
    }

    public function payViaAjax(Request $request)
    {


        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
         $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }


    public function success(Request $request)
    {
//        dd('sarowar');
//        \Log::info('SSL Success Callback', $request->all());
//        dd($request->all()); // ← সাময়িকভাবে এটা দিন
////        dd($request);
////        return $request;
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();
        $booking = Booking::where('code', $tran_id)->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found!');
        }

        DB::beginTransaction();
        try {
            // SSL validation
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

            if ($validation) {

                // Re-authenticate user if needed
                if (!Auth::check() && $booking->customer_id) {
                    Auth::loginUsingId($booking->customer_id);
                }
                // Create Transaction record
                Transaction::create([
                    'user_id' => $booking->customer_id,
                    'booking_id' => $booking->id,
                    'ref_id' => $booking->id,
                    'type' => 'credit',
                    'transaction_type' => 'booking_payment',
                    'amount' => $amount,
                    'status' => 'confirmed',
                    'reference' => 'SSL Payment - ' . $booking->code,
                    'remarks' => 'Payment completed via SSLCommerz for booking ' . $booking->code,
                    'meta' => json_encode([
                        'tran_id' => $tran_id,
                        'currency' => $currency,
                        'booking_code' => $booking->code,
                        'payment_method' => 'ssl',
                    ]),
                    'create_user' => $booking->customer_id,
                    'created_at' => now(),
                    'deposit_date' => now(),
                ]);


                // Update Booking
                $booking->update([
                    'gateway' => 'ssl',
                    'payment_method' => 'ssl',
                    'paid' => $amount,
                    'is_paid' => 1,
                    'pay_now'  => 0,
                    'paid_at' => now(),
                ]);

                DB::commit();

                // Clear session and redirect
//                Session::forget(['selected_flight', 'search_params', 'reissue_data']);

                return redirect()->route('booking.details', $booking->id)
                    ->with('success', 'Payment completed successfully!');

            } else {
                // Validation failed
                DB::rollback();

                return redirect()->route('booking.details', $booking->id)
                    ->with('error', 'Payment validation failed!');
            }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('SSL payment failed: ' . $e->getMessage());

            return redirect()->route('booking.details', $booking->id)
                ->with('error', 'Payment processing failed!');
        }
    }


//    public function success(Request $request)
//    {
//        $tran_id = $request->input('tran_id');
//        $amount = $request->input('amount');
//        $currency = $request->input('currency');
//        $booking = Booking::where('code', $tran_id)->first();
//
//
//
//        $sslc = new SslCommerzNotification();
//
//
//
//        if (!empty($booking) and in_array($booking->status, [$booking::UNPAID])) {
//            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);
//
//            if ($validation) {
////                if ($request->status == "VALID"){
//                $payment = $booking->payment;
//                // Check if payment exists
//                if (!$payment) {
//                    // Payment record nai, notun create kora lagbe
//                    $payment = new Payment();
//                }
//                if ($payment) {
//                    $payment->code = $tran_id;
//                    $payment->booking_id=$booking->id;
//                    $payment->payment_gateway='ssl';
//                    $payment->amount = $amount;
//                    $payment->currency='BDT';
//                    $payment->status = 'completed';
//                    if (auth()->user()){
//                        $payment->create_user = auth()->user()->id;
//                    }
//
//                    $payment->save();
//                }
//                    try{
////                    $oldPaynow = (float)$booking->pay_now;
//                        $booking->paid += (float)$booking->pay_now;
////                    $booking->pay_now = (float)($oldPaynow - $data['originalAmount'] < 0 ? 0 : $oldPaynow - $data['originalAmount']);
//                        $booking->markAsPaid();
//
//                    } catch(\Swift_TransportException $e){
//                        Log::warning($e->getMessage());
//                    }
//                    return redirect($booking->getDetailUrl())->with("success", __("You payment has been processed successfully"));
//
////                }
//
//                echo "<br >Transaction is successfully Completed";
//            }
//        } else {
//
//            $payment = $booking->payment;
//            if ($payment) {
//                $payment->status = 'fail';
////                $payment->logs = \GuzzleHttp\json_encode($response->getData());
//                $payment->save();
//            }
//            try{
//                $booking->markAsPaymentFailed();
//
//            } catch(\Swift_TransportException $e){
//                Log::warning($e->getMessage());
//            }
//            return redirect($booking->getDetailUrl())->with("error", __("Payment Failed"));
//        }
//    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Failed']);
            echo "Transaction is Falied";
        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Canceled']);
            echo "Transaction is Cancel";
        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }


    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                }
            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
