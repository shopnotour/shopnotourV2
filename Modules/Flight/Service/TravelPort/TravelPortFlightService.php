<?php

namespace Modules\Flight\Service\TravelPort;

use Illuminate\Support\Facades\Log;
use Modules\Flight\Service\FlightChargesService;
use Modules\Flight\Service\FlightDataHelper;
use Modules\Flight\Service\FlightDiscountService;
use SimpleXMLElement;

class TravelPortFlightService
{
    private FlightDataHelper $helper;
    private FlightDiscountService $discountService;
    private FlightChargesService $chargesService;

    public function __construct(
        FlightDiscountService $discountService,
        FlightChargesService  $chargesService
    )
    {
        $this->discountService = $discountService;
        $this->chargesService = $chargesService;
        $this->helper = new FlightDataHelper();
    }

    public function search($searchData)
    {
        $travelPortXml = new TravelPortXmlBuildService();
        $travelPortPayload = $travelPortXml->buildXmlRequest($searchData);
//dd($travelPortPayload);
        $travelPort = new TravelPortApiService();
        $travelPortResponse = $travelPort->searchFlights($travelPortPayload);
//        dd($travelPortResponse);
        if (is_array($travelPortResponse)) {
            if (!($travelPortResponse['success'] ?? true)) {
                return [
                    'success' => false,
                    'error' => $travelPortResponse['error'] ?? 'TravelPort API failed',
                    'flights' => [],
                ];
            }
            $xmlString = $travelPortResponse['data'] ?? $travelPortResponse['xml'] ?? $travelPortResponse['body'] ?? null;
        } else {
            $xmlString = $travelPortResponse;
        }

        if (empty($xmlString)) {
            return [
                'success' => false,
                'error' => 'Empty response from TravelPort',
                'flights' => [],
            ];
        }
        return $this->parseFlightSearchResponse($xmlString);
    }

    /**
     * Parse Travelport flight search response
     */
    public function parseFlightSearchResponse(string|array $xmlResponse): array
    {
        if (is_array($xmlResponse)) {
            $xmlResponse = $xmlResponse['raw_xml']
                ?? $xmlResponse['xml']
                ?? $xmlResponse['body']
                ?? $xmlResponse['response']
                ?? $xmlResponse['data']
                ?? null;
        }

        if (!is_string($xmlResponse) || trim($xmlResponse) === '') {
            throw new \Exception('Invalid SOAP XML response - expected string, got: ' . gettype($xmlResponse));
        }

        $xml = new SimpleXMLElement($xmlResponse);
        $this->registerNamespaces($xml);

        // Build lookup maps with keys & xml
        $flightDetailsMap = $this->buildFlightDetailsMap($xml);
        $segmentsMap = $this->buildSegmentsMap($xml, $flightDetailsMap);
        $fareInfoMap = $this->buildFareInfoMap($xml);
        $brandListMap = $this->buildBrandListMap($xml);

        // Transform
        $flights = $this->transformTravelport($xml, $segmentsMap, $fareInfoMap, $brandListMap);

        if (empty($flights)) {
            return [
                'success' => true,
                'flights' => [],
                'total_results' => 0,
                'message' => 'No flights found',
            ];
        }

        return [
            'success' => true,
            'flights' => $flights,
            'total_results' => count($flights),
            'currency' => 'BDT',
        ];
    }

    private function registerNamespaces(SimpleXMLElement $xml): void
    {
        $namespaces = [
            'SOAP' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'air' => 'http://www.travelport.com/schema/air_v52_0',
            'common' => 'http://www.travelport.com/schema/common_v52_0'
        ];

        foreach ($namespaces as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }
    }

    // ========================================
    // BUILD MAPS - WITH KEY & XML
    // ========================================

    /**
     * Build flight details map - with key & xml
     */
    private function buildFlightDetailsMap(SimpleXMLElement $xml): array
    {
        $map = [];
        $flightDetails = $xml->xpath('//air:FlightDetails');

        foreach ($flightDetails as $detail) {
            $attr = $detail->attributes();
            $key = (string)$attr['Key'];

            $map[$key] = [
                'key' => $key,
                'xml' => $detail->asXML(), // ✅ XML added
                'origin' => (string)($attr['Origin'] ?? ''),
                'destination' => (string)($attr['Destination'] ?? ''),
                'departure_time' => (string)($attr['DepartureTime'] ?? ''),
                'arrival_time' => (string)($attr['ArrivalTime'] ?? ''),
                'flight_time' => (int)($attr['FlightTime'] ?? 0),
                'travel_time' => (int)($attr['TravelTime'] ?? 0),
                'ground_time' => (int)($attr['GroundTime'] ?? 0),
                'distance' => (int)($attr['Distance'] ?? 0),
                'equipment' => (string)($attr['Equipment'] ?? ''),
                'origin_terminal' => (string)($attr['OriginTerminal'] ?? ''),
                'destination_terminal' => (string)($attr['DestinationTerminal'] ?? ''),
                'on_time_performance' => (string)($attr['OnTimePerformance'] ?? ''),
            ];
        }

        return $map;
    }

