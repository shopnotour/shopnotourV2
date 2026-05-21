<?php

namespace Modules\Booking\Service;

use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Flight\Service\FlightDataHelper;
use Modules\Flight\Service\TravelPort\TravelPortApiService;

class TravelPortBookingXmlService
{
    private string $targetBranch;
    private string $authorizedBy;
    private string $providerCode;
    private FlightDataHelper $helper;

    public function __construct()
    {
        $this->targetBranch = env('TRAVELPORT_TARGET_BRANCH', 'P7102538');
        $this->authorizedBy = env('TRAVELPORT_AUTHORIZED_BY', 'UAPI');
        $this->providerCode = env('TRAVELPORT_PROVIDER_CODE', '1G');
        $this->helper = new FlightDataHelper();
    }

    /**
     * Build Booking XML Request from Booking ID
     * Returns only XML string - API call is separate
     */


    public function buildBookingRequestFromId(int $bookingId, array $priceVerified)
    {
//        return $priceVerified;
        $booking = Booking::with('passengers')->findOrFail($bookingId);

        $flightData = $booking->flight_raw_data;
        if (is_string($flightData)) {
            $flightData = json_decode($flightData, true);
        }

        $departureDate  = $flightData['legs'][0]['departure']['date'] ?? date('Y-m-d');
        $passengers     = $booking->passengers->toArray();
        $platingCarrier = $flightData['validating_carrier'] ?? 'AI';

        // ✅ price_verified থেকে সরাসরি AirPricingSolution build করো
        $pricingSolutionXml = $this->buildPricingSolutionFromVerified(
            $flightData,
            $priceVerified,
            $passengers,
            $departureDate
        );


//        $xml = $this->buildBookingXml(
//            $passengers,
//            $pricingSolutionXml,
//            $departureDate,
//            $platingCarrier,
//            $booking->id,
//            ['Booking from website']
//        );

        $ticketDate = \Carbon\Carbon::parse(
            $priceVerified['pricing_infos'][0]['latest_ticketing'] ?? now()->addDays(3)
        )->format('Y-m-d');

        $traceId = session('travelport_trace_id');
        $xml = $this->buildBookingXml(
            $passengers,
            $pricingSolutionXml,
            $departureDate,
            $flightData['validating_carrier'] ?? 'AI',
            $traceId,
            ['Booking from Shopno tour'],
            $ticketDate   // ← pass করো
        );

//        dd($xml);
        $responsexml = new TravelPortApiService();
        $result = $responsexml->pnrCreate($xml, $bookingId);
//return $result;
        if (!$result['success']) {
            return [
                'success' => false,
                'error'   => $result['error'] ?? 'PNR তৈরি হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ];
        }

        $booking->update([
            'pnr_id'       => $result['universal_record']['locator_code'] ?? '',
            'booking_date' => \Carbon\Carbon::parse($result['action_status']['ticket_date'])->format('Y-m-d H:i:s'),
            'status'       => 'booked',
            'confirmed_at' => now(),
            'pnr_raw_data' => json_encode($result),
        ]);

        $booking->passengers()->update([
            'pnr'    => $result['universal_record']['locator_code'] ?? '',
            'status' => 'booked',
        ]);

        return $result;
    }

//    private function buildPricingSolutionFromVerified(array $flightData, array $priceVerified, array $passengers, string $departureDate): string
//    {
//        // ✅ price_verified থেকে নাও
//        $solutionKey = $priceVerified['pricing_solution_key'];
//        $totalPrice  = $priceVerified['total_price'];       // e.g. "BDT187950"
//        $basePrice   = $priceVerified['base_price'];        // e.g. "USD1080.00"
//        $taxes       = $priceVerified['taxes'];             // e.g. "BDT55422"
//        $quoteDate   = $priceVerified['quote_date'];
//        $hostTokens  = $priceVerified['host_tokens'];       // [segment_key => token_value]
//        $pricingInfos = $priceVerified['pricing_infos'];    // per pax type
//
//        // Segments — flight_raw_data থেকে (XML intact আছে)
//        $segmentsXml = $this->buildAllSegmentsFromLegs($flightData['legs']);
//
//        // AirPricingInfo — price_verified থেকে
//        $pricingInfoXml = $this->buildPricingInfoFromVerified($pricingInfos, $passengers, $departureDate);
//
//        // FareNotes
//        $fareNotesXml = $this->buildFareNotes($flightData);
//
//        // HostTokens — price_verified থেকে real tokens
//        $hostTokensXml = $this->buildHostTokensFromVerified($hostTokens);
//
//        return <<<XML
//<air:AirPricingSolution xmlns:air="http://www.travelport.com/schema/air_v52_0" Key="{$solutionKey}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$totalPrice}" ApproximateBasePrice="{$totalPrice}" Taxes="{$taxes}" QuoteDate="{$quoteDate}">
//{$segmentsXml}
//{$pricingInfoXml}
//{$fareNotesXml}
//{$hostTokensXml}
//</air:AirPricingSolution>
//XML;
//    }

    private function buildPricingSolutionFromVerified(
        array $flightData,
        array $priceVerified,
        array $passengers,
        string $departureDate
    ): string {

        usort($passengers, function($a, $b) use ($departureDate) {
            $order = ['ADT' => 1, 'INF' => 2, 'CNN' => 3];
            $typeA = $this->helper->getPassengerTypeFromDOB(
                $a['dob'] ?? $a['date_of_birth'] ?? null, $departureDate
            )['type'];
            $typeB = $this->helper->getPassengerTypeFromDOB(
                $b['dob'] ?? $b['date_of_birth'] ?? null, $departureDate
            )['type'];
            return ($order[$typeA] ?? 4) <=> ($order[$typeB] ?? 4);
        });

        $pricingSolutionXml = $priceVerified['pricing_solution_xml'] ?? '';

        $pricingSolutionXml = $priceVerified['pricing_solution_xml'] ?? '';

        if (empty($pricingSolutionXml)) {
            throw new \Exception('price_verified has no pricing_solution_xml');
        }

        // ✅ Step 1: namespace restore
        $pricingSolutionXml = $this->restoreNamespaces($pricingSolutionXml);

        // ✅ Step 2: AirSegmentRef গুলো replace করো full AirSegment দিয়ে
        $pricingSolutionXml = $this->replaceSegmentRefsWithFullSegments(
            $pricingSolutionXml,
            $priceVerified['segments']  // ✅ same AirPriceRsp থেকে, keys guaranteed match
        );

        if (preg_match('/<air:AirSegmentRef/', $pricingSolutionXml)) {
            Log::warning('Unresolved AirSegmentRef found in pricing solution XML');
            // segments key গুলো log করুন
            Log::warning('Available segment keys: ' . implode(', ', array_keys($priceVerified['segments'] ?? [])));
        }
        // ✅ Step 3: PassengerType এ BookingTravelerRef update করো
        $pricingSolutionXml = $this->injectTravelerRefs($pricingSolutionXml, $passengers, $departureDate);

        // ✅ Step 4: XML declaration strip
        $pricingSolutionXml = preg_replace('/<\?xml[^>]*>\n?/', '', $pricingSolutionXml);

        return $pricingSolutionXml;
    }

    private function restoreNamespaces(string $xml): string
    {
        // ✅ AirPricingSolution - দুটো namespace একসাথে
        $xml = str_replace(
            '<AirPricingSolution',
            '<air:AirPricingSolution xmlns:air="http://www.travelport.com/schema/air_v52_0" xmlns:common_v52_0="http://www.travelport.com/schema/common_v52_0"',
            $xml
        );
        $xml = str_replace('</AirPricingSolution>', '</air:AirPricingSolution>', $xml);

        // ✅ air: prefix elements
        $xml = preg_replace('/<(\/?)AirSegment([^R])/', '<$1air:AirSegment$2', $xml);
        $xml = preg_replace('/<(\/?)AirSegmentRef/', '<$1air:AirSegmentRef', $xml);
        $xml = preg_replace('/<(\/?)AirPricingInfo/', '<$1air:AirPricingInfo', $xml);
        $xml = preg_replace('/<(\/?)BookingInfo/', '<$1air:BookingInfo', $xml);
        $xml = preg_replace('/<(\/?)FareInfo([^R])/', '<$1air:FareInfo$2', $xml);
        $xml = preg_replace('/<(\/?)PassengerType/', '<$1air:PassengerType', $xml);
        $xml = preg_replace('/<(\/?)FareNote/', '<$1air:FareNote', $xml);
        $xml = preg_replace('/<(\/?)FareCalc/', '<$1air:FareCalc', $xml);
        $xml = preg_replace('/<(\/?)TaxInfo/', '<$1air:TaxInfo', $xml);
        $xml = preg_replace('/<(\/?)CodeshareInfo/', '<$1air:CodeshareInfo', $xml);
        $xml = preg_replace('/<(\/?)FlightDetails([^R])/', '<$1air:FlightDetails$2', $xml);
        $xml = preg_replace('/<(\/?)FareRuleKey/', '<$1air:FareRuleKey', $xml);
        $xml = preg_replace('/<(\/?)Brand([^I])/', '<$1air:Brand$2', $xml);
        $xml = preg_replace('/<(\/?)ChangePenalty/', '<$1air:ChangePenalty', $xml);
        $xml = preg_replace('/<(\/?)CancelPenalty/', '<$1air:CancelPenalty', $xml);
        $xml = preg_replace('/<(\/?)BaggageAllowances/', '<$1air:BaggageAllowances', $xml);
        $xml = preg_replace('/<(\/?)BaggageAllowanceInfo/', '<$1air:BaggageAllowanceInfo', $xml);

        // ✅ common_v52_0: prefix elements (xmlns ছাড়া — parent এ declare আছে)
        $xml = preg_replace('/<(\/?)Endorsement/', '<$1common_v52_0:Endorsement', $xml);
        $xml = preg_replace('/<(\/?)ServiceData/', '<$1common_v52_0:ServiceData', $xml);
        $xml = preg_replace('/<(\/?)ServiceInfo/', '<$1common_v52_0:ServiceInfo', $xml);
        $xml = preg_replace('/<(\/?)Description([^s])/', '<$1common_v52_0:Description$2', $xml);
        $xml = str_replace('<HostToken', '<common_v52_0:HostToken', $xml);
        $xml = str_replace('</HostToken>', '</common_v52_0:HostToken>', $xml);

        return $xml;
    }
    private function replaceSegmentRefsWithFullSegments(string $xml, array $segments): string
    {
        return preg_replace_callback(
            '/<air:AirSegmentRef\s+Key="([^"]+)"\s*\/>/i',
            function ($matches) use ($segments) {
                $key    = $matches[1];
                $segXml = $segments[$key]['xml'] ?? null;
                if (!$segXml) return $matches[0];

                $segXml = preg_replace('/<\?xml[^>]*>\n?/', '', $segXml);

                // ✅ Duplicate xmlns:air সরাও — parent এ declare আছে
                $segXml = preg_replace('/\s*xmlns:air="[^"]*"/', '', $segXml);
                $segXml = preg_replace('/\s*xmlns:common_v52_0="[^"]*"/', '', $segXml);

                // ✅ namespace prefix নিশ্চিত করো
                $segXml = str_replace('<AirSegment ',       '<air:AirSegment ',       $segXml);
                $segXml = str_replace('</AirSegment>',      '</air:AirSegment>',      $segXml);
                $segXml = str_replace('<CodeshareInfo ',    '<air:CodeshareInfo ',    $segXml);
                $segXml = str_replace('</CodeshareInfo>',   '</air:CodeshareInfo>',   $segXml);
                $segXml = str_replace('<FlightDetails ',    '<air:FlightDetails ',    $segXml);
                $segXml = str_replace('</FlightDetails>',   '</air:FlightDetails>',   $segXml);
                $segXml = str_replace('<Connection/>',      '<air:Connection/>',      $segXml);

                return trim($segXml);
            },
            $xml
        );
    }

    private function injectTravelerRefs(string $xml, array $passengers, string $departureDate): string
    {
        // Passenger type → key mapping তৈরি করো
        $typeToKey = [];
        $typeToAge = [];

        foreach ($passengers as $index => $passenger) {
            $dob = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;
            $typeInfo = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
            $type = $typeInfo['type']; // ADT, INF, CNN

            // প্রতিটি type এর জন্য key list রাখো (multiple CNN হতে পারে)
            $typeToKey[$type][] = base64_encode("BookingTraveler" . ($index + 1));
            $typeToAge[$type][] = $typeInfo['age'];
        }

        // Counter per type
        $typeCounter = [];

        $xml = preg_replace_callback(
            '/<air:PassengerType([^>]*?)(\/>|>)/',
            function ($matches) use (&$typeCounter, $typeToKey, $typeToAge) {
                $attrs = $matches[1];

                // Code attribute থেকে type বের করো
                preg_match('/Code="([^"]+)"/', $attrs, $codeMatch);
                $code = $codeMatch[1] ?? 'ADT';

                // এই type এর কতটা use হয়েছে
                $idx = $typeCounter[$code] ?? 0;
                $typeCounter[$code] = $idx + 1;

                $key     = $typeToKey[$code][$idx] ?? end($typeToKey[$code]);
                $ageInfo = $typeToAge[$code][$idx] ?? null;

                // Age replace
                $attrs = preg_replace('/\s*Age="[^"]*"/', '', $attrs);
                if (in_array($code, ['CNN', 'INF']) && $ageInfo !== null) {
                    $attrs .= " Age=\"{$ageInfo}\"";
                }

                // BookingTravelerRef replace/add
                if (str_contains($attrs, 'BookingTravelerRef=')) {
                    $attrs = preg_replace('/BookingTravelerRef="[^"]*"/', "BookingTravelerRef=\"{$key}\"", $attrs);
                } else {
                    $attrs .= " BookingTravelerRef=\"{$key}\"";
                }

                return '<air:PassengerType' . $attrs . $matches[2];
            },
            $xml
        );

        return $xml;
    }
//    private function injectTravelerRefs(string $xml, array $passengers, string $departureDate): string
//    {
//        $travelerKeys = [];
//        $travelerTypes = []; // ← age sync এর জন্য
//
//        foreach ($passengers as $index => $passenger) {
//            $travelerKeys[] = base64_encode("BookingTraveler" . ($index + 1));
//            $dob = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;
//            $travelerTypes[] = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
//        }
//
//        $index = 0;
//        $xml = preg_replace_callback(
//            '/<air:PassengerType([^>]*?)(\/>|>)/',
//            function ($matches) use (&$index, $travelerKeys, $travelerTypes) {
//                $key      = $travelerKeys[$index] ?? end($travelerKeys);
//                $typeInfo = $travelerTypes[$index] ?? end($travelerTypes);
//                $index++;
//
//                $attrs = $matches[1];
//
//                // ← Age sync: existing Age attribute replace করো
//                $attrs = preg_replace('/\s*Age="[^"]*"/', '', $attrs);
//                if (in_array($typeInfo['type'], ['CNN', 'INF'])) {
//                    $attrs .= " Age=\"{$typeInfo['age']}\"";
//                }
//
//                // ← BookingTravelerRef replace বা add করো
//                if (str_contains($attrs, 'BookingTravelerRef=')) {
//                    $attrs = preg_replace('/BookingTravelerRef="[^"]*"/', "BookingTravelerRef=\"{$key}\"", $attrs);
//                } else {
//                    $attrs .= " BookingTravelerRef=\"{$key}\"";
//                }
//
//                return '<air:PassengerType' . $attrs . $matches[2];
//            },
//            $xml
//        );
//
//        return $xml;
//    }
    private function buildPricingInfoFromVerified(array $pricingInfos, array $passengers, string $departureDate): string
    {
        $xml = '';

        foreach ($pricingInfos as $info) {
            $key             = $info['key'];
            $totalPrice      = $info['total_price'];
            $basePrice       = $info['base_price'];
            $taxes           = $info['taxes'];
            $platingCarrier  = $info['provider_code'] ?? 'AI'; // pricing_info তে plating_carrier আলাদা থাকলে সেটা নাও
            $latestTicketing = $info['latest_ticketing'] ?? '';

            // BookingInfo with HostTokenRef
            $bookingInfoXml = '';
            foreach ($info['booking_infos'] as $bi) {
                $bookingInfoXml .= "        <air:BookingInfo BookingCode=\"{$bi['booking_code']}\" CabinClass=\"{$bi['cabin_class']}\" FareInfoRef=\"{$bi['fare_info_ref']}\" SegmentRef=\"{$bi['segment_ref']}\" HostTokenRef=\"{$bi['host_token_ref']}\"/>\n";
            }

            // PassengerType
            $passengerTypeXml = $this->buildPassengerTypes($passengers, $departureDate);

            $xml .= <<<XML
    <air:AirPricingInfo Key="{$key}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$totalPrice}" Taxes="{$taxes}" LatestTicketingTime="{$latestTicketing}" PricingMethod="Guaranteed" ETicketability="Yes" PlatingCarrier="{$platingCarrier}" ProviderCode="{$this->providerCode}">
{$bookingInfoXml}
{$passengerTypeXml}
    </air:AirPricingInfo>

XML;
        }

        return $xml;
    }

    private function buildHostTokensFromVerified(array $hostTokens): string
    {
        $xml = '';

        foreach ($hostTokens as $key => $value) {
            $xml .= "<common_v52_0:HostToken Key=\"{$key}\">{$value}</common_v52_0:HostToken>\n";
        }

        return $xml;
    }

    /**
     * price_verified session data থেকে real HostTokens + pricing_solution_xml inject করো
     */


    /**
     * ✅ Build complete AirPricingSolution matching demo format
     */
    private function buildAirPricingSolutionFromData(array $flightData, array $passengers, string $departureDate): string
    {
        $pricePoint = $flightData['price_point'] ?? [];

        // Solution attributes
        $solutionKey = $pricePoint['key'] ?? base64_encode('AirPricingSolution' . uniqid());
        $totalPrice = 'BDT' . ($flightData['price']['api_subtotal'] ?? 0);
        $basePrice = 'USD' . number_format($flightData['passengers'][0]['base_fare'] ?? 0, 2, '.', '');
        $taxes = 'BDT' . ($flightData['price']['api_tax'] ?? 0);
        $fees = 'BDT0.00';
        $approximateTaxes = $taxes;
        $quoteDate = date('Y-m-d');

        // ✅ Build ALL segments from legs
        $segmentsXml = $this->buildAllSegmentsFromLegs($flightData['legs']);

        // ✅ Build AirPricingInfo with full FareInfo elements
        $pricingInfoXml = $this->buildCompletePricingInfo($flightData, $passengers, $departureDate);

        // ✅ Build FareNote elements
        $fareNotesXml = $this->buildFareNotes($flightData);

        // ✅ Build HostToken elements (extracted from stored XML or generated)
        $hostTokensXml = $this->buildHostTokensFromData($flightData);

        return <<<XML
<air:AirPricingSolution xmlns:air="http://www.travelport.com/schema/air_v52_0" Key="{$solutionKey}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$totalPrice}" ApproximateBasePrice="{$totalPrice}" Taxes="{$taxes}" Fees="{$fees}" ApproximateTaxes="{$approximateTaxes}" QuoteDate="{$quoteDate}">
{$segmentsXml}
{$pricingInfoXml}
{$fareNotesXml}
{$hostTokensXml}
</air:AirPricingSolution>
XML;
    }

    /**
     * ✅ Build ALL AirSegment elements from legs (using stored XML)
     */
    // ============================================================
// UPDATED METHOD - Replace in TravelPortBookingXmlService.php
// ============================================================

    /**
     * ✅ Build ALL AirSegment elements from legs (using stored XML)
     */
    private function buildAllSegmentsFromLegs(array $legs): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];

