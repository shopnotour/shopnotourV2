<?php

namespace Modules\Booking\Controllers;

use App\Http\Controllers\SslCommerzPaymentController;
use App\Service\SabreApiService;
use App\User;
use http\Env\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\EnquirySendEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingReissue;
use Modules\Booking\Models\BookingRoute;
use Modules\Booking\Service\AirArabia\AirArabiaBookingService;
use Modules\Booking\Service\TravelPort\TravelPortCancelService;
use Modules\Booking\Service\TravelPortBookingXmlService;
use Modules\Flight\Models\Airline;
use Modules\Flight\Models\Airport;
use Modules\Flight\Service\Sabre\SabreApiServices;
use Modules\Flight\Service\Sabre\SabreBookingService ;
use Modules\Flight\Service\Sabre\Sabrerevalidationservice;
use Modules\Flight\Service\TravelPort\TravelPortApiService;
use Modules\Media\Models\MediaFile;
use Modules\User\Events\SendMailUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Enquiry;
use App\Helpers\ReCaptchaEngine;
use Modules\User\Models\Wallet\Transaction;
use App\Http\Controllers\PaymentController;
use Carbon\Carbon ;


class BookingController extends \App\Http\Controllers\Controller
{
    use AuthorizesRequests;

    protected $booking;
    protected $BookingPassenger;
    protected $enquiryClass;
    protected $bookingInst;
    protected $BookingRoute;
    protected $SabreBookingService;
    private $revalidationService;
    private $sabreApiServices;


    public function __construct(Booking $booking, Enquiry $enquiryClass, BookingPassenger $BookingPassenger, BookingRoute $BookingRoute, SabreBookingService $SabreBookingService, SabreRevalidationService $revalidationService,SabreApiServices $sabreApiServices)
    {
        $this->booking = $booking;
        $this->enquiryClass = $enquiryClass;
        $this->BookingPassenger = $BookingPassenger;
        $this->BookingRoute = $BookingRoute;
        $this->SabreBookingService = $SabreBookingService;
        $this->revalidationService = $revalidationService;
        $this->sabreApiServices = $sabreApiServices;
    }


    public function revalidate(Booking $booking)
    {
//        return $booking;
        $result = $this->revalidationService->revalidate($booking);
return $result;
        return response()->json($result);
    }


    protected function validateCheckout($code)
    {

        if (!is_enable_guest_checkout() and !Auth::check()) {
            $error = __("You have to login in to do this");
            if (\request()->isJson()) {
                return $this->sendError($error)->setStatusCode(401);
            }
            return redirect(route('login', ['redirect' => \request()->fullUrl()]))->with('error', $error);
        }

        $booking = $this->booking::where('code', $code)->first();

        $this->bookingInst = $booking;

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        return true;
    }

