<?php

namespace Modules\Flight\Service\AirArabia;

use Illuminate\Support\Facades\Log;
use Modules\Flight\Service\FlightDataHelper;
use Modules\Flight\Service\FlightDiscountService;
use Modules\Flight\Service\FlightChargesService;

/**
 * Air Arabia search response কে Sabre এর মতো
 * normalized format এ parse করে।
 *
 * Input  : Air Arabia ondWiseFlightCombinations array
 * Output : Sabre formatItinerary() এর মতো flight array
 */
class AirArabiaResponseParser
{
    private FlightDataHelper      $helper;
    private FlightDiscountService $discountService;
    private FlightChargesService  $chargesService;
    private AirArabiaXmlBuildService $xmlBuilder;

    public function __construct(
        FlightDiscountService $discountService,
        FlightChargesService  $chargesService
    ) {
        $this->xmlBuilder      = new AirArabiaXmlBuildService();
        $this->helper          = new FlightDataHelper();
        $this->discountService = $discountService;
        $this->chargesService  = $chargesService;
    }

    // ==========================================
    // MAIN ENTRY POINT
    // ==========================================

    /**
     * Air Arabia raw response → normalized flight array
     *
     * @param array $data        — $response->json() থেকে আসা raw data
     * @param array $searchData  — FlightSearchService এর searchData
     * @param array $passengers  — searchData['passengers']
     */