                // Skip duplicates
                if (isset($processedKeys[$key])) {
                    continue;
                }
                $processedKeys[$key] = true;

                // ✅ Use stored XML if available (recommended)
                if (!empty($segment['xml'])) {
                    $segmentXml = $segment['xml'];

                    // Decode if JSON-encoded
                    if (strpos($segmentXml, '\u003C') !== false || strpos($segmentXml, '\\u003C') !== false) {
                        $segmentXml = json_decode('"' . $segmentXml . '"');
                    }

                    // ✅ FIX 1: ENSURE air: namespace prefix exists
                    $segmentXml = preg_replace('/<(\/?)(air:)?AirSegment/', '<$1air:AirSegment', $segmentXml);
                    $segmentXml = preg_replace('/<(\/?)(air:)?FlightDetails/', '<$1air:FlightDetails', $segmentXml);

                    // ✅ FIX 2: Remove FlightDetailsRef and AirAvailInfo (not allowed in booking request)
                    $segmentXml = preg_replace('/<air:FlightDetailsRef[^>]*\/>/', '', $segmentXml);
                    $segmentXml = preg_replace('/<air:AirAvailInfo[^>]*\/>/', '', $segmentXml);

                    // ✅ FIX 3: CRITICAL - Ensure ProviderCode attribute exists
                    if (strpos($segmentXml, 'ProviderCode=') === false) {
                        // Find the last closing bracket (either /> or >)
                        if (strpos($segmentXml, '/>') !== false) {
                            // Self-closing tag
                            $segmentXml = str_replace('/>', ' ProviderCode="' . $this->providerCode . '"/>', $segmentXml);
                        } else {
                            // Has closing tag
                            $segmentXml = preg_replace('/>/', ' ProviderCode="' . $this->providerCode . '">', $segmentXml, 1);
                        }
                    }

                    // ✅ FIX 4: Make AirSegment self-closing if it has no children
                    $segmentXml = preg_replace('/<air:AirSegment([^>]*)>\s*<\/air:AirSegment>/', '<air:AirSegment$1/>', $segmentXml);

                    // Add proper indentation
                    $xml .= "    " . trim($segmentXml) . "\n";
                } else {
                    // ✅ Fallback: Build from data
                    $xml .= $this->buildSegmentFromData($segment);
                }
            }
        }

        return $xml;
    }

    /**
     * ✅ Fallback: Build segment from data if XML not available
     */
    private function buildSegmentFromData(array $segment): string
    {
        $key = $segment['key'];
        $group = $segment['group'];
        $carrier = $segment['carrier'];
        $flightNumber = $segment['flight_number'];
        $origin = $segment['departure']['airport_code'];
        $destination = $segment['arrival']['airport_code'];
        $departureTime = $segment['departure']['date_time_zoon'];
        $arrivalTime = $segment['arrival']['date_time_zoon'];
        $flightTime = $segment['duration'];
        $distance = $segment['miles'] ?? 0;
        $equipment = $segment['aircraft'];
        $classOfService = $segment['booking_info']['booking_code'] ?? 'Y';
        $eTicketability = $segment['eTicketable'] ?? 'Yes';
        $changeOfPlane = $segment['change_of_plane'] ? 'true' : 'false';
        $linkAvailability = $segment['link_availability'] ? 'true' : 'false';
        $optionalServicesIndicator = $segment['optional_services_indicator'] ? 'true' : 'false';
        $participantLevel = $segment['participant_level'] ?? 'Secure Sell';
        $polledAvailabilityOption = $segment['polled_availability_option'] ?? 'Polled avail used';
        $availabilitySource = $segment['availability_source'] ?? 'S';
        $availabilityDisplayType = $segment['availability_display_type'] ?? 'Fare Shop/Optimal Shop';
        $providerCode = $segment['provider_code'] ?? '1G';

        // Handle terminal attributes
        $originTerminal = !empty($segment['departure']['terminal']) ? " OriginTerminal=\"{$segment['departure']['terminal']}\"" : '';
        $destTerminal = !empty($segment['arrival']['terminal']) ? " DestinationTerminal=\"{$segment['arrival']['terminal']}\"" : '';

        return <<<SEGMENT
    <air:AirSegment Key="{$key}" Group="{$group}" Carrier="{$carrier}" FlightNumber="{$flightNumber}" Origin="{$origin}" Destination="{$destination}" DepartureTime="{$departureTime}" ArrivalTime="{$arrivalTime}" FlightTime="{$flightTime}" Distance="{$distance}" ETicketability="{$eTicketability}" Equipment="{$equipment}" ChangeOfPlane="{$changeOfPlane}" ParticipantLevel="{$participantLevel}" LinkAvailability="{$linkAvailability}" PolledAvailabilityOption="{$polledAvailabilityOption}" OptionalServicesIndicator="{$optionalServicesIndicator}" AvailabilitySource="{$availabilitySource}" AvailabilityDisplayType="{$availabilityDisplayType}" ProviderCode="{$providerCode}" ClassOfService="{$classOfService}"{$originTerminal}{$destTerminal}/>

SEGMENT;
    }

    /**
     * ✅ Build complete AirPricingInfo with full FareInfo elements
     */
    private function buildCompletePricingInfo(array $flightData, array $passengers, string $departureDate): string
    {
        $passengerData = $flightData['passengers'][0] ?? [];
        $pricingInfo = $passengerData['pricing_info'] ?? [];

        $key = $pricingInfo['key'] ?? base64_encode('AirPricingInfo' . uniqid());
        $totalPrice = 'BDT' . ($flightData['price']['api_subtotal'] ?? 0);
        $basePrice = 'USD' . number_format($passengerData['base_fare'] ?? 0, 2, '.', '');
        $equivalentBase = 'BDT' . ($passengerData['equivalent_amount'] ?? 0);
        $taxes = 'BDT' . ($flightData['price']['api_tax'] ?? 0);
        $platingCarrier = $flightData['validating_carrier'] ?? 'GF';

        // Ticketing time
        $ticketDate = $flightData['last_ticket_date'] ?? date('Y-m-d', strtotime('+3 days'));
        $ticketTime = $flightData['last_ticket_time'] ?? '23:59:00';
        $latestTicketingTime = $ticketDate . 'T' . $ticketTime . '.000+06:00';

        // Attributes
        $pricingMethod = $flightData['pricing_method'] ?? 'Guaranteed';
        $refundable = $flightData['refundable'] ? 'true' : 'false';
        $eTicketability = $flightData['eTicketable'] ? 'Yes' : 'No';

        // ✅ Build FareInfo elements (using stored XML)
        $fareInfoXml = $this->buildFareInfoFromStoredData($flightData);

        // ✅ Build BookingInfo with HostTokenRef
        $bookingInfoXml = $this->buildBookingInfoFromData($flightData['legs']);

        // ✅ Build TaxInfo
        $taxInfoXml = $this->buildTaxInfo($passengerData);

        // ✅ Build FareCalc
        $fareCalcData = $flightData['fare_calcs'][0] ?? [];
        $fareCalc = htmlspecialchars($fareCalcData['fare_calc'] ?? '', ENT_XML1);
        $fareCalcXml = $fareCalc ? "        <air:FareCalc>{$fareCalc}</air:FareCalc>\n" : '';

        // ✅ Build PassengerType for each passenger
        $passengerTypeXml = $this->buildPassengerTypes($passengers, $departureDate);

        // ✅ Build Change/Cancel Penalties (optional in booking, but good to include)
        $penaltiesXml = ''; // Omitted for booking request

        return <<<XML
    <air:AirPricingInfo Key="{$key}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$totalPrice}" ApproximateBasePrice="{$equivalentBase}" EquivalentBasePrice="{$equivalentBase}" Taxes="{$taxes}" LatestTicketingTime="{$latestTicketingTime}" PricingMethod="{$pricingMethod}" IncludesVAT="false" ETicketability="{$eTicketability}" Refundable="{$refundable}" PlatingCarrier="{$platingCarrier}" ProviderCode="{$this->providerCode}">
{$fareInfoXml}
{$bookingInfoXml}
{$taxInfoXml}
{$fareCalcXml}
{$passengerTypeXml}
    </air:AirPricingInfo>
XML;
    }

    /**
     * ✅ Build FareInfo from stored XML in segments
     */
    private function buildFareInfoFromStoredData(array $flightData): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            foreach ($leg['segments'] as $segment) {
                $fareInfo = $segment['fare_info'] ?? null;
                if (!$fareInfo) continue;

                $fareKey = $fareInfo['key'];

                if (in_array($fareKey, $processedKeys)) continue;
                $processedKeys[] = $fareKey;

                // ✅ Use stored XML if available
                if (!empty($fareInfo['xml'])) {
                    $fareInfoXml = $fareInfo['xml'];

                    // Decode if JSON-encoded
                    if (strpos($fareInfoXml, '\u003C') !== false || strpos($fareInfoXml, '\\u003C') !== false) {
                        $fareInfoXml = json_decode('"' . $fareInfoXml . '"');
                    }

                    // ✅ FIX: ENSURE air: namespace prefix exists
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?FareInfo/', '<$1air:FareInfo', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?FareSurcharge/', '<$1air:FareSurcharge', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?BaggageAllowance/', '<$1air:BaggageAllowance', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?MaxWeight/', '<$1air:MaxWeight', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?FareRuleKey/', '<$1air:FareRuleKey', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?Brand/', '<$1air:Brand', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?UpsellBrand/', '<$1air:UpsellBrand', $fareInfoXml);
                    $fareInfoXml = preg_replace('/<(\/?)(air:)?Title/', '<$1air:Title', $fareInfoXml);

                    // Add proper indentation
                    $lines = explode("\n", trim($fareInfoXml));
                    foreach ($lines as $line) {
                        if (trim($line)) {
                            $xml .= "        " . trim($line) . "\n";
                        }
                    }
                } else {
                    // ✅ Fallback: Build basic FareInfo
                    $xml .= $this->buildBasicFareInfo($fareInfo, $segment);
                }
            }
        }

        return $xml;
    }

    /**
     * ✅ Fallback: Build basic FareInfo if XML not stored
     */
    private function buildBasicFareInfo(array $fareInfo, array $segment): string
    {
        $fareKey = $fareInfo['key'];
        $fareBasis = $fareInfo['fare_basis_code'] ?? 'Y';
        $origin = $segment['departure']['airport_code'];
        $destination = $segment['arrival']['airport_code'];
        $departureDate = $segment['departure']['date'];
        $effectiveDate = $departureDate . 'T00:00:00.000+06:00';

        return <<<FAREINFO
        <air:FareInfo Key="{$fareKey}" FareBasis="{$fareBasis}" PassengerTypeCode="ADT" Origin="{$origin}" Destination="{$destination}" EffectiveDate="{$effectiveDate}" DepartureDate="{$departureDate}"/>

FAREINFO;
    }

    /**
     * ✅ Build BookingInfo with HostTokenRef
     */
    private function buildBookingInfoFromData(array $legs): string
    {
        $xml = '';
        $usedKeys = [];
        $tokenIndex = 1;

        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $segment) {
                $bookingInfo = $segment['booking_info'] ?? [];
                $segmentRef = $bookingInfo['segment_ref'] ?? $segment['key'];

                if (in_array($segmentRef, $usedKeys)) continue;
                $usedKeys[] = $segmentRef;

                $bookingCode = $bookingInfo['booking_code'] ?? 'Y';
                $cabinClass = $bookingInfo['cabin_class'] ?? 'Economy';
                $fareInfoRef = $bookingInfo['fare_info_ref'] ?? '';

                // ✅ Generate HostTokenRef (will match HostToken Key below)
                $hostTokenRef = $this->generateHostTokenKey($segment, $tokenIndex);
                $tokenIndex++;

                $xml .= "        <air:BookingInfo BookingCode=\"{$bookingCode}\" CabinClass=\"{$cabinClass}\" FareInfoRef=\"{$fareInfoRef}\" SegmentRef=\"{$segmentRef}\" HostTokenRef=\"{$hostTokenRef}\"/>\n";
            }
        }

        return $xml;
    }

    /**
     * ✅ Build TaxInfo elements
     */
    private function buildTaxInfo(array $passengerData): string
    {
        $xml = '';
        $taxInfos = $passengerData['tax_info'] ?? [];

        foreach ($taxInfos as $tax) {
            $taxKey = $tax['key'];
            $taxCategory = $tax['category'];
            $taxAmount = $tax['amount'];

            $xml .= "        <air:TaxInfo Category=\"{$taxCategory}\" Amount=\"{$taxAmount}\" Key=\"{$taxKey}\"/>\n";
        }

        return $xml;
    }

    /**
     * ✅ Build PassengerType elements
     */
    private function buildPassengerTypes(array $passengers, string $departureDate): string
    {
        $xml = '';

        foreach ($passengers as $index => $passenger) {
            $dob = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;
            $typeInfo = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
            $code = $typeInfo['type'];
            $travelerKey = $passenger['key'] ?? base64_encode("BookingTraveler" . ($index + 1));

            $ageAttr = '';
            if (in_array($code, ['CNN', 'INF'])) {
                $ageAttr = " Age=\"{$typeInfo['age']}\"";
            }

            $xml .= "        <air:PassengerType Code=\"{$code}\" BookingTravelerRef=\"{$travelerKey}\"{$ageAttr}/>\n";
        }

        return $xml;
    }

    /**
     * ✅ Build FareNote elements
     */
    private function buildFareNotes(array $flightData): string
    {
        $carrier = $flightData['validating_carrier'] ?? 'GF';
        $ticketDate = $flightData['last_ticket_date'] ?? date('Y-m-d', strtotime('+3 days'));
        $ticketDateFormatted = date('dMy', strtotime($ticketDate));
        $origin = $flightData['legs'][0]['departure']['airport_code'] ?? 'DAC';

        $fareNotes = [
            "LAST DATE TO PURCHASE TICKET: {$ticketDateFormatted} {$origin}",
            "TICKETING AGENCY 80EZ",
            "DEFAULT PLATING CARRIER {$carrier}",
            "FARE HAS A PLATING CARRIER RESTRICTION",
            "E-TKT REQUIRED",
            "TICKETING FEES MAY APPLY"
        ];

        $xml = '';
        foreach ($fareNotes as $note) {
            $key = base64_encode('FareNote' . uniqid());
            $xml .= "            <air:FareNote Key=\"{$key}\">{$note}</air:FareNote>\n";
        }

        return $xml;
    }

    /**
     * ✅ Build HostToken elements
     * Since your data doesn't have host_tokens array populated,
     * we'll generate placeholder tokens that match Travelport's format
     */
