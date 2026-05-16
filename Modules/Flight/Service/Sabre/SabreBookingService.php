<?php

namespace Modules\Flight\Service\Sabre;

use App\Service\SabreApiService;
use http\Env\Response;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Service\Sabre\SabreBookingResponseService;
use Modules\Booking\Service\Sabre\SabrePriceCheckPayloadBuilder;
use Modules\Flight\Models\BookingPassengers;
use Modules\Flight\Models\BookingRoutes;
//use Modules\Flight\Service\Sabre\SabreXmlBuilder;
use Modules\Flight\Service\Sabre\SabrePnrBuilder;
use Modules\Flight\Service\Sabre\SabreApiServices;

class   SabreBookingService
{

    private SabrePnrBuilder $pnrBuilder;     // ✅ uncomment করুন
//    private SabreXmlBuilder $xmlBuilder;     // ✅ semicolon আছে
    private SabreApiServices $apiServices;    // ✅ uncomment করুন
    private SabreApiService $apisabre;       // ✅ uncomment করুন

    public function __construct(
        SabrePnrBuilder $pnrBuilder,
//        SabreXmlBuilder $xmlBuilder,
        SabreApiServices $apiServices,
        SabreApiService $apisabre
    )
    {
        $this->pnrBuilder = $pnrBuilder;
//        $this->xmlBuilder = $xmlBuilder;
        $this->apiServices = $apiServices;
        $this->apisabre = $apisabre;
    }
    /**
     * Create PNR in Sabre system
     *
     * @param Booking $booking
     * @return bool
     */
    public function createPnr(Booking $booking): array
    {
        try {
            $passengers = BookingPassengers::where('booking_id', $booking->id)->get();
            $routes = BookingRoutes::where('booking_id', $booking->id)
                ->orderBy('departure_at')
                ->get();

            if ($passengers->isEmpty() || $routes->isEmpty()) {
                return [
                    'success'      => false,
                    'error'        => 'Booking data incomplete.',
                    'booking_code' => $booking->code,
                ];
            }

            // ✅ NDC নাকি ATPCO check করো
            $flightData = session('price_verified') ?? [];
            $isNDC      = $flightData['is_ndc'] ?? false;

            if ($isNDC) {
                return $this->createNdcOrder($booking, $passengers, $flightData);
            } else {
                return $this->createAtpcoPnr($booking, $passengers, $routes);
            }

        } catch (\Exception $e) {
            $this->logError($booking, 'PNR creation exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $booking->update(['pnr_id' => null, 'status' => 'failed']);

            return [
                'success'         => false,
                'error'           => 'Booking system error.',
                'technical_error' => $e->getMessage(),
                'booking_code'    => $booking->code,
            ];
        }
    }

    private function createAtpcoPnr(Booking $booking, $passengers, $routes): array
    {
        $payload = $this->pnrBuilder->build($booking, $passengers, $routes);

        $maxRetries = 1;
        $attempt    = 0;
        $response   = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            $response   = (new \App\Service\SabreApiService())->createBookingPnr($payload);

            $appResults = $response['CreatePassengerNameRecordRS']['ApplicationResults'] ?? null;
            $status     = $appResults['status'] ?? '';
            
            Log::info('ATPCO PNR Response Status', [
    'booking_id'   => $booking->id,
    'booking_code' => $booking->code,
    'status'       => $status,
    'pnr'          => $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'] ?? null,
    'warnings'     => collect($appResults['Warning'] ?? [])
        ->flatMap(fn($w) => collect($w['SystemSpecificResults'] ?? [])
            ->flatMap(fn($s) => collect($s['Message'] ?? [])->pluck('content'))
        )->filter()->values()->toArray(),
    'errors'       => collect($appResults['Error'] ?? [])
        ->flatMap(fn($e) => collect($e['SystemSpecificResults'] ?? [])
            ->flatMap(fn($s) => collect($s['Message'] ?? [])->pluck('content'))
        )->filter()->values()->toArray(),
]);

            if ($status !== 'Incomplete' && isset($response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
                break;
            }

            $warnings = collect($appResults['Warning'] ?? [])
                ->flatMap(fn($w) => collect($w['SystemSpecificResults'] ?? [])
                    ->flatMap(fn($s) => collect($s['Message'] ?? [])->pluck('content'))
                )->filter()->values()->toArray();

            if (!collect($warnings)->contains(fn($w) => str_contains($w, 'status code NN'))) {
                break;
            }

            if ($attempt < $maxRetries) sleep(3);
        }

        $appResults = $response['CreatePassengerNameRecordRS']['ApplicationResults'] ?? null;
        $status     = $appResults['status'] ?? '';

        // ❌ PNR create e fail
        if ($status !== 'Complete' || !isset($response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
            $errorMessages       = $this->extractErrors($response ?? []);
            $userFriendlyMessage = $this->getUserFriendlyErrorMessage($errorMessages);

            $this->logError($booking, 'ATPCO PNR creation failed', [
                'status'   => $status,
                'errors'   => $errorMessages,
                'attempts' => $attempt,
            ]);

            $booking->update(['pnr_id' => null, 'status' => 'failed']);

            return [
                'success'          => false,
                'error'            => $userFriendlyMessage,
                'technical_errors' => $errorMessages,
                'booking_code'     => $booking->code,
            ];
        }

        // ✅ PNR পাওয়া গেছে — সাথে সাথেই booking update করো
        $pnr = $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];

        $booking->update([
            'pnr_id'       => $pnr,
            'status'       => 'booked',
            'confirmed_at' => now(),
            'pnr_raw_data' => json_encode($response), // create response save করো
        ]);

        $booking->passengers()->update([
            'pnr'    => $pnr,
            'status' => 'booked',
        ]);

        Log::info('ATPCO PNR created successfully', [
            'booking_id' => $booking->id,
            'pnr'        => $pnr,
        ]);

        // ✅ getPnr() আলাদাভাবে try করো — fail হলেও booking booked থাকবে
        try {
            $sabreData  = (new \App\Service\SabreApiService())->getPnr($pnr);
            $result     = (new SabreBookingResponseService())->parseGetReservationResponse($sabreData);

            $ticketDate = null;
            if (!empty($result['action_status']['ticket_date'])) {
                $ticketDate = \Carbon\Carbon::parse($result['action_status']['ticket_date'])
                    ->format('Y-m-d H:i:s');
            }

            // ✅ extra data পেলে update করো
            $booking->update([
                'booking_date' => $ticketDate,
                'pnr_raw_data' => json_encode($result),
            ]);

        } catch (\Exception $e) {
            Log::warning('getPnr failed after PNR creation', [
                'booking_id' => $booking->id,
                'pnr'        => $pnr,
                'error'      => $e->getMessage(),
            ]);
            // ✅ কিছুই করার দরকার নেই, booking already booked
        }

        return [
            'success'      => true,
            'pnr'          => $pnr,
            'booking_code' => $booking->code,
            'message'      => 'Booking confirmed successfully',
        ];
    }

    private function createNdcOrder(Booking $booking, $passengers, array $flightData): array
    {
        $offerId      = $flightData['offer_id'] ?? null;
        $offerItemIds = $flightData['offer_item_id'] ?? [];

        if (!$offerId || empty($offerItemIds)) {
            return [
                'success'      => false,
                'error'        => 'NDC offer data not found. Please search again.',
                'booking_code' => $booking->code,
            ];
        }

        $selectedOfferItems = array_map(fn($id) => ['id' => $id], (array)$offerItemIds);

        $firstPax = $passengers->first();
        $phone    = preg_replace('/[^0-9]/', '', $firstPax->phone ?? $booking->phone ?? '01700000000');
        $email    = $booking->email ?? $firstPax->email ?? 'info@test.com';

        $contactInfos     = [];
        $passengerPayload = [];
        $paxIndex         = 1;
        $adultRefId       = null;

        foreach ($passengers as $passenger) {
            $ciId         = 'CI-' . $paxIndex;
            $passengerRef = 'Passenger' . $paxIndex;

            if ($paxIndex === 1) {
                $contactInfos[] = [
                    'id'             => $ciId,
                    'emailAddresses' => [['address' => $email]],
                    'phones'         => [['number'  => $phone]],
                ];
            } else {
                $contactInfos[] = [
                    'id'     => $ciId,
                    'phones' => [['number' => $phone]],
                ];
            }

            $typeCode = match(strtoupper($passenger->traveler_type ?? 'ADULT')) {
                'CHILD', 'CHD', 'CNN' => 'CNN',
                'INFANT', 'INF'       => 'INF',
                default               => 'ADT',
            };

            $isFemale   = stripos($passenger->gender ?? '', 'F') === 0;
            $genderCode = $isFemale ? 'F' : 'M';

            $birthdate = !empty($passenger->dob)
                ? \Carbon\Carbon::parse($passenger->dob)->format('Y-m-d')
                : null;

            $expiryDate = !empty($passenger->passport_expiry_date)
                ? \Carbon\Carbon::parse($passenger->passport_expiry_date)->format('Y-m-d')
                : null;

            $title = strtoupper($passenger->title ?? ($isFemale ? 'MS' : 'MR'));

            $paxData = [
                'id'               => $passengerRef,
                'contactInfoRefId' => $ciId,
                'typeCode'         => $typeCode,
                'givenName'        => $passenger->first_name . ' ' . $title,
                'surname'          => $passenger->last_name,
                'genderCode'       => $genderCode,
            ];

            if ($birthdate) {
                $paxData['birthdate'] = $birthdate;
            }

            if ($typeCode === 'CNN' && $birthdate) {
                $paxData['age'] = \Carbon\Carbon::parse($birthdate)->age;
            }

            if ($typeCode === 'INF' && $adultRefId) {
                $paxData['passengerReference'] = $adultRefId;
            }

            if ($typeCode === 'ADT' && !$adultRefId) {
                $adultRefId = $passengerRef;
            }

            if ($passenger->passport_number && $birthdate && $expiryDate) {
                $paxData['identityDocuments'] = [[
                    'id'                   => 'ID-' . $paxIndex,
                    'documentNumber'       => $passenger->passport_number,
                    'documentTypeCode'     => 'PT',
                    'issuingCountryCode'   => $passenger->country ?? 'BD',
                    'residenceCountryCode' => $passenger->country ?? 'BD',
                    'expiryDate'           => $expiryDate,
                    'birthdate'            => $birthdate,
                    'genderCode'           => $genderCode,
                    'givenName'            => $passenger->first_name . ' ' . $title,
                    'surname'              => $passenger->last_name,
                ]];
            }

            $passengerPayload[] = $paxData;
            $paxIndex++;
        }

        $payload = [
            'contactInfos' => $contactInfos,
            'createOrders' => [[
                'offerId'            => $offerId,
                'selectedOfferItems' => $selectedOfferItems,
            ]],
            'passengers' => $passengerPayload,
        ];

        Log::info('NDC Order Create Payload', $payload);

        $apicall = new \Modules\Api\Sabre\SabreApisServiceClass();

        try {
            $response = $apicall->ndcOrderCreate($payload);
            Log::info('NDC Order Create Response', $response ?? []);

            $order   = $response['order'] ?? null;
            $pnr     = $order['pnrLocator'] ?? null;
            $orderId = $order['id']         ?? null;

            if (!$orderId) {
                $errorMsg = collect($response['errors'] ?? [])
                    ->pluck('message')->implode(', ')
                    ?: 'NDC order creation failed.';

                Log::error('NDC Order Create failed', [
                    'booking_id' => $booking->id,
                    'response'   => $response,
                ]);

                $booking->update(['pnr_id' => null, 'status' => 'failed']);

                return [
                    'success'      => false,
                    'error'        => $errorMsg,
                    'booking_code' => $booking->code,
                ];
            }

            // ✅ Order পাওয়া গেছে — সাথে সাথেই booking update করো
            $booking->update([
                'pnr_id'       => $pnr ?? $orderId,
                'status'       => 'booked',
                'confirmed_at' => now(),
                'pnr_raw_data' => json_encode($response),
            ]);

            $booking->passengers()->update([
                'pnr'    => $pnr ?? $orderId,
                'status' => 'booked',
            ]);

            Log::info('NDC Order created successfully', [
                'booking_id' => $booking->id,
                'order_id'   => $orderId,
                'pnr'        => $pnr,
            ]);

            // ✅ getPnr() আলাদাভাবে try করো — fail হলেও booking booked থাকবে
            try {
                $sabreData  = (new \App\Service\SabreApiService())->getPnr($pnr);
                $result     = (new SabreBookingResponseService())->parseGetReservationResponse($sabreData);

                $ticketDate = null;
                if (!empty($result['action_status']['ticket_date'])) {
                    $ticketDate = \Carbon\Carbon::parse($result['action_status']['ticket_date'])
                        ->format('Y-m-d H:i:s');
                }

                $booking->update([
                    'booking_date' => $ticketDate,
                    'pnr_raw_data' => json_encode($result),
                ]);

            } catch (\Exception $e) {
                Log::warning('getPnr failed after NDC order creation', [
                    'booking_id' => $booking->id,
                    'pnr'        => $pnr,
                    'error'      => $e->getMessage(),
                ]);
                // ✅ booking already booked, কিছু করার দরকার নেই
            }

            return [
                'success'      => true,
                'pnr'          => $pnr,
                'order_id'     => $orderId,
                'booking_code' => $booking->code,
                'message'      => 'NDC booking confirmed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('NDC Order Create exception', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);

            $booking->update(['pnr_id' => null, 'status' => 'failed']);

            return [
                'success'      => false,
                'error'        => $e->getMessage(),
                'booking_code' => $booking->code,
            ];
        }
    }
    private function handlePnrResponse(Booking $booking, ?array $response)
    {
        // Check for successful PNR creation
        if ($this->isPnrCreated($response)) {
            $pnr = $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'] ?? null;

            if (!$pnr) {
                return [
                    'success' => false,
                    'error' => 'PNR creation failed: No PNR ID returned',
                    'booking_code' => $booking->code
                ];
            }

            $booking->update([
                'pnr_id' => $pnr,
                'confirmed_at' => now(),
                'status' => 'booked',
                'pnr_raw_data' => json_encode($response)
            ]);

            Log::info('PNR created successfully', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'pnr' => $pnr
            ]);

            return [
                'success' => true,
                'pnr' => $pnr,
                'booking_code' => $booking->code,
                'message' => 'Booking confirmed successfully'
            ];
        }

        // Handle failure
        $errorMessages = $this->extractErrors($response ?? []);
        $userFriendlyMessage = $this->getUserFriendlyErrorMessage($errorMessages);

        // Get status for logging
        $status = $response['CreatePassengerNameRecordRS']['ApplicationResults']['status'] ?? 'Unknown';

        $this->logError($booking, "PNR creation {$status}", [
            'status' => $status,
            'errors' => $errorMessages,
            'user_message' => $userFriendlyMessage,
            'response' => $response
        ]);

        // Store error details in booking meta
        if (method_exists($booking, 'addMeta')) {
            $booking->addMeta('pnr_error_status', $status);
            $booking->addMeta('pnr_error_messages', $errorMessages);
            $booking->addMeta('pnr_error_response', $response);
            $booking->addMeta('pnr_error_timestamp', now()->toDateTimeString());
        }

        $booking->update([
            'pnr_id' => null,
            'status' => 'failed'
        ]);

        return [
            'success' => false,
            'error' => $userFriendlyMessage,
            'status' => $status,
            'technical_errors' => $errorMessages,
            'booking_code' => $booking->code
        ];


    }


    /**
     * Sabre CreatePassengerNameRecord Response → Clean JSON
     * Usage: return response()->json($this->parseSabrePnrResponse($sabreResponse));
     */
    private function parseSabrePnrResponse(array $response): array
    {
        $rs = $response['CreatePassengerNameRecordRS'] ?? [];

        // ── 1. Status ────────────────────────────────────────────────────────────
        $appResult = $rs['ApplicationResults'] ?? [];
        $status = $appResult['status'] ?? 'Unknown';
        $success = strtolower($status) === 'complete';

        // warnings
        $warnings = [];
        foreach ($appResult['Warning'] ?? [] as $w) {
            foreach ($w['SystemSpecificResults'] ?? [] as $sr) {
                foreach ($sr['Message'] ?? [] as $msg) {
                    $warnings[] = $msg['content'] ?? '';
                }
            }
        }

        // ── 2. PNR / ItineraryRef ────────────────────────────────────────────────
        $pnr = $rs['ItineraryRef']['ID'] ?? null;

        // also try from EndTransaction inside Diagnostics
        if (!$pnr) {
            $frames = $rs['Diagnostics']['TraceData']['Frame']['Frame'] ?? [];
            foreach ($frames as $frame) {
                if (($frame['name'] ?? '') === 'EndTransactionLLSRQ') {
                    foreach ($frame['Item'] ?? [] as $item) {
                        if (($item['Code'] ?? '') === 'RESPONSE') {
                            if (preg_match('/ItineraryRef ID="([A-Z0-9]+)"/', $item['Message'] ?? '', $m)) {
                                $pnr = $m[1];
                            }
                        }
                    }
                }
            }
        }

        // ── 3. Passenger Info ────────────────────────────────────────────────────
        $travelItinerary = $rs['TravelItineraryRead']['TravelItinerary'] ?? [];
        $customerInfo = $travelItinerary['CustomerInfo'] ?? [];
        $passengers = [];

        foreach ($customerInfo['PersonName'] ?? [] as $p) {
            $passengers[] = [
                'name_number' => $p['NameNumber'] ?? null,
                'given_name' => $p['GivenName'] ?? null,
                'surname' => $p['Surname'] ?? null,
                'passenger_type' => $p['PassengerType'] ?? 'ADT',
                'email' => $p['Email'][0]['content'] ?? null,
            ];
        }

        // phone
        $phones = [];
        foreach ($customerInfo['ContactNumbers']['ContactNumber'] ?? [] as $c) {
            $phones[] = [
                'phone' => $c['Phone'] ?? null,
                'location_code' => $c['LocationCode'] ?? null,
            ];
        }

        // ── 4. Flight Segments (from ReservationItems) ───────────────────────────
        $segments = [];
        $items = $travelItinerary['ItineraryInfo']['ReservationItems']['Item'] ?? [];

        foreach ($items as $item) {
            $fs = $item['FlightSegment'][0] ?? [];
            $prd = $item['Product']['ProductDetails']['Air'] ?? [];

            $segments[] = [
                'segment_number' => $fs['SegmentNumber'] ?? null,
                'flight_number' => $fs['FlightNumber'] ?? null,
                'airline_code' => $fs['MarketingAirline']['Code'] ?? null,
                'airline_name' => $fs['MarketingAirline']['Banner'] ?? null,
                'operating_airline' => $fs['OperatingAirline'][0]['Code'] ?? null,
                'origin' => $fs['OriginLocation']['LocationCode'] ?? null,
                'destination' => $fs['DestinationLocation']['LocationCode'] ?? null,
                'departure_datetime' => $fs['DepartureDateTime'] ?? null,
                'arrival_datetime' => $fs['ArrivalDateTime'] ?? null,
                'booking_class' => $fs['ResBookDesigCode'] ?? null,
                'status' => $fs['Status'] ?? null,
                'e_ticket' => $fs['eTicket'] ?? false,
                'cabin' => [
                    'code' => $fs['Cabin']['Code'] ?? null,
                    'name' => $fs['Cabin']['Name'] ?? null,
                ],
                'equipment' => $fs['Equipment']['AirEquipType'] ?? null,
                'elapsed_time' => $fs['ElapsedTime'] ?? null,
                'air_miles' => $fs['AirMilesFlown'] ?? null,
                'meal_codes' => array_column($fs['Meal'] ?? [], 'Code'),
                'supplier_ref' => $fs['SupplierRef']['ID'] ?? null,   // Airline PNR ref
                'segment_booked_date' => $fs['SegmentBookedDate'] ?? null,
            ];
        }

        // ── 5. Air Book Segments (quick view) ────────────────────────────────────
        $airBookSegments = [];
        $abFlights = $rs['AirBook']['OriginDestinationOption']['FlightSegment'] ?? [];
        foreach ($abFlights as $f) {
            $airBookSegments[] = [
                'flight_number' => $f['FlightNumber'] ?? null,
                'airline_code' => $f['MarketingAirline']['Code'] ?? null,
                'origin' => $f['OriginLocation']['LocationCode'] ?? null,
                'destination' => $f['DestinationLocation']['LocationCode'] ?? null,
                'departure_datetime' => $f['DepartureDateTime'] ?? null,
                'arrival_datetime' => $f['ArrivalDateTime'] ?? null,
                'booking_class' => $f['ResBookDesigCode'] ?? null,
                'status' => $f['Status'] ?? null,
                'e_ticket' => $f['eTicket'] ?? false,
            ];
        }

        // ── 6. Fare / Price ──────────────────────────────────────────────────────
        $priceData = $rs['AirPrice'][0]['PriceQuote'] ?? [];
        $solInfo = $priceData['MiscInformation']['SolutionInformation'][0] ?? [];
        $itinFare = $priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['ItinTotalFare'] ?? [];

        // taxes breakdown
        $taxes = [];
        foreach ($itinFare['Taxes']['Tax'] ?? [] as $t) {
            $taxes[] = [
                'tax_code' => $t['TaxCode'] ?? null,
                'tax_name' => $t['TaxName'] ?? null,
                'amount' => $t['Amount'] ?? null,
                'currency' => 'BDT',
            ];
        }

        $fare = [
            'base_fare_usd' => (float)($itinFare['BaseFare']['Amount'] ?? 0),
            'equiv_fare_bdt' => (int)($itinFare['EquivFare']['Amount'] ?? 0),
            'total_taxes_bdt' => (int)($itinFare['Taxes']['TotalAmount'] ?? 0),
            'total_fare_bdt' => (int)($itinFare['TotalFare']['Amount'] ?? 0),
            'currency' => 'BDT',
            'base_currency' => 'USD',
            'validating_carrier' => $priceData['MiscInformation']['HeaderInformation'][0]['ValidatingCarrier']['Code'] ?? null,
            'non_refundable' => ($itinFare['NonRefundableInd'] ?? '') === 'O',
            'endorsements' => $itinFare['Endorsements']['Text'] ?? [],
            'fare_calculation' => $priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['FareCalculation']['Text'] ?? null,
            'taxes_breakdown' => $taxes,
        ];

        // brand fare info
        $brandFare = null;
        foreach ($priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['PTC_FareBreakdown'] ?? [] as $ptc) {
            if (!empty($ptc['BrandedFareInformation'])) {
                $brandFare = [
                    'brand_code' => $ptc['BrandedFareInformation']['BrandCode'] ?? null,
                    'brand_name' => $ptc['BrandedFareInformation']['BrandName'] ?? null,
                    'program_code' => $ptc['BrandedFareInformation']['ProgramCode'] ?? null,
                    'program_name' => $ptc['BrandedFareInformation']['ProgramName'] ?? null,
                    'fare_basis' => $ptc['FareBasis']['Code'] ?? null,
                    'fare_amount' => $ptc['FareBasis']['FareAmount'] ?? null,
                    'cabin' => $ptc['Cabin'] ?? null,
                ];
                break;
            }
        }

        // ── 7. Baggage ───────────────────────────────────────────────────────────
        $baggage = [];
        foreach ($priceData['MiscInformation']['BaggageInfo']['SubCodeProperties'] ?? [] as $b) {
            $baggage[] = [
                'type' => $b['CommercialNameofBaggageItemType'] ?? null,
                'weight_kg' => null, // will be filled from BaggageProvisions
                'carry_on' => str_contains(strtolower($b['CommercialNameofBaggageItemType'] ?? ''), 'carry'),
            ];
        }

        // baggage weight from BaggageProvisions
        foreach ($priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['BaggageProvisions'] ?? [] as $bp) {
            $weight = $bp['WeightLimit']['content'] ?? null;
            $unit = $bp['WeightLimit']['Units'] ?? 'K';
            $provision = $bp['ProvisionType'] ?? '';

            if ($provision === 'A') {
                // checked baggage
                foreach ($baggage as &$b) {
                    if (!$b['carry_on'] && $b['weight_kg'] === null) {
                        $b['weight_kg'] = (int)$weight;
                        $b['weight_unit'] = $unit === 'K' ? 'kg' : 'lbs';
                    }
                }
            } elseif ($provision === 'B') {
                // carry-on
                foreach ($baggage as &$b) {
                    if ($b['carry_on'] && $b['weight_kg'] === null) {
                        $b['weight_kg'] = (int)$weight;
                        $b['weight_unit'] = $unit === 'K' ? 'kg' : 'lbs';
                    }
                }
            }
            unset($b);
        }

        // ── 8. Special Services (SSR) ────────────────────────────────────────────
        $ssrList = [];
        foreach ($travelItinerary['SpecialServiceInfo'] ?? [] as $ssr) {
            $svc = $ssr['Service'] ?? [];
            $ssrList[] = [
                'ssr_type' => $svc['SSR_Type'] ?? null,
                'airline' => $svc['Airline']['Code'] ?? null,
                'text' => $svc['Text'][0] ?? null,
                'passenger' => $svc['PersonName'][0]['content'] ?? null,
                'status_text' => $ssr['Type'] ?? null,
            ];
        }

        // ── 9. Ticketing Info ────────────────────────────────────────────────────
        $ticketing = [];
        foreach ($travelItinerary['ItineraryInfo']['Ticketing'] ?? [] as $t) {
            $ticketing[] = [
                'rph' => $t['RPH'] ?? null,
                'ticket_time_limit' => $t['TicketTimeLimit'] ?? null,
            ];
        }

        // ── 10. Itinerary Reference / Creation Info ──────────────────────────────
        $itinRef = $travelItinerary['ItineraryRef'] ?? [];
        $source = $itinRef['Source'] ?? [];

        $creationInfo = [
            'pnr' => $itinRef['ID'] ?? $pnr,
            'partition_id' => $itinRef['PartitionID'] ?? null,
            'prime_host_id' => $itinRef['PrimeHostID'] ?? null,
            'pcc' => $source['AAA_PseudoCityCode'] ?? null,
            'home_pcc' => $source['HomePseudoCityCode'] ?? null,
            'creation_datetime' => $source['CreateDateTime'] ?? null,
            'creation_agent' => $source['CreationAgent'] ?? null,
            'last_update' => $source['LastUpdateDateTime'] ?? null,
            'received_from' => $source['ReceivedFrom'] ?? null,
        ];

        // ── 11. Price Quote Summary ──────────────────────────────────────────────
        $pqTotals = $travelItinerary['ItineraryPricing']['PriceQuoteTotals'] ?? [];
        $pqSummary = [
            'base_fare_usd' => $pqTotals['BaseFare']['Amount'] ?? null,
            'equiv_fare_bdt' => $pqTotals['EquivFare']['Amount'] ?? null,
            'taxes_bdt' => $pqTotals['Taxes']['Tax']['Amount'] ?? null,
            'total_fare_bdt' => $pqTotals['TotalFare']['Amount'] ?? null,
            'currency' => 'BDT',
        ];

        // ── Airline PNRs per segment ─────────────────────────
        $airlinePnrs = [];
        foreach ($segments as $seg) {
            $airlinePnrs[] = [
                'flight_number' => $seg['flight_number'],
                'airline_code'  => $seg['airline_code'],
                'airline_ref'   => $seg['supplier_ref'],  // e.g. "DCGF"
            ];
        }

        // passenger type from price quote
        $pqPassenger = $travelItinerary['ItineraryPricing']['PriceQuote'][0] ?? [];
        $pqPlus = $pqPassenger['PriceQuotePlus'] ?? [];
        $pricedItins = $pqPassenger['PricedItinerary'] ?? [];

        // ── 12. Assemble Final JSON ──────────────────────────────────────────────
        return [
            'success' => $success,
            'status' => $status,
            'pnr' => $pnr,
            'warnings' => $warnings,

            'creation_info' => $creationInfo,

            'passengers' => $passengers,
            'phones' => $phones,

            'flights' => $segments,       // detailed from TravelItineraryRead
            'air_book' => $airBookSegments, // quick view from AirBook

            'fare' => $fare,
            'brand_fare' => $brandFare,
            'baggage' => $baggage,
            'price_summary' => $pqSummary,

            'ticketing' => $ticketing,
            'ssr' => $ssrList,

            'passenger_type' => [
                'code' => $priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['PassengerTypeQuantity']['Code'] ?? 'ADT',
                'quantity' => $priceData['PricedItinerary']['AirItineraryPricingInfo'][0]['PassengerTypeQuantity']['Quantity'] ?? 1,
            ],

            'pricing_metadata' => [
                'pricing_status' => $pqPlus['PricingStatus'] ?? null,
                'system_indicator' => $pqPlus['SystemIndicator'] ?? null,
                'it_bt_fare' => $pqPlus['IT_BT_Fare'] ?? null,
                'domestic_intl' => $pqPlus['DomesticIntlInd'] ?? null,
                'negotiated_fare' => $pqPlus['NegotiatedFare'] ?? false,
                'requires_rebook' => ($solInfo['RequiresRebook'] ?? 'false') === 'true',
            ],
        ];
    }
    private function getUserFriendlyErrorMessage(array $errorMessages): string
    {
        if (empty($errorMessages)) {
            return 'বুকিং সম্পন্ন হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।';
        }

        $flattenedErrors = [];
        foreach ($errorMessages as $error) {
            if (is_array($error)) {
                $flattenedErrors = array_merge($flattenedErrors, array_values($error));
            } else {
                $flattenedErrors[] = $error;
            }
        }

        $fullErrorText = implode(' ', $flattenedErrors);

        if (stripos($fullErrorText, 'CHECK FLIGHT NUMBER') !== false) {
            return 'এই ফ্লাইটটি এই মুহূর্তে বুক করা সম্ভব হচ্ছে না। সিট শেষ হয়ে গেছে বা ফ্লাইটটি আর উপলব্ধ নেই। অনুগ্রহ করে অন্য ফ্লাইট সিলেক্ট করুন।';
        }

        if (stripos($fullErrorText, 'FORMAT') !== false) {
            return 'ফ্লাইট বুকিং format error। অনুগ্রহ করে অন্য ফ্লাইট সিলেক্ট করুন।';
        }

        if (stripos($fullErrorText, 'Unable to perform air booking') !== false) {
            return 'ফ্লাইট বুকিং সম্পন্ন হয়নি। সিট শেষ হয়ে গেছে বা ফ্লাইটটি আর উপলব্ধ নেই। অনুগ্রহ করে অন্য ফ্লাইট সিলেক্ট করুন।';
        }

        if (stripos($fullErrorText, 'FLIGHT NOOP') !== false ||
            stripos($fullErrorText, 'NO FLIGHT') !== false ||
            stripos($fullErrorText, 'FLIGHT NOT FOUND') !== false) {
            return 'সিলেক্ট করা ফ্লাইটটি আর উপলব্ধ নেই। অনুগ্রহ করে অন্য ফ্লাইট খুঁজুন।';
        }

        if (stripos($fullErrorText, 'SEGMENT NUMBER NOT VALID') !== false ||
            stripos($fullErrorText, 'INVALID SEGMENT') !== false) {
            return 'ফ্লাইট route information সঠিক নয়। অনুগ্রহ করে অন্য ফ্লাইট সিলেক্ট করুন।';
        }

        if (stripos($fullErrorText, 'SYSTEM UNABLE TO PROCESS') !== false ||
            stripos($fullErrorText, 'UNABLE TO PROCESS') !== false ||
            stripos($fullErrorText, 'HOST.ERROR') !== false) {
            return 'Airline সার্ভার এই মুহূর্তে সাড়া দিচ্ছে না। কিছুক্ষণ পর আবার চেষ্টা করুন।';
        }

        if (stripos($fullErrorText, 'NO SEATS AVAILABLE') !== false ||
            stripos($fullErrorText, 'SOLD OUT') !== false ||
            stripos($fullErrorText, 'WAITLIST') !== false) {
            return 'এই ফ্লাইটে কোনো সিট নেই। অনুগ্রহ করে অন্য ফ্লাইট সিলেক্ট করুন।';
        }

        if (stripos($fullErrorText, 'PRICE') !== false &&
            stripos($fullErrorText, 'ERROR') !== false) {
            return 'ফ্লাইটের মূল্য নিশ্চিত করা যাচ্ছে না। অনুগ্রহ করে আবার সার্চ করুন।';
        }

        if (stripos($fullErrorText, 'TIMEOUT') !== false ||
            stripos($fullErrorText, 'TIME OUT') !== false) {
            return 'বুকিং request timeout হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
        }

        if (stripos($fullErrorText, 'PASSENGER') !== false ||
            stripos($fullErrorText, 'NAME') !== false) {
            return 'Passenger information সঠিক নয়। অনুগ্রহ করে তথ্য যাচাই করে আবার চেষ্টা করুন।';
        }

        $firstError = $flattenedErrors[0] ?? 'Unknown error';
        return "বুকিং সম্পন্ন হয়নি: {$firstError}। অনুগ্রহ করে আবার চেষ্টা করুন।";
    }
    /**
     * Check if PNR was successfully created
     *
     * @param array|null $response
     * @return bool
     */
//    private function isPnrCreated(?array $response): bool
//    {
//        return $response
//            && isset($response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])
//            && !empty($response['CreatePassengerNameRecordRS']['ItineraryRef']['ID']);
//    }

    private function isPnrCreated(?array $response): bool
    {
        if (!$response) {
            return false;
        }

        // Check status
        $status = $response['CreatePassengerNameRecordRS']['ApplicationResults']['status'] ?? null;

        if ($status !== 'Complete') {
            return false;
        }

        // Check if PNR ID exists
        $pnr = $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID'] ?? null;

        return !empty($pnr);
    }

    /**
     * Extract error messages from Sabre response
     *
     * @param array $response
     * @return array
     */
//    private function extractErrors(array $response): array
//    {
//        $errors = [];
//
//        // Check for application results
//        if (isset($response['CreatePassengerNameRecordRS']['ApplicationResults'])) {
//            $results = $response['CreatePassengerNameRecordRS']['ApplicationResults'];
//
//            if (isset($results['Error'])) {
//                $errorList = is_array($results['Error']) ? $results['Error'] : [$results['Error']];
//
//                foreach ($errorList as $error) {
//                    $errors[] = [
//                        'type' => $error['type'] ?? 'Unknown',
//                        'code' => $error['SystemSpecificResults'][0]['ShortText'] ?? 'N/A',
//                        'message' => $error['SystemSpecificResults'][0]['Message'] ?? 'Unknown error'
//                    ];
//                }
//            }
//        }
//
//        // Check for warnings
//        if (isset($response['CreatePassengerNameRecordRS']['ApplicationResults']['Warning'])) {
//            $warnings = $response['CreatePassengerNameRecordRS']['ApplicationResults']['Warning'];
//            $warningList = is_array($warnings) && isset($warnings[0]) ? $warnings : [$warnings];
//
//            foreach ($warningList as $warning) {
//                $errors[] = [
//                    'type' => 'Warning',
//                    'code' => $warning['SystemSpecificResults'][0]['ShortText'] ?? 'N/A',
//                    'message' => $warning['SystemSpecificResults'][0]['Message'] ?? 'Unknown warning'
//                ];
//            }
//        }
//
//        return $errors ?: [['type' => 'Unknown', 'code' => 'N/A', 'message' => 'Failed to create PNR']];
//    }
    private function extractErrors(array $response): array
    {
        $errors = [];

        // Check application results
        $appResults = $response['CreatePassengerNameRecordRS']['ApplicationResults'] ?? [];
        $status = $appResults['status'] ?? null;

        // ✅ Handle Incomplete status
        if ($status === 'Incomplete') {
            // Check for errors
            if (isset($appResults['Error'])) {
                $errorList = $appResults['Error'];
                if (!is_array($errorList) || isset($errorList['type'])) {
                    $errorList = [$errorList];
                }

                foreach ($errorList as $error) {
                    $systemResults = $error['SystemSpecificResults'] ?? [];

                    // Handle array of system results
                    if (!isset($systemResults[0])) {
                        $systemResults = [$systemResults];
                    }

                    foreach ($systemResults as $result) {
                        $messages = $result['Message'] ?? [];

                        // Handle array of messages
                        if (!isset($messages[0])) {
                            $messages = [$messages];
                        }

                        foreach ($messages as $message) {
                            if (isset($message['content'])) {
                                $errors[] = $message['content'];
                            }
                        }
                    }
                }
            }

            // Check for warnings
            if (isset($appResults['Warning'])) {
                $warningList = $appResults['Warning'];
                if (!is_array($warningList) || isset($warningList['type'])) {
                    $warningList = [$warningList];
                }

                foreach ($warningList as $warning) {
                    $systemResults = $warning['SystemSpecificResults'] ?? [];

                    if (!isset($systemResults[0])) {
                        $systemResults = [$systemResults];
                    }

                    foreach ($systemResults as $result) {
                        $messages = $result['Message'] ?? [];

                        if (!isset($messages[0])) {
                            $messages = [$messages];
                        }

                        foreach ($messages as $message) {
                            if (isset($message['content'])) {
                                $errors[] = $message['content'];
                            }
                        }
                    }
                }
            }
        }

        // ✅ Handle NotProcessed status
        if ($status === 'NotProcessed') {
            if (isset($appResults['Error'])) {
                $errorList = $appResults['Error'];
                if (!is_array($errorList) || isset($errorList['type'])) {
                    $errorList = [$errorList];
                }

                foreach ($errorList as $error) {
                    $systemResults = $error['SystemSpecificResults'] ?? [];

                    if (!isset($systemResults[0])) {
                        $systemResults = [$systemResults];
                    }

                    foreach ($systemResults as $result) {
                        // Extract Message field
                        if (isset($result['Message'])) {
                            $errors[] = $result['Message'];
                        }
                        // Extract ShortText field
                        if (isset($result['ShortText'])) {
                            $errors[] = $result['ShortText'];
                        }
                    }
                }
            }
        }

        // Remove duplicates and clean
        $errors = array_unique(array_filter($errors));

        return array_values($errors);
    }
    /**
     * Log error with context
     *
     * @param Booking $booking
     * @param string $message
     * @param array $context
     * @return void
     */
//    private function logError(Booking $booking, string $message, array $context = []): void
//    {
//        Log::error($message, array_merge([
//            'booking_id' => $booking->id,
//            'booking_code' => $booking->code,
//        ], $context));
//    }
    private function logError(Booking $booking, string $message, array $context = [])
    {
        Log::error($message, array_merge([
            'booking_id' => $booking->id,
            'booking_code' => $booking->code,
        ], $context));
    }

    /**
     * Issue ticket for a booking
     * TODO: Implement when Sabre ticket issuance API is integrated
     *
     * @param Booking $booking
     * @return bool
     */
    public function issueTicket(Booking $booking): bool
    {
        // Placeholder for future implementation
        throw new \Exception('Ticket issuance not yet implemented');
    }

    /**
     * Void a ticket
     * TODO: Implement when Sabre void API is integrated
     *
     * @param Booking $booking
     * @return bool
     */
    public function voidTicket(Booking $booking): bool
    {
        // Placeholder for future implementation
        throw new \Exception('Ticket void not yet implemented');
    }

    /**
     * Cancel PNR
     * TODO: Implement when Sabre cancellation API is integrated
     *
     * @param Booking $booking
     * @return bool
     */
    public function cancelPnr(Booking $booking): bool
    {
        // Placeholder for future implementation
        throw new \Exception('PNR cancellation not yet implemented');
    }
    public function getPnr($pnr): bool
    {
        // Placeholder for future implementation
        throw new \Exception('PNR cancellation not yet implemented');
    }
}