    /**
     * Build segments map - with key & xml
     */
    private function buildSegmentsMap(SimpleXMLElement $xml, array $flightDetailsMap): array
    {
        $segmentsMap = [];
        $segments = $xml->xpath('//air:AirSegment');

        foreach ($segments as $segment) {
            $attr = $segment->attributes();
            $key = (string)$attr['Key'];

            // Flight detail references
            $flightDetailsRefs = $segment->xpath('.//air:FlightDetailsRef');
            $flightDetails = [];
            $flightDetailsKeys = [];

            foreach ($flightDetailsRefs as $ref) {
                $refKey = (string)$ref->attributes()['Key'];
                $flightDetailsKeys[] = $refKey;
                if (isset($flightDetailsMap[$refKey])) {
                    $flightDetails[] = $flightDetailsMap[$refKey];
                }
            }

            // Codeshare info
            $codeshareInfo = $segment->xpath('.//air:CodeshareInfo')[0] ?? null;
            $operatingCarrier = (string)$attr['Carrier'];
            $operatingFlightNumber = (string)$attr['FlightNumber'];
            $codeshareXml = null;

            if ($codeshareInfo) {
                $codeshareAttr = $codeshareInfo->attributes();
                $operatingCarrier = (string)($codeshareAttr['OperatingCarrier'] ?? $operatingCarrier);
                $operatingFlightNumber = (string)($codeshareAttr['OperatingFlightNumber'] ?? $operatingFlightNumber);
                $codeshareXml = $codeshareInfo->asXML();
            }

            // AirAvailInfo
            $airAvailInfo = $segment->xpath('.//air:AirAvailInfo')[0] ?? null;
            $providerCode = $airAvailInfo ? (string)($airAvailInfo->attributes()['ProviderCode'] ?? '1G') : '1G';

            $segmentsMap[$key] = [
                'key' => $key,
                'xml' => $segment->asXML(), // ✅ XML added
                'group' => (int)$attr['Group'],
                'carrier' => (string)$attr['Carrier'],
                'flight_number' => (string)$attr['FlightNumber'],
                'origin' => (string)$attr['Origin'],
                'destination' => (string)$attr['Destination'],
                'departure_time' => (string)$attr['DepartureTime'],
                'arrival_time' => (string)$attr['ArrivalTime'],
                'flight_time' => (int)($attr['FlightTime'] ?? 0),
                'travel_time' => (int)($attr['TravelTime'] ?? 0),
                'distance' => (int)($attr['Distance'] ?? 0),
                'equipment' => (string)($attr['Equipment'] ?? ''),
                'class_of_service' => (string)($attr['ClassOfService'] ?? ''),
                'number_of_stops' => (int)($attr['NumberOfStops'] ?? 0),
                'change_of_plane' => (string)($attr['ChangeOfPlane'] ?? 'false') === 'true',
                'e_ticketability' =>($attr['ETicketability'] ) ,
                'participant_level' => (string)($attr['ParticipantLevel'] ?? ''),
                'link_availability' => (string)($attr['LinkAvailability'] ?? 'false') === 'true',
                'polled_availability_option' => (string)($attr['PolledAvailabilityOption'] ?? ''),
                'optional_services_indicator' => (string)($attr['OptionalServicesIndicator'] ?? 'false') === 'true',
                'availability_source' => (string)($attr['AvailabilitySource'] ?? ''),
                'availability_display_type' => (string)($attr['AvailabilityDisplayType'] ?? ''),
                'provider_code' => $providerCode,
                'operating_carrier' => $operatingCarrier,
                'operating_flight_number' => $operatingFlightNumber,
                'is_codeshare' => $codeshareInfo !== null,
                'codeshare_xml' => $codeshareXml,
                'flight_details_keys' => $flightDetailsKeys,
                'flight_details' => $flightDetails,
                'total_flight_time' => array_sum(array_column($flightDetails, 'flight_time')),
                'layover_time' => !empty($flightDetails) ? array_sum(array_column($flightDetails, 'ground_time')) : 0,
            ];
        }

        return $segmentsMap;
    }

    /**
     * Build fare info map - with key & xml
     */
    private function buildFareInfoMap(SimpleXMLElement $xml): array
    {
        $fareInfoMap = [];
        $fareInfos = $xml->xpath('//air:FareInfo');

        foreach ($fareInfos as $fareInfo) {
            $fareInfo->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v52_0');

            $attr = $fareInfo->attributes();
            $key = (string)$attr['Key'];

            // Baggage allowance
            $baggageInfo = $this->parseBaggageAllowance($fareInfo);

            // FareTicketDesignator
            $fareTicketDesignator = $fareInfo->xpath('.//air:FareTicketDesignator')[0] ?? null;
            $ticketDesignator = $fareTicketDesignator ? (string)($fareTicketDesignator->attributes()['Value'] ?? '') : null;

            // FareRuleKey
            $fareRuleKeyElement = $fareInfo->xpath('.//air:FareRuleKey')[0] ?? null;
            $fareRuleKey = $fareRuleKeyElement ? (string)$fareRuleKeyElement : null;
            $fareRuleProviderCode = $fareRuleKeyElement ? (string)($fareRuleKeyElement->attributes()['ProviderCode'] ?? '') : null;
            $fareRuleKeyXml = $fareRuleKeyElement ? $fareRuleKeyElement->asXML() : null;

            // Brand information
            $brandInfo = $this->parseBrandInfo($fareInfo);

            $fareInfoMap[$key] = [
                'key' => $key,
                'xml' => $fareInfo->asXML(), // ✅ XML added
                'fare_basis' => (string)($attr['FareBasis'] ?? ''),
                'passenger_type_code' => (string)($attr['PassengerTypeCode'] ?? 'ADT'),
                'origin' => (string)($attr['Origin'] ?? ''),
                'destination' => (string)($attr['Destination'] ?? ''),
                'effective_date' => (string)($attr['EffectiveDate'] ?? ''),
                'departure_date' => (string)($attr['DepartureDate'] ?? ''),
                'amount' => (string)($attr['Amount'] ?? ''),
                'not_valid_before' => (string)($attr['NotValidBefore'] ?? ''),
                'not_valid_after' => (string)($attr['NotValidAfter'] ?? ''),
                'negotiated_fare' => (string)($attr['NegotiatedFare'] ?? 'false') === 'true',
                'baggage' => $baggageInfo,
                'ticket_designator' => $ticketDesignator,
                'fare_rule_key' => $fareRuleKey,
                'fare_rule_key_xml' => $fareRuleKeyXml,
                'fare_rule_provider_code' => $fareRuleProviderCode,
                'brand' => $brandInfo,
            ];
        }

        return $fareInfoMap;
    }

    /**
     * Parse baggage allowance - with xml
     */
    private function parseBaggageAllowance($fareInfo): ?array
    {
        // ✅ xpath এর বদলে direct children access
        $ns = 'http://www.travelport.com/schema/air_v52_0';

        $children = $fareInfo->children($ns);
        $baggage = $children->BaggageAllowance ?? null;

        if (!$baggage) return null;

        $baggageChildren = $baggage->children($ns);
        $maxWeight = $baggageChildren->MaxWeight ?? null;
        $numPieces = $baggageChildren->NumberOfPieces ?? null;

        return [
            'xml'        => $baggage->asXML(),
            'weight'     => $maxWeight ? (int)($maxWeight->attributes()['Value'] ?? 0) : null,
            'unit'       => $maxWeight ? strtolower((string)($maxWeight->attributes()['Unit'] ?? 'kilograms')) : null,
            'piece_count'=> $numPieces ? (int)(string)$numPieces : null,
        ];
    }

    /**
     * Parse brand info - with key & xml
     */
    private function parseBrandInfo($fareInfo): ?array
    {
        $brandElement = $fareInfo->xpath('.//air:Brand')[0] ?? null;

        if (!$brandElement) {
            return null;
        }

        $brandAttr = $brandElement->attributes();

        // UpsellBrand
        $upsellBrand = $brandElement->xpath('.//air:UpsellBrand')[0] ?? null;
        $upsellBrandData = null;
        if ($upsellBrand) {
            $upsellAttr = $upsellBrand->attributes();
            $upsellBrandData = [
                'fare_basis' => (string)($upsellAttr['FareBasis'] ?? ''),
                'fare_info_ref' => (string)($upsellAttr['FareInfoRef'] ?? ''),
                'xml' => $upsellBrand->asXML(),
            ];
        }

        return [
            'key' => (string)($brandAttr['Key'] ?? ''),
            'xml' => $brandElement->asXML(), // ✅ XML added
            'brand_id' => (string)($brandAttr['BrandID'] ?? ''),
            'up_sell_brand_id' => (string)($brandAttr['UpSellBrandID'] ?? ''),
            'brand_tier' => (string)($brandAttr['BrandTier'] ?? ''),
            'brand_found' => (string)($brandAttr['BrandFound'] ?? 'true') !== 'false',
            'up_sell_brand_found' => (string)($brandAttr['UpSellBrandFound'] ?? 'true') !== 'false',
            'upsell_brand' => $upsellBrandData,
        ];
    }