//    private function buildHostTokensFromData(array $flightData): string
//    {
//        $xml = '';
//        $tokenIndex = 1;
//
//        foreach ($flightData['legs'] as $leg) {
//            foreach ($leg['segments'] as $segment) {
//                $bookingInfo = $segment['booking_info'] ?? [];
//                $fareInfo = $segment['fare_info'] ?? [];
//
//                $hostTokenKey = $this->generateHostTokenKey($segment, $tokenIndex);
//                $hostTokenValue = $this->generateHostTokenValue($segment, $bookingInfo, $fareInfo, $tokenIndex);
//
//                $xml .= "            <common_v52_0:HostToken Key=\"{$hostTokenKey}\" xmlns:common_v52_0=\"http://www.travelport.com/schema/common_v52_0\">{$hostTokenValue}</common_v52_0:HostToken>\n";
//
//                $tokenIndex++;
//            }
//        }
//
//        return $xml;
//    }

    private function buildHostTokensFromData(array $flightData): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            foreach ($leg['segments'] as $segment) {
                $segKey      = $segment['key'];
                $bookingInfo = $segment['booking_info'] ?? [];

                if (isset($processedKeys[$segKey])) continue;
                $processedKeys[$segKey] = true;

                $hostTokenKey   = $bookingInfo['host_token_ref'] ?? base64_encode($segKey . '_HT');
                $hostTokenValue = $bookingInfo['host_token'] ?? null;

                if (empty($hostTokenValue)) continue;

                // ✅ xmlns:common_v52_0 সরানো হয়েছে — parent এ declare আছে
                $xml .= "            <common_v52_0:HostToken Key=\"{$hostTokenKey}\">{$hostTokenValue}</common_v52_0:HostToken>\n";
            }
        }

        return $xml;
    }

    /**
     * ✅ Generate HostToken Key (matches format in demo)
     */
    private function generateHostTokenKey(array $segment, int $index): string
    {
        // Use segment key or generate unique key
        $baseKey = $segment['key'] . '_HT' . $index;
        return base64_encode($baseKey);
    }

    /**
     * ✅ Generate HostToken Value matching Travelport format
     * Format: GFB10101ADT00  {segment_num}{fare_basis}...
     */
    private function generateHostTokenValue(array $segment, array $bookingInfo, array $fareInfo, int $segmentNum): string
    {
        $carrier = $segment['carrier'] ?? 'GF';
        $fareBasis = $fareInfo['fare_basis_code'] ?? 'Y';
        $bookingCode = $bookingInfo['booking_code'] ?? 'Y';

        // Format segment number as 2 digits
        $segNumFormatted = str_pad($segmentNum, 2, '0', STR_PAD_LEFT);

        // Generate token in Travelport format
        // Format: GFB10101ADT00  {seg}{fareBasis}{spaces}{provider}{paxType}{bookingCode}
        $token = "GFB10101ADT00  {$segNumFormatted}{$fareBasis}";
        $token = str_pad($token, 50, ' ', STR_PAD_RIGHT);
        $token .= "#{$this->providerCode} ADT{$bookingCode}";

        return $token;
    }

    /**
     * ✅ Build main booking XML envelope
     */
    public function buildBookingXml(
        array  $passengers,
        string $pricingSolutionXml,
        string $departureDate,
        string $platingCarrier = '',
               $traceId = null,
        array  $remarks = [],
        string $ticketDate = ''
    ): string {
        $traceId    = $traceId ?? bin2hex(random_bytes(8));
        $ticketDate = $ticketDate ?: date('Y-m-d', strtotime('+3 days'));

        $travelersXml = $this->buildTravelersXml($passengers, $departureDate, $platingCarrier);
        $remarksXml   = $this->buildGeneralRemarks($remarks);

        $actionStatusXml = "<com:ActionStatus TicketDate=\"{$ticketDate}\" Type=\"TAU\" ProviderCode=\"{$this->providerCode}\"/>";

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <univ:AirCreateReservationReq
            RetainReservation="Both"
            RetrieveProviderReservationDetails="true"
            TraceId="{$traceId}"
            TargetBranch="{$this->targetBranch}"
            AuthorizedBy="{$this->authorizedBy}"
            Version="0"
            xmlns:univ="http://www.travelport.com/schema/universal_v52_0"
            xmlns:com="http://www.travelport.com/schema/common_v52_0"
            xmlns:air="http://www.travelport.com/schema/air_v52_0"
            xmlns:common_v52_0="http://www.travelport.com/schema/common_v52_0">
            <com:BillingPointOfSaleInfo OriginApplication="UAPI"/>
{$travelersXml}
{$remarksXml}
            <com:ContinuityCheckOverride Key="1T">true</com:ContinuityCheckOverride>
{$pricingSolutionXml}
            {$actionStatusXml}
        </univ:AirCreateReservationReq>
    </soap:Body>
</soap:Envelope>
XML;
    }

    /**
     * ✅ Build travelers XML
     */