    public function checkout($code)
    {
        $res = $this->validateCheckout($code);
        if ($res !== true) return $res;

        $booking = $this->bookingInst;

        // Get service (Flight model)
        $service = $booking->service;

        // ✅ Dynamically inject airline based on booking's airline code
        if ($service && $booking->airline) {
            $airline = \Modules\Flight\Models\Airline::where('designator', $booking->airline)->first();
//            if ($airline) {
//                // Add airline dynamically to service object
//                $service->setRelation('airline', $airline);
//            }
        }
//        $airline = Airline::find(1);

//        $imageUrl = $airline->airlineImage ?? null;
//return $imageUrl;

        // For flight bookings, use BookingPassengers model (Flight module), otherwise use BookingPassenger
        if ($booking->object_model === 'flight') {
            $booking['pessengers'] = \Modules\Flight\Models\BookingPassengers::where('booking_id', $booking->id)->get();
        } else {
            $booking['pessengers'] = $this->BookingPassenger::where('booking_id', $booking->id)->get();
        }
        $booking['routes'] = $this->BookingRoute::where('booking_id', $booking->id)->get();

        if (!in_array($booking->status, ['draft', 'unpaid'])) {
            return redirect('/');
        }

        $is_api = request()->segment(1) == 'api';

        $data = [
            'page_title' => __('Checkout'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'airline'    => $airline,
            'gateways' => get_available_gateways(),
            'user'       => auth()->user(),
            'is_api'     => $is_api
        ];

//                return $data;
        return view('Booking::frontend/checkout', $data);
    }

    public function checkStatusCheckout($code)
    {
        $booking = $this->booking::where('code', $code)->first();
        $data = [
            'error'    => false,
            'message'  => '',
            'redirect' => ''
        ];
        if (empty($booking)) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        if (!in_array($booking->status, ['draft', 'unpaid'])) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        return response()->json($data, 200);
    }

    protected function validateDoCheckout()
    {

        $request = \request();
        if (!is_enable_guest_checkout() and !Auth::check()) {
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        if (auth()->user() && !auth()->user()->hasVerifiedEmail() && setting_item('enable_verify_email_register_user') == 1) {
            return $this->sendError(__("You have to verify email first"), ['url' => url('/email/verify')]);
        }
        /**
         * @param Booking $booking
         */
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $code = $request->input('code');

        $booking = $this->booking::where('code', $code)->first();
        $this->bookingInst = $booking;

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        return true;
    }

//    ===============================================================
//    private function generateBookingCode()
//    {
//        do {
//            $code = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
//        } while (Booking::where('code', $code)->exists());
//
//        return $code;
//    }

    /**
     * Get passenger type code for API (Sabre format)
     */

    private function getPassengerTypeCode(string $dob, string $departureDate): string
    {
        $birthDate = new \DateTime($dob);
        $departure = new \DateTime($departureDate);
        $age = $departure->diff($birthDate)->y; // departure date এ বয়স কত হবে

        if ($age < 2) {
            return 'INF'; // Infant: 0-1 বছর
        } elseif ($age >= 2 && $age <= 4) {
            return 'C03'; // Child: 2-4 বছর
        } elseif ($age >= 5 && $age <= 11) {
            return 'C07'; // Child: 5-11 বছর
        } else {
            return 'ADT'; // Adult: 12+ বছর
        }
    }
//    private function getPassengerTypeCode($type)
//    {
//        $codes = [
//            'adult' => 'ADT',
//            'child' => 'CNN', // Child (2-11 years)
//            'infant' => 'INF', // Infant (under 2)
//        ];
//
//        return $codes[$type] ?? 'ADT';
//    }

    /**
     * Get passenger price from flight data
     */
    private function getPassengerPrice($flightData, $passengerType)
    {
        // If flight data has per-passenger pricing
        if (isset($flightData['pricing_per_passenger'][$passengerType])) {
            return $flightData['pricing_per_passenger'][$passengerType]['total'];
        }

        // Otherwise calculate proportionally
        $total = $flightData['price']['total'];
        $adults = $flightData['search_params']['adults'] ?? 1;
        $children = $flightData['search_params']['children'] ?? 0;
        $infants = $flightData['search_params']['infants'] ?? 0;

        // Simple proportional calculation (adults=100%, children=75%, infants=10%)
        $weights = [
            'adult' => 1.0,
            'child' => 0.75,
            'infant' => 0.10,
        ];

        $totalWeight = ($adults * 1.0) + ($children * 0.75) + ($infants * 0.10);
        $perUnit = $total / $totalWeight;

        return round($perUnit * $weights[$passengerType], 2);
    }

    /**
     * Get passenger base price (without tax)
     */
    private function getPassengerBasePrice($flightData, $passengerType)
    {
        if (isset($flightData['pricing_per_passenger'][$passengerType])) {
            return $flightData['pricing_per_passenger'][$passengerType]['base_fare'];
        }

        $baseFare = $flightData['price']['api_base_fare'];
        $adults = $flightData['search_params']['adults'] ?? 1;
        $children = $flightData['search_params']['children'] ?? 0;
        $infants = $flightData['search_params']['infants'] ?? 0;

        $weights = [
            'adult' => 1.0,
            'child' => 0.75,
            'infant' => 0.10,
        ];

        $totalWeight = ($adults * 1.0) + ($children * 0.75) + ($infants * 0.10);
        $perUnit = $baseFare / $totalWeight;

        return round($perUnit * $weights[$passengerType], 2);
    }

    /**
     * Get baggage allowance for passenger
     */
    private function getPassengerBaggage($flightData, $passengerType)
    {
        // Check if flight data has baggage info
        if (isset($flightData['legs'][0]['baggage'][$passengerType])) {
            $baggage = $flightData['legs'][0]['baggage'][$passengerType];

            // Extract number from string like "2PC" or "30KG"
            if (preg_match('/(\d+)/', $baggage, $matches)) {
                return (int)$matches[1];
            }
        }

        // Default baggage allowance
        $defaults = [
            'adult' => 2, // 2 pieces
            'child' => 2,
            'infant' => 1,
        ];

        return $defaults[$passengerType] ?? 2;
    }

    /**
     * Payment gateway callback (for online payments)
     */
    public function paymentCallback(Request $request)
    {
        // Get booking
        $booking = Booking::where('code', $request->booking_code)->firstOrFail();

        DB::beginTransaction();

        try {
            // Verify payment with gateway (implement based on your gateway)
            $paymentVerified = true; // Replace with actual verification

            if ($paymentVerified) {
                // Update booking
                $booking->update([
                    'status' => 'confirmed', // booking status
                    'is_paid' => 1, // payment completed
                    'paid' => $booking->total, // full amount paid
                    'gateway' => $request->gateway ?? 'online',
                    'payment_id' => $request->payment_id ?? null,
                    // Store payment response in vendor_service_fee field temporarily or create new column
                    'customer_notes' => 'Payment completed. Transaction ID: ' . ($request->transaction_id ?? 'N/A'),
                    'confirmed_at' => now(), // NEW COLUMN
                    'paid_at' => now(), // NEW COLUMN
                ]);

                DB::commit();

                // Clear session data after successful payment
                Session::forget(['selected_flight', 'search_params']);

                return redirect()->route('booking.confirmation', $booking->code)
                    ->with('success', 'Payment successful! Your booking is confirmed.');
            } else {
                DB::rollback();

                return redirect()->route('booking.failed', $booking->code)
                    ->with('error', 'Payment verification failed.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payment callback failed: ' . $e->getMessage());

            return redirect()->route('booking.failed', $booking->code)
                ->with('error', 'Payment processing failed.');
        }
    }
    private function getPassengerPricingFromFlightData($flightData, $passengerType)
    {
        $typeMapping = [
            'adult'  => 'ADT',
            'child'  => ['C07', 'C05', 'C03', 'CNN'],
            'infant' => 'INF',
        ];

        $apiType = $typeMapping[$passengerType] ?? 'ADT';

        // ✅ passengers array থেকে fare data
        $passengerFare = null;
        if (isset($flightData['passengers']) && is_array($flightData['passengers'])) {
            foreach ($flightData['passengers'] as $passenger) {
                if (is_array($apiType)) {
                    if (in_array($passenger['type'], $apiType)) {
                        $passengerFare = $passenger;
                        break;
                    }
                } else {
                    if ($passenger['type'] === $apiType) {
                        $passengerFare = $passenger;
                        break;
                    }
                }
            }
        }

        // ✅ passenger_price_breakdown থেকে discount/charge data
        $priceBreakdown = null;
        if (isset($flightData['passenger_price_breakdown']) && is_array($flightData['passenger_price_breakdown'])) {
            foreach ($flightData['passenger_price_breakdown'] as $breakdown) {
                if (is_array($apiType)) {
                    if (in_array($breakdown['passenger_type'], $apiType)) {
                        $priceBreakdown = $breakdown;
                        break;
                    }
                } else {
                    if ($breakdown['passenger_type'] === $apiType) {
                        $priceBreakdown = $breakdown;
                        break;
                    }
                }
            }
        }

        $perPax = $priceBreakdown['per_pax'] ?? [];

        return [
            // API fare data
            'total_fare'       => $passengerFare['total_fare'] ?? 0,
            'base_fare'        => $passengerFare['base_fare'] ?? 0,
            'equivalent_amount'=> $passengerFare['equivalent_amount'] ?? 0,
            'tax_amount'       => $passengerFare['tax_amount'] ?? 0,
            'currency'         => $passengerFare['currency'] ?? 'BDT',
            'refundable'       => $passengerFare['refundable'] ?? false,
            'baggage'          => $passengerFare['baggage'] ?? ['weight' => 0, 'unit' => 'kg'],

            // Price breakdown
            'gross_fare'       => $perPax['gross_fare'] ?? 0,
            'ait_amount'       => $perPax['ait_amount'] ?? 0,
            'service_charge'   => $perPax['service_charge'] ?? 0,
            'user_discount'    => $perPax['user_discount'] ?? 0,
            'user_seg_discount'=> $perPax['user_seg_discount'] ?? 0,
            'user_payable'     => $perPax['user_payable'] ?? 0,
            'own_discount'     => $perPax['own_discount'] ?? 0,
            'own_seg_discount' => $perPax['own_seg_discount'] ?? 0,
            'commission'       => $perPax['commission'] ?? 0,
            'own_cost'         => $perPax['own_cost'] ?? 0,
            'profit'           => $perPax['profit_per_pax'] ?? 0,
        ];
    }
//    ===============================================================


    private function getStartAndEndDates(array $sessionData): array
    {
        $tripType = $sessionData['trip_type'];
        $segments = $sessionData['segments'];

        // Start date সবসময় first segment এর departure
        $startDate = $segments[0]['departure'];

        // End date trip type অনুযায়ী
        $endDate = match($tripType) {
            'oneway' => $startDate, // Same as start date
            'round' => $sessionData['return_date'], // Return date from session
            'multi' => end($segments)['departure'], // Last segment departure
            default => $startDate
        };

        return [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }


// ══════════════════════════════════════════════════════════════
// PRIVATE: Duplicate Booking Check
//
// Return: [ string[] $blocked,  bool $hasWarning ]
//   $blocked    → block হওয়া passenger নামের list (empty = ok)
//   $hasWarning → true হলে 2nd booking warning দেখাও
// ══════════════════════════════════════════════════════════════

    private function checkDuplicateBooking(array $passengers, array $flightData): array
    {
        $departureDateRaw = $flightData['legs'][0]['departure']['date'] ?? null;
        if (!$departureDateRaw) return [[], false];

        $year  = (int) substr($departureDateRaw, 0, 4);
        $month = (int) substr($departureDateRaw, 5, 2);
        $carrierCode = $flightData['legs'][0]['segments'][0]['carrier'] ?? null;

        $blocked    = [];
        $hasWarning = false;

        foreach ($passengers as $p) {
            $passport  = strtoupper(trim($p['passport_number'] ?? ''));
            $firstName = strtoupper(trim($p['first_name']      ?? ''));
            $lastName  = strtoupper(trim($p['last_name']       ?? ''));

            if (!$passport || !$firstName || !$lastName) continue;

            $count = DB::table('bravo_booking_passengers as bp')
                ->join('bravo_bookings as b',       'b.id',          '=', 'bp.booking_id')
                ->join('bravo_booking_routes as br', 'br.booking_id', '=', 'b.id')
                ->whereRaw('UPPER(bp.passport_number) = ?', [$passport])
                ->whereRaw('UPPER(bp.first_name) = ?',      [$firstName])
                ->whereRaw('UPPER(bp.last_name) = ?',        [$lastName])
                ->where('b.object_model',  'flight')
                ->where('br.carrier_code', $carrierCode)
                ->whereRaw('YEAR(br.departure_at) = ?',  [$year])
                ->whereRaw('MONTH(br.departure_at) = ?', [$month])
                ->whereNotIn('b.status', ['cancelled', 'refunded', 'voided'])
                ->distinct()
                ->count('b.id');

            if ($count >= 2) {
                $blocked[] = "{$firstName} {$lastName} (passport: {$passport})";
            } elseif ($count === 1) {
                $hasWarning = true;
            }
        }

        return [$blocked, $hasWarning];
    }

    public function doCheckout(Request $request)
    {
        // 1. Validate
        $request->validate([
            'contact_email'                => 'required|email',
            'contact_phone'                => 'required',
            'passengers'                   => 'required|array',
            'passengers.*.title'           => 'required',
            'passengers.*.gender'          => 'required',
            'passengers.*.first_name'      => 'required|string',
            'passengers.*.last_name'       => 'required|string',
            'passengers.*.dob'             => 'required|date',
            'passengers.*.nationality'     => 'required',
            'passengers.*.passport_number' => 'nullable|string',
            'passengers.*.passport_expiry' => 'nullable|date',
            'passengers.*.type'            => 'required|in:adult,child,infant',
            'payment_method'               => 'required|in:ssl,bkash,wallet,without_payment',
        ]);

        // 2. Session data
        $flightData    = Session::get('selected_flight');
        $priceVerified = session('price_verified');
        $searchParams  = Session::get('flight_search_params');
        $reissueData   = Session::get('reissue_data');

        if (!$flightData) {
                return back()->with('error', 'Flight data not found. Please search again.');
            }

            if (!$searchParams) {
                return back()->with('error', 'Search session expired. Please search again.');
            }
        $dates         = $this->getStartAndEndDates($searchParams);


        // 3. Duplicate booking check ── আলাদা method
        [$blocked, $hasWarning] = $this->checkDuplicateBooking($request->passengers, $flightData);

        if (!empty($blocked)) {
            return back()->with('error',
                'বুকিং সীমা অতিক্রম হয়েছে! ' . implode(', ', $blocked)
                . ' — এই এয়ারলাইনে চলতি মাসে সর্বোচ্চ ২টি বুকিং করা যাবে।'
            );
        }
        if ($hasWarning) {
            session()->flash('warning',
                'একজন বা একাধিক যাত্রীর এই রুটে আগে থেকে বুকিং রয়েছে। '
                . 'এটি তাদের ২য় এবং সর্বশেষ অনুমোদিত বুকিং হবে।'
            );
        }

        // 3. Calculate amounts
        $totalAmount = $flightData['price']['total'] ?? 0;
        $walletAmount = floatval($request->wallet_payment_amount ?? 0);
        $remainingAmount = $totalAmount - $walletAmount;

        // 4. Check wallet balance
        $user = auth()->user();

        // 5. Start database transaction
        DB::beginTransaction();

        try {
            // 6. Create booking
            $booking = Booking::create([
                'code' => $this->generateBookingCode(), // Using existing 'code' column
                'customer_id' => $user->id, // Using existing 'customer_id' column
                'create_user' => $user->id,
                'object_model' => 'flight',
                'source' => $flightData['source'],

                // Contact Information (using existing columns)
                'email' => $request->contact_email,
                'phone' => $request->contact_phone,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'country' => $user->country ?? 'BD',

                // Status (using existing columns)
                'status' => 'pending', // booking_status
                'is_paid' => 0, // payment_status
                'pay_now' => $totalAmount, // payment_status

                // Pricing (using existing columns)
                'base_fee' => $flightData['price']['api_base_fare'] ?? 0,
                'flight_discount' => $flightData['price']['flight_discount'] ?? 0,
                'segment_discount' => $flightData['price']['segment_discount'] ?? 0,
                'total_fee' => $flightData['price']['api_tax'] ?? 0, // Tax amount
                'supplier_fee' => $flightData['charges_details']['ait_amount'] ?? 0, // AIT
                'ticketing_fee' => $flightData['charges_details']['service_charge'] ?? 0,
                'coupon_amount' => $flightData['price']['total_discounts'] ?? 0,
                'total_before_discount' => ($totalAmount + ($flightData['price']['total_discounts'] ?? 0)),
                'total' => $totalAmount, // Total amount

                // Currency
                'currency' => $flightData['price']['currency'] ?? 'BDT',


                // Flight Basic Details (using existing columns)
                'flight_type' => count($flightData['legs']) > 1 ? 'round_trip' : 'one_way',
                'is_round_trip' => count($flightData['legs']) > 1, // NEW COLUMN
                'is_refundable' => $flightData['refundable'] ?? false, // NEW COLUMN
                'seat_class' => $flightData['legs'][0]['segments'][0]['cabin_class'] ?? 'Economy',
                'airline' => $flightData['legs'][0]['segments'][0]['carrier_name'] ?? null,

                // Route Information (using existing columns)
                'flight_from' => $flightData['legs'][0]['departure']['airport_code'] ?? null,
                'flight_to' => $flightData['legs'][0]['arrival']['airport_code'] ?? null,
//                'start_date' => $flightData['legs'][0]['departure']['date'] ?? null,
//                'end_date' => $flightData['legs'][0]['arrival']['date'] ?? null,

                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],

                // Passenger Counts (using existing columns)
                'total_guests' => count($request->passengers),
                'adult_count' => $searchParams['adults'] ?? 0,
                'child_count' => $searchParams['children'] ?? 0,
                'infant_count' => $searchParams['infants'] ?? 0,

                // Baggage (using existing columns)
                'adult_bag' => $flightData['legs'][0]['baggage']['adult'] ?? null,
                'child_bag' => $flightData['legs'][0]['baggage']['child'] ?? null,
                'infant_bag' => $flightData['legs'][0]['baggage']['infant'] ?? null,


                // JSON Data (NEW COLUMNS - need to add these)
                'flight_raw_data' => json_encode($flightData), // NEW COLUMN
                'search_params' => json_encode($searchParams), // NEW COLUMN
                'discount_details' => json_encode($flightData['flight_discount_details'] ?? []), // NEW COLUMN
                'charges_details' => json_encode($flightData['charges_details'] ?? []), // NEW COLUMN

                // Dates (NEW COLUMNS)
//                'booking_date' => now(), // NEW COLUMN
                'created_at' => now(),
            ]);

            // 7. Create passengers
            foreach ($request->passengers as $index => $passengerData) {

                $passportMediaId = null;
                $visaMediaId     = null;

                if ($request->hasFile("passengers.{$index}.passport_image")) {
                    $passportMediaId = $this->saveToMedia(
                        $request->file("passengers.{$index}.passport_image"),
                        'passport'
                    );
                }

                if ($request->hasFile("passengers.{$index}.visa_image")) {
                    $visaMediaId = $this->saveToMedia(
                        $request->file("passengers.{$index}.visa_image"),
                        'visa'
                    );
                }
                // Get pricing for this passenger type from flight data
                $passengerPricing = $this->getPassengerPricingFromFlightData($flightData, $passengerData['type']);

                BookingPassenger::create([
                    'booking_id' => $booking->id,

                    // Passenger Type
                    'traveler_type' => $passengerData['type'], // adult, child, infant
                    'title' => $passengerData['title'], // Mr, Mrs, Ms, Master, Miss
                    'seat_type' => $passengerData['type'], // Using existing seat_type column
                    'passenger_type_code' => $this->getPassengerTypeCode($passengerData['dob'],$dates['start_date']), // ADT, CNN, INF

                    // Personal Information
                    'first_name' => $passengerData['first_name'],
                    'last_name' => $passengerData['last_name'],
                    'email' => $request->contact_email, // Use booking contact email
                    'phone' => $request->contact_phone, // Use booking contact phone
                    'gender' => $passengerData['gender'],
                    'dob' => $passengerData['dob'],

                    // Passport/Document
                    'passport_number' => $passengerData['passport_number'],
                    'passport_expiry_date' => $passengerData['passport_expiry'],
                    'document_type' => 'passport', // Assuming passport
                    'country' => $passengerData['nationality'],
                    'issuing_location' => $passengerData['nationality'], // Passport issuing country

                    // Pricing from flight data (per passenger)
                    'total' => $passengerPricing['total_fare'] ?? 0,
                    'base' => $passengerPricing['equivalent_amount'] ?? 0, // Base fare in BDT
                    'price' => $passengerPricing['total_fare'] ?? 0,
                    'tax' => $passengerPricing['tax_amount'] ?? 0,
                    'currency' => $passengerPricing['currency'] ?? 'BDT',

                    'gross_fare'        => $passengerPricing['gross_fare'] ?? 0,
                    'ait_amount'        => $passengerPricing['ait_amount'] ?? 0,
                    'service_charge'    => $passengerPricing['service_charge'] ?? 0,
                    'user_discount'     => $passengerPricing['user_discount'] ?? 0,
                    'user_seg_discount' => $passengerPricing['user_seg_discount'] ?? 0,
                    'user_payable'      => $passengerPricing['user_payable'] ?? 0,
                    'own_discount'      => $passengerPricing['own_discount'] ?? 0,
                    'own_seg_discount'  => $passengerPricing['own_seg_discount'] ?? 0,
                    'commission'        => $passengerPricing['commission'] ?? 0,
                    'own_cost'          => $passengerPricing['own_cost'] ?? 0,
                    'profit'            => $passengerPricing['profit'] ?? 0,

                    // Baggage from flight data
                    'included_checked_bags' => $passengerPricing['baggage']['weight'] ?? 0,
                    'included_checked_bags_unit' => strtoupper($passengerPricing['baggage']['unit'] ?? 'KG'),

                    // Cabin and Fare (from first segment)
                    'cabin' => $flightData['legs'][0]['segments'][0]['fare_info']['cabin_name'] ?? 'Economy',
                    'class' => $flightData['legs'][0]['segments'][0]['fare_info']['booking_code'] ?? 'Y',
                    'fare_basis' => $flightData['legs'][0]['segments'][0]['fare_info']['fare_basis_code'] ?? null,
                    'fare_option' => $passengerPricing['refundable'] ? 'Refundable' : 'Non-Refundable',

                    // Status
                    'status' => 'pending', // pending, confirmed, cancelled

                    // Object linking (for multi-model support)
                    'object_model' => 'flight',
//                    'object_id' => $booking->id,
                    'city'                => $passengerData['city'] ?? null,
                    'address'             => $passengerData['address'] ?? null,
                    'passport_media_id'   => $passportMediaId,
                    'visa_media_id'       => $visaMediaId,

                    // Timestamps
                    'create_user' => $user->id,
                    'created_at' => now(),
                ]);
            }

            foreach ($flightData['legs'] as $legIndex => $leg) {
                // Loop through each segment in the leg
                foreach ($leg['segments'] as $segmentIndex => $segment) {
                    BookingRoute::create([
                        'booking_id' => $booking->id,

                        // Departure
                        'departure_iata_code' => $segment['departure']['airport_code'],
                        'departure_terminal' => $segment['departure']['terminal'] ?? null,
                        'departure_at' => $segment['departure']['date'] . ' ' . substr($segment['departure']['time'], 0, 8), // "2026-01-06 23:35:00"

                        // Arrival
                        'arrival_iata_code' => $segment['arrival']['airport_code'],
                        'arrival_terminal' => $segment['arrival']['terminal'] ?? null,
                        'arrival_at' => $segment['arrival']['date'] . ' ' . substr($segment['arrival']['time'], 0, 8), // "2026-01-06 23:59:00"

                        // Flight details
                        'carrier_code' => $segment['carrier'],
                        'aircraft_code' => $segment['aircraft'],
                        'flight_number' => $segment['full_flight_number'],
                        'duration' => $segment['duration_formatted'],

                        // Cabin & Class
                        'cabin' => $segment['fare_info']['cabin_name'] ?? 'Economy',
                        'class' => $segment['fare_info']['booking_code'] ?? 'Y',
                        'fare_basis' => $segment['fare_info']['fare_basis_code'] ?? null,

                        // Meta (segment + leg info)
//                        'meta' => json_encode([
//                            'segment_number' => $segment['segment_number'],
//                            'segment_type' => $segment['segment_type'],
//                            'leg_number' => $leg['leg_number'],
//                            'leg_type' => $leg['leg_type'],
//                            'carrier_name' => $segment['carrier_name'],
//                            'operating_carrier' => $segment['operating_carrier'],
//                            'operating_carrier_name' => $segment['operating_carrier_name'],
//                            'is_codeshare' => $segment['is_codeshare'],
//                            'aircraft_name' => $segment['aircraft_name'],
//                            'meal_code' => $segment['meal_code'],
//                            'meal_description' => $segment['meal_description'],
//                            'seats_available' => $segment['fare_info']['seats_available'] ?? null,
//                            'layover_after' => $segment['layover_after'],
//                        ]),

                        'meta' => json_encode([
                            'leg_type' => $leg['leg_type'], // outbound or return
                            'leg_number' => $leg['leg_number'],
                            'leg_index' => $legIndex + 1,
                            'duration_minutes' => $leg['duration'] ?? 0,
                            'stops' => $leg['stops'] ?? 0,
                            'is_direct' => $leg['is_direct'] ?? true,
                            'total_segments' => $leg['total_segments'] ?? 1,

                            // Full departure details
                            'departure' => [
                                'airport_code' => $leg['departure']['airport_code'],
                                'airport_name' => $leg['departure']['airport_name'] ?? null,
                                'city' => $leg['departure']['city'] ?? null,
                                'country' => $leg['departure']['country'] ?? null,
                                'time_12h' => $leg['departure']['time_12h'] ?? null,
                                'date' => $leg['departure']['date'] ?? null,
                            ],

                            // Full arrival details
                            'arrival' => [
                                'airport_code' => $leg['arrival']['airport_code'],
                                'airport_name' => $leg['arrival']['airport_name'] ?? null,
                                'city' => $leg['arrival']['city'] ?? null,
                                'country' => $leg['arrival']['country'] ?? null,
                                'time_12h' => $leg['arrival']['time_12h'] ?? null,
                                'date' => $leg['arrival']['date'] ?? null,
                                'date_adjustment' => $leg['arrival']['date_adjustment'] ?? 0,
                            ],

                            // Carrier details
                            'carrier' => [
                                'code' => $leg['segments'][0]['carrier'] ?? null,
                                'name' => $leg['segments'][0]['carrier_name'] ?? null,
                                'operating_carrier' => $leg['segments'][0]['operating_carrier'] ?? null,
                                'operating_carrier_name' => $leg['segments'][0]['operating_carrier_name'] ?? null,
                                'is_codeshare' => $leg['segments'][0]['is_codeshare'] ?? false,
                            ],

                            // Aircraft details
                            'aircraft' => [
                                'code' => $leg['segments'][0]['aircraft'] ?? null,
                                'type' => $leg['segments'][0]['aircraft_type'] ?? null,
                                'name' => $leg['segments'][0]['aircraft_name'] ?? null,
                            ],

                            // Stops detail (if any)
                            'stops_detail' => $leg['stops_detail'] ?? [],

                            // All segments
                            'segments' => $leg['segments'] ?? [],
                        ]),

                        'create_user' => $user->id,
                        'created_at' => now(),
                    ]);
                }
            }

            // Update reissue if exists
            if ($reissueData && isset($reissueData['reissue_id'])) {
                BookingReissue::where('id', $reissueData['reissue_id'])
                    ->update([
                        'new_booking_id' => $booking->id,
                        'updated_at' => now()
                    ]);
            }

            // Initialize
            $pnrResult = null;

            try {
                if ($flightData['source'] === 'sabre') {
                    $booking   = Booking::where('id', $booking->id)->first();
                    $pnrResult = $this->SabreBookingService->createPnr($booking);
//return $pnrResult;
                } elseif ($flightData['source'] === 'travelport') {
                    $priceVerified = session('price_verified');

//                    return $priceVerified;
                    $travelportbookingxml = new TravelPortBookingXmlService();
                    $pnrResult= $travelportbookingxml->buildBookingRequestFromId($booking->id, $priceVerified);
//                    return $pnrResult;
                    // session clear
//                    session()->forget('price_verified');
//return $pnrResult;
                }elseif ($flightData['source'] === 'air_arabia'){
                    $airArabiaBooking = new AirArabiaBookingService();
                    $pnrResult        = $airArabiaBooking->createPnr($booking);
                } else {
                    throw new \Exception('Unknown flight source: ' . $flightData['source']);
                }

                // PNR সফল হলে
                if ($pnrResult && isset($pnrResult['success']) && $pnrResult['success']) {
                    // ✅ Service এ already update হয়েছে, এখানে আর দরকার নেই
                    DB::commit();
                    Session::forget(['selected_flight', 'search_params', 'reissue_data']);

                    return redirect()->route('booking.confirmation', $booking->id)
                        ->with('success', 'Booking confirmed!');
                } else {
                    // ✅ Service এ already 'failed' update হয়েছে, শুধু commit করো
                    DB::commit();

                    $rawError = '';
                    if (!empty($pnrResult['errors']) && is_array($pnrResult['errors'])) {
                        $rawError = implode(', ', $pnrResult['errors']);
                    } elseif (!empty($pnrResult['error'])) {
                        $rawError = $pnrResult['error'];
                    } elseif (!empty($pnrResult['message'])) {
                        $rawError = $pnrResult['message'];
                    } else {
                        $rawError = 'PNR creation failed.';
                    }

                    $cleanError = $rawError;
                    if (str_contains($rawError, 'CHECK FLIGHT NUMBER') || str_contains($rawError, 'SYSTEM UNABLE TO PROCESS')) {
                        $cleanError = 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না। অনুগ্রহ করে আবার সার্চ করুন।';
                    } elseif (str_contains($rawError, 'Unable to perform air booking') || str_contains($rawError, 'sold out') || str_contains($rawError, 'no longer available')) {
                        $cleanError = 'ফ্লাইট বুকিং সম্পন্ন হয়নি। সিট নাও থাকতে পারে।';
                    } elseif (str_contains($rawError, 'session nodes are busy') || str_contains($rawError, 'all session nodes')) {
                        $cleanError = 'সার্ভার ব্যস্ত আছে। কিছুক্ষণ পর আবার চেষ্টা করুন।';
                    } elseif (str_contains($rawError, 'NDC Order Error')) {
                        $cleanError = 'NDC বুকিং সম্পন্ন হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।';
                    }

                    \Log::error('PNR creation failed: ' . $rawError, [
                        'booking_id' => $booking->id,
                        'source'     => $flightData['source'] ?? 'unknown',
                    ]);

                    return redirect()->back()->with('error', $cleanError);
                }



//                return redirect()->back()
//                    ->with('error', 'PNR creation failed. Please try again.');

            } catch (\Exception $e) {
                DB::rollBack();
                $booking->update(['status' => 'pnr_pending']);

                $rawError = $e->getMessage();
                $cleanError = 'কিছু একটা সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';

                if (str_contains($rawError, 'CHECK FLIGHT NUMBER')) {
                    $cleanError = 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না।';
                } elseif (str_contains($rawError, 'session nodes are busy')) {
                    $cleanError = 'সার্ভার ব্যস্ত আছে। কিছুক্ষণ পর আবার চেষ্টা করুন।';
                }

                \Log::error('PNR creation exception: ' . $rawError, [
                    'booking_id' => $booking->id,
                    'source'     => $flightData['source'] ?? 'unknown',
                ]);

                return redirect()->back()->with('error', $cleanError);
            }
//            return redirect()->back();

            // 9. Process wallet payment if applicable
             DB::commit();
            $paymentMethod = $request->payment_method; // wallet, without_payment, bkash, ssl, nagad, etc.
            if ($paymentMethod === 'wallet' || $paymentMethod === 'wallet_only') {
                DB::beginTransaction(); // Start transaction
                try {
                    // Check if user has sufficient balance
                    if ($user->credit_balance < $walletAmount) {
                        return back()->with('error', 'Insufficient wallet balance!');
                    }

                    // Deduct from wallet
                    $user->decrement('credit_balance', $walletAmount);

                    // Create wallet transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'booking_id' => $booking->id,
                        'ref_id' => $booking->id,
                        'type' => 'debit',
                        'transaction_type' => 'booking_payment',
                        'amount' => $walletAmount,
                        'status' => 'payment',
                        'reference' => 'Flight booking - ' . $booking->code,
                        'remarks' => 'Payment for flight booking ' . $booking->code,
                        'meta' => json_encode([
                            'balance_before' => $user->credit_balance + $walletAmount,
                            'balance_after' => $user->credit_balance,
                            'booking_code' => $booking->code,
                            'payment_method' => 'wallet',
                        ]),
                        'create_user' => $user->id,
                        'created_at' => now(),
                        'deposit_date' => now(),
                    ]);

                    // Update booking as paid
                    $booking->update([
                        'gateway' => 'wallet',
                        'payment_method' => $paymentMethod,
                        'wallet_credit_used' => $walletAmount,
                        'wallet_amount' => $walletAmount,
                        'online_amount' => $remainingAmount,
                        'paid' => $walletAmount,
                        'pay_now' => $remainingAmount,
                        'is_paid' => $remainingAmount > 0 ? 0 : 1,
                        'paid_at' => now(),
                    ]);

                    DB::commit(); // Commit transaction
                    // Clear session and redirect
                    Session::forget(['selected_flight', 'search_params', 'reissue_data']);
                    return redirect()->route('booking.confirmation', $booking->code)
                        ->with('success', 'Booking confirmed! Payment completed via wallet.');

                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Wallet payment failed: ' . $e->getMessage());
                    return back()->with('error', 'Payment failed. Please try again.');
                }
            }elseif ($paymentMethod === 'without_payment') {
                $booking->update([
                    'paid' => 0,
                    'wallet_credit_used' => 0,
                    'is_paid' => 0,
//                    'status' => 'pending',
                    'pay_now' => $totalAmount,
                    'gateway' => 'unpaid',
                ]);

                // Clear session and redirect
                Session::forget(['selected_flight', 'search_params', 'reissue_data']);
                return redirect()->route('booking.confirmation', $booking->code)
                    ->with('info', 'Booking confirmed without payment.');

            }elseif ($paymentMethod == 'ssl'){
                // SSL gateway redirect (no transaction needed here)
                $gatewayssl = new SslCommerzPaymentController();
                return $gatewayssl->index($request, $booking, $totalAmount);

            }elseif ($paymentMethod == 'bkash'){
                $bkashGateway = new PaymentController();
                return $bkashGateway->createPayment($request,$booking);

            }else {
                // ========== INVALID PAYMENT METHOD ==========
                DB::rollback();
                return back()->with('error', 'Invalid payment method selected!');
            }

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Log error
            \Log::error('Booking creation failed: ' . $e->getMessage());

            return back()->with('error', 'Booking failed. Please try again. Error: ' . $e->getMessage());
        }
    }

    private function saveToMedia(\Illuminate\Http\UploadedFile $file, string $collection): ?int
    {
        $extension  = $file->getClientOriginalExtension();
        $fileName   = time() . '_' . uniqid() . '.' . $extension;
        $folderPath = "passengers/{$collection}";
        $path       = $file->storeAs($folderPath, $fileName, 'uploads');

        $media = MediaFile::create([
            'file_name'      => $file->getClientOriginalName(),
            'file_path'      => $path,
            'file_size'      => $file->getSize(),
            'file_type'      => $file->getMimeType(),
            'file_extension' => $extension,
            'driver'         => 'uploads',
            'is_private'     => 0,
        ]);

        return $media->id;
    }

    /**
     * Generate unique booking code
     */
    private function generateBookingCode()
    {
        do {
            $code = 'BK' . strtoupper(uniqid());
        } while (Booking::where('code', $code)->exists());

        return $code;
    }

    protected function savePassengers(Booking $booking, Request $request)
    {

        //echo '<pre>'; print_r($booking); die();
        /*if ($booking->service && is_callable([$booking->service, 'savePassengers'])) {
            call_user_func([$booking->service, 'savePassengers'], $booking, $request);
            //die('alimulinside');
            return;
        }*/
        //if ($totalPassenger = $booking->calTotalPassenger()) {
        if ($totalPassenger = $booking->total_guests) {
            //$booking->passengers()->delete();
            $input = $request->input('passengers', []);

            // For flight bookings, use BookingPassengers model (Flight module), otherwise use BookingPassenger
            $isFlight = $booking->object_model === 'flight';
            $passengerModel = $isFlight ? \Modules\Flight\Models\BookingPassengers::class : BookingPassenger::class;

            // Get existing passengers for this booking
            $existingPassengers = $passengerModel::where('booking_id', $booking->id)->get()->keyBy('id');

            // Handle both 0-based and 1-based indexing
            $passengerIndexes = [];
            foreach ($input as $key => $value) {
                if (is_numeric($key)) {
                    $passengerIndexes[] = $key;
                }
            }

            // If no numeric keys found, try 1-based indexing
            if (empty($passengerIndexes)) {
                for ($i = 1; $i <= $totalPassenger; $i++) {
                    $passengerIndexes[] = $i;
                }
            } else {
                // Sort to process in order
                sort($passengerIndexes);
            }

            foreach ($passengerIndexes as $idx => $i) {
                if (!isset($input[$i])) {
                    continue;
                }

                $pessenger_id = $input[$i]['id'] ?? '';

                // Try to find existing passenger if no ID provided
                if (empty($pessenger_id) && $existingPassengers->count() > $idx) {
                    $existingPassenger = $existingPassengers->values()[$idx] ?? null;
                    if ($existingPassenger) {
                        $pessenger_id = $existingPassenger->id;
                    }
                }

                // Base data array
                $data = [
                    'booking_id' => $booking->id,
                    'seat_type' => $booking->seat_class ?? '',
                    'first_name' => $input[$i]['first_name'] ?? '',
                    'last_name'  => $input[$i]['last_name'] ?? '',
                    //'email'      => $input[$i]['email'] ?? '',\
                    'gender'     => $input[$i]['gender'] ?? '',
                    'passport_number' => $input[$i]['passport_number'] ?? '',
                    'passport_expiry_date' => $input[$i]['passport_expiry_date'] ?? '',
                    'phone'      => $input[$i]['phone'] ?? '',
                    'dob'         => $input[$i]['dob'] ?? '',
                ];

                // Try to add country and zip_code directly (will be ignored if columns don't exist)
                if ($isFlight) {
                    $data['country'] = $input[$i]['country'] ?? '';
                    $data['zip_code'] = $input[$i]['zip_code'] ?? '';
                }

                // Initialize meta array
                $meta = [];

                // Get existing meta data if updating existing passenger
                if ($pessenger_id && isset($existingPassengers[$pessenger_id])) {
                    $existingPassenger = $existingPassengers[$pessenger_id];
                    if (!empty($existingPassenger->meta)) {
                        $existingMeta = is_array($existingPassenger->meta) ? $existingPassenger->meta : json_decode($existingPassenger->meta, true);
                        if (is_array($existingMeta)) {
                            $meta = $existingMeta;
                        }
                    }
                }

                // Store country and zip_code in meta as well (backup in case columns don't exist)
                $country = $input[$i]['country'] ?? '';
                $zipCode = $input[$i]['zip_code'] ?? '';
                if ($country !== '') {
                    $meta['country'] = $country;
                }
                if ($zipCode !== '') {
                    $meta['zip_code'] = $zipCode;
                }

                // Add gender to meta if provided (always update, even if empty to clear it)
                $gender = $input[$i]['gender'] ?? '';
                if ($gender !== '') {
                    $meta['gender'] = $gender;
                }

                // Add passport fields to meta (always update, even if empty to clear them)
                $passportNumber = $input[$i]['passport_number'] ?? '';
                $passportExpiryDate = $input[$i]['passport_expiry_date'] ?? '';

                if ($passportNumber !== '') {
                    $meta['passport_number'] = $passportNumber;
                }
                if ($passportExpiryDate !== '') {
                    $meta['passport_expiry_date'] = $passportExpiryDate;
                }

                // Always include meta in data (even if empty array)
                $data['meta'] = $meta;

                // Store DOB before filtering (in case filterPassengerData removes it)
                $dobValue = $data['dob'] ?? '';

                // Allow service to filter/modify passenger data
                $data = $booking->service->filterPassengerData($data, $booking, $request, $i);

                // Ensure meta is still in data after filtering
                if (!isset($data['meta'])) {
                    $data['meta'] = $meta;
                }

                // Ensure DOB is still in data after filtering
                if (empty($data['dob']) && !empty($dobValue)) {
                    $data['dob'] = $dobValue;
                }

                // Format DOB as date if it's a string
                if (!empty($data['dob']) && is_string($data['dob'])) {
                    try {
                        // Try to parse the date - handle common formats
                        $dobDate = \Carbon\Carbon::parse($data['dob']);
                        $data['dob'] = $dobDate->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        // If parsing fails, keep the original value
                    }
                }

                if ($pessenger_id) {
                    // Update existing passenger
                    $passenger = $passengerModel::find($pessenger_id);
                    if ($passenger) {
                        $passenger->fillByAttr(array_keys($data), $data);
                        $passenger->save();
                    }
                } else {
                    // Create new passenger
                    $passenger = new $passengerModel();
                    $passenger->fillByAttr(array_keys($data), $data);
                    $passenger->save();
                }
            }
        }
    }

    public function confirmPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->confirmPayment($request);
    }

