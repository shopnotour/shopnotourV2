<?php

namespace Modules\Booking\Service;

use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use SimpleXMLElement;

class TravelPortBookingResponseService
{
    /**
     * Main parser method with SOAP Fault handling
     */
    public function parseAirPriceResponse($xmlString): array
    {
        try {
            // ✅ Clean XML
            $xmlString = $this->cleanXmlString($xmlString);

            if (empty($xmlString)) {
                return [
                    'success' => false,
                    'error' => 'Empty XML string after cleaning'
                ];
            }

            // ✅ Load XML
            libxml_use_internal_errors(true);
            $xml = new SimpleXMLElement($xmlString);
            libxml_clear_errors();

            // ✅ Register namespaces
            $this->registerNamespaces($xml);

            // ✅ Check for SOAP Fault FIRST
            $soapFault = $xml->xpath('//SOAP:Fault');
            if (!empty($soapFault)) {
                return $this->parseSoapFault($soapFault[0], $xml);
            }

            // ✅ Get AirPriceRsp
            $airPriceRsp = $xml->xpath('//air:AirPriceRsp');
            if (empty($airPriceRsp)) {
                return [
                    'success' => false,
                    'error' => 'AirPriceRsp not found in XML',
                    'xml_preview' => substr($xmlString, 0, 200)
                ];
            }

            $airPriceRsp = $airPriceRsp[0];
            $rspAttr = $airPriceRsp->attributes();

            // ✅ Parse successful response
            $response = [
                'success' => true,
                'trace_id' => (string)($rspAttr['TraceId'] ?? ''),
                'transaction_id' => (string)($rspAttr['TransactionId'] ?? ''),
                'response_time' => (int)($rspAttr['ResponseTime'] ?? 0),
                'messages' => $this->parseMessages($xml),
                'itinerary' => $this->parseItinerary($xml),
                'price_result' => $this->parsePriceResult($xml),
            ];

            return $response;

        } catch (\Exception $e) {
            Log::error('XML Parse Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    /**
     * ✅ Parse SOAP Fault
     */
    private function parseSoapFault(SimpleXMLElement $fault, SimpleXMLElement $xml): array
    {
        $this->registerNamespaces($xml);

        $faultCode = (string)$fault->faultcode;
        $faultString = (string)$fault->faultstring;

        // Get detailed error info
        $errorInfo = $xml->xpath('//common:ErrorInfo');

        $errorDetails = [];
        if (!empty($errorInfo)) {
            $errorInfo = $errorInfo[0];
            $this->registerNamespaces($errorInfo);

            $errorDetails = [
                'code' => (string)($errorInfo->{'common:Code'} ?? ''),
                'service' => (string)($errorInfo->{'common:Service'} ?? ''),
                'type' => (string)($errorInfo->{'common:Type'} ?? ''),
                'description' => (string)($errorInfo->{'common:Description'} ?? ''),
                'transaction_id' => (string)($errorInfo->{'common:TransactionId'} ?? ''),
                'trace_id' => (string)($errorInfo->{'common:TraceId'} ?? ''),
            ];
        }

        return [
            'success' => false,
            'soap_fault' => true,
            'fault_code' => $faultCode,
            'fault_string' => trim($faultString),
            'error_details' => $errorDetails,
            'error_message' => $errorDetails['description'] ?? $faultString,
        ];
    }

    /**
     * ✅ Parse Price Result with proper passenger pricing
     */
    private function parsePriceResult(SimpleXMLElement $xml): array
    {
        $this->registerNamespaces($xml);

        $priceResult = [
            'solutions' => [],
            'xml' => ''
        ];

        $pricingSolutions = $xml->xpath('//air:AirPricingSolution');

        if (!empty($pricingSolutions)) {
            foreach ($pricingSolutions as $solution) {
                $priceResult['solutions'][] = $this->parsePricingSolution($solution);
            }
        }

        $priceResultNode = $xml->xpath('//air:AirPriceResult');
        if (!empty($priceResultNode)) {
            $priceResult['xml'] = $priceResultNode[0]->asXML();
        }

        return $priceResult;
    }

    /**
     * ✅ Parse Pricing Solution with structured prices
     */
    private function parsePricingSolution(SimpleXMLElement $solution): array
    {
        $this->registerNamespaces($solution);
        $attr = $solution->attributes();

        $solutionData = [
            'key' => (string)($attr['Key'] ?? ''),

            // ✅ Total Prices (parsed)
            'total_price' => $this->parsePrice((string)($attr['TotalPrice'] ?? '')),
            'base_price' => $this->parsePrice((string)($attr['BasePrice'] ?? '')),
            'taxes' => $this->parsePrice((string)($attr['Taxes'] ?? '')),
            'fees' => $this->parsePrice((string)($attr['Fees'] ?? '')),
            'approximate_total_price' => $this->parsePrice((string)($attr['ApproximateTotalPrice'] ?? '')),
            'approximate_base_price' => $this->parsePrice((string)($attr['ApproximateBasePrice'] ?? '')),
            'equivalent_base_price' => $this->parsePrice((string)($attr['EquivalentBasePrice'] ?? '')),
            'approximate_taxes' => $this->parsePrice((string)($attr['ApproximateTaxes'] ?? '')),
            'quote_date' => (string)($attr['QuoteDate'] ?? ''),

            'segment_refs' => $this->parseSegmentRefs($solution),
            'pricing_info' => $this->parsePricingInfos($solution),
            'fare_notes' => $this->parseFareNotes($solution),
            'host_tokens' => $this->parseHostTokens($solution),
        ];

        return $solutionData;
    }

    /**
     * ✅ Parse all AirPricingInfo (individual passenger pricing)
     */
    private function parsePricingInfos(SimpleXMLElement $solution): array
    {
        $this->registerNamespaces($solution);
        $pricingInfos = [];

        $infos = $solution->xpath('./air:AirPricingInfo');

        if (!empty($infos)) {
            foreach ($infos as $info) {
                $pricingInfos[] = $this->parsePricingInfo($info);
            }
        }

        return $pricingInfos;
    }

    /**
     * ✅ Parse individual AirPricingInfo
     * 🔥 FIXED: Now counts all passengers in this pricing block
     */
    private function parsePricingInfo($info): array
    {
        $this->registerNamespaces($info);
        $attr = $info->attributes();

        // ✅ Get ALL passenger types (can be multiple in one pricing block)
        $passengerTypes = $info->xpath('./air:PassengerType');
        $passengerCode = '';
        $passengerAge = null;
        $passengerCount = count($passengerTypes); // ✅ Count passengers

        // ✅ Collect all passenger details
        $allPassengerDetails = [];
        foreach ($passengerTypes as $pt) {
            $ptAttr = $pt->attributes();
            $allPassengerDetails[] = [
                'code' => (string)($ptAttr['Code'] ?? ''),
                'age' => isset($ptAttr['Age']) ? (int)$ptAttr['Age'] : null,
            ];
        }

        // ✅ Get first passenger info (representative of the group)
        if (!empty($passengerTypes)) {
            $ptAttr = $passengerTypes[0]->attributes();
            $passengerCode = (string)($ptAttr['Code'] ?? '');
            $passengerAge = isset($ptAttr['Age']) ? (int)$ptAttr['Age'] : null;
        }

        $data = [
            'key' => (string)($attr['Key'] ?? ''),

            // ✅ Passenger Info
            'passenger_type' => $passengerCode,
            'passenger_age' => $passengerAge,
            'passenger_count' => $passengerCount, // ✅ NEW: How many passengers in this pricing block
            'all_passengers' => $allPassengerDetails, // ✅ NEW: Full breakdown

            // ✅ Individual Prices (parsed)
            'total_price' => $this->parsePrice((string)($attr['TotalPrice'] ?? '')),
            'base_price' => $this->parsePrice((string)($attr['BasePrice'] ?? '')),
            'taxes' => $this->parsePrice((string)($attr['Taxes'] ?? '')),
            'approximate_total_price' => $this->parsePrice((string)($attr['ApproximateTotalPrice'] ?? '')),
            'approximate_base_price' => $this->parsePrice((string)($attr['ApproximateBasePrice'] ?? '')),
            'equivalent_base_price' => $this->parsePrice((string)($attr['EquivalentBasePrice'] ?? '')),
            'approximate_taxes' => $this->parsePrice((string)($attr['ApproximateTaxes'] ?? '')),

            // ✅ Other Info
            'latest_ticketing_time' => (string)($attr['LatestTicketingTime'] ?? ''),
            'pricing_method' => (string)($attr['PricingMethod'] ?? ''),
            'refundable' => (string)($attr['Refundable'] ?? 'false') === 'true',
            'includes_vat' => (string)($attr['IncludesVAT'] ?? 'false') === 'true',
            'eticketability' => (string)($attr['ETicketability'] ?? ''),
            'plating_carrier' => (string)($attr['PlatingCarrier'] ?? ''),
            'provider_code' => (string)($attr['ProviderCode'] ?? ''),

            // ✅ Details
            'fare_info' => $this->parseFareInfos($info),
            'booking_info' => $this->parseBookingInfos($info),
            'tax_info' => $this->parseTaxInfos($info),
            'fare_calc' => $this->parseFareCalc($info),
            'passenger_types' => $this->parsePassengerTypes($info), // ✅ Keep for backward compatibility
            'penalties' => $this->parsePenalties($info),
            'baggage_allowances' => $this->parseBaggageAllowances($info),
        ];

        return $data;
    }

    /**
     * ✅ Parse price string helper
     */
    private function parsePrice(string $priceString): array
    {
        if (empty($priceString)) {
            return [
                'raw' => '',
                'currency' => '',
                'amount' => 0.0,
                'formatted' => ''
            ];
        }

        // Extract currency and amount
        // Format: "BDT168139", "USD1078.00"
        preg_match('/^([A-Z]{3})(.+)$/', $priceString, $matches);

        $currency = $matches[1] ?? '';
        $amount = isset($matches[2]) ? floatval(str_replace(',', '', $matches[2])) : 0.0;

        return [
            'raw' => $priceString,
            'currency' => $currency,
            'amount' => $amount,
            'formatted' => $currency . ' ' . number_format($amount, 2)
        ];
    }

    /**
     * ✅ Parse Segment Refs
     */
    private function parseSegmentRefs(SimpleXMLElement $solution): array
    {
        $refs = [];
        $segmentRefs = $solution->xpath('./air:AirSegmentRef');

        if (!empty($segmentRefs)) {
            foreach ($segmentRefs as $ref) {
                $refs[] = ['key' => (string)($ref['Key'] ?? '')];
            }
        }

        return $refs;
    }

    /**
     * ✅ Parse Fare Notes
     */
    private function parseFareNotes(SimpleXMLElement $solution): array
    {
        $notes = [];
        $fareNotes = $solution->xpath('./air:FareNote');

        if (!empty($fareNotes)) {
            foreach ($fareNotes as $note) {
                $notes[] = [
                    'key' => (string)($note['Key'] ?? ''),
                    'text' => trim((string)$note),
                ];
            }
        }

        return $notes;
    }

    /**
     * ✅ Parse Host Tokens
     */
    private function parseHostTokens(SimpleXMLElement $solution): array
    {
        $tokens = [];
        $hostTokens = $solution->xpath('./common:HostToken');

        if (!empty($hostTokens)) {
            foreach ($hostTokens as $token) {
                $tokens[] = [
                    'key' => (string)($token['Key'] ?? ''),
                    'value' => trim((string)$token),
                ];
            }
        }

        return $tokens;
    }

    /**
     * ✅ Parse Fare Infos
     */
    private function parseFareInfos(SimpleXMLElement $info): array
    {
        $fareInfos = [];
        $fares = $info->xpath('./air:FareInfo');

        if (!empty($fares)) {
            foreach ($fares as $fare) {
                $fareInfos[] = $this->parseFareInfo($fare);
            }
        }

        return $fareInfos;
    }

    /**
     * ✅ Parse Booking Infos
     */
    private function parseBookingInfos(SimpleXMLElement $info): array
    {
        $bookingInfos = [];
        $bookings = $info->xpath('./air:BookingInfo');

        if (!empty($bookings)) {
            foreach ($bookings as $booking) {
                $bookAttr = $booking->attributes();
                $bookingInfos[] = [
                    'booking_code' => (string)($bookAttr['BookingCode'] ?? ''),
                    'cabin_class' => (string)($bookAttr['CabinClass'] ?? ''),
                    'fare_info_ref' => (string)($bookAttr['FareInfoRef'] ?? ''),
                    'segment_ref' => (string)($bookAttr['SegmentRef'] ?? ''),
                    'host_token_ref' => (string)($bookAttr['HostTokenRef'] ?? ''),
                ];
            }
        }

        return $bookingInfos;
    }

    /**
     * ✅ Parse Tax Infos
     */
    private function parseTaxInfos(SimpleXMLElement $info): array
    {
        $taxes = [];
        $taxInfos = $info->xpath('./air:TaxInfo');

        if (!empty($taxInfos)) {
            foreach ($taxInfos as $tax) {
                $taxAttr = $tax->attributes();
                $taxes[] = [
                    'category' => (string)($taxAttr['Category'] ?? ''),
                    'amount' => $this->parsePrice((string)($taxAttr['Amount'] ?? '')),
                    'key' => (string)($taxAttr['Key'] ?? ''),
                ];
            }
        }

        return $taxes;
    }

    /**
     * ✅ Parse Fare Calc
     */
    private function parseFareCalc(SimpleXMLElement $info): ?array
    {
        $fareCalc = $info->xpath('./air:FareCalc');

        if (!empty($fareCalc)) {
            return [
                'text' => trim((string)$fareCalc[0])
            ];
        }

        return null;
    }

    /**
     * ✅ Parse Passenger Types (backward compatibility)
     */
    private function parsePassengerTypes(SimpleXMLElement $info): array
    {
        $types = [];
        $passengerTypes = $info->xpath('./air:PassengerType');

        if (!empty($passengerTypes)) {
            foreach ($passengerTypes as $type) {
                $ptAttr = $type->attributes();
                $types[] = [
                    'code' => (string)($ptAttr['Code'] ?? ''),
                    'age' => isset($ptAttr['Age']) ? (int)$ptAttr['Age'] : null,
                ];
            }
        }

        return $types;
    }

    /**
     * ✅ Parse Penalties
     */
    private function parsePenalties(SimpleXMLElement $info): array
    {
        $penalties = [];

        // Change Penalty
        $changePenalty = $info->xpath('./air:ChangePenalty');
        if (!empty($changePenalty)) {
            $cpAttr = $changePenalty[0]->attributes();
            $amount = $changePenalty[0]->xpath('./air:Amount');

            $penalties['change'] = [
                'applies' => (string)($cpAttr['PenaltyApplies'] ?? ''),
                'amount' => !empty($amount) ? $this->parsePrice(trim((string)$amount[0])) : null,
            ];
        }

        // Cancel Penalty
        $cancelPenalty = $info->xpath('./air:CancelPenalty');
        if (!empty($cancelPenalty)) {
            $cpAttr = $cancelPenalty[0]->attributes();
            $amount = $cancelPenalty[0]->xpath('./air:Amount');

            $penalties['cancel'] = [
                'applies' => (string)($cpAttr['PenaltyApplies'] ?? ''),
                'amount' => !empty($amount) ? $this->parsePrice(trim((string)$amount[0])) : null,
            ];
        }

        return $penalties;
    }

    /**
     * ✅ Parse Baggage Allowances
     */
    private function parseBaggageAllowances(SimpleXMLElement $info): array
    {
        $baggageAllowances = $info->xpath('./air:BaggageAllowances');

        if (!empty($baggageAllowances)) {
            return $this->parseBaggageAllowancesDetail($baggageAllowances[0]);
        }

        return [];
    }

    private function parseBaggageAllowancesDetail($baggageAllowances): array
    {
        $data = [
            'baggage_allowance_info' => [],
            'carry_on_allowance_info' => [],
        ];

        // Baggage Allowance Info
        $allowances = $baggageAllowances->xpath('./air:BaggageAllowanceInfo');
        if (!empty($allowances)) {
            foreach ($allowances as $allowance) {
                $allowAttr = $allowance->attributes();
                $textInfo = $allowance->xpath('.//air:TextInfo/air:Text');
                $texts = [];
                foreach ($textInfo as $text) {
                    $texts[] = trim((string)$text);
                }

                $data['baggage_allowance_info'][] = [
                    'traveler_type' => (string)($allowAttr['TravelerType'] ?? ''),
                    'origin' => (string)($allowAttr['Origin'] ?? ''),
                    'destination' => (string)($allowAttr['Destination'] ?? ''),
                    'carrier' => (string)($allowAttr['Carrier'] ?? ''),
                    'texts' => $texts,
                ];
            }
        }

        // Carry On Allowance Info
        $carryOns = $baggageAllowances->xpath('./air:CarryOnAllowanceInfo');
        if (!empty($carryOns)) {
            foreach ($carryOns as $carryOn) {
                $carryOnAttr = $carryOn->attributes();
                $textInfo = $carryOn->xpath('.//air:TextInfo/air:Text');
                $texts = [];
                foreach ($textInfo as $text) {
                    $texts[] = trim((string)$text);
                }

                $data['carry_on_allowance_info'][] = [
                    'origin' => (string)($carryOnAttr['Origin'] ?? ''),
                    'destination' => (string)($carryOnAttr['Destination'] ?? ''),
                    'carrier' => (string)($carryOnAttr['Carrier'] ?? ''),
                    'texts' => $texts,
                ];
            }
        }

        return $data;
    }

    // ... Keep your existing methods below (cleanXmlString, registerNamespaces, parseMessages, parseItinerary, parseSegment, parseFareInfo, parseBrand, updatePricesFromParsedData, etc.)

    private function cleanXmlString($xmlString): string
    {
        if (!is_string($xmlString)) {
            return '';
        }

        $xmlString = trim($xmlString, '"\'');
        $xmlString = preg_replace('/[◀▶]/u', '', $xmlString);
        $xmlString = preg_replace('/^[\x00-\x1F\x80-\xFF]{1,3}/', '', $xmlString);
        $xmlString = trim($xmlString);

        if (!str_starts_with($xmlString, '<')) {
            $pos = strpos($xmlString, '<');
            if ($pos !== false) {
                $xmlString = substr($xmlString, $pos);
            }
        }

        return $xmlString;
    }

    private function registerNamespaces(SimpleXMLElement $xml): void
    {
        $xml->registerXPathNamespace('SOAP', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v52_0');
        $xml->registerXPathNamespace('common', 'http://www.travelport.com/schema/common_v52_0');
    }

    private function parseMessages(SimpleXMLElement $xml): array
    {
        $messages = [];
        $messageElements = $xml->xpath('//common:ResponseMessage');

        if (!empty($messageElements)) {
            foreach ($messageElements as $msg) {
                $attr = $msg->attributes();
                $messages[] = [
                    'code' => (string)($attr['Code'] ?? ''),
                    'type' => (string)($attr['Type'] ?? ''),
                    'provider_code' => (string)($attr['ProviderCode'] ?? ''),
                    'message' => trim((string)$msg),
                ];
            }
        }

        return $messages;
    }

    private function parseItinerary(SimpleXMLElement $xml): array
    {
        $itinerary = [
            'segments' => [],
        ];

        $segments = $xml->xpath('//air:AirItinerary/air:AirSegment');

        if (!empty($segments)) {
            foreach ($segments as $segment) {
                $itinerary['segments'][] = $this->parseSegment($segment);
            }
        }

        return $itinerary;
    }

    private function parseSegment($segment): array
    {
        $attr = $segment->attributes();

        return [
            'key' => (string)($attr['Key'] ?? ''),
            'group' => (int)($attr['Group'] ?? 0),
            'carrier' => (string)($attr['Carrier'] ?? ''),
            'flight_number' => (string)($attr['FlightNumber'] ?? ''),
            'origin' => (string)($attr['Origin'] ?? ''),
            'destination' => (string)($attr['Destination'] ?? ''),
            'departure_time' => (string)($attr['DepartureTime'] ?? ''),
            'arrival_time' => (string)($attr['ArrivalTime'] ?? ''),
            'class_of_service' => (string)($attr['ClassOfService'] ?? ''),
        ];
    }

    private function parseFareInfo($fareInfo): array
    {
        $attr = $fareInfo->attributes();

        return [
            'key' => (string)($attr['Key'] ?? ''),
            'fare_basis' => (string)($attr['FareBasis'] ?? ''),
            'passenger_type_code' => (string)($attr['PassengerTypeCode'] ?? ''),
            'origin' => (string)($attr['Origin'] ?? ''),
            'destination' => (string)($attr['Destination'] ?? ''),
            'amount' => $this->parsePrice((string)($attr['Amount'] ?? '')),
        ];
    }

    public function updatePricesFromParsedData(int $bookingId, array $parsedData): array
    {
        try {
            if (!isset($parsedData['success']) || !$parsedData['success']) {
                return ['success' => false, 'error' => 'Invalid parsed data'];
            }

            $booking = Booking::with('passengers')->findOrFail($bookingId);
            $solution = $parsedData['price_result']['solutions'][0] ?? null;

            if (!$solution) {
                return ['success' => false, 'error' => 'No pricing solution'];
            }

            // ✅ Use parsed prices
            $approximateTotal = $solution['approximate_total_price']['amount'] ?? 0;
            $total = $approximateTotal + ($booking->ticketing_fee + $booking->supplier_fee) - $booking->coupon_amount;

            $booking->update([
                'total' => $total,
                'base_fee' => $solution['approximate_base_price']['amount'] ?? 0,
                'total_fee' => $solution['taxes']['amount'] ?? 0,
                'currency' => $solution['total_price']['currency'] ?? 'BDT',
                'price_raw_data' => json_encode($parsedData),
            ]);

            // ✅ Update passengers
            $this->updatePassengersFromParsedSolution($booking, $solution);

            Log::info('Booking prices updated', [
                'booking_id' => $bookingId,
                'total' => $booking->total
            ]);

            return [
                'success' => true,
                'total_price' => $booking->total,
                'currency' => $booking->currency
            ];

        } catch (\Exception $e) {
            Log::error('Price update error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function updatePassengersFromParsedSolution($booking, $solution)
    {
        $pricingInfos = $solution['pricing_info'] ?? [];

        foreach ($pricingInfos as $info) {
            $passengerType = $info['passenger_type'] ?? null;
            if (!$passengerType) continue;

            BookingPassenger::where('booking_id', $booking->id)
                ->where('passenger_type_code', $passengerType)
                ->update([
                    'total' => $info['total_price']['amount'] ?? 0,
                    'base' => $info['base_price']['amount'] ?? 0,
                    'tax' => $info['taxes']['amount'] ?? 0,
                    'currency' => $info['total_price']['currency'] ?? 'BDT'
                ]);
        }
    }

    // Backward compatibility
    private function extractAmount(string $priceString): float
    {
        return $this->parsePrice($priceString)['amount'];
    }

    private function extractCurrency(string $priceString): string
    {
        return $this->parsePrice($priceString)['currency'];
    }
}