//    private function buildTravelersXml(array $passengers, string $departureDate, string $carrier): string
//    {
//        $xml = '';
//        foreach ($passengers as $index => $passenger) {
//            $passenger['departure_date'] = $departureDate;
//            $xml .= $this->buildSingleTraveler($passenger, $index, $carrier);
//        }
//        return $xml;
//    }


    private function buildTravelersXml(array $passengers, string $departureDate, string $carrier): string
    {
        usort($passengers, function($a, $b) use ($departureDate) {
            $order = ['ADT' => 1, 'INF' => 2, 'CNN' => 3];
            $typeA = $this->helper->getPassengerTypeFromDOB($a['dob'] ?? $a['date_of_birth'] ?? null, $departureDate)['type'];
            $typeB = $this->helper->getPassengerTypeFromDOB($b['dob'] ?? $b['date_of_birth'] ?? null, $departureDate)['type'];
            return ($order[$typeA] ?? 4) <=> ($order[$typeB] ?? 4);
        });

        $xml = '';
        $addressAdded = false;
        foreach ($passengers as $index => $passenger) {
            $passenger['departure_date'] = $departureDate;
            $passenger['_skip_address'] = $addressAdded;
            if (!$addressAdded && (!empty($passenger['address']) || !empty($passenger['city']))) {
                $addressAdded = true;
            }
            $xml .= $this->buildSingleTraveler($passenger, $index, $carrier);
        }
        return $xml;
    }
    /**
     * ✅ Build single traveler
     */
    private function buildSingleTraveler(array $passenger, int $index, string $carrier = ''): string
    {
        $dob           = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;
        $departureDate = $passenger['departure_date'] ?? null;

        $typeInfo     = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
        $travelerType = $typeInfo['type'];
        $age          = $typeInfo['age'];

        $key = $passenger['key'] ?? base64_encode("BookingTraveler" . ($index + 1));

        $genderRaw = strtolower($passenger['gender'] ?? 'male');
        $isFemale  = in_array($genderRaw, ['f', 'female']);
        $gender    = $isFemale ? 'F' : 'M';

        // ── Prefix: DB title থেকে নাও, না থাকলে gender+type থেকে auto ──
        $dbTitle = strtoupper(trim($passenger['title'] ?? ''));
        $allowed = ['MR', 'MRS', 'MS', 'MISS', 'MSTR', 'DR', 'PROF'];

        if ($dbTitle && in_array($dbTitle, $allowed)) {
            $prefix = ucfirst(strtolower($dbTitle));
        } else {
            if (in_array($travelerType, ['CNN', 'INF'])) {
                $prefix = $isFemale ? 'Miss' : 'Mstr';
            } else {
                $prefix = $isFemale ? 'Ms' : 'Mr';
            }
        }

        // ✅ Prefix এর শেষে space — GDS এ FirstName এর আগে space থাকবে
//        $prefix = rtrim($prefix) . ' ';
        $prefix = ' ' . rtrim($prefix);

        // ── Name — trim করো, DB তে অতিরিক্ত space থাকলে সরাও ──
        $firstName = strtoupper(trim($passenger['first_name'] ?? ''));
        $lastName  = strtoupper(trim($passenger['last_name']  ?? ''));

        // ── DOB attribute ──
        $dobAttr = '';
        if ($dob) {
            $ts = strtotime($dob);
            if ($ts !== false && $ts > 0) {
                $dobAttr = ' DOB="' . date('Y-m-d', $ts) . '"';
            }
        }

        // ── Age attribute (CNN/INF only) ──
        $ageAttr = '';
        if (in_array($travelerType, ['CNN', 'INF'])) {
            $ageAttr = " Age=\"{$age}\"";
        }

        $phoneXml   = $this->buildPhoneNumber($passenger, $index);
        $emailXml   = $this->buildEmail($passenger);
        $ssrXml     = $this->buildSSR($passenger, $index, $carrier, $travelerType);
        $contactSsr = ($index === 0) ? $this->buildContactSSRs($passenger, $carrier) : '';
        $addressXml = $this->buildAddress($passenger);

        $xml  = "            <com:BookingTraveler Key=\"{$key}\" TravelerType=\"{$travelerType}\" Gender=\"{$gender}\"{$dobAttr}{$ageAttr}>\n";
        $xml .= "                <com:BookingTravelerName Prefix=\"{$prefix}\" First=\"{$firstName}\" Last=\"{$lastName}\"/>\n";

        if ($phoneXml)   $xml .= $phoneXml   . "\n";
        if ($emailXml)   $xml .= $emailXml   . "\n";
        if ($ssrXml)     $xml .= $ssrXml     . "\n";
        if ($contactSsr) $xml .= $contactSsr . "\n";

        if ($travelerType === 'CNN') {
            $ageFormatted = str_pad($age, 2, '0', STR_PAD_LEFT);
            $xml .= "                <com:NameRemark><com:RemarkData>P-C{$ageFormatted}</com:RemarkData></com:NameRemark>\n";
        }

        if ($addressXml) $xml .= $addressXml . "\n";

        $xml .= "            </com:BookingTraveler>\n";

        return $xml;
    }


    private function buildNameRemark(string $travelerType, int $age): string
    {
        if ($travelerType !== 'CNN') return '';
        $ageFormatted = str_pad($age, 2, '0', STR_PAD_LEFT);
        return "                <com:NameRemark><com:RemarkData>P-C{$ageFormatted}</com:RemarkData></com:NameRemark>\n";
    }
    private function buildPhoneNumber(array $passenger, int $index): string
    {
        $phone = $passenger['phone'] ?? $passenger['mobile'] ?? null;
        if (!$phone) return '';

        $key    = 1005359 + $index;
        $number = preg_replace('/[^0-9]/', '', $phone);

        $countryIso = strtoupper($passenger['country'] ?? 'BD');
        $country    = \App\Models\Country::where('code', $countryIso)->first();

        $phoneCode = $country ? explode('-', $country->phone_code)[0] : '880';
        $location  = $country?->iata_code ?? 'DAC';

        return "                <com:PhoneNumber Key=\"{$key}\" CountryCode=\"{$phoneCode}\" Location=\"{$location}\" Number=\"{$number}\" Type=\"Mobile\"/>";
    }

    private function buildEmail(array $passenger): string
    {
        $email = $passenger['email'] ?? null;
        if (!$email) return '';

        return <<<EMAIL
                <com:Email Type="Home" EmailID="{$email}"/>
EMAIL;
    }

    private function buildSSR(array $passenger, int $index, string $carrier = '', string $travelerType = ''): string
    {
        // ✅ Passport — string cast করো (leading zeros রক্ষার জন্য)
        $passportNumber = (string)($passenger['passport_number'] ?? '');
        if (empty($passportNumber)) return '';

        $passportNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($passportNumber)));
        if (empty($passportNumber)) return '';

        $dob          = $passenger['dob'] ?? $passenger['date_of_birth'] ?? '';
        $nationality  = strtoupper($passenger['nationality'] ?? 'BD');
        $issueCountry = strtoupper($passenger['passport_issue_country'] ?? $nationality);
        $gender       = strtoupper(substr($passenger['gender'] ?? 'M', 0, 1));
        $expiry       = $passenger['passport_expiry_date'] ?? '';
        $lastName     = strtoupper(trim($passenger['last_name']  ?? ''));
        $firstName    = strtoupper(trim($passenger['first_name'] ?? ''));

        // ✅ DOB — validate করো, empty হলে SSR skip
        $dobFormatted = '';
        if (!empty($dob)) {
            $ts = strtotime($dob);
            if ($ts !== false && $ts > 0) {
                $dobFormatted = strtoupper(date('dMy', $ts));
            }
        }
        if (empty($dobFormatted)) {
            Log::warning('DOCS SSR skipped — DOB missing or invalid', [
                'index'    => $index,
                'passport' => $passportNumber,
                'dob_raw'  => $dob,
            ]);
            return '';
        }

        // ✅ Expiry — validate করো
        $expiryFormatted = '';
        if (!empty($expiry)) {
            $ts = strtotime($expiry);
            if ($ts !== false && $ts > 0) {
                $expiryFormatted = strtoupper(date('dMy', $ts));
            }
        }

        // ✅ INF gender suffix
        if ($travelerType === 'INF') {
            $gender .= 'I';
        }

        $freeText    = "P/{$issueCountry}/{$passportNumber}/{$nationality}/{$dobFormatted}/{$gender}/{$expiryFormatted}/{$lastName}/{$firstName}";
        $carrierAttr = $carrier ? " Carrier=\"{$carrier}\"" : '';

        Log::info('DOCS SSR', [
            'passport'       => $passportNumber,
            'dob_raw'        => $dob,
            'dob_format'     => $dobFormatted,
            'expiry_raw'     => $expiry,
            'expiry_format'  => $expiryFormatted,
            'freeText'       => $freeText,
        ]);

        return "                <com:SSR Key=\"{$index}\" Type=\"DOCS\" Status=\"HK\"{$carrierAttr} FreeText=\"{$freeText}\"/>";
    }

    private function buildContactSSRs(array $passenger, string $carrier): string
    {
        if (empty($carrier)) return '';

        $xml = '';
        $phone = preg_replace('/[^0-9]/', '', $passenger['phone'] ?? $passenger['mobile'] ?? '');
        $email = $passenger['email'] ?? '';

        if ($phone) {
            $xml .= "                <com:SSR Type=\"CTCM\" Status=\"HK\" Carrier=\"{$carrier}\" FreeText=\"{$phone}\"/>\n";
        }
        if ($email) {
            // ✅ Official format: @ → //, _ → .., - → ./
            $formattedEmail = str_replace('@', '//', $email);
            $formattedEmail = str_replace('_', '..', $formattedEmail);
            $formattedEmail = str_replace('-', './', $formattedEmail);

            $xml .= "                <com:SSR Type=\"CTCE\" Status=\"HK\" Carrier=\"{$carrier}\" FreeText=\"{$formattedEmail}\"/>\n";
        }

        return $xml;
    }



    private function buildAddress(array $passenger): string
    {
        if (empty($passenger['address']) && empty($passenger['city'])) {
            return '';
        }

        $addressName = $passenger['address_name'] ?? ($passenger['first_name'] . ' ' . $passenger['last_name']);
        $street = $passenger['address'] ?? $passenger['street'] ?? '';
        $street2 = $passenger['address2'] ?? $passenger['street2'] ?? '';
        $city = $passenger['city'] ?? '';
        $state = $passenger['state'] ?? '';
        $postalCode = $passenger['postal_code'] ?? $passenger['zip'] ?? '';
        $country = $passenger['country'] ?? 'BD';

        $streetXml = $street ? "                    <com:Street>{$street}</com:Street>\n" : '';
        $street2Xml = $street2 ? "                    <com:Street>{$street2}</com:Street>\n" : '';
        $stateXml = $state ? "                    <com:State>{$state}</com:State>\n" : '';
        $postalXml = $postalCode ? "                    <com:PostalCode>{$postalCode}</com:PostalCode>\n" : '';

        return <<<ADDRESS
                <com:Address>
                    <com:AddressName>{$addressName}</com:AddressName>
{$streetXml}{$street2Xml}                    <com:City>{$city}</com:City>
{$stateXml}{$postalXml}                    <com:Country>{$country}</com:Country>
                </com:Address>
ADDRESS;
    }

    private function buildGeneralRemarks(array $remarks): string
    {
        if (empty($remarks)) {
            return '';
        }

        $xml = '';
        foreach ($remarks as $remark) {
            $text = is_array($remark) ? ($remark['text'] ?? '') : $remark;
            $type = is_array($remark) ? ($remark['type'] ?? 'Basic') : 'Basic';

            // ✅ xmlns:com সরানো হয়েছে
            $xml .= <<<REMARK
            <com:GeneralRemark UseProviderNativeMode="true" TypeInGds="{$type}">
                <com:RemarkData>{$text}</com:RemarkData>
            </com:GeneralRemark>

REMARK;
        }

        return $xml;
    }






    private function buildAirPricingInfoFromData(array $flightData, array $passengers, string $departureDate): string
    {
        $pricingInfo = $flightData['passengers'][0]['pricing_info'] ?? [];
        $key = $pricingInfo['key'] ?? base64_encode('AirPricingInfo' . uniqid());

        $totalPrice = 'BDT' . ($flightData['price']['api_subtotal'] ?? 0);
        $basePrice = 'USD' . number_format($flightData['passengers'][0]['base_fare'] ?? 0, 2, '.', '');
        $equivalentBase = 'BDT' . ($flightData['passengers'][0]['equivalent_amount'] ?? 0);
        $taxes = 'BDT' . ($flightData['price']['api_tax'] ?? 0);
        $platingCarrier = $flightData['validating_carrier'] ?? '';
        $refundable = ($flightData['refundable'] ?? false) ? 'true' : 'false';

        // ✅ LatestTicketingTime from last_ticket_date/time
        $latestTicketingTime = ($flightData['last_ticket_date'] ?? date('Y-m-d', strtotime('+3 days'))) . 'T23:59:00.000+06:00';

        // ✅ Build FareInfo elements (not FareInfoRef) - matching demo
        $fareInfoXml = $this->buildFareInfoElements($flightData);

        // ✅ Build BookingInfo with HostTokenRef - matching demo
        $bookingInfoXml = $this->buildBookingInfoWithHostToken($flightData['legs']);

        // ✅ Build TaxInfo elements
        $taxInfoXml = '';
        $taxInfos = $flightData['passengers'][0]['tax_info'] ?? [];
        foreach ($taxInfos as $tax) {
            $taxKey = $tax['key'] ?? base64_encode('TaxInfo' . uniqid());
            $taxCategory = $tax['category'] ?? '';
            $taxAmount = $tax['amount'] ?? '';
            $taxInfoXml .= "        <air:TaxInfo Category=\"{$taxCategory}\" Amount=\"{$taxAmount}\" Key=\"{$taxKey}\"/>\n";
        }

        // ✅ Build FareCalc
        $fareCalc = htmlspecialchars($flightData['fare_calcs'][0]['fare_calc'] ?? '', ENT_XML1);
        $fareCalcXml = $fareCalc ? "        <air:FareCalc>{$fareCalc}</air:FareCalc>\n" : '';

        // ✅ Build PassengerType for each passenger
        $passengerTypeXml = $this->buildPassengerTypesFromData($passengers, $departureDate);

        // Note: Demo doesn't have penalties in AirPricingInfo, they're in FareInfo/Brand
        // So we skip penalties here

        return <<<XML
    <air:AirPricingInfo Key="{$key}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$totalPrice}" ApproximateBasePrice="{$equivalentBase}" EquivalentBasePrice="{$equivalentBase}" Taxes="{$taxes}" LatestTicketingTime="{$latestTicketingTime}" PricingMethod="Guaranteed" IncludesVAT="false" ETicketability="Yes" PlatingCarrier="{$platingCarrier}" ProviderCode="{$this->providerCode}">
{$fareInfoXml}
{$bookingInfoXml}
{$taxInfoXml}
{$fareCalcXml}
{$passengerTypeXml}
    </air:AirPricingInfo>
XML;
    }



    /**
     * ✅ UPDATED: Build BookingInfo WITH HostTokenRef (like demo)
     */
    private function buildBookingInfoWithHostToken(array $legs): string
    {
        $xml = '';
        $usedKeys = [];

        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $segment) {
                $bookingInfo = $segment['booking_info'] ?? [];
                $segmentRef = $bookingInfo['segment_ref'] ?? $segment['key'];

                // Skip duplicates
                if (in_array($segmentRef, $usedKeys)) {
                    continue;
                }
                $usedKeys[] = $segmentRef;

                $bookingCode = $bookingInfo['booking_code'] ?? 'Y';
                $cabinClass = $bookingInfo['cabin_class'] ?? 'Economy';
                $fareInfoRef = $bookingInfo['fare_info_ref'] ?? '';

                // ✅ Add HostTokenRef (critical for demo format)
                $hostTokenRef = $bookingInfo['host_token_ref'] ?? base64_encode('HostToken' . $segmentRef);

                $xml .= "        <air:BookingInfo BookingCode=\"{$bookingCode}\" CabinClass=\"{$cabinClass}\" FareInfoRef=\"{$fareInfoRef}\" SegmentRef=\"{$segmentRef}\" HostTokenRef=\"{$hostTokenRef}\"/>\n";
            }
        }

        return $xml;
    }

    /**
     * ✅ NEW: Build FareNote elements (like demo)
     */
    private function buildFareNotesnow(array $flightData): string
    {
        $fareNotes = $flightData['fare_notes'] ?? [
            'LAST DATE TO PURCHASE TICKET: ' . date('dMy', strtotime('+3 days')) . ' DAC',
            'TICKETING AGENCY 80EZ',
            'DEFAULT PLATING CARRIER ' . ($flightData['validating_carrier'] ?? 'BS'),
            'FARE HAS A PLATING CARRIER RESTRICTION',
            'E-TKT REQUIRED',
            'TICKETING FEES MAY APPLY'
        ];

        $xml = '';
        foreach ($fareNotes as $note) {
            $key = base64_encode('FareNote' . uniqid());
            $xml .= "            <air:FareNote Key=\"{$key}\">{$note}</air:FareNote>\n";
        }

        return $xml;
    }

    /**
     * ✅ NEW: Build HostToken elements (like demo)
     */
    private function buildHostTokens(array $flightData): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            foreach ($leg['segments'] as $segment) {
                $segKey      = $segment['key'];
                $bookingInfo = $segment['booking_info'] ?? [];

                if (isset($processedKeys[$segKey])) continue;
                $processedKeys[$segKey] = true;

                $hostToken    = $bookingInfo['host_token'] ?? null;
                if (!$hostToken) continue; // fake token পাঠানো বিপদজনক

                $hostTokenRef = $bookingInfo['host_token_ref'] ?? base64_encode('HostToken' . $segKey);

                // ✅ xmlns:common_v52_0 সরানো হয়েছে
                $xml .= "            <common_v52_0:HostToken Key=\"{$hostTokenRef}\">{$hostToken}</common_v52_0:HostToken>\n";
            }
        }

        return $xml;
    }

    /**
     * ✅ NEW: Generate host token if not available from API
     */
    private function generateHostToken(array $segment, array $bookingInfo): string
    {
        // This is a simplified version
        // Real tokens come from the pricing response
        $fareBasis = $bookingInfo['fare_basis'] ?? 'Y';
        $bookingCode = $bookingInfo['booking_code'] ?? 'Y';
        $carrier = $segment['carrier'] ?? '';

        return "GFB10101ADT00  {$fareBasis}                                 #GFMCSIP306N {$carrier} ADT{$bookingCode}";
    }

    private function buildFareInfoElements(array $flightData): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            foreach ($leg['segments'] as $segment) {
                $fareInfo = $segment['fare_info'] ?? null;
                if (!$fareInfo) continue;

                $fareKey = $fareInfo['key'] ?? base64_encode('FareInfo' . uniqid());

                if (in_array($fareKey, $processedKeys)) continue;
                $processedKeys[] = $fareKey;

                $fareBasis = $fareInfo['fare_basis_code'] ?? 'Y';
                $passengerTypeCode = 'ADT'; // Can be dynamic based on passengers
                $origin = $segment['departure']['airport_code'] ?? '';
                $destination = $segment['arrival']['airport_code'] ?? '';
                $departureDate = $segment['departure']['date'] ?? date('Y-m-d');
                $effectiveDate = date('Y-m-d\TH:i:s', strtotime($departureDate)) . '.000+06:00';
                $amount = 'BDT' . ($fareInfo['amount'] ?? '0');

                // Validity dates
                $notValidBefore = $departureDate;
                $notValidAfter = $departureDate;
                $taxAmount = 'BDT' . ($fareInfo['tax_amount'] ?? '0');

                // ✅ Build endorsements if available
                $endorsementsXml = $this->buildEndorsements($fareInfo);

                // ✅ Build FareRuleKey
                $fareRuleKey = $fareInfo['fare_rule_key'] ?? base64_encode('FareRule' . $fareKey);
                $fareRuleKeyXml = <<<FARERULE
                  <air:FareRuleKey FareInfoRef="{$fareKey}" ProviderCode="{$this->providerCode}">{$fareRuleKey}</air:FareRuleKey>

FARERULE;

                // ✅ Build Brand info if available (demo has extensive Brand info)
                $brandXml = $this->buildBrandInfo($fareInfo, $fareKey);

                $xml .= <<<FAREINFO
        <air:FareInfo Key="{$fareKey}" FareBasis="{$fareBasis}" PassengerTypeCode="{$passengerTypeCode}" Origin="{$origin}" Destination="{$destination}" EffectiveDate="{$effectiveDate}" DepartureDate="{$departureDate}" Amount="{$amount}" NotValidBefore="{$notValidBefore}" NotValidAfter="{$notValidAfter}" TaxAmount="{$taxAmount}">
{$endorsementsXml}{$fareRuleKeyXml}{$brandXml}        </air:FareInfo>

FAREINFO;
            }
        }

        return $xml;
    }


    /**
     * ✅ NEW: Build Brand info (demo has extensive brand information)
     * Note: This is optional but demo includes it
     */
    private function buildBrandInfo(array $fareInfo, string $fareKey): string
    {
        // If brand info not available, return empty
        if (empty($fareInfo['brand'])) {
            return '';
        }

        $brand = $fareInfo['brand'];
        $brandID = $brand['brand_id'] ?? '245008';
        $upSellBrandID = $brand['upsell_brand_id'] ?? '245011';
        $name = $brand['name'] ?? 'Saver';
        $carrier = $brand['carrier'] ?? 'SA';
        $brandTier = $brand['tier'] ?? '0001';

        // Build titles
        $titleExternalXml = '';
        $titleShortXml = '';
        if (!empty($brand['title_external'])) {
            $titleExternalXml = "                     <air:Title Type=\"External\" LanguageCode=\"EN\">{$brand['title_external']}</air:Title>\n";
        }
        if (!empty($brand['title_short'])) {
            $titleShortXml = "                     <air:Title Type=\"Short\" LanguageCode=\"EN\">{$brand['title_short']}</air:Title>\n";
        }

        // Build text descriptions
        $textConsumerXml = '';
        $textAgentXml = '';
        if (!empty($brand['text_consumer'])) {
            $textConsumerXml = "                     <air:Text Type=\"MarketingConsumer\" LanguageCode=\"EN\">{$brand['text_consumer']}</air:Text>\n";
        }
        if (!empty($brand['text_agent'])) {
            $textAgentXml = "                     <air:Text Type=\"MarketingAgent\" LanguageCode=\"EN\">{$brand['text_agent']}</air:Text>\n";
        }

        // Note: Demo has OptionalServices and Rules, but they're complex
        // For now, we'll add placeholders or skip them

        return <<<BRAND
                  <air:Brand Key="{$fareKey}" BrandID="{$brandID}" UpSellBrandID="{$upSellBrandID}" Name="{$name}" Carrier="{$carrier}" BrandTier="{$brandTier}">
{$titleExternalXml}{$titleShortXml}{$textConsumerXml}{$textAgentXml}                  </air:Brand>

BRAND;
    }
    /**
     * ✅ NEW: Build endorsements (like demo: NONEND, NONREF, etc.)
     */
    private function buildEndorsements(array $fareInfo): string
    {
        $endorsements = $fareInfo['endorsements'] ?? [];

        // endorsements না থাকলে default দেওয়া বন্ধ — carrier specific endorsement ভুল হতে পারে
        if (empty($endorsements)) {
            return '';
        }

        $xml = '';
        foreach ($endorsements as $endorsement) {
            // ✅ xmlns:common_v52_0 সরানো হয়েছে — parent এ declare আছে
            $xml .= "                  <common_v52_0:Endorsement Value=\"{$endorsement}\"/>\n";
        }

        return $xml;
    }
    /**
     * ✅ NEW: Build FareInfo elements from flight data
     */
    private function buildFareInfoFromDataold(array $flightData): string
    {
        $xml = '';

        // Get fare info from passengers data
        $fareInfoKeys = $flightData['passengers'][0]['fare_info_keys'] ?? [];

        // If no fare info keys, build from legs
        if (empty($fareInfoKeys)) {
            return $this->buildFareInfoFromLegs($flightData['legs']);
        }

        // Build FareInfo for each leg/direction
        foreach ($flightData['legs'] as $legIndex => $leg) {
            $fareKey = $fareInfoKeys[$legIndex] ?? "fareInfo" . ($legIndex + 1);

            $origin = $leg['segments'][0]['departure']['airport_code'] ?? '';
            $destination = end($leg['segments'])['arrival']['airport_code'] ?? '';
            $departureDate = $leg['departure']['date'] ?? date('Y-m-d');

            // Get fare basis from first segment of leg
            $fareBasis = $leg['segments'][0]['booking_info']['fare_basis'] ?? 'KBDXB6M';

            $xml .= <<<FAREINFO
        <air:FareInfo
            Key="{$fareKey}"
            FareBasis="{$fareBasis}"
            PassengerTypeCode="ADT"
            Origin="{$origin}"
            Destination="{$destination}"
            EffectiveDate="{$departureDate}"
            DepartureDate="{$departureDate}">
            <air:FareRuleKey FareInfoRef="{$fareKey}" ProviderCode="{$this->providerCode}" Value="{$fareBasis}"/>
        </air:FareInfo>

FAREINFO;
        }

        return $xml;
    }


    /**
     * ✅ FIXED: Build FareInfo from actual data with correct keys
     */
    private function buildFareInfoFromData(array $flightData): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            foreach ($leg['segments'] as $segment) {
                $fareInfo = $segment['fare_info'] ?? null;
                if (!$fareInfo) continue;

                $fareKey = $fareInfo['key'] ?? '';

                if (in_array($fareKey, $processedKeys)) continue;
                $processedKeys[] = $fareKey;

                // ✅ Build FareInfo manually instead of using stored XML
                $fareBasis = $fareInfo['fare_basis_code'] ?? 'Y';
                $origin = $segment['departure']['airport_code'] ?? '';
                $destination = $segment['arrival']['airport_code'] ?? '';
                $departureDate = $segment['departure']['date'] ?? date('Y-m-d');

                $xml .= <<<FAREINFO
        <air:FareInfo Key="{$fareKey}" FareBasis="{$fareBasis}" PassengerTypeCode="ADT" Origin="{$origin}" Destination="{$destination}" EffectiveDate="{$departureDate}" DepartureDate="{$departureDate}"/>

FAREINFO;
            }
        }

        return $xml;
    }

    /**
     * ✅ FIXED: Build BookingInfo with correct segment references
     */
    private function buildBookingInfoFromLegs(array $legs): string
    {
        $xml = '';
        $usedKeys = [];

        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $segment) {
                $segmentRef = $segment['booking_info']['segment_ref'] ?? $segment['key'];

                // ✅ Add duplicate check
//                if (in_array($segmentRef, $usedKeys)) {
//                    continue;
//                }
//                $usedKeys[] = $segmentRef;
                $bookingInfo = $segment['booking_info'] ?? [];

                $bookingCode = $bookingInfo['booking_code'] ?? 'Y';
                $cabinClass = $bookingInfo['cabin_class'] ?? 'Economy';
                $fareInfoRef = $bookingInfo['fare_info_ref'] ?? '';
                $segmentRef = $bookingInfo['segment_ref'] ?? $segment['key'];

                // ✅ Add HostTokenRef if available
                $hostTokenAttr = '';
                if (!empty($bookingInfo['host_token_ref'])) {
                    $hostTokenAttr = " HostTokenRef=\"{$bookingInfo['host_token_ref']}\"";
                }

                $xml .= "        <air:BookingInfo BookingCode=\"{$bookingCode}\" CabinClass=\"{$cabinClass}\" FareInfoRef=\"{$fareInfoRef}\" SegmentRef=\"{$segmentRef}\"{$hostTokenAttr}/>\n";
            }
        }

        return $xml;
    }

    /**
     * ✅ FIXED: Build segments - only ONE segment per leg (not duplicates)
     */
    private function buildBookingSegmentsFromLegs(array $legs): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($legs as $leg) {
            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];

                if (isset($processedKeys[$key])) {
                    continue;
                }
                $processedKeys[$key] = true;

                // ✅ Use stored XML if available (it has correct timezone)
                if (!empty($segment['xml'])) {
                    $segmentXml = $segment['xml'];

                    // Decode if encoded
                    if (strpos($segmentXml, '\u003C') !== false) {
                        $segmentXml = json_decode('"' . $segmentXml . '"');
                    }

                    $xml .= "    " . $segmentXml . "\n";
                }
            }
        }

        return $xml;
    }
    private function buildBookingSegmentsFromLegsold2(array $legs): string
    {
        $xml = '';
        $processedKeys = [];

        foreach ($legs as $leg) {
//            $group = ($leg['leg_number'] ?? 1) - 1;

            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];

                // ✅ Avoid duplicate AirSegment
                if (isset($processedKeys[$key])) {
                    continue;
                }
                $processedKeys[$key] = true;

                $group        = $segment['group'];
                $carrier        = $segment['carrier'];
                $flightNumber   = $segment['flight_number'];
                $origin         = $segment['departure']['airport_code'];
                $destination    = $segment['arrival']['airport_code'];

                // ✅ DateTime with timezone
//                $departureTime = $segment['departure']['date'] . 'T' . $segment['departure']['time'];
//                $arrivalTime   = $segment['arrival']['date'] . 'T' . $segment['arrival']['time'];
                $departureTime = $segment['departure']['date_time_zoon'];
                $arrivalTime   = $segment['arrival']['date_time_zoon'];

//                if (!preg_match('/[+-]\d{2}:\d{2}$/', $departureTime)) {
//                    $departureTime .= '.000+02:00';
//                }
//                if (!preg_match('/[+-]\d{2}:\d{2}$/', $arrivalTime)) {
//                    $arrivalTime .= '.000+02:00';
//                }

                $flightTime     = $segment['duration'] ?? 0;
                $travelTime     = $segment['duration'] ?? 0;
                $distance       = $segment['miles'] ?? 0;
                $eticket       = $segment['eTicketable'] ?? 0;
                $change_of_plane       = $segment['change_of_plane'] ?? '';
                $equipment      = $segment['aircraft'] ?? '';
                $link_availability     = $segment['link_availability'] ?? '';
                $optional_services_indicator    = $segment['optional_services_indicator'] ?? '';
                $participant_level    = $segment['participant_level'] ?? '';
                $polled_availability_option   = $segment['polled_availability_option'] ?? '';
                $availability_source   = $segment['availability_source'] ?? '';
                $availability_display_type   = $segment['availability_display_type'] ?? '';
                $class_of_service   = $segment['class_of_service'] ?? '';
                $provider_code   = $segment['provider_code'] ?? '';
                $classOfService = $segment['booking_info']['booking_code'] ?? 'Y';

                // Codeshare (optional)
                $codeshareXml = '';
                if (!empty($segment['operating_carrier'])) {
                    $opCarrier = $segment['operating_carrier'];
                    $opName    = $segment['operating_carrier_name'] ?? '';
                    $codeshareXml = <<<CS
               <air:CodeshareInfo OperatingCarrier="{$opCarrier}">{$opName}</air:CodeshareInfo>

CS;
                }

                // FlightDetails Key (separate, unique)
                $flightDetailsKey = base64_encode($key . '_FD');

                $xml .= <<<SEGMENT
    <air:AirSegment
        Key="{$key}"
        Group="{$group}"
        Carrier="{$carrier}"
        FlightNumber="{$flightNumber}"
        Origin="{$origin}"
        Destination="{$destination}"
        DepartureTime="{$departureTime}"
        ArrivalTime="{$arrivalTime}"
        FlightTime="{$flightTime}"
        Distance="{$distance}"
        ETicketability="{$eticket}"
        Equipment="{$equipment}"
        ChangeOfPlane="{$change_of_plane}"
        ParticipantLevel="{$participant_level}"
        LinkAvailability="{$link_availability}"
        PolledAvailabilityOption="{$polled_availability_option}"
        OptionalServicesIndicator="{$optional_services_indicator}"
        AvailabilitySource="{$availability_source}"
        AvailabilityDisplayType="{$availability_display_type}"
        ProviderCode="{$provider_code}"
        ClassOfService="{$class_of_service}">

{$codeshareXml}
               <air:FlightDetails
                    Key="{$flightDetailsKey}"
                    Origin="{$origin}"
                    Destination="{$destination}"
                    DepartureTime="{$departureTime}"
                    ArrivalTime="{$arrivalTime}"
                    FlightTime="{$flightTime}"
                    TravelTime="{$travelTime}"
                    Distance="{$distance}"/>
    </air:AirSegment>

SEGMENT;
            }
        }

        return $xml;
    }

    /**
     * ✅ NEW: Build FareInfo from legs when fare_info_keys not available
     */
    private function buildFareInfoFromLegs(array $legs): string
    {
        $xml = '';

        foreach ($legs as $legIndex => $leg) {
            $fareKey = "fareInfo" . ($legIndex + 1);

            $origin = $leg['segments'][0]['departure']['airport_code'] ?? '';
            $destination = end($leg['segments'])['arrival']['airport_code'] ?? '';
            $departureDate = $leg['departure']['date'] ?? date('Y-m-d');

            // Get fare basis from booking info
            $fareBasis = 'Y'; // Default
            if (!empty($leg['segments'][0]['booking_info']['fare_basis'])) {
                $fareBasis = $leg['segments'][0]['booking_info']['fare_basis'];
            } elseif (!empty($leg['segments'][0]['booking_info']['booking_code'])) {
                $fareBasis = $leg['segments'][0]['booking_info']['booking_code'];
            }

            $xml .= <<<FAREINFO
        <air:FareInfo
            Key="{$fareKey}"
            FareBasis="{$fareBasis}"
            PassengerTypeCode="ADT"
            Origin="{$origin}"
            Destination="{$destination}"
            EffectiveDate="{$departureDate}"
            DepartureDate="{$departureDate}">
            <air:FareRuleKey FareInfoRef="{$fareKey}" ProviderCode="{$this->providerCode}" Value="{$fareBasis}"/>
        </air:FareInfo>

FAREINFO;
        }

        return $xml;
    }

    /**
     * ✅ UPDATED: Build BookingInfo with proper FareInfoRef linking
     */
    private function buildBookingInfoFromLegsold(array $legs): string
    {
        $xml = '';

        foreach ($legs as $legIndex => $leg) {
            $fareInfoRef = "fareInfo" . ($legIndex + 1);

            foreach ($leg['segments'] as $segment) {
                $bookingInfo = $segment['booking_info'] ?? [];
                $bookingCode = $bookingInfo['booking_code'] ?? 'Y';
                $cabinClass = $bookingInfo['cabin_class'] ?? 'Economy';
                $segmentRef = $segment['key'];

                // ✅ Use generated fareInfoRef
                $xml .= "        <air:BookingInfo BookingCode=\"{$bookingCode}\" CabinClass=\"{$cabinClass}\" FareInfoRef=\"{$fareInfoRef}\" SegmentRef=\"{$segmentRef}\"/>\n";
            }
        }

        return $xml;
    }


    /**
     * ✅ NEW: Build PassengerType from passengers data
     */
    private function buildPassengerTypesFromData(array $passengers, string $departureDate): string
    {
        $xml = '';

        foreach ($passengers as $index => $passenger) {
            $dob = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;
            $typeInfo = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
            $code = $typeInfo['type'];
            $travelerKey = $passenger['key'] ?? base64_encode("BookingTraveler" . ($index + 1));

            $ageAttr = '';
            if (in_array($code, ['CNN', 'INF'])) {
                $ageAttr = " Age=\"{$typeInfo['age']}\"";
            }

            $xml .= "        <air:PassengerType Code=\"{$code}\" BookingTravelerRef=\"{$travelerKey}\"{$ageAttr}/>\n";
        }

        return $xml;
    }

    /**
     * ✅ NEW: Build Penalties from data
     */
    private function buildPenaltiesFromData(array $penalties): string
    {
        $xml = '';

        if (!empty($penalties['change'])) {
            $changeApplies = $penalties['change']['applies'] ?? 'Anytime';
            $changeAmount = $penalties['change']['amount'] ?? 0;
            $xml .= <<<PENALTY
        <air:ChangePenalty PenaltyApplies="{$changeApplies}">
            <air:Amount>BDT{$changeAmount}</air:Amount>
        </air:ChangePenalty>

PENALTY;
        }

        if (!empty($penalties['cancel'])) {
            $cancelApplies = $penalties['cancel']['applies'] ?? 'Anytime';
            $cancelAmount = $penalties['cancel']['amount'] ?? 0;
            $xml .= <<<PENALTY
        <air:CancelPenalty PenaltyApplies="{$cancelApplies}">
            <air:Amount>BDT{$cancelAmount}</air:Amount>
        </air:CancelPenalty>

PENALTY;
        }

        return $xml;
    }

    /**
     * Build Booking XML from data
     */




    public function buildBookingXmlnow(
        array  $passengers,
        string $pricingSolutionXml,
        string $departureDate,
        string $platingCarrier = '',
               $traceId = null,
        array  $remarks = []
    ): string
    {
        $traceId = $traceId ?? bin2hex(random_bytes(8));

        // Build components
        $travelersXml = $this->buildTravelersXml($passengers, $departureDate, $platingCarrier);
        $remarksXml = $this->buildGeneralRemarks($remarks);
        $actionStatusXml = $this->buildActionStatus($this->getTicketDate());

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <univ:AirCreateReservationReq
            RetainReservation="Both"
            RetrieveProviderReservationDetails="true"
            TraceId="{$traceId}"
            TargetBranch="{$this->targetBranch}"
            AuthorizedBy="{$this->authorizedBy}"
            Version="0"
            xmlns:univ="http://www.travelport.com/schema/universal_v52_0">
            <com:BillingPointOfSaleInfo OriginApplication="UAPI" xmlns:com="http://www.travelport.com/schema/common_v52_0"/>
{$travelersXml}
{$remarksXml}
            <com:ContinuityCheckOverride Key="1T" xmlns:com="http://www.travelport.com/schema/common_v52_0">true</com:ContinuityCheckOverride>
{$pricingSolutionXml}
{$actionStatusXml}
        </univ:AirCreateReservationReq>
    </soap:Body>
</soap:Envelope>
XML;
    }



    private function buildActionStatus(?string $ticketDate): string
    {
        if (empty($ticketDate)) {
            return '';
        }

        return <<<XML
            <com:ActionStatus TicketDate="{$ticketDate}" Type="TAU" ProviderCode="{$this->providerCode}" xmlns:com="http://www.travelport.com/schema/common_v52_0"/>
XML;
    }

    private function buildActionStatusnow(?string $ticketDate): string
    {
        if (empty($ticketDate)) {
            return '';
        }

        return <<<XML
            <com:ActionStatus TicketDate="{$ticketDate}" Type="TAU" ProviderCode="{$this->providerCode}" xmlns:com="http://www.travelport.com/schema/common_v52_0"/>
XML;
    }
    private function getTicketDate(): ?string
    {
        // Option 1: From price response (recommended)
        if (!empty($this->latestTicketingTime)) {
            return $this->latestTicketingTime;
        }

        // Option 2: Default (24 hours from now)
         return date('Y-m-d\TH:i:s', strtotime('+24 hours'));

        // Option 3: Return null (ActionStatus won't be added)
        return null;
    }
    /**
     * ✅ NEW: Build General Remarks
     */
    private function buildGeneralRemarksnow(array $remarks): string
    {
        if (empty($remarks)) {
            return '';
        }

        $xml = '';
        foreach ($remarks as $remark) {
            $text = is_array($remark) ? ($remark['text'] ?? '') : $remark;
            $type = is_array($remark) ? ($remark['type'] ?? 'Basic') : 'Basic';

            $xml .= <<<REMARK
            <com:GeneralRemark UseProviderNativeMode="true" TypeInGds="{$type}" xmlns:com="http://www.travelport.com/schema/common_v52_0">
                <com:RemarkData>{$text}</com:RemarkData>
            </com:GeneralRemark>

REMARK;
        }

        return $xml;
    }



    /**
     * ✅ NEW: Build Address
     */
    private function buildAddressnow(array $passenger): string
    {
        // Check if address info exists
        if (empty($passenger['address']) && empty($passenger['city'])) {
            return '';
        }

        $addressName = $passenger['address_name'] ?? ($passenger['first_name'] . ' ' . $passenger['last_name']);
        $street = $passenger['address'] ?? $passenger['street'] ?? '';
        $street2 = $passenger['address2'] ?? $passenger['street2'] ?? '';
        $city = $passenger['city'] ?? '';
        $state = $passenger['state'] ?? '';
        $postalCode = $passenger['postal_code'] ?? $passenger['zip'] ?? '';
        $country = $passenger['country'] ?? 'BD';

        $streetXml = $street ? "                    <com:Street>{$street}</com:Street>\n" : '';
        $street2Xml = $street2 ? "                    <com:Street>{$street2}</com:Street>\n" : '';
        $stateXml = $state ? "                    <com:State>{$state}</com:State>\n" : '';
        $postalXml = $postalCode ? "                    <com:PostalCode>{$postalCode}</com:PostalCode>\n" : '';

        return <<<ADDRESS
                <com:Address>
                    <com:AddressName>{$addressName}</com:AddressName>
{$streetXml}{$street2Xml}                    <com:City>{$city}</com:City>
{$stateXml}{$postalXml}                    <com:Country>{$country}</com:Country>
                </com:Address>
ADDRESS;
    }



    // ========================================
    // PRICE REQUEST BUILDING
    // ========================================

    public function buildPriceRequestXml($bookingId)
    {
        $booking = Booking::with('passengers')->findOrFail($bookingId);
        $flightData = $booking->flight_raw_data;

        if (is_string($flightData)) {
            $flightData = json_decode($flightData, true);
        }

        $passengers = $booking->passengers->toArray();
        $traceId = uniqid('price_', true);
        $departureDate = $flightData['legs'][0]['departure']['date'] ?? date('Y-m-d');

        $segmentsXml = $this->buildPriceRequestSegments($flightData);
        $passengersXml = $this->buildPriceRequestPassengers($passengers, $departureDate);
        $pricingModifiersXml = $this->buildSegmentPricingModifiers($flightData);

        $xml= <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <AirPriceReq xmlns="http://www.travelport.com/schema/air_v52_0" TraceId="{$traceId}" AuthorizedBy="{$this->authorizedBy}" TargetBranch="{$this->targetBranch}">
            <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v52_0" OriginApplication="uAPI"/>
            <AirItinerary>
{$segmentsXml}
            </AirItinerary>
            <AirPricingModifiers InventoryRequestType="DirectAccess">
            </AirPricingModifiers>
{$passengersXml}
            <AirPricingCommand>
{$pricingModifiersXml}
            </AirPricingCommand>
            <FormOfPayment xmlns="http://www.travelport.com/schema/common_v52_0" Type="Credit"/>
        </AirPriceReq>
    </soap:Body>
</soap:Envelope>
XML;

        $apiClass=new TravelPortApiService();
        $priceCheck=$apiClass->priceFlight($xml);

//        dd($priceCheck);
        $responseClass = new TravelPortBookingResponseService();
        $responseData = $responseClass->parseAirPriceResponse($priceCheck);
        return $responseData;
//        $updateBooking=$responseClass->updatePricesFromParsedData($bookingId, $responseData);
//
//
//        return $updateBooking;
    }

    private function buildPriceRequestSegments(array $flightData): string
    {
        $xml = '';
//        $uniqueSegments = $this->getUniqueSegments($flightData);
        $usedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            $group = $leg['leg_number'] - 1;

            // ✅ সব segments loop করুন (শুধু প্রথমটা না)
            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];
                if (in_array($key, $usedKeys)) {
                    continue;
                }
                $usedKeys[] = $key;

                $carrier = $segment['carrier'];
                $flightNumber = $segment['flight_number'];
                $origin = $segment['departure']['airport_code'];
                $destination = $segment['arrival']['airport_code'];
                $departureTime = $segment['departure']['date_time_zoon'];
                $arrivalTime = $segment['arrival']['date_time_zoon'];
                $flightTime = $segment['duration'] ?? 0;
                $distance = $segment['miles'] ?? 0;
                $equipment = $segment['aircraft'] ?? '';

                $classOfService = $segment['booking_info']['booking_code'] ?? 'Y';
                $providerCode = $segment['provider_code'] ?? '1G';
                $eticket = $segment['eTicketable'] ?? 'Yes';
                $availabilitySource = $segment['availability_source'] ?? 'A';
                $availabilityDisplayType = $segment['availability_display_type'] ?? 'Fare Shop/Optimal Shop';

                $xml .= <<<SEGMENT
            <AirSegment Key="{$key}" Group="{$group}" Carrier="{$carrier}" FlightNumber="{$flightNumber}" Origin="{$origin}" Destination="{$destination}" DepartureTime="{$departureTime}" ArrivalTime="{$arrivalTime}" FlightTime="{$flightTime}" Distance="{$distance}" ETicketability="{$eticket}" Equipment="{$equipment}" AvailabilitySource="{$availabilitySource}" AvailabilityDisplayType="{$availabilityDisplayType}" ProviderCode="{$providerCode}" ClassOfService="{$classOfService}"/>