    public function callbackPayment(Request $request, $gateway)
    {
        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        if (!empty($request->input('is_normal'))) {
            return $gatewayObj->callbackNormalPayment();
        }
        return $gatewayObj->callbackPayment($request);
    }

    public function cancelPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->cancelPayment($request);
    }

    /**
     * @param Request $request
     * @return string json
     * @todo Handle Add To Cart Validate
     *
     */
    public function addToCart(Request $request)
    {
//        return $request;
        if (!is_enable_guest_checkout() and !Auth::check()) {
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }
        if (auth()->user() && !auth()->user()->hasVerifiedEmail() && setting_item('enable_verify_email_register_user') == 1) {
            return $this->sendError(__("You have to verify email first"), ['url' => url('/email/verify')]);
        }

        $validator = Validator::make($request->all(), [
            //'service_id'   => 'required|integer',
            'service_type' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();

        // Debug logging
        Log::info('AddToCart Request', [
            'service_type' => $service_type,
            'service_id' => $service_id,
            'service_id_type' => gettype($service_id),
            'all_services' => array_keys($allServices)
        ]);

        if (empty($allServices[$service_type])) {
            Log::error('Service type not found', [
                'service_type' => $service_type,
                'available_types' => array_keys($allServices)
            ]);
            return $this->sendError(__('Service type not found'));
        }

        $module = $allServices[$service_type];

        // For flight bookings from Sabre, service_id might be a Sabre ID, not a database ID
        // We need to handle this case - either find the flight or use a default/template flight
        $service = null;
        $service_id = $request->input('service_id');

        // Try to convert to integer if possible
        if (is_numeric($service_id)) {
            $service_id = (int) $service_id;
            $service = $module::find($service_id);
        }

        // If service not found and it's a flight booking with flight_data,
        // we can use a default flight or create a virtual one
        if (empty($service) && $service_type === 'flight') {
            // Check if we have flight_data (Sabre booking)
            $flight_data = $request->input('flight_data');
            if (!empty($flight_data)) {
                // For Sabre bookings, try to find a default/template flight
                // Or use the first available flight as a template
                $defaultFlight = $module::where('status', 'publish')
                    ->orderBy('id', 'asc')
                    ->first();

                if ($defaultFlight) {
                    $service = $defaultFlight;
                    $service_id = $defaultFlight->id;
                    Log::info('Using default flight for Sabre booking', [
                        'original_service_id' => $request->input('service_id'),
                        'default_flight_id' => $service_id
                    ]);
                } else {
                    Log::error('No default flight found for Sabre booking', [
                        'original_service_id' => $request->input('service_id')
                    ]);
                    return $this->sendError(__('No flight template available. Please contact administrator.'));
                }
            }
        }

        // Final validation
        if (empty($service_id) || $service_id <= 0) {
            Log::error('Invalid service_id', [
                'service_id' => $request->input('service_id'),
                'converted_id' => $service_id
            ]);
            return $this->sendError(__('Invalid service ID'));
        }

        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            Log::error('Service not found or not bookable', [
                'service_id' => $service_id,
                'module' => $module,
                'service_exists' => !empty($service),
                'is_bookable' => $service ? is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable') : false
            ]);
            return $this->sendError(__('Service not found'));
        }

        Log::info('Service lookup result', [
            'service_id' => $service_id,
            'service_found' => !empty($service),
            'service_class' => $service ? get_class($service) : null
        ]);
        if (!$service->isBookable()) {
            return $this->sendError(__('Service is not bookable'));
        }

        if (\auth()->user() && Auth::id() == $service->author_id) {
            return $this->sendError(__('You cannot book your own service'));
        }

        // Update request with correct service_id if we used a default flight
        if ($service_id != $request->input('service_id')) {
            $request->merge(['service_id' => $service_id]);
        }

//                return $request;
        return $service->addToCart($request);
    }

    public function detail(Request $request, $code)
    {
        if (!is_enable_guest_checkout() and !Auth::check()) {
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        $booking = $this->booking::where('code', $code)->first();
        $booking['passengers'] = $this->BookingPassenger::where('booking_id', $booking->id)->get();
        $booking['routes'] = $this->BookingRoute::where('booking_id', $booking->id)->get();
        if (empty($booking)) {
            abort(404);
        }

        if ($booking->status == 'draft') {
            return redirect($booking->getCheckoutUrl());
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        $airline = null;
        if ($booking->airline) {
            $airline = \Modules\Flight\Models\Airline::where('designator', $booking->airline)->first();
        }
        $data = [
            'page_title' => __('Booking Details'),
            'booking'    => $booking,
            'airline'    => $airline,
            'service'    => $booking->service,
        ];
        //echo '<pre>'; print_r($booking); die();
        if ($booking->gateway) {
            $data['gateway'] = get_payment_gateway_obj($booking->gateway);
        }
        return view('Booking::frontend/detail', $data);
    }

    public function exportIcal($type, $id = false)
    {
        if (empty($type) or empty($id)) {
            return $this->sendError(__('Service not found'));
        }

        $allServices = get_bookable_services();
        $allServices['room'] = 'Modules\Hotel\Models\HotelRoom';
        if (empty($allServices[$type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$type];

        $path = '/ical/';
        $fileName = 'booking_' . $type . '_' . $id . '.ics';
        $fullPath = $path . $fileName;

        $content = $this->booking::getContentCalendarIcal($type, $id, $module);
        Storage::disk('uploads')->put($fullPath, $content);
        $file = Storage::disk('uploads')->get($fullPath);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        echo $file;
    }

    public function addEnquiry(Request $request)
    {
        $rules = [
            'service_id'    => 'required|integer',
            'service_type'  => 'required',
            'enquiry_name'  => 'required',
            'enquiry_note'  => 'required',
            'enquiry_email' => [
                'required',
                'email',
                'max:255',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }

        if (setting_item('booking_enquiry_enable_recaptcha')) {
            $codeCapcha = trim($request->input('g-recaptcha-response'));
            if (empty($codeCapcha) or !ReCaptchaEngine::verify($codeCapcha)) {
                return $this->sendError(__("Please verify the captcha"));
            }
        }

        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);
        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        $row = new $this->enquiryClass();
        $row->fill([
            'name'  => $request->input('enquiry_name'),
            'email' => $request->input('enquiry_email'),
            'phone' => $request->input('enquiry_phone'),
            'note'  => $request->input('enquiry_note'),
        ]);
        $row->object_id = $request->input("service_id");
        $row->object_model = $request->input("service_type");
        $row->status = "pending";
        $row->vendor_id = $service->author_id;
        $row->save();
        event(new EnquirySendEvent($row));
        return $this->sendSuccess([
            'message' => __("Thank you for contacting us! We will be in contact shortly.")
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPaidAmount(Request $request)
    {

        $rules = [
            'remain' => 'required',
            'id'     => 'required'
        ];
//return $rules;
//        $validator = Validator::make($request->all(), $rules);
//        return $validator;
//        if ($validator->fails()) {
//            return $this->sendError('', ['errors' => $validator->errors()]);
//        }


        $id = $request->input('id');

        $remain = floatval($request->input('remain'));

        if ($remain < 0) {
            return $this->sendError(__('Remain can not smaller than 0'));
        }

        $booking = Booking::where('id', $id)->first();
        if (empty($booking)) {
            return $this->sendError(__('Booking not found'));
        }


        if (!Auth::user()->hasPermission('dashboard_vendor_access')) {
            if ($booking->vendor_id != Auth()->id()) {
                return $this->sendError(__("You don't have access."));
            }
        }


        $booking->pay_now = $remain;
        $booking->paid = floatval($booking->total) - $remain;


//        event(new SetPaidAmountEvent($booking));

        if ($remain == 0) {

            $booking->status = $booking::PAID;
            $booking->update(
                [
                    'status' => $booking->status

                ]
            );



            //            $booking->sendStatusUpdatedEmails();
            event(new BookingUpdatedEvent($booking));
        }


        // $booking->save();

        return $this->sendSuccess([
            'message' => __("You booking has been changed successfully")
        ]);
    }
public function saverCreatePnr($booking, $id)
{
if (empty($booking)) return false;

    // Get passengers - check if using Flight module's BookingPassengers model
    $isFlight = ($booking->object_model === 'flight');
    $passengerModel = $isFlight ? \Modules\Flight\Models\BookingPassengers::class : BookingPassenger::class;
    $passengers = $passengerModel::where('booking_id', $booking->id)->get();

//return $booking->object_model;
    // Get routes - check if using Flight module's BookingRoutes model
    $routeModel = $isFlight ? \Modules\Flight\Models\BookingRoutes::class : BookingRoute::class;
    $routes = $routeModel::where('booking_id', $booking->id)->orderBy('departure_at')->get();


    // Build PNR payload
    $pnrPayload = $this->buildPnrPayload($booking, $passengers, $routes);
//return $pnrPayload;
    // Call Sabre to create PNR
    $sabreService = new \App\Service\SabreApiService();
    $response = $sabreService->createBookingPnr($pnrPayload);

return $response;

    if ($response && isset($response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
        $pnr = $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];
        $booking->update(['pnr_id' => $pnr, 'status' => 'booked']);

        return true;
    }

    // store error details
    $errorMessages = method_exists($this, 'extractSabreErrors') ? $this->extractSabreErrors($response ?? []) : ($response ?? []);
    Log::error('PNR Creation failed', ['booking_id' => $booking->id, 'errors' => $errorMessages, 'response' => $response]);
    if (method_exists($booking, 'addMeta')) {
        $booking->addMeta('pnr_error_messages', $errorMessages);
        $booking->addMeta('pnr_error_response', $response);
        $booking->addMeta('pnr_error_timestamp', now()->toDateTimeString());
    }

    $booking->update(['pnr_id' => null, 'status' => 'failed']);
    return false;
}

/**
 * Build CreatePassengerNameRecordRQ payload for Sabre API
 * Handles one-way, round-trip, and multi-city flights
 *
 * @param \Modules\Booking\Models\Booking $booking
 * @param \Illuminate\Database\Eloquent\Collection $passengers
 * @param \Illuminate\Database\Eloquent\Collection $routes
 * @return array
 */



private function buildPnrPayload($booking, $passengers, $routes)
{

    // --- 1. PRE-PROCESS: SORT ROUTES CHRONOLOGICALLY ---
    // FIX: Convert Collection to Array and Sort by Date
    // This handles both Arrays and Eloquent Collections automatically
    $sortedRoutes = collect($routes)
        ->sortBy('departure_at')
        ->values() // Re-index keys (0, 1, 2...)
        ->all();   // Convert back to plain PHP array

    // --- 2. HELPER: FORMATTERS ---
    $formatDT = fn($d) => \Carbon\Carbon::parse($d)->format("Y-m-d\TH:i:s");

    $formatDate = function($d, $isExpiry = false) {
        if (empty($d) || (is_numeric($d) && strlen((string)$d) < 6)) {
            return $isExpiry ? \Carbon\Carbon::now()->addYears(5)->format('Y-m-d') : '1990-01-01';
        }
        try {
            return \Carbon\Carbon::parse($d)->format('Y-m-d');
        } catch (\Exception $e) {
            return '1990-01-01';
        }
    };

    // --- 3. CONFIGURATION ---
    $targetCity   = '27YK';
    $receivedFrom = 'SabreAPIsTools';
    $ticketType   = '7TAW';
    $totalCount   = count($passengers);

    // --- 4. BUILD FLIGHT SEGMENTS ---
    $flightSegments = [];

    foreach ($sortedRoutes as $i => $r) {
        // Ensure $r is an object (handles both array/object inputs)
        $r = (object)$r;

        $flightNumber = trim($r->flight_number);
        if (preg_match('/^[A-Z]{2}(\d+)$/', $flightNumber, $matches)) {
            $flightNumber = $matches[1];
        }

        // --- MARRIAGE GROUP LOGIC (Auto-Detect Connection) ---
        $marriageGrp = 'O'; // Default to New Leg

        if ($i > 0) {
            $prevRoute = (object)$sortedRoutes[$i - 1];

            $prevArrival = \Carbon\Carbon::parse($prevRoute->arrival_at);
            $currDepart  = \Carbon\Carbon::parse($r->departure_at);
            $hoursDiff   = $prevArrival->diffInHours($currDepart);

            // Logic: Same Airport AND < 24 hours = Connection (I)
            if ($r->departure_iata_code === $prevRoute->arrival_iata_code && $hoursDiff < 24) {
                $marriageGrp = 'I';
            }
        }

        $flightSegments[] = [
            "DepartureDateTime"   => $formatDT($r->departure_at),
            "FlightNumber"        => $flightNumber,
            "Status"              => "NN",
            "ResBookDesigCode"    => $r->class ?? 'Y',
            "NumberInParty"       => (string)$totalCount,
            "DestinationLocation" => ["LocationCode" => $r->arrival_iata_code],
            "MarketingAirline"    => ["Code" => $r->carrier_code, "FlightNumber" => $flightNumber],
            "MarriageGrp"         => $marriageGrp,
            "OriginLocation"      => ["LocationCode" => $r->departure_iata_code]
        ];
    }

    // --- 5. PASSENGERS & SERVICES ---
    $personNames       = [];
    $contactNumbers    = [];
    $secureFlights     = [];
    $advancePassengers = [];
    $serviceRequests   = [];
    $emails            = [];

    foreach ($passengers as $i => $p) {
        $p = (object)$p;
        $nameNumber = ($i + 1) . ".1";

        $firstName  = $p->first_name;
        $surname    = $p->last_name;
        $type    = $p->passenger_type_code;
        $dob        = $formatDate($p->dob ?? '');
        $gender     = (isset($p->gender) && (stripos($p->gender, 'F') === 0)) ? 'F' : 'M';
        $pptNum     = !empty($p->passport_number) ? (string)$p->passport_number : 'A00000000';
        $pptExp     = $formatDate($p->passport_expiry_date ?? '', true);
        $nationality= $p->country ?? 'XX';

        // A. Person Name
        $personNames[] = [
            "NameNumber"    => $nameNumber,
            "PassengerType" => $type,
            "GivenName"     => $firstName,
            "Surname"       => $surname
        ];

        // B. Contact (1st Passenger)
        if ($i === 0) {
            $rawPhone = $p->phone ?? $booking['phone'] ?? '';
            $phone    = preg_replace('/[^0-9]/', '', $rawPhone);
            $emailAddr= !empty($p->email) ? $p->email : ($booking['email'] ?? '');

            // Use Origin of the FIRST flight
            $locCode  = $sortedRoutes[0]->departure_iata_code ?? 'DAC';

            if ($phone) {
                $contactNumbers[] = [
                    "LocationCode" => $locCode,
                    "NameNumber"   => $nameNumber,
                    "Phone"        => $phone,
                    "PhoneUseType" => "M"
                ];
                $serviceRequests[] = [
                    "SegmentNumber" => "A",
                    "SSR_Code"      => "CTCM",
                    "PersonName"    => ["NameNumber" => $nameNumber],
                    "Text"          => $phone
                ];
            }

            if ($emailAddr) {
                $emails[] = [
                    "NameNumber" => $nameNumber,
                    "Address"    => $emailAddr
                ];
                $serviceRequests[] = [
                    "SegmentNumber" => "A",
                    "SSR_Code"      => "CTCE",
                    "PersonName"    => ["NameNumber" => $nameNumber],
                    "Text"          => str_replace('@', '//', $emailAddr)
                ];
            }
        }

        // C. Secure Flight
        $secureFlights[] = [
            "SegmentNumber" => "A",
            "PersonName"    => [
                "NameNumber"  => $nameNumber,
                "DateOfBirth" => $dob,
                "Gender"      => $gender,
                "GivenName"   => $firstName,
                "Surname"     => $surname
            ],
            "VendorPrefs"   => ["Airline" => ["Hosted" => false]]
        ];

        // D. Advance Passenger
        $advancePassengers[] = [
            "SegmentNumber" => "A",
            "Document"      => [
                "Number"             => $pptNum,
                "ExpirationDate"     => $pptExp,
                "Type"               => "P",
                "IssueCountry"       => $nationality,
                "NationalityCountry" => $nationality
            ],
            "PersonName"    => [
                "NameNumber"  => $nameNumber,
                "GivenName"   => $firstName,
                "Surname"     => $surname,
                "Gender"      => $gender,
                "DateOfBirth" => $dob
            ]
        ];
    }

    // --- 6. PRICING & FINAL PAYLOAD ---
    $pricingQualifiers = [
        [
            "Code"     => "ADT",
            "Quantity" => (string)$totalCount
        ]
    ];

    $specialServiceInfo = [];
    if (!empty($advancePassengers)) $specialServiceInfo['AdvancePassenger'] = $advancePassengers;
    if (!empty($secureFlights))     $specialServiceInfo['SecureFlight']     = $secureFlights;
    if (!empty($serviceRequests))   $specialServiceInfo['Service']          = $serviceRequests;

    return [
        "CreatePassengerNameRecordRQ" => [
            "version"             => "2.5.0",
            "targetCity"          => $targetCity,
            "haltOnAirPriceError" => true,
            "TravelItineraryAddInfo" => [
                "AgencyInfo"   => ["Ticketing" => ["TicketType" => $ticketType]],
                "CustomerInfo" => [
                    "ContactNumbers" => ["ContactNumber" => $contactNumbers],
                    "Email"          => $emails,
                    "PersonName"     => $personNames
                ]
            ],
            "AirBook" => [
                "HaltOnStatus" => [
                    ["Code" => "NO"], ["Code" => "NN"], ["Code" => "UC"], ["Code" => "US"],
                    ["Code" => "UN"], ["Code" => "LL"], ["Code" => "HL"], ["Code" => "HX"], ["Code" => "WL"]
                ],
                "OriginDestinationInformation" => ["FlightSegment" => $flightSegments],
                "RedisplayReservation" => [
                    "NumAttempts" => 3,
                    "WaitInterval" => 3000
                ]
            ],
            "AirPrice" => [[
                "PriceRequestInformation" => [
                    "Retain" => true,
                    "OptionalQualifiers" => [
                        "PricingQualifiers" => [
                            "PassengerType" => $pricingQualifiers
                        ]
                    ]
                ]
            ]],
            "SpecialReqDetails" => ["SpecialService" => ["SpecialServiceInfo" => $specialServiceInfo]],
            "PostProcessing" => [
                "RedisplayReservation" => [
                    "waitInterval"             => 100,
                    "returnExtendedPriceQuote" => true
                ],
                "EndTransaction" => [
                    "Source" => ["ReceivedFrom" => $receivedFrom]
                ]
            ]
        ]
    ];
}
    /**
     * Price the Itinerary (Step 2)
     * Get fare details, taxes, total price using AirPriceLLSRQ
     */
//    public function priceItinerary($recordLocator, $route,$passengers)
//    {
//
//
//        $sabreService = new \App\Service\SabreApiService();
//
//
//        $priceResp = $sabreService->createPriceQuote($recordLocator, $route,$passengers);
//
//        // $storeResp = $sabreService->storeFareQuote($recordLocator, $priceQuote);
//        $storeFareQuote = $sabreService->endTransaction($recordLocator);
//        $ticketData= $this->issueTicket($recordLocator, $priceResp['price_quotes']);
//
//        if ($ticketData && !empty($ticketData)) {
//
//            $ticketCollection = collect($ticketData);
//            $updatedCount = 0;
//
//            foreach ($passengers as $passenger) {
//
//                // Find matching ticket using Laravel collection
//                $matchingTicket = $ticketCollection->first(function($ticket) use ($passenger) {
//                    return strtoupper(trim($ticket['first_name'])) === strtoupper(trim($passenger->first_name))
//                        && strtoupper(trim($ticket['last_name'])) === strtoupper(trim($passenger->last_name));
//                });
//
//                if ($matchingTicket) {
//                    $passenger->update([
//                        'ticket_number' => $matchingTicket['ticket_number'],
//                        'pnr' => $matchingTicket['pnr'],
//                        'ticket_amount' => $matchingTicket['total_amount'],
//                        'ticket_currency' => $matchingTicket['currency_code'],
//                        'ticket_issued_at' => now(),
//                        'ticket_status' => 'issued',
//                        'document_type' => $matchingTicket['document_type'] ?? 'TKT',
//                        'issuing_location' => $matchingTicket['issuing_location'] ?? '27YK',
//                    ]);
//
//                    $updatedCount++;
//                }
//            }
//
//            // Update booking
//            $booking->update([
//                'status' => 'ticketed',
//                'ticket_issued_at' => now()
//            ]);
//
//            return redirect()->back()->with('success', "$updatedCount out of " . $passengers->count() . " tickets issued successfully!");
//
//        } else {
//            $booking->update(['status' => 'error']);
//            return redirect()->back()->with('error', 'Ticket issuance failed.');
//        }

//    }


    /**
     * Issue Ticket (Step 3)
     * Issue E-ticket using AirTicketRQ and get ticket number
     */
//    public function issueTicket($recordLocator, $priceQuotes)
//    {
//
////return $priceQuotes;
//        $sabreService = new \App\Service\SabreApiService();
//
//
//        // $printValue = $sabreService->assignPrinter();
//
//
//        // Call Sabre ticketing API
//        $response = $sabreService->issueTicketSoap($recordLocator, "27YK", "624EF0", $priceQuotes);
//
//
//        return $response;
//
//
//    }

    /**
     * Extract error messages from Sabre API response
     */
    private function extractSabreErrors($response)
    {
        $errors = [];

        if (!isset($response['CreatePassengerNameRecordRS']['ApplicationResults'])) {
            return ['No error details available in response'];
        }

        $appResults = $response['CreatePassengerNameRecordRS']['ApplicationResults'];

        // Extract Error messages
        if (isset($appResults['Error'])) {
            $errorArray = is_array($appResults['Error']) && isset($appResults['Error'][0])
                ? $appResults['Error']
                : [$appResults['Error']];

            foreach ($errorArray as $error) {
                if (isset($error['SystemSpecificResults'][0]['Message'])) {
                    $messages = is_array($error['SystemSpecificResults'][0]['Message']) && isset($error['SystemSpecificResults'][0]['Message'][0])
                        ? $error['SystemSpecificResults'][0]['Message']
                        : [$error['SystemSpecificResults'][0]['Message']];

                    foreach ($messages as $msg) {
                        $code = $msg['code'] ?? 'UNKNOWN';
                        $content = $msg['content'] ?? 'No error message';
                        $errors[] = "[{$code}] {$content}";
                    }
                }
            }
        }

        // Extract Warning messages (some warnings are actually critical errors)
        if (isset($appResults['Warning'])) {
            $warningArray = is_array($appResults['Warning']) && isset($appResults['Warning'][0])
                ? $appResults['Warning']
                : [$appResults['Warning']];

            foreach ($warningArray as $warning) {
                if (isset($warning['SystemSpecificResults'][0]['Message'])) {
                    $messages = is_array($warning['SystemSpecificResults'][0]['Message']) && isset($warning['SystemSpecificResults'][0]['Message'][0])
                        ? $warning['SystemSpecificResults'][0]['Message']
                        : [$warning['SystemSpecificResults'][0]['Message']];

                    foreach ($messages as $msg) {
                        $code = $msg['code'] ?? 'UNKNOWN';
                        $content = $msg['content'] ?? 'No warning message';

                        // Flag critical warnings as errors
                        if (stripos($content, 'FLIGHT NOOP') !== false ||
                            stripos($content, 'SEGMENT NUMBER NOT VALID') !== false ||
                            stripos($content, 'ITINERARY REQUIRED') !== false ||
                            stripos($content, 'SYSTEM UNABLE TO PROCESS') !== false) {
                            $errors[] = "[WARNING: {$code}] {$content}";
                        }
                    }
                }
            }
        }

        // If no errors found, check status
        if (empty($errors) && isset($appResults['status'])) {
            $errors[] = "Status: {$appResults['status']}";
        }

        return empty($errors) ? ['Unknown error occurred'] : $errors;
    }

    /**
     * Map internal passenger types to Sabre passenger types
     */
    private function mapPassengerType($internalType, $passengerTypeCode = null)
    {
        $sabreCodes = [
            'ADT','CNN','CHD','INF','INS',
            'C02','C03','C04','C05','C06','C07','C08','C09','C10','C11'
        ];

        if ($passengerTypeCode) {
            $normalized = strtoupper($passengerTypeCode);
            if (in_array($normalized, $sabreCodes, true)) {
                return $normalized;
            }
        }

        $mapping = [
            'ADULT' => 'ADT',
            'CHILD' => 'CHD',
            'INFANT' => 'INF'
        ];

        return $mapping[strtoupper($internalType)] ?? 'ADT';
    }

    /**
     * Resolve passenger DOB based on stored value or static mapping
     */
    private function resolvePassengerDob($passenger, $passengerType, $passengerTypeCode)
    {
        $now = Carbon::now();


        if (!empty($passenger->dob)) {
            try {
                $dobParsed = Carbon::parse($passenger->dob);
                if ($dobParsed->year > 1900) {
                    return $dobParsed;
                }
            } catch (\Exception $e) {
                // ignore – will fall back to static mapping
            }
        }

        $passengerTypeCode = strtoupper($passengerTypeCode ?? '');

        // Infants (under 2 years)
        if (in_array($passengerType, ['INF', 'INS'], true)) {
            return $now->copy()->subMonths($passengerType === 'INS' ? 18 : 12);
        }

        // Children (2-11 years) – static age map by Sabre code
        $childAgeMap = [
            'C02' => 2,
            'C03' => 3,
            'C04' => 4,
            'C05' => 5,
            'C06' => 6,
            'C07' => 7,
            'C08' => 8,
            'C09' => 9,
            'C10' => 10,
            'C11' => 11,
            'CHD' => 8,
            'CNN' => 8,
        ];

        if (isset($childAgeMap[$passengerTypeCode])) {
            return $now->copy()->subYears($childAgeMap[$passengerTypeCode])->subMonths(3);
        }

        if (in_array($passengerType, ['CHD', 'CNN'], true)) {
            return $now->copy()->subYears(8);
        }

        // Default adult fallback (approx. 30 years old)
        return $now->copy()->subYears(30);
    }

    /**
     * Get booking status from Sabre API
     */
    public function getSabreBookingStatus($booking)
    {
        try {
            if (!$booking->booking_id) {
                return ['status' => 'error', 'message' => 'No Sabre record locator found'];
            }

            $sabreService = new \App\Service\SabreApiService();
            $sabreResponse = $sabreService->getBooking($booking->booking_id);

            if ($sabreResponse) {
                return [
                    'status' => 'success',
                    'data' => $sabreResponse,
                    'record_locator' => $booking->booking_id
                ];
            } else {
                return ['status' => 'error', 'message' => 'Failed to retrieve booking from Sabre'];
            }
        } catch (\Exception $e) {
            Log::error("Error retrieving Sabre booking status: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Cancel booking in Sabre API
     */

    public function booking_details($book)
    {
        $booking    = Booking::findOrFail($book);
        $passengers = BookingPassenger::where('booking_id', $booking->id)->get();
        $routes     = BookingRoute::where('booking_id', $booking->id)
            ->orderBy('departure_at', 'asc')
            ->get();
//return $passengers;
        /* ── pnr_raw_data → flightData (সব extra info এখানেই আছে) ── */
        $flightData = is_string($booking->pnr_raw_data)
            ? json_decode($booking->pnr_raw_data, true)
            : (array)($booking->pnr_raw_data ?? []);

//        return $flightData;
        /* ── Airline PNR ── */
        $airlinePnr = $flightData['supplier_locator']['locator_code'] ?? null;

        /* ── TAU Deadline ── */
        $tauDeadline = null;
        if (!empty($flightData['action_status']['ticket_date'])) {
            try {
                $tauCarbon      = \Carbon\Carbon::parse($flightData['action_status']['ticket_date'])
                    ->setTimezone(config('app.timezone', 'Asia/Dhaka'))
                    ->subMinutes(45);
                $tauDeadline    = $tauCarbon->format('d M Y, h:i A');
                $tauDeadlineIso = $tauCarbon->toIso8601String();
            } catch (\Exception $e) {
                $tauDeadline = $flightData['action_status']['ticket_date'];
            }
        }

        /* ── Routes with layover + formatted duration ── */
        $routesWithLayover = [];
        foreach ($routes as $index => $route) {
            $routeData = $route->toArray();
            if ($index < count($routes) - 1) {
                $layoverMinutes = \Carbon\Carbon::parse($route->arrival_at)
                    ->diffInMinutes(\Carbon\Carbon::parse($routes[$index + 1]->departure_at));
                $routeData['layover'] = $this->formatDuration($layoverMinutes);
            }
            $routeData['formatted_duration'] = $this->convertIsoDuration($route->duration);
            $routesWithLayover[] = $routeData;
        }

        /* ── Can void? ── */
        $canVoid = false;
        if ($booking->ticket_issued_at) {
            $canVoid = \Carbon\Carbon::parse($booking->ticket_issued_at)
                ->startOfDay()->eq(\Carbon\Carbon::now()->startOfDay());
        }
//return $flightData;
        return view('Booking::frontend.detail.details', [
            'booking'     => $booking,
            'passengers'  => $passengers,
            'routes'      => $routesWithLayover,
            'flightData'  => $flightData,   // pnr_raw_data — all extra info here
            'canVoid'     => $canVoid,
            'airlinePnr'  => $airlinePnr,
            'tauDeadline'    => $tauDeadline,
            'tauDeadlineIso' => $tauDeadlineIso ?? '',
            'summary'     => [
                'total_passengers' => count($passengers),
                'adults'           => $booking->adult_count,
                'children'         => $booking->child_count,
                'infants'          => $booking->infant_count,
                'total_segments'   => count($routes),
                'departure_city'   => $routes->first()->departure_iata_code ?? 'N/A',
                'arrival_city'     => $routes->last()->arrival_iata_code    ?? 'N/A',
                'departure_date'   => $routes->first()->departure_at        ?? null,
                'arrival_date'     => $routes->last()->arrival_at           ?? null,
            ],
        ]);
    }


// Helper function to convert ISO 8601 duration to readable format
    private function convertIsoDuration($duration)
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches);
        $hours = isset($matches[1]) ? (int)$matches[1] : 0;
        $minutes = isset($matches[2]) ? (int)$matches[2] : 0;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

// Helper function to format layover duration
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }
    public function modal(Booking $booking)
    {
        if (!is_admin() and $booking->vendor_id != auth()->id() and $booking->customer_id != auth()->id()) abort(404);
        $booking['passengers'] = $this->BookingPassenger::where('booking_id', $booking->id)->get();
        $booking['routes'] = $this->BookingRoute::where('booking_id', $booking->id)->get();


        return view('Booking::frontend.detail.modal', ['booking' => $booking, 'service' => $booking->service]);
    }

    public function get_booking_details($id)
    {
        $booking = Booking::where('id', $id)->first();
//        return $booking;
        return view('Booking::admin.page.pnr-details', [
            'booking' => $booking,
            'passengers' => $booking->passengers,
            'routes' => $booking->routes
        ]);

    }

    public function getPnr($pnr)
    {
//        return $pnr;
//        $booking = Booking::where('id', $id)->first();
        $sabrebookingservice = new SabreApiService();
        $pnr=$sabrebookingservice->getPnr($pnr);
//        return $pnr;
        return view('Booking::admin.page.pnr_details_new', [
            'bookingData' => $pnr
        ]);
    }

//    public function pnrSearchPage()
//    {
//        return view('Booking::admin.page.pnr-details', [
//            'booking' => null,
//            'pnrData' => null
//        ]);
//    }

    public function showBookingDetails(Request $request,$pnr = null)
    {
        $pnr = $pnr ?? $request->input('pnr');

        // ✅ OPTIMIZATION 1: Early return if no PNR
        if (!$pnr) {
            return view('Booking::admin.page.pnr-details', [
                'booking' => null,
                'passengers' => collect(),
                'routes' => collect()
            ]);
        }
//        $sabreBookingService = new SabreApiService();
//        $pnrResponse = $sabreBookingService->getPnr($pnr);

        // Create new booking
//        $booking = $this->createNewBooking($pnrResponse);
//        $booking=  $pnrResponse['travelers'];
//        return $booking;
        // ✅ OPTIMIZATION 2: Load booking with relationships in single query
        $booking = Booking::with(['passengers', 'routes'])
            ->where('pnr_id', $pnr)
            ->first();

        // ✅ OPTIMIZATION 3: If not found, fetch from Sabre and create
        if (!$booking) {
            try {
                $sabreBookingService = new SabreApiService();
                $pnrResponse = $sabreBookingService->getPnr($pnr);

                // Create new booking
                $booking = $this->createNewBooking($pnrResponse);
//                $booking=  $pnrResponse['travelers'];
//return $booking;
                // ✅ OPTIMIZATION 4: Reload with relationships (no need to query again)
                $booking->load(['passengers', 'routes']);

            } catch (\Exception $e) {
                Log::error('Failed to fetch PNR from Sabre', [
                    'pnr' => $pnr,
                    'error' => $e->getMessage()
                ]);

                return redirect()->back()->with('error', 'PNR not found or unable to fetch from Sabre.');
            }
        }

        // ✅ OPTIMIZATION 5: Use loaded relationships (no extra queries)
        return view('Booking::admin.page.pnr-details', [
            'booking' => $booking,
            'passengers' => $booking->passengers,
            'routes' => $booking->routes
        ]);
    }

    private function createNewBooking($pnrResponse)
    {
        DB::beginTransaction();

        try {
            // ✅ OPTIMIZATION 1: Extract reusable data ONCE (loop er baire)
            $travelers = $pnrResponse['travelers'] ?? [];
            $flights = $pnrResponse['flights'] ?? [];
            $totalPayment = $pnrResponse['payments']['flightTotals'][0] ?? [];
            $firstFlight = $flights[0] ?? [];
            $firstJourney = $pnrResponse['journeys'][0] ?? [];

            $airline=$pnrResponse['flights'][0] ?? [];

            // ✅ OPTIMIZATION 2: Passenger counts (cleaner with array_count_values)
            $passengerCounts = array_count_values(array_column($travelers, 'passengerCode'));

            // ✅ OPTIMIZATION 3: Country from first passenger (simplified)
            $firstPassportDoc = $this->getPassportDocument($travelers[0]['identityDocuments'] ?? []);

            // 1. Create Booking
            $booking = Booking::create([
                'pnr_id' => $pnrResponse['bookingId'],
                'country' => $firstPassportDoc['issuingCountryCode'] ?? null,
                'object_model' => 'flight',
                'status' => 'processing',
                'status' => 'processing',
                'airline' => $airline['operatingAirlineCode'],

                // Trip Info
                'flight_type' => $this->getTripType($pnrResponse),
                'flight_from' => $firstJourney['firstAirportCode'] ?? null,
                'flight_to' => $firstJourney['lastAirportCode'] ?? null,

                // Passenger Counts
                'total_guests' => count($travelers),
                'adult_count' => $passengerCounts['ADT'] ?? 0,
                'child_count' => $passengerCounts['CNN'] ?? 0,
                'infant_count' => $passengerCounts['INF'] ?? 0,

                // Dates
                'start_date' => $pnrResponse['startDate'] ?? null,
                'end_date' => $pnrResponse['endDate'] ?? null,

                // Payment
                'currency' => $totalPayment['currencyCode'] ?? 'BDT',
                'base_fee' => $totalPayment['subtotal'] ?? 0,
                'total' => $totalPayment['total'] ?? 0,
                'pay_now' => $totalPayment['total'] ?? 0,
                'total_before_discount' => $totalPayment['total'] ?? 0,

                // ✅ FIX: Source field logic corrected
                'source' => ($pnrResponse['creationDetails']['userWorkPcc'] ?? null) === '27YK' ? 'Saver' : 'Other',
            ]);

            // 2. Create Passengers
            foreach ($travelers as $index => $traveler) {
                // ✅ FIX: Handle infant passengers differently
                if ($traveler['passengerCode'] === 'INF') {
                    // Infants don't have their own identityDocuments
                    // Their passport info is in the first adult's documents
                    $firstAdult = $pnrResponse['travelers'][0] ?? [];
                    $passportDoc = $this->getPassportDocument(
                        $firstAdult['identityDocuments'] ?? [],
                        $traveler  // Pass traveler to match by name
                    );
                } else {
                    // Adults/Children have their own documents
                    $passportDoc = $this->getPassportDocument(
                        $traveler['identityDocuments'] ?? []
                    );
                }

//                $priceData = $this->getPassengerPrice($pnrResponse, $index);
//                $passportDoc = $this->getPassportDocument($traveler['identityDocuments'] ?? []);
                $priceData = $this->getPassengerPrice($pnrResponse, $index);

                BookingPassenger::create([
                    'booking_id' => $booking->id,

                    // Basic Info
                    'first_name' => $traveler['givenName'],
                    'last_name' => $traveler['surname'],
                    'email' => $traveler['emails'][0] ?? null,
                    'phone' => $this->formatPhone($traveler['phones'] ?? []),

                    // Personal Details
                    'dob' => $passportDoc['birthDate'] ?? null,
                    'country' => $passportDoc['issuingCountryCode'] ?? null,
                    'gender' => $this->mapGender($passportDoc['gender'] ?? null),

                    // Passport Info
                    'passport_number' => $passportDoc['documentNumber'] ?? null,
                    'passport_expiry_date' => $passportDoc['expiryDate'] ?? null,

                    // Flight Info
                    'seat_type' => $firstFlight['cabinTypeName'] ?? 'ECONOMY',
                    'cabin' => $firstFlight['cabinTypeCode'] ?? 'Y',
                    'class' => $firstFlight['bookingClass'] ?? null,

                    // Traveler Type
                    'traveler_type' => $traveler['type'],
                    'passenger_type_code' => $traveler['passengerCode'],

                    // Price
                    'base' => $priceData['subtotal'],
                    'total' => $priceData['total'],

                    // Meta
                    'object_model' => json_encode($traveler),
                    'update_user' => auth()->id(),

                    // ✅ REMOVED: Unnecessary null fields
                    // 'flight_id', 'flight_seat_id', 'zip_code', 'id_card', 'ticket_number', 'ticket_issued_at'
                ]);
            }

            // 3. Create Flight Routes
            foreach ($flights as $flight) {
                // ✅ OPTIMIZATION 4: Fixed date/time concatenation
                $departureDateTime = $flight['departureDate'] . ' ' . $flight['departureTime'];
                $arrivalDateTime = $flight['arrivalDate'] . ' ' . $flight['arrivalTime'];

                BookingRoute::create([
                    'booking_id' => $booking->id,
                    'departure_at' => $departureDateTime,
                    'arrival_at' => $arrivalDateTime,
                    'departure_iata_code' => $flight['fromAirportCode'] ?? null,
                    'arrival_iata_code' => $flight['toAirportCode'] ?? null,
                    'departure_terminal' => $flight['departureTerminalName'] ?? null,
                    'arrival_terminal' => $flight['arrivalTerminalName'] ?? null,
                    'carrier_code' => $flight['airlineCode'],
                    'flight_number' => $flight['flightNumber'],
                    'duration' => $flight['durationInMinutes'],
                    'class' => $flight['bookingClass'],
                    'aircraft_code' => $flight['aircraftTypeCode'],
                    'meta' => json_encode($flight),
                ]);
            }

            DB::commit();
            return $booking;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create booking from PNR', [
                'pnr' => $pnrResponse['bookingId'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // ✅ ADDED: Better debugging
            ]);
            throw $e;
        }
    }

// ✅ UNCHANGED: Helper methods (already optimized)
    private function getTripType($pnrResponse)
    {
        $journeys = $pnrResponse['journeys'] ?? [];
        $journeyCount = count($journeys);

        if ($journeyCount === 0) return 'unknown';
        if ($journeyCount === 1) return 'oneway';

        if ($journeyCount === 2) {
            $first = $journeys[0];
            $second = $journeys[1];

            if ($first['firstAirportCode'] === $second['lastAirportCode']
                && $first['lastAirportCode'] === $second['firstAirportCode']) {
                return 'roundtrip';
            }
        }

        return 'multicity';
    }


    private function getPassportDocument($identityDocuments)
    {
        foreach ($identityDocuments as $doc) {
            if (($doc['documentType'] ?? null) === 'PASSPORT') {
                return $doc;
            }
        }
        return [];
    }

    private function formatPhone($phones)
    {
        if (empty($phones)) return null;

        $phone = is_array($phones) ? $phones[0] : $phones;
        return is_array($phone) ? ($phone['number'] ?? null) : $phone;
    }

    private function mapGender($gender)
    {
        if (!$gender) return null;

        $genderMap = [
            'MALE' => 'M', 'M' => 'M', 'MI' => 'M',
            'FEMALE' => 'F', 'F' => 'F', 'FI' => 'F',
            'INFANT_MALE' => 'M', // ✅ ADDED: Your JSON has this format
            'INFANT_FEMALE' => 'F',
        ];

        return $genderMap[strtoupper($gender)] ?? null;
    }
    public function invoiceBooking($id)
    {
        $booking = Booking::where('id', $id)->first();
        if (empty($booking)) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

       $result= $this->SabreBookingService->createPnr($booking);
        if ($result['success']) {
            return redirect()->back()
                ->with('success', $result['message'] ?? 'Booking confirmed successfully!')
                ->with('pnr', $result['pnr']);
        }

        // ✅ Handle failure
        return redirect()->back()
            ->with('error', $result['error'])
            ->with('booking_code', $result['booking_code'])
            ->withInput(); // Preserve form data
    }

    public function createPnrForBooking($bookingId)
    {
        $booking = Booking::where('id', $bookingId)->first();

        if (empty($booking)) {
            return ['success' => false, 'error' => 'Booking not found'];
        }

        try {
            $result = $this->SabreBookingService->createPnr($booking);
dd($result);
            // Service এ already update হয়ে যাচ্ছে, শুধু result return করুন
            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'booking_code' => $booking->code
            ];
        }
    }

    public function issueTickets($id) {

//        return $id;
        $booking = Booking::where('id', $id)->first();
        $routes = BookingRoute::where('booking_id', $booking->id)->get();
        $passengers = BookingPassenger::where('booking_id', $booking->id)->get();

        $sabreService = new \App\Service\SabreApiService();
        $ticket=$sabreService->buildTicketRecords($booking,$passengers);
//        return $ticket;

        $response = $sabreService->issueTicketSoap($booking->pnr_id, "27YK", "624EF0", $ticket);

        // ✅ Check if response is valid
        if (!$response || empty($response)) {
            $booking->update(['status' => 'ticket_failed']);
            return redirect()->back()->with('error', 'Failed to get response from Sabre. Please try again.');
        }

        // ✅ Parse response
        $ticketData = $response; // Assuming $response is already the array of tickets

        // ✅ Validate ticket data
        if (!is_array($ticketData) || count($ticketData) === 0) {
            $booking->update(['status' => 'ticket_failed']);
            return redirect()->back()->with('error', 'No ticket data received from Sabre.');
        }

        // ✅ Process tickets
        $ticketCollection = collect($ticketData);
        $updatedCount = 0;
        $ticketNumbers = [];

        foreach ($passengers as $passenger) {
            // Find matching ticket
            $matchingTicket = $ticketCollection->first(function($ticket) use ($passenger) {
                return strtoupper(trim($ticket['first_name'])) === strtoupper(trim($passenger->first_name))
                    && strtoupper(trim($ticket['last_name'])) === strtoupper(trim($passenger->last_name));
            });

            if ($matchingTicket) {
                // ✅ Update passenger
                $passenger->update([
                    'ticket_number' => $matchingTicket['ticket_number'],
//                    'pnr' => $matchingTicket['pnr'],
                    'ticket_amount' => $matchingTicket['total_amount'],
                    'currency' => $matchingTicket['currency_code'],
                    'ticket_issued_at' => $matchingTicket['issue_date_time'] ?? now(),
                    'status' => 'ticketed', // ✅ Changed from 'issued' to 'ticketed'
                    'document_type' => $matchingTicket['document_type'] ?? 'TKT',
                    'issuing_location' => $matchingTicket['issuing_location'] ?? '27YK',
                ]);

                $ticketNumbers[] = $matchingTicket['ticket_number'];
                $updatedCount++;

                Log::info('✅ Ticket assigned to passenger', [
                    'passenger_id' => $passenger->id,
                    'ticket_number' => $matchingTicket['ticket_number']
                ]);
            } else {
                Log::warning('⚠️ No matching ticket found for passenger', [
                    'passenger_id' => $passenger->id,
                    'name' => $passenger->first_name . ' ' . $passenger->last_name
                ]);
            }
        }

        // ✅ Update booking with all ticket numbers
        $booking->update([
            'status' => 'ticketed',
            'ticket_number' => json_encode($ticketNumbers), // ✅ Store all ticket numbers
            'ticket_issued_at' => now()->toDateTimeString()
        ]);

// ✅ Create transaction record (optional but recommended)
//        if ($booking->user_id) {
//            Transaction::create([
//                'user_id' => $booking->user_id,
//                'booking_id' => $booking->id,
//                'ref_id' => $booking->id,
//                'type' => 'debit',
//                'transaction_type' => 'ticket_issued',
//                'amount' => 0, // Or ticketing fee if any
//                'status' => 'completed',
//                'reference' => 'Ticket issued - Booking #' . $booking->id,
//                'remarks' => "Tickets issued: " . implode(', ', $ticketNumbers),
//                'meta' => json_encode([
//                    'tickets' => $ticketNumbers,
//                    'pnr' => $booking->pnr_id,
//                    'issued_at' => now()->toDateTimeString()
//                ]),
//                'create_user' => auth()->id(),
//                'created_at' => now(),
//            ]);
//        }

        Log::info('✅✅✅ Tickets Issued Successfully', [
            'booking_id' => $booking->id,
            'pnr' => $booking->pnr_id,
            'tickets_issued' => $updatedCount,
            'total_passengers' => $passengers->count(),
            'ticket_numbers' => $ticketNumbers
        ]);

        // ✅ Better success message
        if ($updatedCount === $passengers->count()) {
            return redirect()->back()->with('success', "All {$updatedCount} ticket(s) issued successfully! Tickets: " . implode(', ', $ticketNumbers));
        } else {
            return redirect()->back()->with('warning', "{$updatedCount} out of {$passengers->count()} ticket(s) issued. Please check passenger details.");
        }

    }

//    public function cancelBooking($id, $cancelledBy = null)
//    {
////        dd(Carbon::now());
//        $isAuto = $cancelledBy !== null;
//
//        Log::info('🔴 Cancel Booking Request Started', [
//            'booking_id' => $id,
//            'by'         => $cancelledBy ?? auth()->id(),
//            'auto'       => $isAuto,
//        ]);
//
//        try {
//            DB::beginTransaction();
//
//            /* ── 1. Load booking ──────────────────────────────── */
//            $booking = Booking::with(['passengers'])->findOrFail($id);
//
//            /* ── 2. Guard: already cancelled ─────────────────── */
//            if ($booking->status === 'cancelled') {
//                throw new \Exception('This booking is already cancelled.');
//            }
//
//            /* ── 3. Guard: allowed statuses ──────────────────── */
//            $cancellable = ['booked'];
//            if (!in_array($booking->status, $cancellable)) {
//                throw new \Exception(
//                    'Booking cannot be cancelled. Current status: ' . $booking->status
//                );
//            }
//
//            /* ── 4. Guard: must have PNR ─────────────────────── */
//            if (!$booking->pnr_id) {
//                throw new \Exception('No PNR found for this booking.');
//            }
//
//            Log::info('📦 Booking Found', [
//                'booking_id' => $booking->id,
//                'pnr'        => $booking->pnr_id,
//                'source'     => $booking->source,
//                'status'     => $booking->status,
//                'paid'       => $booking->paid,
//                'passengers' => $booking->passengers->count(),
//            ]);
//
//            /* ── 5. GDS cancellation ─────────────────────────── */
//            $gdsResponse = null;
//
//            if ($booking->source === 'sabre') {
//                $sabreService = new \App\Service\SabreApiService();
//                $gdsResponse = $sabreService->cancelBooking($booking->pnr_id);
//
//                Log::info('📥 Sabre Cancel Response', ['response' => $gdsResponse]);
//
//                if (!$gdsResponse || (isset($gdsResponse['status']) && $gdsResponse['status'] === 'error')) {
//                    throw new \Exception(
//                        'Sabre cancellation failed: ' . ($gdsResponse['message'] ?? 'Unknown error')
//                    );
//                }
//
//            }elseif ($booking->source === 'travelport') {
//                    $pnrData = json_decode($booking->pnr_raw_data, true);
//
//                    $urLocatorCode = $pnrData['universal_record']['ur_locator_code']
//                        ?? $pnrData['universal_record']['locator_code']
//                        ?? null;
//
//                    if (!$urLocatorCode) {
//                        throw new \Exception('Universal Record Locator Code not found in PNR data');
//                    }
//
//                    $cancelService = new TravelPortCancelService();
//                    $gdsResponse   = $cancelService->cancelEntireBooking($urLocatorCode);
//
//                    Log::info('📥 Travelport Cancel Response', ['response' => $gdsResponse]);
//
//                    if (!$gdsResponse || !($gdsResponse['success'] ?? false)) {
//                        throw new \Exception(
//                            'Travelport cancellation failed: ' . ($gdsResponse['fault_string'] ?? $gdsResponse['error'] ?? 'Unknown error')
//                        );
//                    }
//                } else {
//                throw new \Exception('Unknown booking source: ' . ($booking->source ?? 'null'));
//            }
//
//            /* ── 6. Update booking status ────────────────────── */
//            $cancelNote = 'Cancelled on ' . now()->format('d M Y, h:i A') .
//                ' by ' . ($cancelledBy ?? auth()->user()->name ?? 'Admin') .
//                ' (ID: ' . ($cancelledBy ?? auth()->id()) . ')';
//
//            $booking->update([
//                'status'         => 'cancelled',
//                'customer_notes' => $booking->customer_notes
//                    ? $booking->customer_notes . ' | ' . $cancelNote
//                    : $cancelNote,
//            ]);
//
//            /* ── 7. Cancel all passengers ────────────────────── */
//            $updatedPassengers = $booking->passengers()->update([
//                'status' => 'cancelled',
//            ]);
//
//            Log::info('✅ Passengers Cancelled', ['count' => $updatedPassengers]);
//
//            /* ── 8. Transaction — only if paid > 0 ──────────── */
//            $paidAmount = (float) ($booking->paid ?? 0);
//
//            if ($paidAmount > 0 && $booking->user_id) {
//
//                Transaction::create([
//                    'user_id'          => $booking->user_id,
//                    'booking_id'       => $booking->id,
//                    'ref_id'           => $booking->id,
//                    'type'             => 'credit',
//                    'transaction_type' => 'booking_cancelled',
//                    'amount'           => $paidAmount,
//                    'status'           => 'pending',
//                    'reference'        => 'Booking Cancelled - #' . $booking->code,
//                    'remarks'          => 'Booking cancelled. PNR: ' . $booking->pnr_id,
//                    'meta'             => json_encode([
//                        'pnr'          => $booking->pnr_id,
//                        'paid_amount'  => $paidAmount,
//                        'currency'     => 'BDT',
//                        'cancelled_at' => now()->toDateTimeString(),
//                        'cancelled_by' => $cancelledBy ?? auth()->id(),
//                        'source'       => $booking->source,
//                        'gds_response' => $gdsResponse,
//                    ]),
//                    'create_user' => $cancelledBy ?? auth()->id(),
//                ]);
//
//                Log::info('💳 Refund Transaction Created', [
//                    'booking_id' => $booking->id,
//                    'amount'     => $paidAmount,
//                ]);
//
//            } else {
//                Log::info('ℹ️ No transaction — paid is 0 or no user', [
//                    'booking_id' => $booking->id,
//                    'paid'       => $paidAmount,
//                    'user_id'    => $booking->user_id,
//                ]);
//            }
//
//            DB::commit();
//
//            Log::info('✅✅✅ Booking Cancelled Successfully', [
//                'booking_id'         => $booking->id,
//                'pnr'                => $booking->pnr_id,
//                'source'             => $booking->source,
//                'passengers_updated' => $updatedPassengers,
//                'refund_created'     => $paidAmount > 0,
//                'refund_amount'      => $paidAmount,
//                'auto_cancelled'     => $isAuto,
//            ]);
//
//            $msg = "Booking cancelled successfully!\nPNR: {$booking->pnr_id}\nPassengers updated: {$updatedPassengers}";
//            if ($paidAmount > 0) {
//                $msg .= "\nRefund of ৳" . number_format($paidAmount, 2) . " queued for review.";
//            }
//
//            // Auto cancel (Job থেকে call) হলে true return করো
//            if ($isAuto) {
//                return true;
//            }
//
//            return redirect()->back()->with('success', $msg);
//
//        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
//            DB::rollBack();
//            Log::error('❌ Booking Not Found', ['booking_id' => $id]);
//
//            if ($isAuto) {
//                throw $e; // Job retry করবে
//            }
//            return redirect()->back()->with('error', 'Booking not found.');
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('❌❌❌ Booking Cancellation Failed', [
//                'booking_id' => $id,
//                'error'      => $e->getMessage(),
//                'line'       => $e->getLine(),
//                'file'       => $e->getFile(),
//            ]);
//
//            if ($isAuto) {
//                throw $e; // Job retry করবে
//            }
//            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
//        }
//    }
//
//
//
//    public function cancelTickets($id)
//    {
//        Log::info('🔴 Cancel Ticket Request Started', [
//            'booking_id' => $id,
//            'admin_id' => auth()->id()
//        ]);
//
//        try {
//            DB::beginTransaction();
//
//            $booking = Booking::with(['passengers'])->findOrFail($id);
//
//            // ✅ Validate booking status
//            if (!in_array($booking->status, ['ticketed', 'completed', 'booked'])) {
//                throw new \Exception('Booking cannot be cancelled. Current status: ' . $booking->status);
//            }
//
//            // ✅ Check PNR
//            if (!$booking->pnr_id) {
//                throw new \Exception('No PNR found for this booking');
//            }
//
//            // ✅ Check if already cancelled
//            if ($booking->status === 'cancelled') {
//                throw new \Exception('This booking is already cancelled');
//            }
//
//            Log::info('📦 Booking Found', [
//                'booking_id' => $booking->id,
//                'pnr' => $booking->pnr_id,
//                'status' => $booking->status,
//                'passengers' => $booking->passengers->count()
//            ]);
//
//            if ($booking->source === 'sabre') {
//                // ✅ Call Sabre Cancel API
//                $sabreService = new \App\Service\SabreApiService();
//                $sabreResponse = $sabreService->cancelBooking($booking->pnr_id);
//                Log::info('📥 Sabre Response Received', [
//                    'response' => $sabreResponse
//                ]);
//            }
//
//            // ✅ Validate Sabre response
//            if (!$sabreResponse || (isset($sabreResponse['status']) && $sabreResponse['status'] === 'error')) {
//                throw new \Exception('Failed to cancel booking in Sabre: ' . ($sabreResponse['message'] ?? 'Unknown error'));
//            }
//
//            // ✅ Update booking status
//            $booking->update([
//                'status' => 'cancelled',
//                'customer_notes' => ($booking->customer_notes ? $booking->customer_notes . ' | ' : '') .
//                    'Cancelled on ' . now()->format('d M Y, h:i A') . ' by ' . (auth()->user()->name ?? 'Admin')
//            ]);
//
//            // ✅ Update all passengers
//            $updatedPassengers = $booking->passengers()->update([
//                'status' => 'cancelled'
//            ]);
//
//            Log::info('✅ Passengers Updated', [
//                'count' => $updatedPassengers
//            ]);
//
//            // ✅ Create transaction record (for refund tracking)
//            if ($booking->user_id) {
//                Transaction::create([
//                    'user_id' => $booking->user_id,
//                    'booking_id' => $booking->id,
//                    'ref_id' => $booking->id,
//                    'type' => 'credit',
//                    'transaction_type' => 'booking_cancelled',
//                    'amount' => 0, // Will be updated when refund is processed
//                    'status' => 'pending',
//                    'reference' => 'Booking cancelled - #' . $booking->id,
//                    'remarks' => 'Ticket cancelled. PNR: ' . $booking->pnr_id,
//                    'meta' => json_encode([
//                        'pnr' => $booking->pnr_id,
//                        'cancelled_at' => now()->toDateTimeString(),
//                        'cancelled_by' => auth()->id(),
//                        'sabre_response' => $sabreResponse
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                ]);
//            }
//
//            DB::commit();
//
//            Log::info('✅✅✅ Ticket Cancelled Successfully', [
//                'booking_id' => $booking->id,
//                'pnr' => $booking->pnr_id,
//                'passengers_updated' => $updatedPassengers
//            ]);
//
//            return redirect()->back()->with('success',
//                "Ticket cancelled successfully!\n\nPNR: {$booking->pnr_id}\nPassengers: {$updatedPassengers}"
//            );
//
//        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
//            DB::rollBack();
//            Log::error('❌ Booking Not Found', [
//                'booking_id' => $id
//            ]);
//            return redirect()->back()->with('error', 'Booking not found');
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('❌❌❌ Ticket Cancellation Failed', [
//                'booking_id' => $id,
//                'error' => $e->getMessage(),
//                'line' => $e->getLine(),
//                'file' => $e->getFile()
//            ]);
//
//            return redirect()->back()->with('error', 'Failed to cancel ticket: ' . $e->getMessage());
//        }
//    }


    public function cancelBooking($id, $cancelledBy = null)
    {
        $isAuto = $cancelledBy !== null;

        Log::info('🔴 Cancel Booking Request Started', [
            'booking_id' => $id,
            'by' => $cancelledBy ?? auth()->id(),
            'auto' => $isAuto,
        ]);

        try {
            DB::beginTransaction();

            /* ── 1. Load booking ──────────────────────────────── */
            $booking = Booking::with(['passengers'])->findOrFail($id);

            /* ── 2. Guard: already cancelled ─────────────────── */
            if ($booking->status === 'cancelled') {
                throw new \Exception('This booking is already cancelled.');
            }

            /* ── 3. Guard: allowed statuses ──────────────────── */
            // পরে status অনুযায়ী restrict করা হবে
            // $cancellable = ['booked'];
            // if (!in_array($booking->status, $cancellable)) {
            //     throw new \Exception('Booking cannot be cancelled. Current status: ' . $booking->status);
            // }

            /* ── 4. Guard: must have PNR ─────────────────────── */
            if (!$booking->pnr_id) {
                throw new \Exception('No PNR found for this booking.');
            }

            Log::info('📦 Booking Found', [
                'booking_id' => $booking->id,
                'pnr' => $booking->pnr_id,
                'source' => $booking->source,
                'status' => $booking->status,
                'paid' => $booking->paid,
                'passengers' => $booking->passengers->count(),
            ]);

            /* ── 5. GDS cancellation ─────────────────────────── */
            $gdsResponse = null;

            if ($booking->source === 'sabre') {
                $sabreService = new \App\Service\SabreApiService();
                $gdsResponse = $sabreService->cancelBooking($booking->pnr_id);

                Log::info('📥 Sabre Cancel Response', ['response' => $gdsResponse]);

                if (!$gdsResponse || (isset($gdsResponse['status']) && $gdsResponse['status'] === 'error')) {
                    throw new \Exception(
                        'Sabre cancellation failed: ' . ($gdsResponse['message'] ?? 'Unknown error')
                    );
                }

            } elseif ($booking->source === 'travelport') {

                $pnrData = json_decode($booking->pnr_raw_data, true);

                $urLocatorCode = $pnrData['universal_record']['ur_locator_code']
                    ?? $pnrData['universal_record']['locator_code']
                    ?? null;

                if (!$urLocatorCode) {
                    throw new \Exception('Universal Record Locator Code not found in PNR data');
                }

                $cancelService = new TravelPortCancelService();
                $gdsResponse = $cancelService->cancelEntireBooking($urLocatorCode);

                Log::info('📥 Travelport Cancel Response', ['response' => $gdsResponse]);

                if (!$gdsResponse || !($gdsResponse['success'] ?? false)) {
                    throw new \Exception(
                        'Travelport cancellation failed: ' . ($gdsResponse['fault_string'] ?? $gdsResponse['error'] ?? 'Unknown error')
                    );
                }

            } elseif ($booking->source === 'manual') {
                // Manual booking — GDS call লাগবে না
                $gdsResponse = ['success' => true, 'message' => 'Manual booking cancelled locally'];
                Log::info('📥 Manual Booking Cancel (no GDS call)');

            } else {
                // অন্য source — GDS call skip করো, শুধু locally cancel
                $gdsResponse = ['success' => true, 'message' => 'Cancelled locally, source: ' . $booking->source];
                Log::warning('⚠️ Unknown source, cancelling locally', ['source' => $booking->source]);
            }

            /* ── 6. Update booking status ────────────────────── */
            $cancelNote = 'Cancelled on ' . now()->format('d M Y, h:i A') .
                ' by ' . ($cancelledBy ?? auth()->user()->name ?? 'Admin') .
                ' (ID: ' . ($cancelledBy ?? auth()->id()) . ')';

            $booking->update([
                'status' => 'cancelled',
//                'booking_date' => now(),  // cancel date set করো
                'customer_notes' => $booking->customer_notes
                    ? $booking->customer_notes . ' | ' . $cancelNote
                    : $cancelNote,
            ]);

            /* ── 7. Cancel all passengers ────────────────────── */
            $updatedPassengers = $booking->passengers()->update([
                'status' => 'cancelled',
            ]);

            Log::info('✅ Passengers Cancelled', ['count' => $updatedPassengers]);

            /* ── 8. Transaction — only if paid > 0 ──────────── */
            $paidAmount = (float)($booking->paid ?? 0);

            if ($paidAmount > 0 && $booking->customer_id) {

                \Modules\User\Models\Wallet\Transaction::create([
                    'user_id' => $booking->customer_id,
                    'booking_id' => $booking->id,
                    'ref_id' => $booking->id,
                    'type' => 'credit',
                    'transaction_type' => 'booking_cancelled',
                    'amount' => $paidAmount,
                    'status' => 'pending',
                    'reference' => 'Booking Cancelled - #' . $booking->code,
                    'remarks' => 'Booking cancelled. PNR: ' . $booking->pnr_id,
                    'deposit_date' => now(),
                    'meta' => json_encode([
                        'pnr' => $booking->pnr_id,
                        'paid_amount' => $paidAmount,
                        'currency' => 'BDT',
                        'cancelled_at' => now()->toDateTimeString(),
                        'cancelled_by' => $cancelledBy ?? auth()->id(),
                        'source' => $booking->source,
                        'gds_response' => $gdsResponse,
                    ]),
                    'create_user' => $cancelledBy ?? auth()->id(),
                ]);

                Log::info('💳 Refund Transaction Created', [
                    'booking_id' => $booking->id,
                    'amount' => $paidAmount,
                ]);

            } else {
                Log::info('ℹ️ No transaction — paid is 0 or no user', [
                    'booking_id' => $booking->id,
                    'paid' => $paidAmount,
                    'user_id' => $booking->customer_id,
                ]);
            }

            DB::commit();

            Log::info('✅✅✅ Booking Cancelled Successfully', [
                'booking_id' => $booking->id,
                'pnr' => $booking->pnr_id,
                'source' => $booking->source,
                'passengers_updated' => $updatedPassengers,
                'refund_created' => $paidAmount > 0,
                'refund_amount' => $paidAmount,
                'auto_cancelled' => $isAuto,
            ]);

            $msg = "Booking cancelled successfully!\nPNR: {$booking->pnr_id}\nPassengers updated: {$updatedPassengers}";
            if ($paidAmount > 0) {
                $msg .= "\nRefund of ৳" . number_format($paidAmount, 2) . " queued for review.";
            }

            if ($isAuto) {
                return true;
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('❌ Booking Not Found', ['booking_id' => $id]);

            if ($isAuto) throw $e;
            return redirect()->back()->with('error', 'Booking not found.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌❌❌ Booking Cancellation Failed', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            if ($isAuto) throw $e;
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

//    public function cancelTickets($id) {
//        $booking = Booking::where('id', $id)->first();
//
//
//        try {
//            if (!$booking->pnr_id) {
//                return ['status' => 'error', 'message' => 'No Sabre record locator found'];
//            }
//
//            $sabreService = new \App\Service\SabreApiService();
//            $sabreResponse = $sabreService->cancelBooking($booking->pnr_id);
//
//            if ($sabreResponse) {
//                $booking->update(['status' => 'cancelled']);
//
//                Log::info("Sabre booking cancelled successfully", [
//                    'booking_id' => $booking->id,
//                    'record_locator' => $booking->prn_id
//                ]);
//
//                return redirect()->back()->with('success', 'Ticket cancelled successfully.');
////                return [
////                    'status' => 'success',
////                    'message' => 'Booking cancelled successfully',
////                    'data' => $sabreResponse
////                ];
//            } else {
//                return ['status' => 'error', 'message' => 'Failed to cancel booking in Sabre'];
//            }
//        } catch (\Exception $e) {
//            Log::error("Error cancelling Sabre booking: " . $e->getMessage());
//            return ['status' => 'error', 'message' => $e->getMessage()];
//        }
////        $this->cancelSabreBooking($booking);
////        return redirect()->back()->with('success', 'Ticket cancelled successfully.');
//    }

//    public function cancelSabreBooking($booking)
//    {
//        try {
//            if (!$booking->pnr_id) {
//                return ['status' => 'error', 'message' => 'No Sabre record locator found'];
//            }
//
//            $sabreService = new \App\Service\SabreApiService();
//            $sabreResponse = $sabreService->cancelBooking($booking->pnr_id);
//
//            if ($sabreResponse) {
//                $booking->update(['status' => 'cancelled']);
//
//                Log::info("Sabre booking cancelled successfully", [
//                    'booking_id' => $booking->id,
//                    'record_locator' => $booking->prn_id
//                ]);
//
//                return [
//                    'status' => 'success',
//                    'message' => 'Booking cancelled successfully',
//                    'data' => $sabreResponse
//                ];
//            } else {
//                return ['status' => 'error', 'message' => 'Failed to cancel booking in Sabre'];
//            }
//        } catch (\Exception $e) {
//            Log::error("Error cancelling Sabre booking: " . $e->getMessage());
//            return ['status' => 'error', 'message' => $e->getMessage()];
//        }
//    }
}
