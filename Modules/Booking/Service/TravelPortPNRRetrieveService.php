<?php

namespace Modules\Booking\Service;

use Illuminate\Support\Facades\Log;
use Modules\Flight\Service\TravelPort\TravelPortApiService;

class TravelPortPNRRetrieveService
{
    private $targetBranch;
    private $providerCode;
    private $apiClient;
    private $parser;
    private $urEndpoint;

    public function __construct()
    {
        $this->targetBranch = env('TRAVELPORT_TARGET_BRANCH');
        $this->providerCode = config('travelport.provider_code', '1G');
        $this->parser = new TravelPortPNRResponseParser();
        $this->urEndpoint = env('TRAVELPORT_UR_ENDPOINT'); // UniversalRecordService endpoint
    }

    /**
     * Retrieve PNR by Provider Locator Code
     */
    public function retrievePNR(
        string $pnrCode,
        ?string $travelerLastName = null,
        bool $viewOnly = false,
        bool $retrieveDetails = true
    ): string|array|false    {
        try {
            $xmlRequest = $this->buildRetrieveRequest($pnrCode, $travelerLastName, $viewOnly, $retrieveDetails);
//            $xmlRequest = $this->buildURRetrieveRequest($pnrCode, $travelerLastName);
//            Log::info('PNR Retrieve Request', [
//                'pnr' => $pnrCode,
//                'xml' => $xmlRequest
//            ]);
//dd($xmlRequest);
            $apiService = new TravelPortApiService();
            $response = $apiService->retrivePNR($xmlRequest); // endpoint injected
//            dd($response);
//return $response;
            // Fix: ensure it's a string
            if (is_array($response)) {
                $response = $response['response'] ?? $response[0] ?? json_encode($response);
            }
            if ($response === false || $response === null) {
                return ['success' => false, 'error' => 'API returned no response'];
            }

            $parsedData = $this->parser->parseRetrieveResponse((string)$response);
return $parsedData;
//            $parsedData = $this->parser->parseRetrieveResponse($response);
dd($parsedData);
            if ($parsedData['success'] ?? false) {
                Log::info('PNR Retrieved Successfully', [
                    'pnr' => $pnrCode,
                    'ur_locator' => $parsedData['ur_locator'] ?? null
                ]);
            }

            return $parsedData;

        } catch (\Exception $e) {
            Log::error('PNR Retrieve Error', [
                'pnr' => $pnrCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build PNR Retrieve XML Request
     */
    private function buildRetrieveRequest(
        string $pnrCode,
        ?string $travelerLastName,
        bool $viewOnly,
        bool $retrieveDetails
    ): string {
        $traceId = 'pnr_retrieve_' . uniqid();

        $optionalAttrs = '';
        if ($travelerLastName) {
            $optionalAttrs .= ' TravelerLastName="' . htmlspecialchars($travelerLastName, ENT_XML1) . '"';
        }

        $viewOnlyAttr = $viewOnly ? ' ViewOnlyInd="true"' : '';
        $retrieveDetailsAttr = $retrieveDetails ? ' RetrieveProviderReservationDetails="true"' : '';

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:univ="http://www.travelport.com/schema/universal_v52_0"
    xmlns:com="http://www.travelport.com/schema/common_v52_0">

    <soapenv:Header/>

    <soapenv:Body>
        <univ:UniversalRecordRetrieveReq
            TraceId="{$traceId}"
            TargetBranch="{$this->targetBranch}"{$optionalAttrs}{$viewOnlyAttr}{$retrieveDetailsAttr}>
            <com:BillingPointOfSaleInfo OriginApplication="uAPI"/>
            <univ:ProviderReservationInfo
                ProviderCode="{$this->providerCode}"
                ProviderLocatorCode="{$pnrCode}"/>
        </univ:UniversalRecordRetrieveReq>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        return $xml;
    }

    /**
     * Retrieve Universal Record by UR Locator Code
     */
    public function retrieveByURLocator(string $urLocator, ?string $travelerLastName = null): array
    {
        try {
            $xmlRequest = $this->buildURRetrieveRequest($urLocator, $travelerLastName);

            Log::info('UR Retrieve Request', ['ur_locator' => $urLocator, 'xml' => $xmlRequest]);

            $apiService = new TravelPortApiService();
            $response = $apiService->retrivePNR($xmlRequest, $this->urEndpoint);

            $parsedData = $this->parser->parseRetrieveResponse($response);

            return $parsedData;

        } catch (\Exception $e) {
            Log::error('UR Retrieve Error', [
                'ur_locator' => $urLocator,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build UR Retrieve XML Request
     */
    private function buildURRetrieveRequest(string $urLocator, ?string $travelerLastName): string
    {
        $traceId = 'ur_retrieve_' . uniqid();

        $optionalAttrs = '';
        if ($travelerLastName) {
            $optionalAttrs .= ' TravelerLastName="' . htmlspecialchars($travelerLastName, ENT_XML1) . '"';
        }

        $username = htmlspecialchars(env('TRAVELPORT_USERNAME'), ENT_XML1);
        $password = htmlspecialchars(env('TRAVELPORT_PASSWORD'), ENT_XML1);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:univ="http://www.travelport.com/schema/universal_v52_0"
    xmlns:com="http://www.travelport.com/schema/common_v52_0"
    xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/07/secext">

    <soapenv:Header>
        <wsse:Security>
            <wsse:UsernameToken>
                <wsse:Username>{$username}</wsse:Username>
                <wsse:Password>{$password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>

    <soapenv:Body>
        <univ:UniversalRecordRetrieveReq
            TraceId="{$traceId}"
            TargetBranch="{$this->targetBranch}"
            UniversalRecordLocatorCode="{$urLocator}"{$optionalAttrs}>
            <com:BillingPointOfSaleInfo OriginApplication="uAPI"/>
        </univ:UniversalRecordRetrieveReq>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        return $xml;
    }
}