SEGMENT;
            }
        }

        return $xml;
    }

    private function buildPriceRequestPassengers(array $passengers, string $departureDate): string
    {
        $xml = '';

        foreach ($passengers as $index => $passenger) {
            $key = base64_encode("Pax" . ($index + 1) . uniqid());
            $dob = $passenger['dob'] ?? $passenger['date_of_birth'] ?? null;

            $typeInfo = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
            $code = $typeInfo['type'];
            $age = $typeInfo['age'];

            $ageAttr = in_array($code, ['CNN', 'INF']) ? " Age=\"{$age}\"" : '';

            $xml .= <<<PASSENGER
            <SearchPassenger xmlns="http://www.travelport.com/schema/common_v52_0" Code="{$code}" BookingTravelerRef="{$key}" Key="{$key}"{$ageAttr}/>

PASSENGER;
        }

        return $xml;
    }

    private function buildSegmentPricingModifiers(array $flightData): string
    {
        $xml = '';
        $usedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            // ✅ সব segments loop করুন
            foreach ($leg['segments'] as $segment) {
                $segmentRef = $segment['key'];
                if (in_array($segmentRef, $usedKeys)) {
                    continue;
                }
                $usedKeys[] = $segmentRef;
                $bookingCode = $segment['booking_info']['booking_code'] ?? 'Y';

                $xml .= <<<MODIFIER
                <AirSegmentPricingModifiers AirSegmentRef="{$segmentRef}">
                    <PermittedBookingCodes>
                        <BookingCode Code="{$bookingCode}"/>
                    </PermittedBookingCodes>
                </AirSegmentPricingModifiers>

MODIFIER;
            }
        }

        return $xml;
    }

    // ========================================
    // HELPERS
    // ========================================