    /**
     * Build brand list map - with key & xml
     */
    private function buildBrandListMap(SimpleXMLElement $xml): array
    {
        $brandMap = [];
        $brands = $xml->xpath('//air:BrandList/air:Brand');

        foreach ($brands as $brand) {
            $attr = $brand->attributes();
            $key = (string)$attr['Key'];

            // Titles
            $titles = [];
            $titleElements = $brand->xpath('.//air:Title');
            foreach ($titleElements as $title) {
                $titleAttr = $title->attributes();
                $titles[] = [
                    'type' => (string)($titleAttr['Type'] ?? 'External'),
                    'language_code' => (string)($titleAttr['LanguageCode'] ?? 'EN'),
                    'value' => (string)$title,
                    'xml' => $title->asXML(),
                ];
            }

            // Texts
            $texts = [];
            $textElements = $brand->xpath('.//air:Text');
            foreach ($textElements as $text) {
                $textAttr = $text->attributes();
                $texts[] = [
                    'type' => (string)($textAttr['Type'] ?? 'Description'),
                    'language_code' => (string)($textAttr['LanguageCode'] ?? 'EN'),
                    'value' => trim((string)$text),
                    'xml' => $text->asXML(),
                ];
            }

            // Images
            $images = [];
            $imageElements = $brand->xpath('.//air:ImageLocation');
            foreach ($imageElements as $image) {
                $imageAttr = $image->attributes();
                $images[] = [
                    'type' => (string)($imageAttr['Type'] ?? ''),
                    'image_width' => (string)($imageAttr['ImageWidth'] ?? ''),
                    'image_height' => (string)($imageAttr['ImageHeight'] ?? ''),
                    'url' => (string)$image,
                    'xml' => $image->asXML(),
                ];
            }

            // Extract specific titles/texts
            $externalTitle = null;
            $shortTitle = null;
            $strapline = null;

            foreach ($titles as $t) {
                if ($t['type'] === 'External') $externalTitle = $t['value'];
                if ($t['type'] === 'Short') $shortTitle = $t['value'];
            }

            foreach ($texts as $t) {
                if ($t['type'] === 'Strapline') $strapline = $t['value'];
            }

            $brandMap[$key] = [
                'key' => $key,
                'xml' => $brand->asXML(), // ✅ XML added
                'brand_id' => (string)($attr['BrandID'] ?? ''),
                'name' => (string)($attr['Name'] ?? ''),
                'carrier' => (string)($attr['Carrier'] ?? ''),
                'branded_details_available' => (string)($attr['BrandedDetailsAvailable'] ?? 'false') === 'true',
                'title' => $externalTitle,
                'short_title' => $shortTitle,
                'strapline' => $strapline,
                'titles' => $titles,
                'texts' => $texts,
                'images' => $images,
            ];

            // Also index by BrandID
            $brandId = (string)($attr['BrandID'] ?? '');
            if ($brandId) {
                $brandMap['by_id_' . $brandId] = $brandMap[$key];
            }
        }

        return $brandMap;
    }

    // ========================================
    // TRANSFORM METHODS
    // ========================================

    private function transformTravelport(SimpleXMLElement $xml, array $segmentsMap, array $fareInfoMap, array $brandListMap): array
    {
        $flights = [];
        $airPricePoints = $xml->xpath('//air:AirPricePoint');

        $itineraryId = 1;

        foreach ($airPricePoints as $pricePoint) {
            $flight = $this->formatItinerary($xml, $pricePoint, $segmentsMap, $fareInfoMap, $brandListMap, $itineraryId);

            if ($flight) {
                $flights[] = $flight;
                $itineraryId++;
            }
        }

        return $flights;
    }

    /**
     * Format single itinerary - with all keys & xml
     */
    private function formatItinerary($xml, $pricePoint, array $segmentsMap, array $fareInfoMap, array $brandListMap, int $itineraryId): ?array
    {
        $pricePointAttr = $pricePoint->attributes();

        $allPricingInfos = $pricePoint->xpath('.//air:AirPricingInfo');

        if (empty($allPricingInfos)) {
            return null;
        }

        $firstPricingInfo = $allPricingInfos[0];
        $firstPricingAttr = $firstPricingInfo->attributes();

        // Parse legs with segment keys & xml
        $legs = $this->formatLegs($firstPricingInfo, $segmentsMap, $fareInfoMap);

        if (empty($legs)) {
            return null;
        }

        // Total segments
        $totalSegments = 0;
        foreach ($legs as $leg) {
            $totalSegments += $leg['total_segments'];
        }

        // ✅ ঠিক (4 arguments)
        $priceData = $this->calculatePricing($pricePointAttr, $legs, $totalSegments, $allPricingInfos);

        // Passengers with pricing info keys & xml
        $passengers = $this->formatPassengerBreakdown($allPricingInfos, $fareInfoMap, $brandListMap);

        // Penalties
        $penalties = $this->parsePenalties($firstPricingInfo);

        // Fare calcs
        $fareCalcs = $this->collectFareCalcs($allPricingInfos);

        // Host tokens with keys & xml
        $hostTokens = $this->extractHostTokens($xml);

        // Build pricing solution XML
        $pricingSolutionXml = $this->buildPricingSolutionXml($pricePoint, $xml);

        return [
            'id' => $itineraryId,
            'source' => 'travelport',

            // ✅ Price Point Key & XML
            'price_point' => [
                'key' => (string)($pricePointAttr['Key'] ?? ''),
                'xml' => $pricePoint->asXML(),
            ],

            'legs' => $legs,

            'price' => [
                'api_base_fare'            => $priceData['api_base_fare'],
                'api_tax'                  => $priceData['api_tax'],
                'api_subtotal'             => $priceData['api_subtotal'],
                'ait_amount'               => $priceData['ait_amount'],
                'service_charge'           => $priceData['service_charge'],
                'subtotal_before_discount' => $priceData['subtotal_before_discount'],
                'flight_discount'          => $priceData['flight_discount'],
                'segment_discount'         => $priceData['segment_discount'],
                'total_discounts'          => $priceData['total_discounts'],
                'total'                    => $priceData['total'],
                'currency'                 => 'BDT',
                'base_currency'            => $priceData['base_currency'],
                // ✅ নতুন
                'own_discount'             => $priceData['own_discount'],
                'own_seg_discount'         => $priceData['own_seg_discount'],
                'total_commission'         => $priceData['total_commission'],
                'own_cost'                 => $priceData['own_cost'],
                'gross_profit'             => $priceData['gross_profit'],
            ],

// ✅ নতুন
            'passenger_price_breakdown' => $priceData['flight_discount_details']['passenger_breakdowns'],
            'flight_discount_details' => $priceData['flight_discount_details'],

            'charges_details' => [
                'ait_charge_percentage' => $priceData['flight_discount_details']['ait_charge_percentage'],
                'ait_amount' => $priceData['ait_amount'],
                'service_charge' => $priceData['service_charge'],
                'segment_discount_per_segment' => $priceData['flight_discount_details']['segment_discount_per_segment'],
                'segment_discount_total' => $priceData['segment_discount'],
                'flight_discount_label'        => $priceData['flight_discount_label'] ?? null,
                'segment_discount_label'       => $priceData['segment_discount_label'] ?? null,
            ],

            'passengers' => $passengers,

            'refundable' => (string)($firstPricingAttr['Refundable'] ?? 'false') === 'true',
            'eTicketable' => (string)($firstPricingAttr['ETicketability'] ?? 'Yes') === 'Yes',
            'validating_carrier' => (string)($firstPricingAttr['PlatingCarrier'] ?? ''),
            'vita' => false,
            'last_ticket_date' => $this->helper->extractDate((string)($firstPricingAttr['LatestTicketingTime'] ?? '')),
            'last_ticket_time' => $this->helper->extractTime((string)($firstPricingAttr['LatestTicketingTime'] ?? '')),
            'pricing_source' => 'travelport',
            'provider_code' => (string)($firstPricingAttr['ProviderCode'] ?? '1G'),
            'pricing_method' => (string)($firstPricingAttr['PricingMethod'] ?? ''),
            'cat35_indicator' => (string)($firstPricingAttr['Cat35Indicator'] ?? 'false') === 'true',
            'complete_itinerary' => (string)($pricePointAttr['CompleteItinerary'] ?? 'true') === 'true',

            'penalties' => $penalties,
            'fare_calcs' => $fareCalcs,

            // ✅ Host Tokens with keys & xml
            'host_tokens' => $hostTokens,

            // ✅ Ready-to-use booking XML
            'pricing_solution_xml' => $pricingSolutionXml,
        ];
    }

