<?php

namespace Modules\Flight\Service\TravelPort;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Service\TravelPortBookingResponseService;
use Modules\Booking\Service\TravelPortPNRResponseService;
use Modules\Booking\Service\TravelPortPNRRetrieveService;

class TravelPortApiService
{
    private $username;
    private $password;
    private $targetBranch;
    private $endpoint;

    public function __construct()
    {
        $this->username = env('TRAVELPORT_USERNAME');
        $this->password = env('TRAVELPORT_PASSWORD');
        $this->targetBranch = env('TRAVELPORT_TARGET_BRANCH');
        $this->endpoint = env('TRAVELPORT_ENDPOINT');
    }
    /**
     * Method 1: XML Request তৈরি করুন
     *
     * @param array $searchData - আপনার format এর data
     * @return string - SOAP XML
     */


    /**
     * Method 2: Flight Search করুন এবং Response return করুন
     *
     * @param array $searchData - আপনার format এর data
     * @return array
     */
    public function searchFlights($searchData)
    {

        try {
            // Log করুন (debugging এর জন্য)
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($searchData);

//            \Log::info("Travelport Request XML:\n" . $dom->saveXML());
            Log::info('Low Fare XML' . "\n" . $searchData);

            // API তে request পাঠান (HTTP Basic Auth দিয়ে)
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'Content-Type' => 'application/soap+xml; charset=utf-8',
                    'SOAPAction' => ''
                ])
                ->withBody($searchData, 'text/xml')
                ->timeout(60) // 60 seconds timeout
                ->post($this->endpoint);

            // Log response
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($response->body());

            \Log::info("Low Fare XML:\n" . $dom->saveXML());
//            Log::info('Travelport Response', [
//                'status' => $response->status(),
//                'body' => $response->body()
//            ]);

            return $response->body();

        } catch (\Exception $e) {
            Log::error('Travelport Search Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatXmlForLog(string $xml): string
    {
        try {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            return $dom->saveXML();
        } catch (\Exception $e) {
            return $xml; // parse না হলে raw XML return করো
        }
    }
    public function pnrCreate($xmlBody, $bookingId)
    {
        try {
            Log::info('Travelport PNR Create Request' . "\n" . $this->formatXmlForLog($xmlBody));

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'Content-Type' => 'text/xml;charset=UTF-8',
                    'SOAPAction'   => ''
                ])
                ->withBody($xmlBody, 'text/xml')
                ->timeout(60)
                ->post($this->endpoint);

            Log::info('Travelport PNR Create Response' . "\n" . 'Status: ' . $response->status() . "\n" . $this->formatXmlForLog($response->body()));

            $xml = new \SimpleXMLElement($response->body());
            $xml->registerXPathNamespace('SOAP', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('air',  'http://www.travelport.com/schema/air_v52_0');
            $xml->registerXPathNamespace('com',  'http://www.travelport.com/schema/common_v52_0');
            $xml->registerXPathNamespace('univ', 'http://www.travelport.com/schema/universal_v52_0');

            // ✅ Step 1: SOAP Fault check
            $fault = $xml->xpath('//SOAP:Fault');
            if (!empty($fault)) {
                $faultString = (string)($fault[0]->faultstring ?? '');

                // air:ErrorMessage থেকে specific message বের করো
                $airErrorMsg = $xml->xpath('//air:AirSegmentError/air:ErrorMessage');
                $specificError = !empty($airErrorMsg) ? trim((string)$airErrorMsg[0]) : '';

                $userMessage = $this->getTravelportUserMessage($faultString, $specificError);

                Log::error('Travelport PNR SOAP Fault', [
                    'fault_string'  => $faultString,
                    'air_error'     => $specificError,
                    'booking_id'    => $bookingId,
                ]);

                return [
                    'success' => false,
                    'error'   => $userMessage,
                ];
            }

            // ✅ Step 2: ProviderLocatorCode check
            $providerReservationInfo = $xml->xpath('//univ:ProviderReservationInfo')[0] ?? null;
            $providerLocatorCode = $providerReservationInfo
                ? (string)($providerReservationInfo->attributes()['LocatorCode'] ?? null)
                : null;

            if (empty($providerLocatorCode)) {
                Log::error('PNR Create: ProviderLocatorCode not found', ['body' => $response->body()]);
                return ['success' => false, 'error' => 'PNR তৈরি হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।'];
            }

            $pnrService  = new TravelPortPNRRetrieveService();
            $pnrResponse = $pnrService->retrievePNR($providerLocatorCode);
            return $pnrResponse;

        } catch (\Exception $e) {
            Log::error('Travelport pnrCreate exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'সার্ভার সংযোগে সমস্যা হয়েছে।'];
        }
    }

    private function getTravelportUserMessage(string $faultString, string $airError): string
    {
        $combined = strtoupper($faultString . ' ' . $airError);

        if (str_contains($combined, 'AVAIL') || str_contains($combined, 'WL CLOSED') || str_contains($combined, 'NOT BOOKABLE')) {
            return 'এই ফ্লাইটে সিট পাওয়া যাচ্ছে না। অনুগ্রহ করে নতুনভাবে সার্চ করুন।';
        }

        if (str_contains($combined, 'SOLD OUT')) {
            return 'ফ্লাইটের সব সিট বিক্রি হয়ে গেছে।';
        }

        if (str_contains($combined, 'INVALID') || str_contains($combined, 'NOT VALID')) {
            return 'ফ্লাইট তথ্য সঠিক নয়। নতুনভাবে সার্চ করুন।';
        }

        if (str_contains($combined, 'TIMEOUT') || str_contains($combined, 'UNAVAILABLE')) {
            return 'সার্ভার সাড়া দিচ্ছে না। কিছুক্ষণ পর আবার চেষ্টা করুন।';
        }

        // fallback: faultstring টা দেখান log এ আছে, user কে generic দিন
        return 'ফ্লাইট বুকিং সম্পন্ন হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন।';
    }
    public function priceFlight($xmlBody)
    {
        try {
            Log::info('Travelport Price Request' . "\n" . $this->formatXmlForLog($xmlBody));

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'Content-Type' => 'text/xml;charset=UTF-8',
                    'SOAPAction' => ''
                ])
                ->withBody($xmlBody, 'text/xml')
                ->timeout(60)
                ->post($this->endpoint);

            Log::info('Travelport Price Response' . "\n" . 'Status: ' . $response->status() . "\n" . $this->formatXmlForLog($response->body()));
//            $responseClass = new TravelPortBookingResponseService();
//            $responseData = $responseClass->parseAirPriceResponse($response->body());
            return $response->body();

        } catch (\Exception $e) {
            Log::error('Travelport PNR Create Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function retrivePNR($xmlBody)
    {
        $endpoint = env('TRAVELPORT_UR_ENDPOINT');
        try {
            Log::info('Travelport Retrieve PNR Request', ['xml' => $xmlBody]);

            $response = Http::withBasicAuth($this->username, $this->password) // ✅ এটা যোগ করুন
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=UTF-8',
                'SOAPAction'   => ''
            ])
                ->withBody($xmlBody, 'text/xml')
                ->timeout(60)
                ->post($endpoint);

            Log::info('Travelport Retrieve PNR Response', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            return $response->body();

        } catch (\Exception $e) {
            Log::error('Travelport PNR Retrieve Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }



}
