<?php
namespace Modules\Booking\Service\TravelPort;

class TravelportPriceResponseParser
{
    private \SimpleXMLElement $xml;
    private \SimpleXMLElement $xmlOriginal;

    public function __construct(string $xmlResponse)
    {
        $this->xmlOriginal = simplexml_load_string($xmlResponse);

        $cleaned = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlResponse);
        $cleaned = preg_replace('/<[a-z0-9_]+:/i', '<', $cleaned);
        $cleaned = preg_replace('/<\/[a-z0-9_]+:/i', '</', $cleaned);
        $this->xml = simplexml_load_string($cleaned);
    }

//    public function checkAndPrepare(int $sessionTotalPrice, $selectedPricingKey): array
//    {
//        $solutions = $this->xml->xpath('//AirPricingSolution');
//        $solution = null;
//        foreach ($solutions as $s) {
//            if ($selectedPricingKey && (string)$s['Key'] === $selectedPricingKey) {
//                $solution = $s;
//                break;
//            }
//        }
//
//        if (!$solution) {
//            foreach ($solutions as $s) {
//                $price = (int)preg_replace('/[^0-9]/', '', (string)$s['TotalPrice']);
//                if ($price === $sessionTotalPrice) {
//                    $solution = $s;
//                    break;
//                }
//            }
//        }
//        if (!$solution) {
//            $solution = $solutions[0] ?? null;
//        }
//        if (!$solution) {
//            return ['status' => 'error', 'message' => 'AirPricingSolution পাওয়া যায়নি।'];
//        }
//
//        $rawPrice = (string)$solution['TotalPrice'];
//        $apiTotalPrice = (int)preg_replace('/[^0-9]/', '', $rawPrice);
//
//        if ($apiTotalPrice !== $sessionTotalPrice) {
//            return [
//                'status' => 'mismatch',
//                'message' => 'দুঃখিত, এই ফ্লাইটের মূল্য পরিবর্তন হয়ে গেছে।',
//                'session_price' => $sessionTotalPrice,
//                'new_price' => $apiTotalPrice,
//            ];
//        }
//
//        $parsed = $this->parse($solution);
//
//        return ['status' => 'matched', 'message' => 'Price confirmed.', 'data' => $parsed];
//    }

    public function checkAndPrepare(int $sessionTotalPrice, $selectedPricingKey, string $selectedBrandName = ''): array
    {
        $solutions = $this->xml->xpath('//AirPricingSolution');
        if (empty($solutions)) {
            return ['status' => 'error', 'message' => 'AirPricingSolution পাওয়া যায়নি।'];
        }

        $solution = null;

        // ✅ Step 1: Brand name দিয়ে match করো (সবচেয়ে নির্ভরযোগ্য)
        if ($selectedBrandName) {
            foreach ($solutions as $s) {
                $brandNodes = $s->xpath('.//Brand');
                foreach ($brandNodes as $brand) {
                    $name = strtolower(trim((string)($brand['Name'] ?? '')));
                    if ($name === strtolower(trim($selectedBrandName))) {
                        $solution = $s;
                        break 2;
                    }
                }
            }
        }

        // ✅ Step 2: Price দিয়ে match করো
        if (!$solution) {
            foreach ($solutions as $s) {
                $price = (int)preg_replace('/[^0-9]/', '', (string)$s['TotalPrice']);
                if ($price === $sessionTotalPrice) {
                    $solution = $s;
                    break;
                }
            }
        }

        // ✅ Step 3: Fallback — প্রথম solution
        if (!$solution) {
            $solution = $solutions[0];
        }

        // ✅ Price check
        $apiTotalPrice = (int)preg_replace('/[^0-9]/', '', (string)$solution['TotalPrice']);

        if ($apiTotalPrice !== $sessionTotalPrice) {
            return [
                'status'        => 'mismatch',
                'message'       => 'দুঃখিত, এই ফ্লাইটের মূল্য পরিবর্তন হয়ে গেছে।',
                'session_price' => $sessionTotalPrice,
                'new_price'     => $apiTotalPrice,
            ];
        }

        $parsed = $this->parse($solution);
        return ['status' => 'matched', 'message' => 'Price confirmed.', 'data' => $parsed];
    }

    private function parse(\SimpleXMLElement $solution): array
    {
        // ============================================
        // ✅ 1. pricing_solution_xml — namespace সহ
        // ============================================
//        $pricingSolutionXml = $this->extractPricingSolutionXml();
        $selectedKey = (string)$solution['Key'];
        $pricingSolutionXml = $this->extractPricingSolutionXml($selectedKey);

        // ============================================
        // ✅ 2. Segments — response root থেকে সব AirSegment
        // ============================================
        $segments = $this->extractSegments();

        // ============================================
        // ✅ 3. HostTokens — original namespace সহ
        // ============================================
        $hostTokens = $this->extractHostTokens($solution);

        // ============================================
        // ✅ 4. PricingInfos — stripped xml থেকে parse
        // ============================================
        $pricingInfos = $this->extractPricingInfos($solution);

        return [
            'pricing_solution_key' => (string)$solution['Key'],
            'total_price' => (string)$solution['TotalPrice'],
            'total_price_numeric' => (int)preg_replace('/[^0-9]/', '', (string)$solution['TotalPrice']),
            'base_price' => (string)$solution['BasePrice'],
            'taxes' => (string)$solution['Taxes'],
            'quote_date' => (string)$solution['QuoteDate'],
            'verified_at' => now()->toDateTimeString(),
            'pricing_solution_xml' => $pricingSolutionXml,
            'segments' => $segments,
            'host_tokens' => $hostTokens,
            'pricing_infos' => $pricingInfos,
        ];
    }

    // ============================================
    // ✅ pricing_solution_xml extract
    // ============================================