    /**
     * Extract host tokens - with key & xml
     */
    private function extractHostTokens(SimpleXMLElement $xml): array
    {
        $hostTokens = [];
        $hostTokenElements = $xml->xpath('//*[local-name()="HostToken"]');

        foreach ($hostTokenElements as $token) {
            $tokenAttr = $token->attributes();
            $hostTokens[] = [
                'key' => (string)($tokenAttr['Key'] ?? ''),
                'xml' => $token->asXML(), // ✅ XML added
                'value' => (string)$token,
            ];
        }

        return $hostTokens;
    }

    /**
     * Build pricing solution XML for booking
     */
    private function buildPricingSolutionXml($pricePoint, SimpleXMLElement $xml): string
    {
        $attr = $pricePoint->attributes();

        // Get segments
        $segmentsXml = '';
        $bookingInfos = $pricePoint->xpath('.//air:BookingInfo');
        foreach ($bookingInfos as $bi) {
            $ref = (string)$bi->attributes()['SegmentRef'];
            $segment = $xml->xpath("//air:AirSegment[@Key='{$ref}']")[0] ?? null;
            if ($segment) {
                $segmentsXml .= $segment->asXML();
            }
        }

        // Get host tokens
        $hostTokensXml = '';
        $hostTokens = $xml->xpath('//*[local-name()="HostToken"]');
        foreach ($hostTokens as $token) {
            $hostTokensXml .= $token->asXML();
        }

        // Get pricing info
        $pricingInfoXml = '';
        $pricingInfos = $pricePoint->xpath('.//air:AirPricingInfo');
        foreach ($pricingInfos as $pi) {
            $pricingInfoXml .= $pi->asXML();
        }

        $key = (string)($attr['Key'] ?? '');
        $totalPrice = (string)($attr['TotalPrice'] ?? '');
        $basePrice = (string)($attr['BasePrice'] ?? '');
        $approxTotal = (string)($attr['ApproximateTotalPrice'] ?? '');
        $approxBase = (string)($attr['ApproximateBasePrice'] ?? '');
        $taxes = (string)($attr['Taxes'] ?? '');
        $quoteDate = date('Y-m-d');

        return <<<XML
<air:AirPricingSolution Key="{$key}" TotalPrice="{$totalPrice}" BasePrice="{$basePrice}" ApproximateTotalPrice="{$approxTotal}" ApproximateBasePrice="{$approxBase}" Taxes="{$taxes}" QuoteDate="{$quoteDate}" xmlns:air="http://www.travelport.com/schema/air_v52_0">
{$segmentsXml}
{$pricingInfoXml}
{$hostTokensXml}
</air:AirPricingSolution>
XML;
    }

    /**
     * Calculate pricing
     */
//    private function calculatePricing($pricePointAttr, array $legs, int $totalSegments): array
//    {
//        $approximateTotalPrice = $this->helper->parsePrice((string)($pricePointAttr['ApproximateTotalPrice'] ?? (string)$pricePointAttr['TotalPrice']));
//        $approximateBasePrice = $this->helper->parsePrice((string)($pricePointAttr['ApproximateBasePrice'] ?? (string)$pricePointAttr['BasePrice']));
//        $taxes = $this->helper->parsePrice((string)$pricePointAttr['Taxes']);
//        $baseCurrency = $this->helper->extractCurrency((string)$pricePointAttr['BasePrice']);
//
//        $departureCode = $legs[0]['departure']['airport_code'] ?? null;
//        $arrivalCode = $legs[0]['arrival']['airport_code'] ?? null;
//        $validatingCarrier = $legs[0]['segments'][0]['carrier'] ?? '';
//
//        $flightDiscountInfo = $this->discountService->calculate(
//            $validatingCarrier,
//            $departureCode,
//            $arrivalCode,
//            $approximateTotalPrice,
//            $approximateBasePrice,
//            $totalSegments
//        );
//
//        $flightDiscountInfo = $this->discountService->calculate(
//            $validatingCarrier,
//            $departureCode,
//            $arrivalCode,
//            $passengerInfoList,
//            $totalSegments
//        );
//
//        $priceBeforeDiscounts = $approximateTotalPrice
//            + $flightDiscountInfo['ait_amount']
//            + $flightDiscountInfo['service_charge'];
//
//        $finalPrice = $priceBeforeDiscounts - $flightDiscountInfo['total_discounts'];
//
//        return [
//            'api_base_fare' => $approximateBasePrice,
//            'api_tax' => $taxes,
//            'api_subtotal' => $approximateTotalPrice,
//            'ait_amount' => $flightDiscountInfo['ait_amount'],
//            'service_charge' => $flightDiscountInfo['service_charge'],
//            'subtotal_before_discount' => round($priceBeforeDiscounts, 2),
//            'flight_discount' => $flightDiscountInfo['flight_discount_amount'],
//            'segment_discount' => $flightDiscountInfo['segment_discount_total'],
//            'total_discounts' => round($flightDiscountInfo['total_discounts'], 2),
//            'total' => round($finalPrice, 2),
//            'base_currency' => $baseCurrency,
//            'flight_discount_details' => $flightDiscountInfo,
//        ];
//    }

