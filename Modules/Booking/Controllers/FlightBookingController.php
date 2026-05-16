<?php

namespace Modules\Booking\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\SelectSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingReissue;
use Modules\Booking\Service\Sabre\SabreItineraryParser;
use Modules\Booking\Service\Sabre\SabrePriceCheckPayloadBuilder;
use Modules\Booking\Service\Sabre\SabreRevalidateBuilder;
use Modules\Booking\Service\TravelPort\TravelportAirPriceReqBuilder;
use Modules\Booking\Service\TravelPort\TravelportPriceResponseParser;
use Modules\Flight\Models\Airline;
use Modules\Flight\Service\AirArabia\AirArabiaFlightService;
use Modules\Flight\Service\AirArabia\AirArabiaPriceParser;
use Modules\Flight\Service\AirArabia\AirArabiaService;
use Modules\Flight\Service\AirArabia\AirArabiaXmlBuildService;
use Modules\Flight\Service\FlightChargesService;
use Modules\Flight\Service\FlightDiscountService;
use Modules\Flight\Service\TravelPort\TravelPortApiService;
use mysql_xdevapi\Result;

class FlightBookingController extends Controller
{
    public function flightToCart(Request $request)
    {
        $flight      = $request->flight;
//        return $flight;
        $reissueData = session('reissue_data');

        session([
            'selected_flight'          => $flight,
            'selected_at'              => now(),
            'service_type'             => 'flight',
            'booking_timer_expires_at' => now()->addMinutes(30),
        ]);

        SelectSession::log([
            'type'       => 'flight_selected',
            'flight'     => $flight,
            'selected_at'=> now()->toDateTimeString(),
            'source'     => $flight['source'] ?? 'unknown',
        ]);

        // ✅ Reissue flow — price check নেই, শুধু save করো
        if (!empty($reissueData)) {
            $reissueId = $reissueData['reissue_id'] ?? null;

            if ($reissueId && $flight) {
                BookingReissue::where('id', $reissueId)->update([
                    'new_flight_details' => json_encode($flight),
                    'status'             => 'selected',
                ]);
            }

            session()->forget(['reissue_data', 'reissue_search_params']);

            return response()->json([
                'success'      => true,
                'redirect_url' => route('user.reissue.index'),
            ]);
        }
//return $flight;
        $source = $flight['source'] ?? '';
        if (($flight['source'] ?? '') == 'travelport') {
            $builder    = new TravelportAirPriceReqBuilder($flight, env('TRAVELPORT_TARGET_BRANCH'));
            $xml        = $builder->build();

            $apiService = new TravelPortApiService();
            $response   = $apiService->priceFlight($xml);

            $sessionBrandName = $flight['passengers'][0]['brand']['name'] ?? '';
            $sessionApiPrice  = (int) round($flight['price']['api_subtotal']);
            $selectedKey      = $flight['price_point']['key'];

            $parser = new TravelportPriceResponseParser($response);
            $result = $parser->checkAndPrepare($sessionApiPrice, $selectedKey, $sessionBrandName);

            if ($result['status'] === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => 'Price check ব্যর্থ হয়েছে। পুনরায় চেষ্টা করুন।',
                ], 200); // ✅ 500 → 200
            }

            if ($result['status'] === 'mismatch') {
                return response()->json([
                    'success'       => false,
                    'price_changed' => true,
                    'message'       => 'মূল্য পরিবর্তন হয়েছে। নতুন করে সার্চ করুন।',
                    'old_price'     => $flight['price']['total'],
                    'new_api_price' => $result['new_price'],
                ], 200); // ✅ 409 → 200
            }

            session(['price_verified' => $result['data']]);
        }
        // ── Air Arabia ────────────────────────────────────────────────────────
        elseif ($source === 'air_arabia') {
            // Air Arabia block এর শুরুতে
            session()->forget(['selected_flight', 'price_verified', 'air_arabia_jsession_id', 'air_arabia_transaction_id']);
            $xmlBuilder = new AirArabiaXmlBuildService();
            $xml        = $xmlBuilder->buildGetPriceXmlFromFlight($flight);

            $service  = new AirArabiaService();
            $priceXml = $service->getPrice($xml);

            $session = $service->getSession();
            session([
                'air_arabia_jsession_id'    => $session['jsessionId'],
                'air_arabia_transaction_id' => $session['transactionId'],
            ]);

            $parser = new AirArabiaPriceParser($flight);
            $result = $parser->checkAndPrepare($priceXml);
//return $result['data'];
            if ($result['status'] === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => 'Price check ব্যর্থ হয়েছে। পুনরায় চেষ্টা করুন।',
                ], 500);
            }

            // ✅ Full data session এ রাখো
            session([
                'price_verified'  => $result['data'],
                'selected_flight' => $result['data'],
            ]);

            // ✅ Full data frontend এ পাঠাও
            return response()->json([
                'success'      => false,
                'air_arabia'   => true,
                'old_price'    => $flight['price']['total'],
                'data'         => $result['data'],
                'redirect_url' => route('flightCheckout'),
            ], 200);
        }

        // ── Sabre ─────────────────────────────────────────────────────────
        elseif ($source === 'sabre') {
            $pricePayloadBuilder = new SabrePriceCheckPayloadBuilder();

            try {
                $result = $pricePayloadBuilder->getPriceForBooking($flight);
//                return $result;
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                // JSON error হলে clean message দাও
                if (str_contains($msg, 'NotProcessed') || str_contains($msg, 'INVALIDREQ')) {
                    $msg = 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না। অনুগ্রহ করে আবার চেষ্টা করুন।';
                }
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 200);
            }

            if (!isset($result['status']) || $result['status'] === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না। নতুন করে সার্চ করুন।',
                ], 200);
            }

            session([
                'price_verified'  => $result['data'],
                'selected_flight' => $result['data'],
            ]);

            $oldPrice     = $flight['price']['total'];
            $newPrice     = $result['data']['price']['total'];
            $priceChanged = $result['data']['price_changed'] ?? false;

            if ($result['data']['is_ndc'] ?? false) {
                return response()->json([
                    'success'      => false,
                    'sabre_price'  => true,  // ← existing flag
                    'old_price'    => $oldPrice,
                    'new_price'    => $newPrice,
                    'data'         => $result['data'],
                    'redirect_url' => route('flightCheckout'),
                ], 200);
            }