    public function search(array $searchData): array
    {
        try {
            $payload   = $this->xmlBuilder->buildSearchPayload($searchData);
            $trip_type = $this->xmlBuilder->getJourneyType($searchData['trip_type']);

            Log::channel('daily')->info('AirArabia Search Request: ' . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $airArabia = new AirArabiaService();
            $response  = $airArabia->getFlight($payload, $trip_type);

            if ($response->failed()) {
                Log::channel('daily')->error('AirArabia Search Failed: HTTP ' . $response->status() . ' — ' . $response->body());
                return ['success' => false, 'flights' => [], 'error' => 'HTTP: ' . $response->status()];
            }

            Log::channel('daily')->info('AirArabia Search Response: ' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $data    = $response->json();
//            dd($data);
            $flights = $this->parse($data, $searchData);

            Log::channel('daily')->info('AirArabia Search Parsed: total=' . count($flights));

            return [
                'success'       => true,
                'flights'       => $flights,
                'total_results' => count($flights),
                'currency'      => 'BDT',
                'error'         => null,
            ];

        } catch (Exception $e) {
            Log::channel('daily')->error('AirArabia Search Exception: ' . $e->getMessage());
            return ['success' => false, 'flights' => [], 'error' => $e->getMessage()];
        }
    }
    public function parse(array $data, array $searchData): array
    {
        $combinations = $data['ondWiseFlightCombinations'] ?? [];
        $passengers   = $searchData['passengers'];
        $tripType     = $searchData['trip_type'];

        if (empty($combinations)) {
            return [];
        }

        // Round trip: outbound ও return combinations আলাদা করি
        $ondKeys    = array_keys($combinations);
        $isReturn   = strtolower($tripType) === 'round';

        if ($isReturn && count($ondKeys) === 2) {
            return $this->parseRoundTrip($combinations, $ondKeys, $passengers);
        }

        // Oneway বা multicity: প্রতিটি OND আলাদাভাবে
        return $this->parseOneway($combinations, $passengers);
    }

    // ==========================================
    // ONE-WAY PARSER
    // ==========================================

    private function parseOneway(array $combinations, array $passengers): array
    {
        $flights = [];

        foreach ($combinations as $ondRef => $ondData) {
            $dateWise = $ondData['dateWiseFlightCombinations'] ?? [];

            foreach ($dateWise as $date => $dateData) {
                foreach ($dateData['flightOptions'] ?? [] as $optionIndex => $option) {
                    $segments    = $option['flightSegments'] ?? [];
                    $cabinPrices = $option['cabinPrices']    ?? [];

                    if (empty($segments) || empty($cabinPrices)) continue;
                    if (($option['availabilityStatus'] ?? '') === 'NOT_AVAILABLE') continue;

                    $flight = $this->formatFlight(
                        id: $ondRef . '_' . $date . '_' . $optionIndex,
                        legs: [$this->buildLeg($segments, $date, 1, 'outbound')],
                        cabinPrices: $cabinPrices,
                        passengers: $passengers,
                        airline: substr($segments[0]['flightNumber'] ?? 'G9', 0, 2)
                    );

                    if ($flight) $flights[] = $flight;
                }
            }
        }

        return $flights;
    }

    // ==========================================
    // ROUND TRIP PARSER
    // ==========================================

    /**
     * Round trip এ outbound ও return combination করে flights বানায়।
     * প্রতিটি outbound option × প্রতিটি return option = একটি flight।
     */
    private function parseRoundTrip(array $combinations, array $ondKeys, array $passengers): array
    {
        $flights = [];

        $outboundOnd  = $ondKeys[0];
        $returnOnd    = $ondKeys[1];

        $outboundOptions = $this->extractOptions($combinations[$outboundOnd] ?? []);
        $returnOptions   = $this->extractOptions($combinations[$returnOnd]   ?? []);

        foreach ($outboundOptions as $outIdx => [$outSegments, $outDate, $outPrices]) {
            foreach ($returnOptions as $retIdx => [$retSegments, $retDate, $retPrices]) {

                // দুটো leg এর combined price নেওয়া হয় outbound থেকে
                // (Air Arabia returns combined price in outbound cabinPrices for round trips)
                $combinedPrices = $outPrices;

                $flight = $this->formatFlight(
                    id: $outboundOnd . '_' . $outDate . '_' . $outIdx . '_' . $retIdx,
                    legs: [
                        $this->buildLeg($outSegments, $outDate, 1, 'outbound'),
                        $this->buildLeg($retSegments, $retDate, 2, 'return'),
                    ],
                    cabinPrices: $combinedPrices,
                    passengers: $passengers,
                    airline: substr($outSegments[0]['flightNumber'] ?? 'G9', 0, 2)
                );

                if ($flight) $flights[] = $flight;
            }
        }

        return $flights;
    }

    /**
     * OND data থেকে available options extract করে।
     * Return: [[$segments, $date, $cabinPrices], ...]
     */
    private function extractOptions(array $ondData): array
    {
        $options  = [];
        $dateWise = $ondData['dateWiseFlightCombinations'] ?? [];

        foreach ($dateWise as $date => $dateData) {
            foreach ($dateData['flightOptions'] ?? [] as $option) {
                $segments    = $option['flightSegments'] ?? [];
                $cabinPrices = $option['cabinPrices']    ?? [];

                if (empty($segments) || empty($cabinPrices)) continue;
                if (($option['availabilityStatus'] ?? '') === 'NOT_AVAILABLE') continue;

                $options[] = [$segments, $date, $cabinPrices];
            }
        }

        return $options;
    }

    // ==========================================
    // BUILD LEG (Sabre এর formatLegs এর মতো)
    // ==========================================

    /**
     * Air Arabia flightSegments → Sabre leg format
     */
    private function buildLeg(
        array  $segments,
        string $date,
        int    $legNumber,
        string $legType
    ): array {
        $formattedSegments = $this->buildSegments($segments);
        $firstSeg          = $formattedSegments[0]          ?? [];
        $lastSeg           = end($formattedSegments)         ?: [];

        $stops      = count($segments) - 1;
        $isDirect   = $stops === 0;
        $duration   = $this->calculateLegDuration($segments);

        return [
            'leg_number'         => $legNumber,
            'leg_type'           => $legType,
            'duration'           => $duration,
            'duration_formatted' => $this->helper->formatDuration($duration),
            'stops'              => $stops,
            'is_direct'          => $isDirect,
            'total_segments'     => count($segments),

            'departure' => [
                'airport_code' => $firstSeg['departure']['airport_code'] ?? null,
                'airport_name' => $this->helper->getAirportName($firstSeg['departure']['airport_code'] ?? null),
                'city'         => $this->helper->getAirportAddress($firstSeg['departure']['airport_code'] ?? null),
                'address'      => $this->helper->getAirportAddress($firstSeg['departure']['airport_code'] ?? null),
                'country'      => $this->helper->getAirportCountry($firstSeg['departure']['airport_code'] ?? null),
                'time'         => $firstSeg['departure']['time']     ?? null,
                'time_12h'     => $this->helper->formatTime12h($firstSeg['departure']['time'] ?? null),
                'date'         => $firstSeg['departure']['date']     ?? $date,
                'terminal'     => $firstSeg['departure']['terminal'] ?? null,
            ],

            'arrival' => [
                'airport_code'   => $lastSeg['arrival']['airport_code'] ?? null,
                'airport_name'   => $this->helper->getAirportName($lastSeg['arrival']['airport_code'] ?? null),
                'city'           => $this->helper->getAirportAddress($lastSeg['arrival']['airport_code'] ?? null),
                'address'        => $this->helper->getAirportAddress($lastSeg['arrival']['airport_code'] ?? null),
                'country'        => $this->helper->getAirportCountry($lastSeg['arrival']['airport_code'] ?? null),
                'time'           => $lastSeg['arrival']['time']     ?? null,
                'time_12h'       => $this->helper->formatTime12h($lastSeg['arrival']['time'] ?? null),
                'date'           => $lastSeg['arrival']['date']     ?? $date,
                'terminal'       => $lastSeg['arrival']['terminal'] ?? null,
                'date_adjustment'=> 0,
            ],

            'stops_detail' => $this->buildStopsDetail($formattedSegments),
            'segments'     => $this->helper->addLayoverInformation($formattedSegments),
        ];
    }

    // ==========================================
    // BUILD SEGMENTS (Sabre এর formatSegments এর মতো)
    // ==========================================

    private function buildSegments(array $rawSegments): array
    {
        $formatted = [];

        foreach ($rawSegments as $index => $seg) {
            $flightNumber = $seg['flightNumber'] ?? '';
            $airlineCode  = substr($flightNumber, 0, 2);

            $depCode = $seg['origin']['airportCode']      ?? null;
            $arrCode = $seg['destination']['airportCode'] ?? null;
            $depTime = $this->extractTime($seg['departureDateTimeLocal'] ?? '');
            $arrTime = $this->extractTime($seg['arrivalDateTimeLocal']   ?? '');
            $depDate = $this->extractDate($seg['departureDateTimeLocal'] ?? '');
            $arrDate = $this->extractDate($seg['arrivalDateTimeLocal']   ?? '');

            $formatted[] = [
                'segment_number'          => $index + 1,
                'carrier'                 => $airlineCode,
                'carrier_name'            => $this->helper->getAirlineName($airlineCode),
                'carrier_images'          => [
                    'thumb'  => $this->helper->getAirlineImage($airlineCode, 'thumb'),
                    'medium' => $this->helper->getAirlineImage($airlineCode, 'medium'),
                    'large'  => $this->helper->getAirlineImage($airlineCode, 'large'),
                    'full'   => $this->helper->getAirlineImage($airlineCode, 'full'),
                ],
                'operating_carrier'       => $airlineCode,
                'operating_carrier_name'  => $this->helper->getAirlineName($airlineCode),
                'is_codeshare'            => false,
                'flight_number'           => (int) substr($flightNumber, 2),
                'operating_flight_number' => (int) substr($flightNumber, 2),
                'full_flight_number'      => $airlineCode . '-' . substr($flightNumber, 2),

                'departure' => [
                    'airport_code' => $depCode,
                    'airport_name' => $this->helper->getAirportName($depCode),
                    'city'         => $this->helper->getAirportAddress($depCode),
                    'address'      => $this->helper->getAirportAddress($depCode),
                    'country'      => $this->helper->getAirportCountry($depCode),
                    'country_code' => $seg['origin']['countryCode'] ?? null,
                    'time'         => $depTime,
                    'time_12h'     => $this->helper->formatTime12h($depTime),
                    'date'         => $depDate,
                    'terminal'     => $seg['origin']['terminal'] ?: null,
                ],

                'arrival' => [
                    'airport_code'   => $arrCode,
                    'airport_name'   => $this->helper->getAirportName($arrCode),
                    'city'           => $this->helper->getAirportAddress($arrCode),
                    'address'        => $this->helper->getAirportAddress($arrCode),
                    'country'        => $this->helper->getAirportCountry($arrCode),
                    'country_code'   => $seg['destination']['countryCode'] ?? null,
                    'time'           => $arrTime,
                    'time_12h'       => $this->helper->formatTime12h($arrTime),
                    'date'           => $arrDate,
                    'date_adjustment'=> 0,
                    'terminal'       => $seg['destination']['terminal'] ?: null,
                ],

                'duration'                => $this->calculateSegmentDuration($seg),
                'duration_formatted'      => $this->helper->formatDuration($this->calculateSegmentDuration($seg)),
                'miles'                   => null,
                'aircraft'                => $seg['aircraftModel'] ?? null,
                'aircraft_type_first_leg' => null,
                'aircraft_type_last_leg'  => null,
                'aircraft_name'           => $seg['aircraftModel'] ?? null,
                'meal_code'               => null,
                'meal_description'        => null,
                'eTicketable'             => true,
                'stop_count'              => 0,
                'frequency'               => null,

                'fare_info' => [
                    'fare_basis_code'    => null,
                    'booking_code'       => null,
                    'cabin_code'         => 'Y',
                    'cabin_name'         => 'Economy',
                    'seats_available'    => $this->getAdultSeats($seg['availablePaxCounts'] ?? []),
                    'availability_break' => false,
                ],

                'alliances'           => null,
                'disclosure'          => null,
                'message'             => null,
                'message_type'        => null,
                'traffic_restriction' => null,

                // Air Arabia specific — getPrice তে লাগবে
                'segment_code'  => $seg['segmentCode']      ?? null,
                'segment_ref'   => $seg['flightSegmentRef'] ?? null,
                'rph'           => $this->buildRph($seg),
            ];
        }

        return $formatted;
    }

    // ==========================================
    // BUILD STOPS DETAIL
    // ==========================================

    private function buildStopsDetail(array $formattedSegments): array
    {
        $stops = [];

        for ($i = 0; $i < count($formattedSegments) - 1; $i++) {
            $current = $formattedSegments[$i];
            $next    = $formattedSegments[$i + 1];

            $arrDate  = $current['arrival']['date']    ?? '';
            $depDate  = $next['departure']['date']      ?? '';
            $arrTime  = $current['arrival']['time']     ?? '';
            $depTime  = $next['departure']['time']      ?? '';

            $layoverMinutes = 0;
            try {
                $arr = new \DateTime($arrDate . ' ' . $this->extractTimeComponent($arrTime));
                $dep = new \DateTime($depDate . ' ' . $this->extractTimeComponent($depTime));
                $layoverMinutes = max(0, ($dep->getTimestamp() - $arr->getTimestamp()) / 60);
            } catch (\Exception $e) {}

            $stopCode = $current['arrival']['airport_code'] ?? null;

            $stops[] = [
                'stop_number'        => $i + 1,
                'airport_code'       => $stopCode,
                'airport_name'       => $this->helper->getAirportName($stopCode),
                'city'               => $this->helper->getAirportAddress($stopCode),
                'address'            => $this->helper->getAirportAddress($stopCode),
                'country'            => $this->helper->getAirportCountry($stopCode),
                'arrival_time'       => $arrTime,
                'arrival_time_12h'   => $this->helper->formatTime12h($arrTime),
                'arrival_date'       => $arrDate,
                'arrival_terminal'   => $current['arrival']['terminal']    ?? null,
                'departure_time'     => $depTime,
                'departure_time_12h' => $this->helper->formatTime12h($depTime),
                'departure_date'     => $depDate,
                'departure_terminal' => $next['departure']['terminal']     ?? null,
                'layover_minutes'    => (int) $layoverMinutes,
                'layover_formatted'  => $this->helper->formatDuration((int) $layoverMinutes),
                'is_overnight'       => $arrDate !== $depDate,
                'terminal_change'    => $this->helper->checkTerminalChange(
                    $current['arrival']['terminal'] ?? null,
                    $next['departure']['terminal']  ?? null
                ),
            ];
        }

        return $stops;
    }

    // ==========================================
    // FORMAT FLIGHT (Sabre এর formatItinerary এর মতো)
    // ==========================================

    private function formatFlight(
        string $id,
        array  $legs,
        array  $cabinPrices,
        array  $passengers,
        string $airline
    ): ?array {
        $cabinPrice = $cabinPrices[0] ?? null;
        if (!$cabinPrice) return null;

        // পাসেঞ্জার breakdown তৈরি — FlightDiscountService compatible format
        $passengerInfoList = $this->buildPassengerInfoList(
            $cabinPrice['paxTypeWiseBasePrices'] ?? [],
            $passengers
        );

        // Total segments count
        $totalSegments = array_sum(array_column($legs, 'total_segments'));

        // Airline code — first leg first segment
        $validatingCarrier = $airline;
        $departureCode     = $legs[0]['departure']['airport_code'] ?? null;
        $arrivalCode       = $legs[0]['arrival']['airport_code']   ?? null;

        // Discount calculation — Sabre এর মতোই
        $flightDiscountInfo = $this->discountService->calculate(
            $validatingCarrier,
            $departureCode,
            $arrivalCode,
            $passengerInfoList,
            $totalSegments,
            'airarabia'
        );

        $grandTotal = $flightDiscountInfo['grand_total'];

        $apiSubtotal = $grandTotal['api_subtotal'];
        $priceBeforeDiscounts = $apiSubtotal
            + $grandTotal['total_ait']
            + $grandTotal['total_service_charge'];

        $finalPrice = $priceBeforeDiscounts
            - $grandTotal['total_user_discount']
            - $grandTotal['total_user_seg_discount'];

        return [
            'id'     => $id,
            'source' => 'air_arabia',
            'legs'   => $legs,

            'price' => [
                'api_base_fare'            => $cabinPrice['price'] ?? 0,
                'api_tax'                  => 0, // Air Arabia search এ tax breakdown নেই
                'tax_note'        => 'Taxes & fees will be calculated at booking',
                'api_subtotal'             => $apiSubtotal,
                'ait_amount'               => $grandTotal['total_ait'],
                'service_charge'           => $grandTotal['total_service_charge'],
                'subtotal_before_discount' => round($priceBeforeDiscounts, 2),
                'flight_discount'          => $grandTotal['total_user_discount'],
                'segment_discount'         => $grandTotal['total_user_seg_discount'],
                'total_discounts'          => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),
                'total'                    => round($finalPrice, 2),
                'currency'                 => $passengers['currency'] ?? 'BDT',
                'base_currency'            => 'AED',
                'own_discount'             => $grandTotal['total_own_discount'],
                'own_seg_discount'         => $grandTotal['total_own_seg_discount'],
                'total_commission'         => $grandTotal['total_commission'],
                'own_cost'                 => $grandTotal['total_own_cost'],
                'gross_profit'             => $grandTotal['gross_profit'],
            ],

            'flight_discount_details'    => $flightDiscountInfo,
            'passenger_price_breakdown'  => $flightDiscountInfo['passenger_breakdowns'],

            'charges_details' => [
                'ait_charge_percentage'        => $flightDiscountInfo['ait_charge_percentage'],
                'ait_amount'                   => $grandTotal['total_ait'],
                'service_charge'               => $grandTotal['total_service_charge'],
                'segment_discount_per_segment' => $flightDiscountInfo['segment_discount_per_segment'],
                'segment_discount_total'       => $grandTotal['total_user_seg_discount'],
            ],

            'passengers'          => $this->formatPassengerSummary($passengerInfoList),
            'refundable'          => null, // Air Arabia search এ নেই — getPrice এ পাওয়া যাবে
            'eTicketable'         => true,
            'validating_carrier'  => $validatingCarrier,
            'vita'                => null,
            'last_ticket_date'    => null,
            'last_ticket_time'    => null,
            'pricing_source'      => 'air_arabia',
            'taxes_breakdown'     => [], // getPrice এ পাওয়া যাবে
            'fare_family'         => $cabinPrice['fareFamily']   ?? null,
            'booking_classes'     => $cabinPrice['fareOndWiseBookingClassCodes'] ?? [],
            'provider'            => 'air_arabia',
        ];
    }

    // ==========================================
    // PASSENGER INFO LIST (FlightDiscountService compatible)
    // ==========================================

    /**
     * Air Arabia paxTypeWiseBasePrices → FlightDiscountService passengerInfoList format
     *
     * Air Arabia তে tax আলাদা নেই search response এ।
     * পুরো price টাই equivalentAmount হিসেবে pass করা হচ্ছে।
     */
    private function buildPassengerInfoList(array $paxTypeWisePrices, array $passengers): array
    {
        $priceMap = collect($paxTypeWisePrices)->keyBy('paxType')->toArray();

        $passengerInfoList = [];

        // ADT
        if (($passengers['adults'] ?? 0) > 0) {
            $price = (float)($priceMap['ADT']['price'] ?? 0);
            $passengerInfoList[] = [
                'passengerInfo' => [
                    'passengerType'   => 'ADT',
                    'passengerNumber' => (int) $passengers['adults'],
                    'passengerTotalFare' => [
                        'equivalentAmount' => $price,
                        'totalTaxAmount'   => 0,
                        'totalFare'        => $price,
                    ],
                ],
            ];
        }

        // CHD
        if (($passengers['children'] ?? 0) > 0) {
            $price = (float)($priceMap['CHD']['price'] ?? 0);
            // CHD price 0 হলে ADT price নাও
            if ($price === 0.0) {
                $price = (float)($priceMap['ADT']['price'] ?? 0);
            }
            $passengerInfoList[] = [
                'passengerInfo' => [
                    'passengerType'   => 'CHD',
                    'passengerNumber' => (int) $passengers['children'],
                    'passengerTotalFare' => [
                        'equivalentAmount' => $price,
                        'totalTaxAmount'   => 0,
                        'totalFare'        => $price,
                    ],
                ],
            ];
        }

        // INF
        if (($passengers['infants'] ?? 0) > 0) {
            $price = (float)($priceMap['INF']['price'] ?? 0);
            $passengerInfoList[] = [
                'passengerInfo' => [
                    'passengerType'   => 'INF',
                    'passengerNumber' => (int) $passengers['infants'],
                    'passengerTotalFare' => [
                        'equivalentAmount' => $price,
                        'totalTaxAmount'   => 0,
                        'totalFare'        => $price,
                    ],
                ],
            ];
        }

        return $passengerInfoList;
    }

    /**
     * Passenger summary — Sabre এর formatPassengerBreakdown এর মতো
     */
    private function formatPassengerSummary(array $passengerInfoList): array
    {
        return collect($passengerInfoList)->map(function ($item) {
            $info  = $item['passengerInfo'];
            $fare  = $info['passengerTotalFare'];
            $type  = $info['passengerType'];
            $count = $info['passengerNumber'];

            return [
                'type'                => $type,
                'type_label'          => $this->helper->getPassengerTypeLabel($type),
                'count'               => $count,
                'total_fare'          => $fare['totalFare']        ?? 0,
                'base_fare'           => null, // AED base নেই search এ
                'base_fare_currency'  => 'AED',
                'tax_amount'          => $fare['totalTaxAmount']   ?? 0,
                'equivalent_amount'   => $fare['equivalentAmount'] ?? 0,
                'equivalent_currency' => 'BDT',
                'currency'            => 'BDT',
                'exchange_rate'       => null,
                'exchange_from'       => 'AED',
                'exchange_to'         => 'BDT',
                'refundable'          => null,
                'baggage'             => null, // getBaggageDetails এ পাওয়া যাবে
            ];
        })->toArray();
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * RPH তৈরি করে — getPrice SOAP request এ লাগবে
     * Format: G9$SHJ/COK$1013059$20260410132500$20260410184500
     */
    private function buildRph(array $seg): string
    {
        $flightNumber = $seg['flightNumber'] ?? '';
        $airline      = substr($flightNumber, 0, 2);
        $segCode      = $seg['segmentCode']      ?? '';
        $segRef       = $seg['flightSegmentRef'] ?? '';
        $dep          = str_replace(['T', '-', ':'], '', $seg['departureDateTimeLocal'] ?? '');
        $arr          = str_replace(['T', '-', ':'], '', $seg['arrivalDateTimeLocal']   ?? '');

        return "{$airline}\${$segCode}\${$segRef}\${$dep}\${$arr}";
    }

    private function extractDate(string $datetime): string
    {
        return substr($datetime, 0, 10); // "2026-04-10T13:25:00" → "2026-04-10"
    }

    private function extractTime(string $datetime): string
    {
        // "2026-04-10T13:25:00" → "13:25:00+00:00"
        $time = substr($datetime, 11); // "13:25:00"
        return $time ? $time . '+00:00' : '';
    }

    private function extractTimeComponent(string $time): string
    {
        // "13:25:00+00:00" → "13:25:00"
        preg_match('/(\d{2}:\d{2}:\d{2})/', $time, $m);
        return $m[1] ?? '00:00:00';
    }

    private function getAdultSeats(array $availablePaxCounts): ?int
    {
        foreach ($availablePaxCounts as $pax) {
            if (($pax['paxType'] ?? '') === 'ADT') {
                return (int) $pax['count'];
            }
        }
        return null;
    }

    /**
     * Segment এর duration মিনিটে calculate করে।
     */
    private function calculateSegmentDuration(array $seg): int
    {
        try {
            $dep = new \DateTime($seg['departureDateTimeZulu'] ?? $seg['departureDateTimeLocal'] ?? '');
            $arr = new \DateTime($seg['arrivalDateTimeZulu']   ?? $seg['arrivalDateTimeLocal']   ?? '');
            return max(0, (int)(($arr->getTimestamp() - $dep->getTimestamp()) / 60));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Leg এর total duration সব segments এর duration যোগ করে।
     */
    private function calculateLegDuration(array $segments): int
    {
        if (empty($segments)) return 0;

        try {
            $first = new \DateTime(
                $segments[0]['departureDateTimeZulu'] ?? $segments[0]['departureDateTimeLocal'] ?? ''
            );
            $last  = new \DateTime(
                end($segments)['arrivalDateTimeZulu'] ?? end($segments)['arrivalDateTimeLocal'] ?? ''
            );
            return max(0, (int)(($last->getTimestamp() - $first->getTimestamp()) / 60));
        } catch (\Exception $e) {
            return 0;
        }
    }
}