    private function calculatePricing($pricePointAttr, array $legs, int $totalSegments, array $allPricingInfos): array
    {
        $approximateTotalPrice = $this->helper->parsePrice((string)($pricePointAttr['ApproximateTotalPrice'] ?? (string)$pricePointAttr['TotalPrice']));
        $approximateBasePrice  = $this->helper->parsePrice((string)($pricePointAttr['ApproximateBasePrice'] ?? (string)$pricePointAttr['BasePrice']));
        $taxes                 = $this->helper->parsePrice((string)$pricePointAttr['Taxes']);
        $baseCurrency          = $this->helper->extractCurrency((string)$pricePointAttr['BasePrice']);

//        $departureCode     = $legs[0]['departure']['airport_code'] ?? null;
//        $arrivalCode       = $legs[0]['arrival']['airport_code'] ?? null;
//        $routePairs = [];
//        foreach ($legs as $leg) {
//            $routePairs[] = [
//                'departure' => $leg['departure']['airport_code'] ?? null,
//                'arrival'   => $leg['arrival']['airport_code'] ?? null,
//            ];
//        }

        // $validatingCarrier = $legs[0]['segments'][0]['carrier'] ?? '';
         $firstPricingInfo = $allPricingInfos[0] ?? null;
        $validatingCarrier = $firstPricingInfo
            ? (string)($firstPricingInfo->attributes()['PlatingCarrier'] ?? '')
            : '';
        if (!$validatingCarrier) {
            $validatingCarrier = $legs[0]['segments'][0]['carrier'] ?? '';
        }

        // ✅ TravelPort passenger data কে Sabre format এ convert করো
        $passengerInfoList = $this->buildPassengerInfoList($allPricingInfos);

//        $flightDiscountInfo = $this->discountService->calculate(
//            $validatingCarrier,
//            $routePairs,        // ← array
//            $passengerInfoList,
//            $totalSegments,
//            'travelport'
//        );
//
//        log::info(' discount check', [
//            $validatingCarrier=>$flightDiscountInfo,
//            'travelport'
//        ]);

        // ✅ validating carrier এর best route বের করো
        $bestRoute  = ['departure' => null, 'arrival' => null];
        $bestScore  = -1;

        foreach ($legs as $leg) {
            foreach ($leg['segments'] ?? [] as $segment) {
                if ($segment['carrier'] !== $validatingCarrier) continue;

                $dep   = $segment['departure']['airport_code'] ?? null;
                $arr   = $segment['arrival']['airport_code']   ?? null;
                $score = $this->discountService->getRouteMatchScore(
                    $validatingCarrier, $dep, $arr, 'travelport'
                );

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestRoute = ['departure' => $dep, 'arrival' => $arr];
                }
            }
        }

// fallback
        if (!$bestRoute['departure']) {
            $bestRoute['departure'] = $legs[0]['departure']['airport_code'] ?? null;
        }
        if (!$bestRoute['arrival']) {
            $lastLeg = end($legs);
            $bestRoute['arrival'] = $lastLeg['arrival']['airport_code'] ?? null;
        }

        $flightDiscountInfo = $this->discountService->calculate(
            $validatingCarrier,
            $bestRoute['departure'],
            $bestRoute['arrival'],
            $passengerInfoList,
            $totalSegments,
            'travelport'
        );

        $grandTotal = $flightDiscountInfo['grand_total'];

        $priceBeforeDiscounts = $grandTotal['api_subtotal']
            + $grandTotal['total_ait']
            + $grandTotal['total_service_charge'];

        $finalPrice = $priceBeforeDiscounts
            - $grandTotal['total_user_discount']
            - $grandTotal['total_user_seg_discount'];