// ATPCO logic (existing)
            if (!$priceChanged) {
                return response()->json([
                    'success'      => true,
                    'redirect_url' => route('flightCheckout'),
                ], 200);
            }

            return response()->json([
                'success'      => false,
                'sabre_price'  => true,
                'old_price'    => $oldPrice,
                'new_price'    => $newPrice,
                'data'         => $result['data'],
                'redirect_url' => route('flightCheckout'),
            ], 200);
//        }
//            =====================================
//            $pcc     = env('SABRE_PCC');                           // .env থেকে PCC নাও
//            $builder = new SabreRevalidateBuilder($flight, $pcc);
//            $payload = $builder->build();
////            return $payload;
//            $sabreService = new \App\Service\SabreApiService();
//            $response     = $sabreService->revalidateItinerary($payload);
////return response()->json($response, 200);
//            $parser = new SabreItineraryParser($response, $flight);
//            $result = $parser->checkAndPrepare();
//            if ($result['status'] === 'error') {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Sabre revalidation ব্যর্থ হয়েছে। পুনরায় চেষ্টা করুন।',
//                ], 500);
//            }
//
//            if ($result['status'] === 'mismatch') {
//                return response()->json([
//                    'success'       => false,
//                    'price_changed' => true,
//                    'message'       => 'মূল্য পরিবর্তন হয়েছে। নতুন করে সার্চ করুন।',
//                    'old_price'     => $flight['price']['total'],
//                    'new_api_price' => $result['new_price'],
//                ], 409);
//            }

//             ✅ Revalidation সফল — verified data session এ রাখো
//            session(['price_verified' => $result['data']]);
        }
//
        return response()->json([
            'success'      => true,
            'message'      => 'Flight added to cart',
            'redirect_url' => route('flightCheckout'),
        ]);
    }
    public function flightCheckout()
    {
        if (!Auth::check()) {
            return redirect()->back()->with('message', 'You need to login first');
        }
//
//        $priceVerified = session('price_verified');
//        return $priceVerified;
        $selectedFlight = session('selected_flight');
        $searchParams = session('flight_search_params');
//return $searchParams;
        $countries = Cache::remember('countries', 60 * 24, fn() =>
        Country::orderBy('name')->get()
        );

        $remainingSeconds = max(0, now()->diffInSeconds(session('booking_timer_expires_at'), false));

        return view('Booking::frontend.flight_booking.checkout', compact(
            'selectedFlight', 'remainingSeconds', 'searchParams', 'countries'
        ));
    }

    public function saveDraft(Request $request)
    {
        session(['passenger_draft' => $request->all()]);
        return response()->json(['ok' => true]);
    }
    public function confirmation($bookingCode)
    {
        $booking = Booking::where('id', $bookingCode)
            ->select('total','source','code','status','customer_id','id','base_fee','total_fee',
                'ticketing_fee','supplier_fee','flight_discount','segment_discount','pnr_raw_data',
                'first_name','last_name','email','phone','address','address2',
                'city','state','zip_code','country','customer_notes')
            ->firstOrFail();

        if ($booking->customer_id !== auth()->id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        $flightData = json_decode($booking->pnr_raw_data, true);

        // Mail পাঠান
        $customerEmail = auth()->user()->email ?? null;


        $customerEmail = auth()->user()->email ?? null;
        if ($customerEmail && !empty($flightData)) {

            // PDF generate করুন
            $pdf = Pdf::loadView('Booking::emails.parts.panel-flight-confirmation', [
                'booking'    => $booking,
                'flightData' => $flightData,
                'logoUrl'    => get_file_url(setting_item('logo_id'), 'full'),
            ])->setPaper('a4', 'portrait');

            \Mail::to($customerEmail)->send(
                (new \Modules\Booking\Emails\NewBookingEmail($booking, 'customer', [
                    'flightData' => $flightData,
                    'logoUrl'    => get_file_url(setting_item('logo_id'), 'full'),
                ]))->attachData(
                    $pdf->output(),
                    'Booking-' . $booking->code . '.pdf',
                    ['mime' => 'application/pdf']
                )
            );
        }

        return view('Booking::frontend.flight_booking.confirmation', compact(
            'booking',
            'flightData'
        ));
    }
}