//    private function extractPricingSolutionXml(): string
//    {
//        $nodes = $this->xmlOriginal->xpath('//*[local-name()="AirPricingSolution"]');
//        if (empty($nodes)) return '';
//
//        $dom = dom_import_simplexml($nodes[0]);
//
//        // ✅ standalone document বানাও — namespace ঠিক থাকবে
//        $newDoc = new \DOMDocument('1.0', 'UTF-8');
//        $newDoc->formatOutput = false;
//        $imported = $newDoc->importNode($dom, true);
//        $newDoc->appendChild($imported);
//
//        $xml = $newDoc->saveXML($imported);
//        return preg_replace('/<\?xml[^>]*>\n?/', '', $xml);
//    }

    private function extractPricingSolutionXml(string $selectedKey = ''): string
    {
        $nodes = $this->xmlOriginal->xpath('//*[local-name()="AirPricingSolution"]');
        if (empty($nodes)) return '';

        $targetNode = $nodes[0];
        if ($selectedKey) {
            foreach ($nodes as $node) {
                if ((string)$node['Key'] === $selectedKey) {
                    $targetNode = $node;
                    break;
                }
            }
        }

        $dom = dom_import_simplexml($targetNode);
        $newDoc = new \DOMDocument('1.0', 'UTF-8');
        $imported = $newDoc->importNode($dom, true);
        $newDoc->appendChild($imported);

        $xml = $newDoc->saveXML($imported);
        return preg_replace('/<\?xml[^>]*>\n?/', '', $xml);
    }
    // ============================================
    // ✅ Segments extract — full AirSegment xml সহ
    // ============================================
    private function extractSegments(): array
    {
        $segments = [];
//        $nodes = $this->xmlOriginal->xpath('//*[local-name()="AirSegment"]');
        $nodes = $this->xmlOriginal->xpath(
            '//*[local-name()="AirItinerary"]/*[local-name()="AirSegment"]'
        );
        foreach ($nodes as $seg) {
            $key = (string)$seg['Key'];
            if (!$key) continue;

            if (isset($segments[$key])) continue;

            $dom = dom_import_simplexml($seg);
            $newDoc = new \DOMDocument('1.0', 'UTF-8');
            $imported = $newDoc->importNode($dom, true);
            $newDoc->appendChild($imported);

            // ✅ air: namespace manually add করো segment xml এ
            $segXml = $newDoc->saveXML($imported);
            $segXml = preg_replace('/<\?xml[^>]*>\n?/', '', $segXml);

            // namespace prefix নিশ্চিত করো
            $segXml = $this->ensureAirNamespace($segXml);

            $segments[$key] = [
                'key' => $key,
                'xml' => $segXml,
                'carrier' => (string)$seg['Carrier'],
                'flight_number' => (string)$seg['FlightNumber'],
                'origin' => (string)$seg['Origin'],
                'destination' => (string)$seg['Destination'],
                'departure' => (string)$seg['DepartureTime'],
                'arrival' => (string)$seg['ArrivalTime'],
                'class' => (string)$seg['ClassOfService'],
                'group' => (string)$seg['Group'],
                'provider_code' => (string)$seg['ProviderCode'],
            ];
        }

        return $segments;
    }

    // ============================================
    // ✅ HostTokens extract — original namespace সহ
    // ============================================
