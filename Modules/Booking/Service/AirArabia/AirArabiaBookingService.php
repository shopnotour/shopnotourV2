<?php

namespace Modules\Booking\Service\AirArabia;

use Modules\Booking\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;
use Modules\Flight\Service\AirArabia\AirArabiaService;
use Modules\Flight\Service\AirArabia\AirArabiaXmlBuildService;

class AirArabiaBookingService
{
    private AirArabiaService        $service;
    private AirArabiaXmlBuildService $xmlBuilder;

    public function __construct()
    {
        $this->service    = new AirArabiaService();
        $this->xmlBuilder = new AirArabiaXmlBuildService();
    }

    // ==========================================
    // MAIN: createPnr()
    // Called from doCheckout Controller
    // ==========================================

    public function createPnr(Booking $booking): array
    {
        try {
            // ==========================================
            // 1. Session থেকে data নাও
            // ==========================================
            $jsessionId    = session('air_arabia_jsession_id');
            $transactionId = session('air_arabia_transaction_id');
            $selectedFlight = session('selected_flight');

            if (!$jsessionId || !$transactionId) {
                throw new Exception('Air Arabia session expired. Please select flight again.');
            }

            // ==========================================
            // 2. Service এ session set করো
            // ==========================================
            $this->service->setSession($jsessionId, $transactionId);


            // ✅ Booking save হওয়ার পরপরই fresh getPrice করো
            $xmlBuilder    = new AirArabiaXmlBuildService();
            $priceXml      = $xmlBuilder->buildGetPriceXmlFromFlight($selectedFlight);
            $priceResponse = $this->service->getPrice($priceXml);

// নতুন RPH নাও
            $newRphs = $this->extractRphsFromPriceResponse($priceResponse);

            Log::channel('daily')->info('Fresh RPHs: ' . json_encode($newRphs));
            Log::channel('daily')->info('Fresh Session: ' . json_encode($this->service->getSession()));

// এখনই book করো
            $segments = $this->buildSegmentsFromBooking($booking, $selectedFlight, $newRphs);
            // ==========================================
            // 3. Booking থেকে segments বানাও
            // ==========================================
//            $segments = $this->buildSegmentsFromBooking($booking, $selectedFlight);

            // ==========================================
            // 4. Passengers বানাও
            // ==========================================
            $passengers = $this->buildPassengersFromBooking($booking);

            // ==========================================
            // 5. Contact বানাও
            // ==========================================
            $contact = [
                'title'       => $booking->passengers->first()->title ?? 'Mr',
                'firstName'   => $booking->first_name ?: $booking->passengers->first()->first_name,
                'lastName'    => $booking->last_name  ?: $booking->passengers->first()->last_name,
                'phone'       => $booking->phone,
                'countryCode' => $this->getPhoneCountryCode($booking->country ?? 'BD'),
                'email'       => $booking->email,
                'country'     => $booking->country ?? 'Bangladesh',
                'countryIso'  => $booking->country ?? 'BD',
                'city'        => $booking->passengers->first()->city ?? 'Dhaka',
            ];

            // 6. Book XML বানাও
            $bookXml = $this->xmlBuilder->buildBookXmlFromBooking(
                $segments,
                $passengers,
                $contact,
                $transactionId,
                (float)$booking->total,
                $booking->currency ?? 'BDT'
            );

            Log::channel('daily')->info('AirArabia Book Request: ' . $bookXml);
//dd($bookXml);
// 7. Book call — শুধু XML পাঠাও
            $bookResponse = $this->service->book($bookXml);

            Log::channel('daily')->info('AirArabia Book Response: ' . $bookResponse);
dd($bookResponse);
            // ==========================================
            // 8. Response parse করো
            // ==========================================
            $pnr = $this->extractPnr($bookResponse);

            if (!$pnr) {
                return [
                    'success' => false,
                    'message' => 'PNR পাওয়া যায়নি। Response: ' . substr($bookResponse, 0, 500),
                ];
            }

            // ==========================================
            // 9. Booking এ PNR save করো
            // ==========================================
            $booking->update([
                'pnr'         => $pnr,
                'gds_pnr'     => $pnr,
                'status'      => 'booked',
                'booking_ref' => $pnr,
            ]);

            // Session clear
            session()->forget(['air_arabia_jsession_id', 'air_arabia_transaction_id']);

            Log::channel('daily')->info('AirArabia PNR Created: ' . $pnr . ' for booking #' . $booking->id);

            return [
                'success' => true,
                'pnr'     => $pnr,
                'message' => 'Booking confirmed! PNR: ' . $pnr,
            ];

        } catch (Exception $e) {
            Log::channel('daily')->error('AirArabia Book Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function extractRphsFromPriceResponse(string $xml): array
    {
        $rphs = [];

        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('ns1', 'http://www.opentravel.org/OTA/2003/05');

            foreach ($xpath->query('//ns1:FlightSegment/@RPH') as $rph) {
                $rphs[] = $rph->nodeValue;
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('AirArabia extractRphs error: ' . $e->getMessage());
        }

        return $rphs;
    }
    // ==========================================
    // BUILD SEGMENTS FROM BOOKING
    // ==========================================

    private function buildSegmentsFromBooking(
        Booking $booking,
        ?array  $selectedFlight,
        array   $newRphs = []
    ): array {
        $segments = [];

        // নতুন RPH থাকলে সেটা ব্যবহার করো, না থাকলে session এর RPH
        $rphs = !empty($newRphs) ? $newRphs : ($selectedFlight['rphs'] ?? []);

        Log::channel('daily')->info('AirArabia Building segments with RPHs: ' . json_encode($rphs));

        foreach ($booking->routes as $index => $route) {
            $meta       = json_decode($route->meta, true) ?? [];
            $legType    = $meta['leg_type'] ?? 'outbound';
            $returnFlag = $legType === 'return' ? 'true' : 'false';

            // datetime format: "2026-04-16 10:10:00" → "2026-04-16T10:10:00"
            $depDatetime = str_replace(' ', 'T', $route->departure_at);
            $arrDatetime = str_replace(' ', 'T', $route->arrival_at);

            $segments[] = [
                'flightNumber'      => str_replace('-', '', $route->flight_number), // G9-113 → G9113
                'departureDateTime' => $depDatetime,
                'arrivalDateTime'   => $arrDatetime,
                'origin'            => $route->departure_iata_code,
                'originTerminal'    => $route->departure_terminal ?? '',
                'destination'       => $route->arrival_iata_code,
                'destTerminal'      => $route->arrival_terminal ?? '',
                'airlineCode'       => $route->carrier_code,
                'rph'               => $rphs[$index] ?? '',
                'returnFlag'        => $returnFlag,
            ];
        }

        Log::channel('daily')->info('AirArabia Segments built: ' . json_encode($segments));

        return $segments;
    }

    // ==========================================
    // BUILD PASSENGERS FROM BOOKING
    // ==========================================

    private function buildPassengersFromBooking(Booking $booking): array
    {
        $passengers = [];
        $rphMap     = ['ADT' => 'A', 'CHD' => 'C', 'INF' => 'I'];
        $typeCounts = ['ADT' => 0, 'CHD' => 0, 'INF' => 0];

        foreach ($booking->passengers as $pax) {
            // passenger_type_code: ADT, CHD, INF
            $paxCode = strtoupper($pax->passenger_type_code ?? 'ADT');

            // CNN → CHD normalize
            if (str_starts_with($paxCode, 'C') && $paxCode !== 'CHD') {
                $paxCode = 'CHD';
            }

            $typeCounts[$paxCode] = ($typeCounts[$paxCode] ?? 0) + 1;
            $rphNum  = $typeCounts[$paxCode];
            $rphPrefix = $rphMap[$paxCode] ?? 'A';

            // INF এর RPH format: I1/A1
            $rph = $paxCode === 'INF'
                ? $rphPrefix . $rphNum . '/A1'
                : $rphPrefix . $rphNum;

            $passengers[] = [
                'paxType'      => $paxCode,
                'title'        => $pax->title,
                'firstName'    => $pax->first_name,
                'lastName'     => $pax->last_name,
                'birthDate'    => $pax->dob ?? '1990-01-01',
                'nationality'  => $pax->country ?? 'BD',
                'phone'        => $pax->phone ?? $booking->phone,
                'countryCode'  => $this->getPhoneCountryCode($pax->country ?? 'BD'),
                'rph'          => $rph,
            ];
        }

        return $passengers;
    }

    // ==========================================
    // EXTRACT PNR FROM RESPONSE
    // ==========================================

    private function extractPnr(string $xml): ?string
    {
        // OTA_AirBookRS থেকে UniqueID extract করো
        if (preg_match('/UniqueID[^>]+ID="([^"]+)"/i', $xml, $matches)) {
            return $matches[1];
        }

        // Fallback: BookingReferenceID
        if (preg_match('/BookingReferenceID[^>]+ID="([^"]+)"/i', $xml, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // ==========================================
    // PHONE COUNTRY CODE
    // ==========================================

    private function getPhoneCountryCode(string $countryIso): string
    {
        return match(strtoupper($countryIso)) {
            'BD' => '880',
            'IN' => '91',
            'AE' => '971',
            'SA' => '966',
            'GB' => '44',
            'US' => '1',
            'PK' => '92',
            default => '880',
        };
    }
}
