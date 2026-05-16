<?php

namespace Modules\Flight\Service\AirArabia;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Flight\Service\FlightDiscountService;
use Modules\Flight\Service\FlightChargesService;
use Exception;

class AirArabiaFlightService
{
    private AirArabiaXmlBuildService $xmlBuilder;
    private AirArabiaResponseParser  $parser;
    private FlightDiscountService    $discountService;
    private FlightChargesService     $chargesService;



    public function __construct(
        FlightDiscountService $discountService,
        FlightChargesService  $chargesService
    ) {
        $this->discountService = $discountService;
        $this->chargesService  = $chargesService;
        $this->xmlBuilder      = new AirArabiaXmlBuildService();
        $this->parser          = new AirArabiaResponseParser($discountService, $chargesService);

    }

    // ==========================================
    // MAIN: search()
    // ==========================================

    public function search(array $searchData): array
    {
        try {
            $token   = $this->authenticate();
            $payload = $this->xmlBuilder->buildSearchPayload($searchData);

            // ✅ Request Log
            Log::channel('daily')->info('AirArabia Search Request: ' . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


            $response = $this->httpClient()
                ->withHeaders([
                    'Authorization'        => 'Bearer ' . $token,
                    'Content-Type'         => 'application/json',
                    'X-AERO-SALES-CHANNEL' => 'OTA',
                    'X-AERO-JOURNEY-TYPE'  => $this->xmlBuilder->getJourneyType($searchData['trip_type']),
                    'X-AERO-USERID'        => $this->username,
                    'X-AERO-AGENT-CODE'    => $this->agentCode,
                ])
                ->timeout(60)
                ->post($this->searchUrl, $payload);

            // ✅ Response Log
            Log::channel('daily')->info('AirArabia Search Response: ' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($response->failed()) {
                Log::channel('daily')->error('AirArabia Search Failed', [
                    'timestamp'  => now()->toIso8601String(),
                    'http_status'=> $response->status(),
                    'body'       => $response->body(),
                ]);
                return ['success' => false, 'flights' => [], 'error' => 'HTTP: ' . $response->status()];
            }

            $data    = $response->json();
            $flights = $this->parser->parse($data, $searchData);

            Log::channel('daily')->info('AirArabia Search Parsed', [
                'timestamp'     => now()->toIso8601String(),
                'total_flights' => count($flights),
            ]);

            return [
                'success'       => true,
                'flights'       => $flights,
                'total_results' => count($flights),
                'currency'      => 'BDT',
                'error'         => null,
            ];

        } catch (Exception $e) {
            Log::channel('daily')->error('AirArabia Search Exception', [
                'timestamp' => now()->toIso8601String(),
                'message'   => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'flights' => [], 'error' => $e->getMessage()];
        }
    }

}
