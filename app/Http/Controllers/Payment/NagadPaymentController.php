<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xenon\NagadApi\Base;

class NagadPaymentController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->config = config('nagad_payment');
    }

    /**
     * Create Payment
     */
    public function create(Request $request,$booking)
    {
//        return $booking;
        try {
//            $booking = Booking::findOrFail($request->booking_id);

            // Create Nagad payment instance
            $callbackURL='http://127.0.0.1:8000/nagad/callback';
            $nagad = new Base($this->config, [
                'amount' => $booking->total,
                'invoice' => $booking->code, // Use booking code as invoice
                'merchantCallback' => $callbackURL,
            ]);

            // Auto redirect
          $respon=  $nagad->payNow($nagad);
            return $respon;
          dd($respon);
          return $respon;
            // Payment URL get koren (without auto redirect)
// Payment URL get without redirect
            $paymentUrl = $nagad->payNowWithoutRedirection($nagad);

            // Debug - URL dekhun
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'config' => $this->config,
                'params' => [
                    'amount' => $booking->total,
                    'invoice' => $booking->code,
                    'callback' => route('nagad.callback')
                ]
            ]);
            // Get payment URL without auto redirect
            $paymentUrl = $nagad->payNowWithoutRedirection($nagad);

            // Redirect to Nagad payment page
            return redirect($paymentUrl);

        } catch (\Exception $e) {
            Log::error('Nagad Payment Create Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Payment initialization failed. Please try again.');
        }
    }

    /**
     * Payment Callback/Success
     */
    public function callback(Request $request)
    {
        try {
            // Get success response from URL parameters
            $successUrl = $request->fullUrl();
            $response = Helper::successResponse($successUrl);

            // Verify payment
            $helper = new Helper($this->config);
            $verifyResponse = $helper->verifyPayment($response['payment_ref_id']);

            // Convert to array if object
            if (is_object($verifyResponse)) {
                $verifyResponse = json_decode(json_encode($verifyResponse), true);
            }

            // Check if payment successful
            if (isset($verifyResponse['status']) && $verifyResponse['status'] === 'Success') {

                // Find booking
                $booking = Booking::where('code', $verifyResponse['orderId'])->first();

                if (!$booking) {
                    return redirect()->back()->with('error', 'Booking not found');
                }

                // Create or update payment
                $payment = Payment::updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'code' => $verifyResponse['orderId'],
                        'payment_gateway' => 'nagad',
                        'amount' => $verifyResponse['amount'],
                        'currency' => 'BDT',
                        'status' => 'completed',
                        'transaction_id' => $verifyResponse['paymentRefId'],
                        'create_user' => auth()->id() ?? null,
                    ]
                );

                // Update booking
                try {
                    $booking->paid += (float)$verifyResponse['amount'];
                    $booking->markAsPaid();
                } catch (\Exception $e) {
                    Log::warning('Booking update error: ' . $e->getMessage());
                }

                return redirect($booking->getDetailUrl())
                    ->with('success', __('Your payment has been processed successfully'));

            } else {
                // Payment failed
                return redirect()->back()
                    ->with('error', 'Payment verification failed: ' . ($verifyResponse['status'] ?? 'Unknown'));
            }

        } catch (\Exception $e) {
            Log::error('Nagad Payment Callback Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Payment processing failed. Please contact support.');
        }
    }

    /**
     * Payment Cancel
     */
    public function cancel(Request $request)
    {
        return redirect()->back()
            ->with('error', 'Payment was cancelled');
    }
}