//    private function updatePassengerTypesInXml(
//        string $pricingSolutionXml,
//        array $passengers,
//        string $departureDate
//    ): string {
//        $dom = new \DOMDocument();
//        $dom->loadXML($pricingSolutionXml);
//
//        $xpath = new \DOMXPath($dom);
//        $xpath->registerNamespace('air', 'http://www.travelport.com/schema/air_v52_0');
//
//        $nodes = $xpath->query('//air:PassengerType');
//
//        foreach ($nodes as $i => $node) {
//            if (!isset($passengers[$i])) continue;
//
//            $type = $this->helper->getPassengerTypeFromDOB(
//                $passengers[$i]['dob'] ?? null,
//                $departureDate
//            )['type'];
//
//            $node->setAttribute('Code', $type);
//        }
//
//        return $dom->saveXML($dom->documentElement);
//    }

    private function updatePassengerTypesInXml(
        string $pricingSolutionXml,
        array $passengers,
        string $departureDate
    ): string {

        $types = [];
        foreach ($passengers as $pax) {
            $dob = $pax['dob'] ?? $pax['date_of_birth'] ?? null;
            $types[] = $this->helper->getPassengerTypeFromDOB($dob, $departureDate);
        }

        $xml = new \SimpleXMLElement($pricingSolutionXml);
        $xml->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v52_0');

        // -------------------------------
        // 1) Get selected segment keys from FlightOptionsList
        // -------------------------------
        $selectedKeys = [];
        $options = $xml->xpath('//air:FlightOptionsList//air:BookingInfo');

        foreach ($options as $opt) {
            $segRef = (string)$opt['SegmentRef'];
            if ($segRef) {
                $selectedKeys[$segRef] = true;
            }
        }

        // -------------------------------
        // 2) Remove FlightOptionsList (shopping only)
        // -------------------------------
        $flightOptions = $xml->xpath('//air:FlightOptionsList');
        foreach ($flightOptions as $node) {
            $dom = dom_import_simplexml($node);
            $dom->parentNode->removeChild($dom);
        }

        // -------------------------------
        // 3) Keep only selected AirSegments
        // -------------------------------
        if (!empty($selectedKeys)) {
            $segments = $xml->xpath('//air:AirSegment');
            foreach ($segments as $segment) {
                $key = (string)$segment['Key'];

                if (!isset($selectedKeys[$key])) {
                    $dom = dom_import_simplexml($segment);
                    $dom->parentNode->removeChild($dom);
                }
            }
        }

        // -------------------------------
        // 4) Update PassengerType + Age
        // -------------------------------
        $passengerTypes = $xml->xpath('//air:PassengerType');

        foreach ($passengerTypes as $index => $paxType) {
            if (!isset($types[$index])) continue;

            $type = $types[$index]['type'];
            $paxType['Code'] = $type;

            if (in_array($type, ['CNN', 'INF'])) {
                $paxType['Age'] = $types[$index]['age'];
            } else {
                unset($paxType['Age']);
            }
        }

        // -------------------------------
        // 5) Ensure AirSegmentRef exists in AirPricingInfo
        // -------------------------------
        $pricingInfos = $xml->xpath('//air:AirPricingInfo');
        foreach ($pricingInfos as $pricingInfo) {
            // if already exists, skip
            if ($pricingInfo->xpath('.//air:AirSegmentRef')) {
                continue;
            }

            // add selected segment refs
            foreach ($selectedKeys as $key => $_) {
                $pricingInfo->addChild('air:AirSegmentRef', null, 'http://www.travelport.com/schema/air_v52_0')
                    ->addAttribute('Key', $key);
            }
        }

        // -------------------------------
        // Return cleaned XML
        // -------------------------------
        $result = $xml->asXML();
        return preg_replace('/<\?xml.*\?>\n?/', '', $result);
    }




    private function getDefaultPrefix(string $gender, string $travelerType): string
    {
        $genderRaw = strtolower($gender);
        $isFemale = in_array($genderRaw, ['f', 'female']);

        if (in_array($travelerType, ['CNN', 'INF'])) {
            return $isFemale ? 'Miss' : 'Mstr';
        }

        return $isFemale ? 'Ms' : 'Mr';
    }

    // TravelPortBookingXmlService.php এ

    private function getUniqueSegments(array $flightData): array
    {
        $uniqueSegments = [];
        $usedKeys = [];

        foreach ($flightData['legs'] as $leg) {
            $group = $leg['leg_number'] - 1;

            foreach ($leg['segments'] as $segment) {
                $key = $segment['key'];

                if (in_array($key, $usedKeys)) {
                    continue;
                }
                $usedKeys[] = $key;

                $segment['group'] = $group;
                $uniqueSegments[] = $segment;
            }
        }

        return $uniqueSegments;
    }

}