//    private function extractHostTokens(): array
//    {
//        $hostTokens = [];
//        $nodes = $this->xmlOriginal->xpath('//*[local-name()="HostToken"]');
//
//        foreach ($nodes as $token) {
//            $key = (string)$token['Key'];
//            if ($key) {
//                $hostTokens[$key] = (string)$token;
//            }
//        }
//
//        return $hostTokens;
//    }
    private function extractHostTokens(\SimpleXMLElement $solution): array
    {
        $hostTokens = [];

        // ✅ Solution এর ভেতরের HostToken গুলো নাও, সব response এর না
        $nodes = $solution->xpath('.//*[local-name()="HostToken"]');
        // কিন্তু stripped xml এ namespace নেই, তাই original থেকে নিতে হবে

        // Solution Key দিয়ে original xml থেকে match করো
        $selectedKey = (string)$solution['Key'];
        $originalSolutions = $this->xmlOriginal->xpath(
            '//*[local-name()="AirPricingSolution"][@Key="' . $selectedKey . '"]'
        );

        if (!empty($originalSolutions)) {
            $tokens = $originalSolutions[0]->xpath('.//*[local-name()="HostToken"]');
            foreach ($tokens as $token) {
                $key = (string)$token['Key'];
                if ($key) {
                    $hostTokens[$key] = (string)$token;
                }
            }
        }

        return $hostTokens;
    }
    // ============================================
    // ✅ PricingInfos extract — stripped xml থেকে
    // ============================================
    private function extractPricingInfos(\SimpleXMLElement $solution): array
    {
        $pricingInfos = [];

        foreach ($solution->AirPricingInfo as $info) {

            // PassengerType
            $passengerTypes = [];
            foreach ($info->PassengerType as $pt) {
                $passengerTypes[] = [
                    'code' => (string)$pt['Code'],
                    'age' => (string)($pt['Age'] ?? ''),
                ];
            }

            // BookingInfo
            $bookingInfos = [];
            foreach ($info->BookingInfo as $bi) {
                $bookingInfos[] = [
                    'booking_code' => (string)$bi['BookingCode'],
                    'cabin_class' => (string)$bi['CabinClass'],
                    'fare_info_ref' => (string)$bi['FareInfoRef'],
                    'segment_ref' => (string)$bi['SegmentRef'],
                    'host_token_ref' => (string)$bi['HostTokenRef'],
                ];
            }

            // FareInfo
            $fareInfos = [];
            foreach ($info->FareInfo as $fi) {
                $fareInfos[] = [
                    'key' => (string)$fi['Key'],
                    'fare_basis' => (string)$fi['FareBasis'],
                    'pax_type' => (string)$fi['PassengerTypeCode'],
                    'origin' => (string)$fi['Origin'],
                    'destination' => (string)$fi['Destination'],
                    'amount' => (string)$fi['Amount'],
                ];
            }

            $pricingInfos[] = [
                'key' => (string)$info['Key'],
                'pax_type' => (string)($passengerTypes[0]['code'] ?? 'ADT'),
                'passenger_types' => $passengerTypes,
                'total_price' => (string)$info['TotalPrice'],
                'base_price' => (string)$info['BasePrice'],
                'taxes' => (string)$info['Taxes'],
                'provider_code' => (string)$info['ProviderCode'],
                'booking_infos' => $bookingInfos,
                'fare_infos' => $fareInfos,
                'change_penalty' => (string)($info->ChangePenalty->Amount ?? ''),
                'cancel_penalty' => (string)($info->CancelPenalty->Amount ?? ''),
                'latest_ticketing' => (string)$info['LatestTicketingTime'],
            ];
        }

        return $pricingInfos;
    }

    // ============================================
    // ✅ Segment xml এ air: namespace নিশ্চিত করো
    // ============================================
    private function ensureAirNamespace(string $xml): string
    {
        // prefix না থাকলে add করো
        $xml = preg_replace(
            '/^<AirSegment\s/',
            '<air:AirSegment xmlns:air="http://www.travelport.com/schema/air_v52_0" ',
            $xml
        );
        $xml = str_replace('</AirSegment>', '</air:AirSegment>', $xml);
        $xml = preg_replace('/<CodeshareInfo\s/', '<air:CodeshareInfo ', $xml);
        $xml = str_replace('</CodeshareInfo>', '</air:CodeshareInfo>', $xml);
        $xml = preg_replace('/<FlightDetails\s/', '<air:FlightDetails ', $xml);
        $xml = str_replace('</FlightDetails>', '</air:FlightDetails>', $xml);

        return $xml;
    }
}