        return [
            'api_base_fare'            => $approximateBasePrice,
            'api_tax'                  => $taxes,
            'api_subtotal'             => $approximateTotalPrice,
            'ait_amount'               => $grandTotal['total_ait'],
            'service_charge'           => $grandTotal['total_service_charge'],
            'subtotal_before_discount' => round($priceBeforeDiscounts, 2),
            'flight_discount'          => $grandTotal['total_user_discount'],
            'segment_discount'         => $grandTotal['total_user_seg_discount'],
            'total_discounts'          => round($grandTotal['total_user_discount'] + $grandTotal['total_user_seg_discount'], 2),
            'total'                    => round($finalPrice, 2),
            'base_currency'            => $baseCurrency,
            'flight_discount_details'  => $flightDiscountInfo,
            'own_discount'             => $grandTotal['total_own_discount'],
            'own_seg_discount'         => $grandTotal['total_own_seg_discount'],
            'total_commission'         => $grandTotal['total_commission'],
            'own_cost'                 => $grandTotal['total_own_cost'],
            'gross_profit'             => $grandTotal['gross_profit'],
        ];
    }


    /**
     * TravelPort AirPricingInfo কে Sabre passengerInfoList format এ convert করো
     */
    private function buildPassengerInfoList(array $allPricingInfos): array
    {
        $passengerInfoList = [];

        foreach ($allPricingInfos as $pricingInfo) {
            $pricingAttr = $pricingInfo->attributes();

            $passengerTypes  = $pricingInfo->xpath('.//air:PassengerType');
            $passengerCount  = count($passengerTypes);
            $firstPassenger  = $passengerTypes[0] ?? null;
            $passengerCode   = $firstPassenger
                ? (string)($firstPassenger->attributes()['Code'] ?? 'ADT')
                : 'ADT';

            // BDT equivalent amounts
            $approximateTotalPrice  = $this->helper->parsePrice((string)($pricingAttr['ApproximateTotalPrice'] ?? '0'));
            $approximateBasePrice   = $this->helper->parsePrice((string)($pricingAttr['ApproximateBasePrice']  ?? '0')); // ← নতুন
            $equivalentBasePrice    = $this->helper->parsePrice((string)($pricingAttr['EquivalentBasePrice']   ?? '0'));
            $taxes                  = $this->helper->parsePrice((string)($pricingAttr['Taxes'] ?? '0'));

// EquivalentBasePrice 0 হলে ApproximateBasePrice fallback
            $baseFare = $equivalentBasePrice > 0 ? $equivalentBasePrice : $approximateBasePrice; // ← নতুন

// Sabre format এ convert
            foreach ($passengerTypes as $passenger) {
                $passengerCode = (string)($passenger->attributes()['Code'] ?? 'ADT');

                $passengerInfoList[] = [
                    'passengerInfo' => [
                        'passengerType'   => $passengerCode,
                        'passengerNumber' => 1,
                        'passengerTotalFare' => [
                            'totalFare'        => $approximateTotalPrice,  // BDT total per pax
                            'equivalentAmount' => $approximateBasePrice,   // BDT base fare per pax ← AIT এখানে calculate হবে
                            'baseFareAmount'   => $approximateBasePrice,   // same
                            'totalTaxAmount'   => $taxes,                  // tax per pax
                        ],
                    ],
                ];
            }
        }

        return $passengerInfoList;
    }
    /**
     * Format legs - with segment keys & xml
     */
    private function formatLegs($pricingInfo, array $segmentsMap, array $fareInfoMap): array
    {
        $legs = [];
        $flightOptions = $pricingInfo->xpath('.//air:FlightOption');

        $legNumber = 1;
        $totalLegs = count($flightOptions);

        foreach ($flightOptions as $flightOption) {
            $flightOptionAttr = $flightOption->attributes();
            $options = $flightOption->xpath('.//air:Option');

            if (empty($options)) continue;

            $option = $options[0];
            $optAttr = $option->attributes();
            $travelTimeISO = (string)($optAttr['TravelTime'] ?? '');
            $travelTimeMinutes = $this->helper->parseISODuration($travelTimeISO);

            $segments = $this->formatSegmentsFromOption($option, $segmentsMap, $fareInfoMap);

            if (empty($segments)) continue;

            $segmentsWithLayover = $this->helper->addLayoverInformation($segments);

            $firstSegment = $segments[0];
            $lastSegment = end($segments);

            $departureAirportCode = $firstSegment['departure']['airport_code'];
            $arrivalAirportCode = $lastSegment['arrival']['airport_code'];

            $legs[] = [
                // ✅ Leg Key & XML
                'key' => (string)($flightOptionAttr['LegRef'] ?? ''),
                'option_key' => (string)($optAttr['Key'] ?? ''),

                'leg_number' => $legNumber,
                'leg_type' => $this->helper->identifyLegType($legNumber - 1, $totalLegs),
                'duration' => $travelTimeMinutes ?: ($lastSegment['duration'] ?? 0),
                'duration_formatted' => $this->helper->formatDuration($travelTimeMinutes ?: ($lastSegment['duration'] ?? 0)),
                'stops' => count($segments) - 1,
                'is_direct' => count($segments) === 1,
                'total_segments' => count($segments),
                'departure' => [
                    'airport_code' => $departureAirportCode,
                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
                    'city' => $this->helper->getAirportCity($departureAirportCode),
                    'address' => $this->helper->getAirportAddress($departureAirportCode),
                    'country' => $this->helper->getAirportCountry($departureAirportCode),
                    'time' => $firstSegment['departure']['time'],
                    'time_12h' => $firstSegment['departure']['time_12h'],
                    'date' => $firstSegment['departure']['date'],
                    'date_time_zoon' => $firstSegment['departure']['date_time_zoon'],
                    'terminal' => $firstSegment['departure']['terminal'],
                ],
                'arrival' => [
                    'airport_code' => $arrivalAirportCode,
                    'airport_name' => $this->helper->getAirportName($arrivalAirportCode),
                    'city' => $this->helper->getAirportCity($arrivalAirportCode),
                    'address' => $this->helper->getAirportAddress($arrivalAirportCode),
                    'country' => $this->helper->getAirportCountry($arrivalAirportCode),
                    'time' => $lastSegment['arrival']['time'],
                    'time_12h' => $lastSegment['arrival']['time_12h'],
                    'date' => $lastSegment['arrival']['date'],
                    'date_time_zoon' => $firstSegment['arrival']['date_time_zoon'],
                    'terminal' => $lastSegment['arrival']['terminal'],
                    'date_adjustment' => $lastSegment['arrival']['date_adjustment'] ?? 0,
                ],
                'stops_detail' => $this->helper->extractStopDetails($segmentsWithLayover),
                'segments' => $segmentsWithLayover,
            ];

            $legNumber++;
        }

        return $legs;
    }

    /**
     * Format segments from option - with key & xml
     */
    private function formatSegmentsFromOption($option, array $segmentsMap, array $fareInfoMap): array
    {
        $segments = [];
        $bookingInfos = $option->xpath('.//air:BookingInfo');

        foreach ($bookingInfos as $bookingInfo) {
            $bookAttr = $bookingInfo->attributes();
            $segmentRef = (string)$bookAttr['SegmentRef'];
            $bookingCode = (string)($bookAttr['BookingCode'] ?? '');
            $bookingCount = (int)($bookAttr['BookingCount'] ?? 0);
            $cabinClass = (string)($bookAttr['CabinClass'] ?? 'Economy');
            $fareInfoRef = (string)($bookAttr['FareInfoRef'] ?? '');
            $hostTokenRef = (string)($bookAttr['HostTokenRef'] ?? '');

            $segmentData = $segmentsMap[$segmentRef] ?? null;
            if (!$segmentData) continue;

            $fareInfo = $fareInfoMap[$fareInfoRef] ?? null;

            // ✅ Add booking info with key & xml
            $bookingInfoData = [
                'xml' => $bookingInfo->asXML(),
                'booking_code' => $bookingCode,
                'booking_count' => $bookingCount,
                'cabin_class' => $cabinClass,
                'fare_info_ref' => $fareInfoRef,
                'segment_ref' => $segmentRef,
                'host_token_ref' => $hostTokenRef,
            ];

            $formattedSegments = $this->formatSegment($segmentData, count($segments) + 1, $bookingCode, $bookingCount, $cabinClass, $fareInfo, $bookingInfoData);

            foreach ($formattedSegments as $fs) {
                $segments[] = $fs;
            }
        }

        // Reassign sequential segment numbers
        foreach ($segments as $i => &$seg) {
            $seg['segment_number'] = $i + 1;
        }
        unset($seg);

        return $segments;
    }

    /**
     * Format single segment - with key & xml
     */
    private function formatSegment(array $segmentData, int $segmentNumber, string $bookingCode, int $bookingCount, string $cabinClass, ?array $fareInfo, array $bookingInfoData): array
    {
        $details = $segmentData['flight_details'] ?? [];

        $buildSegment = function (array $data, int $segNum) use ($segmentData, $bookingCode, $bookingCount, $cabinClass, $fareInfo, $bookingInfoData) {
            $carrierCode = $segmentData['carrier'] ?? ($data['carrier'] ?? null);
            $operatingCode = $segmentData['operating_carrier'] ?? $carrierCode;
            $departureAirportCode = $data['origin'] ?? $segmentData['origin'] ?? null;
            $arrivalAirportCode = $data['destination'] ?? $segmentData['destination'] ?? null;

            $departureDateTime = $this->helper->parseDateTime((string)($data['departure_time'] ?? $segmentData['departure_time'] ?? ''));
            $arrivalDateTime = $this->helper->parseDateTime((string)($data['arrival_time'] ?? $segmentData['arrival_time'] ?? ''));

            $durationMinutes = (int)($data['flight_time'] ?? $segmentData['flight_time'] ?? 0);

            return [
                // ✅ Segment Key & XML
                'key' => $segmentData['key'],
                'xml' => $segmentData['xml'],

                'segment_number' => $segNum,
                'group' => $segmentData['group'] ?? ($data['group'] ?? null),
                'carrier' => $carrierCode,
                'carrier_name' => $this->helper->getAirlineName($carrierCode),
                'carrier_images' => [
                    'thumb' => $this->helper->getAirlineImage($carrierCode, 'thumb'),
                    'medium' => $this->helper->getAirlineImage($carrierCode, 'medium'),
                    'large' => $this->helper->getAirlineImage($carrierCode, 'large'),
                    'full' => $this->helper->getAirlineImage($carrierCode, 'full'),
                ],
                'operating_carrier' => $operatingCode,
                'operating_carrier_name' => $this->helper->getAirlineName($operatingCode),
                'is_codeshare' => $carrierCode !== $operatingCode,
                'flight_number' => $segmentData['flight_number'] ?? null,
                'operating_flight_number' => $segmentData['operating_flight_number'] ?? null,
                'full_flight_number' => ($carrierCode ?? '') . '-' . ($segmentData['flight_number'] ?? ''),
                'departure' => [
                    'airport_code' => $departureAirportCode,
                    'airport_name' => $this->helper->getAirportName($departureAirportCode),
                    'city' => $this->helper->getAirportCity($departureAirportCode),
                    'address' => $this->helper->getAirportAddress($departureAirportCode),
                    'country' => $this->helper->getAirportCountry($departureAirportCode),
                    'country_code' => $this->helper->getAirportCountryCode($departureAirportCode),
                    'time' => $departureDateTime['time'],
                    'time_12h' => $departureDateTime['time_12h'],
                    'date' => $departureDateTime['date'],
                    'date_time_zoon' => (string)($data['departure_time'] ?? $segmentData['departure_time'] ?? ''),
                    'terminal' => $data['origin_terminal'] ?? null,
                ],
                'arrival' => [
                    'airport_code' => $arrivalAirportCode,
                    'airport_name' => $this->helper->getAirportName($arrivalAirportCode),
                    'city' => $this->helper->getAirportCity($arrivalAirportCode),
                    'address' => $this->helper->getAirportAddress($arrivalAirportCode),
                    'country' => $this->helper->getAirportCountry($arrivalAirportCode),
                    'country_code' => $this->helper->getAirportCountryCode($arrivalAirportCode),
                    'time' => $arrivalDateTime['time'],
                    'time_12h' => $arrivalDateTime['time_12h'],
                    'date' => $arrivalDateTime['date'],
                    'date_adjustment' => $this->helper->calculateDateAdjustment($departureDateTime['date'], $arrivalDateTime['date']),
                    'date_time_zoon' => (string)($data['arrival_time'] ?? $segmentData['arrival_time'] ?? ''),
                    'terminal' => $data['destination_terminal'] ?? null,
                ],
                'duration' => $durationMinutes,
                'duration_formatted' => $this->helper->formatDuration($durationMinutes),
                'miles' => (int)($data['distance'] ?? $segmentData['distance'] ?? 0),
                'aircraft' => $data['equipment'] ?? $segmentData['equipment'] ?? null,
                'aircraft_name' => $this->helper->getAircraftName($data['equipment'] ?? $segmentData['equipment'] ?? null),
                'change_of_plane' => (bool)($segmentData['change_of_plane'] ?? true),
                'link_availability' => (bool)($segmentData['link_availability'] ?? true),
                'optional_services_indicator' => (bool)($segmentData['optional_services_indicator'] ?? true),
                'participant_level' => (string)($segmentData['participant_level'] ?? ''),
                'polled_availability_option' => (string)($segmentData['polled_availability_option'] ?? ''),
                'availability_source' => (string)($segmentData['availability_source'] ?? ''),
                'availability_display_type' => (string)($segmentData['availability_display_type'] ?? ''),
                'class_of_service' => (string)($segmentData['class_of_service'] ?? ''),
                'provider_code' => (string)($segmentData['provider_code'] ?? ''),
                'eTicketable' => (string)($segmentData['e_ticketability'] ?? ' '),
                'stop_count' => $segmentData['number_of_stops'] ?? 0,

                // ✅ Fare Info with key & xml
                'fare_info' => [
                    'key' => $fareInfo['key'] ?? null,
                    'xml' => $fareInfo['xml'] ?? null,
                    'fare_basis_code' => $fareInfo['fare_basis'] ?? null,
                    'booking_code' => $bookingCode,
                    'cabin_code' => $this->helper->getCabinCode($cabinClass),
                    'cabin_name' => $this->helper->getCabinName($cabinClass),
                    'seats_available' => $bookingCount,
                    'availability_break' => false,

                    'baggage_kg'      => $fareInfo['baggage']['weight']      ?? null,
                    'baggage_unit'    => $fareInfo['baggage']['unit']        ?? 'kg',
                    'baggage_pieces'  => $fareInfo['baggage']['piece_count'] ?? null,

                ],

                // ✅ Booking Info with key & xml
                'booking_info' => $bookingInfoData,

                // ✅ Flight Details with keys
                'flight_details_keys' => $segmentData['flight_details_keys'] ?? [],
                'flight_details' => $segmentData['flight_details'] ?? [],
            ];
        };

        if (!empty($details) && is_array($details)) {
            $out = [];
            $num = $segmentNumber;
            foreach ($details as $detail) {
                $out[] = $buildSegment($detail, $num++);
            }
            return $out;
        }

        return [$buildSegment($segmentData, $segmentNumber)];
    }

    /**
     * Format passenger breakdown - with pricing info keys & xml
     */
    private function formatPassengerBreakdown(array $allPricingInfos, array $fareInfoMap, array $brandListMap = []): array
    {
        $passengers = [];

        foreach ($allPricingInfos as $pricingInfo) {
            $pricingAttr = $pricingInfo->attributes();

            $passengerTypes = $pricingInfo->xpath('.//air:PassengerType');
            $passengerCount = count($passengerTypes);

            $firstPassenger = $passengerTypes[0] ?? null;
            $passengerCode  = $firstPassenger ? (string)($firstPassenger->attributes()['Code'] ?? 'ADT') : 'ADT';
            $passengerAge   = $firstPassenger ? (string)($firstPassenger->attributes()['Age']  ?? '')    : '';

            $totalPrice            = $this->helper->parsePrice((string)($pricingAttr['TotalPrice']           ?? '0'));
            $basePrice             = $this->helper->parsePrice((string)($pricingAttr['BasePrice']            ?? '0'));
            $basePriceCurrency     = $this->helper->extractCurrency((string)($pricingAttr['BasePrice']       ?? 'USD0'));
            $approximateTotalPrice = $this->helper->parsePrice((string)($pricingAttr['ApproximateTotalPrice'] ?? '0'));
            $equivalentBasePrice   = $this->helper->parsePrice((string)($pricingAttr['EquivalentBasePrice']  ?? '0'));
            $taxes                 = $this->helper->parsePrice((string)($pricingAttr['Taxes']                ?? '0'));

            $exchangeRate = ($basePrice > 0 && $equivalentBasePrice > 0)
                ? round($equivalentBasePrice / $basePrice, 4)
                : null;

            $fareInfoRefs    = $pricingInfo->xpath('.//air:FareInfoRef');
            $baggage         = null;
            $brandDetails    = null;
            $fareInfoKeys    = [];
            $baggageBySegment = [];

            foreach ($fareInfoRefs as $ref) {
                $key = (string)$ref->attributes()['Key'];
                $fareInfoKeys[] = $key;

                if (isset($fareInfoMap[$key])) {
                    $fareData = $fareInfoMap[$key];

                    // Overall fallback baggage
                    if (!empty($fareData['baggage']) && !$baggage) {
                        $baggage = $fareData['baggage'];
                    }

                    // Brand
                    if (!empty($fareData['brand']) && !$brandDetails) {
                        $brandKey = $fareData['brand']['key']      ?? '';
                        $brandId  = $fareData['brand']['brand_id'] ?? '';

                        if ($brandKey && isset($brandListMap[$brandKey])) {
                            $brandDetails = $brandListMap[$brandKey];
                        } elseif ($brandId && isset($brandListMap['by_id_' . $brandId])) {
                            $brandDetails = $brandListMap['by_id_' . $brandId];
                        } else {
                            $brandDetails = $fareData['brand'];
                        }
                    }

                    // ✅ Per-leg baggage — Sabre format
                    $baggageBySegment[] = [
                        'route'       => ($fareData['origin'] ?? '') . ' → ' . ($fareData['destination'] ?? ''),
                        'departure'   => $fareData['origin']                   ?? null,
                        'arrival'     => $fareData['destination']              ?? null,
                        'airline'     => null,
                        'weight'      => $fareData['baggage']['weight']        ?? null,
                        'unit'        => isset($fareData['baggage']['weight']) ? 'kg' : null,
                        'piece_count' => $fareData['baggage']['piece_count']   ?? null,
                    ];
                }
            }

            // Tax info
            $taxInfos        = [];
            $taxInfoElements = $pricingInfo->xpath('.//air:TaxInfo');
            foreach ($taxInfoElements as $tax) {
                $taxAttr    = $tax->attributes();
                $taxInfos[] = [
                    'key'      => (string)($taxAttr['Key']      ?? ''),
                    'xml'      => $tax->asXML(),
                    'category' => (string)($taxAttr['Category'] ?? ''),
                    'amount'   => (string)($taxAttr['Amount']   ?? ''),
                ];
            }

            $passengers[] = [
                'pricing_info' => [
                    'key' => (string)($pricingAttr['Key'] ?? ''),
                    'xml' => $pricingInfo->asXML(),
                ],

                'type'                => $passengerCode,
                'type_label'          => $this->helper->getPassengerTypeLabel($passengerCode),
                'count'               => $passengerCount,
                'total_fare'          => $approximateTotalPrice,
                'base_fare'           => $basePrice,
                'base_fare_currency'  => $basePriceCurrency,
                'tax_amount'          => $taxes,
                'equivalent_amount'   => $equivalentBasePrice,
                'equivalent_currency' => 'BDT',
                'currency'            => 'BDT',
                'exchange_rate'       => $exchangeRate,
                'exchange_from'       => $basePriceCurrency,
                'exchange_to'         => 'BDT',
                'refundable'          => (string)($pricingAttr['Refundable'] ?? 'false') === 'true',

                'baggage' => $baggage ? [
                    'xml'        => $baggage['xml']         ?? null,
                    'weight'     => $baggage['weight']      ?? null,
                    'unit'       => $baggage['unit']        ?? 'kg',
                    'piece_count'=> $baggage['piece_count'] ?? null,
                ] : null,

                // ✅ Sabre এর মতো same variable name
                'baggage_by_segment' => $baggageBySegment,

                'age' => $passengerAge ? (int)$passengerAge : null,

                'brand' => $brandDetails ? [
                    'key'      => $brandDetails['key']      ?? null,
                    'xml'      => $brandDetails['xml']      ?? null,
                    'brand_id' => $brandDetails['brand_id'] ?? null,
                    'name'     => $brandDetails['name']     ?? null,
                    'title'    => $brandDetails['title']    ?? null,
                ] : null,

                'fare_info_keys' => $fareInfoKeys,
                'tax_info'       => $taxInfos,
            ];
        }

        return $passengers;
    }

    /**
     * Parse penalties - with xml
     */
    private function parsePenalties($pricingInfo): array
    {
        $penalties = [
            'change' => null,
            'cancel' => null,
        ];

        $changePenalty = $pricingInfo->xpath('.//air:ChangePenalty')[0] ?? null;
        if ($changePenalty) {
            $amount = $changePenalty->xpath('.//air:Amount')[0] ?? null;
            $percentage = $changePenalty->xpath('.//air:Percentage')[0] ?? null;

            $penalties['change'] = [
                'xml' => $changePenalty->asXML(),
                'applies' => (string)($changePenalty->attributes()['PenaltyApplies'] ?? ''),
                'amount' => $amount ? $this->helper->parsePrice((string)$amount) : null,
                'percentage' => $percentage ? (float)(string)$percentage : null,
            ];
        }

        $cancelPenalty = $pricingInfo->xpath('.//air:CancelPenalty')[0] ?? null;
        if ($cancelPenalty) {
            $amount = $cancelPenalty->xpath('.//air:Amount')[0] ?? null;
            $percentage = $cancelPenalty->xpath('.//air:Percentage')[0] ?? null;

            $penalties['cancel'] = [
                'xml' => $cancelPenalty->asXML(),
                'applies' => (string)($cancelPenalty->attributes()['PenaltyApplies'] ?? ''),
                'amount' => $amount ? $this->helper->parsePrice((string)$amount) : null,
                'percentage' => $percentage ? (float)(string)$percentage : null,
            ];
        }

        return $penalties;
    }

    /**
     * Collect fare calcs - with xml
     */
    private function collectFareCalcs(array $allPricingInfos): array
    {
        $fareCalcs = [];

        foreach ($allPricingInfos as $pricingInfo) {
            $passengerType = $pricingInfo->xpath('.//air:PassengerType')[0] ?? null;
            $passengerCode = $passengerType ? (string)($passengerType->attributes()['Code'] ?? 'ADT') : 'ADT';

            $fareCalc = $pricingInfo->xpath('.//air:FareCalc')[0] ?? null;

            if ($fareCalc) {
                $fareCalcs[] = [
                    'xml' => $fareCalc->asXML(),
                    'passenger_type' => $passengerCode,
                    'fare_calc' => (string)$fareCalc,
                ];
            }
        }

        return $fareCalcs;
    }
}
