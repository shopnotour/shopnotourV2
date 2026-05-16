<?php

namespace Modules\Booking\Service\TravelPort;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TravelPortCancelService
{
    private string $username;
    private string $password;
    private string $targetBranch;
    private string $urEndpoint;
    private string $airVersion;
    private string $comVersion;
    private string $univVersion;

    public function __construct()
    {
        $this->username     = env('TRAVELPORT_USERNAME');
        $this->password     = env('TRAVELPORT_PASSWORD');
        $this->targetBranch = env('TRAVELPORT_TARGET_BRANCH');
        $this->urEndpoint   = env('TRAVELPORT_UR_ENDPOINT');

        $schema             = env('TRAVELPORT_SCHEMA_VERSION', 'v52_0');
        $this->airVersion   = "http://www.travelport.com/schema/air_{$schema}";
        $this->comVersion   = "http://www.travelport.com/schema/common_{$schema}";
        $this->univVersion  = "http://www.travelport.com/schema/universal_{$schema}";
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  1. পুরো Booking Cancel (UniversalRecordCancelReq)
    //     — সব segment একসাথে cancel হবে
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Cancel entire booking (all segments)
     *
     * @param string $providerLocatorCode  GDS PNR (যেমন: GBLKLC)
     * @param string $providerCode         Default: 1G
     * @return array
     */
    public function cancelEntireBooking(
        string $providerLocatorCode,
        string $providerCode = '1G'
    ): array {
        try {
            $xml = $this->buildCancelEntireBookingXml($providerLocatorCode, $providerCode);

            Log::info('Travelport Cancel Entire Booking Request', [
                'pnr' => $providerLocatorCode,
                'xml' => $xml,
            ]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'Content-Type' => 'text/xml;charset=UTF-8',
                    'SOAPAction'   => '',
                ])
                ->withBody($xml, 'text/xml')
                ->timeout(60)
                ->post($this->urEndpoint);

            Log::info('Travelport Cancel Entire Booking Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseCancelResponse($response->body());

        } catch (\Exception $e) {
            Log::error('Travelport Cancel Entire Booking Error', [
                'pnr'   => $providerLocatorCode,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  2. Specific Segment Cancel (UniversalRecordModifyReq + AirDelete)
    //     — একটা বা একাধিক segment cancel করবে, বাকিগুলো থাকবে
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Cancel specific air segments
     *
     * @param string $providerLocatorCode   GDS PNR (যেমন: GBLKLC)
     * @param array  $segments              Segment keys (retrieve response থেকে)
     *   [
     *     [
     *       'key'                  => 'jCAOz1VqWDKAcWMsDAAAAA==',  // AirSegment Key
     *       'booking_traveler_ref' => 'Qm9va2luZ1RyYXZlbGVyMQ==', // BookingTravelerRef
     *     ],
     *     ...
     *   ]
     * @param string $providerCode          Default: 1G
     * @return array
     */
    public function cancelSegments(
        string $providerLocatorCode,
        array  $segments,
        string $providerCode = '1G'
    ): array {
        try {
            $xml = $this->buildCancelSegmentsXml($providerLocatorCode, $segments, $providerCode);

            Log::info('Travelport Cancel Segments Request', [
                'pnr'      => $providerLocatorCode,
                'segments' => $segments,
                'xml'      => $xml,
            ]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders([
                    'Content-Type' => 'text/xml;charset=UTF-8',
                    'SOAPAction'   => '',
                ])
                ->withBody($xml, 'text/xml')
                ->timeout(60)
                ->post($this->urEndpoint);

            Log::info('Travelport Cancel Segments Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->parseModifyResponse($response->body());

        } catch (\Exception $e) {
            Log::error('Travelport Cancel Segments Error', [
                'pnr'   => $providerLocatorCode,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  XML BUILDERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Build XML for entire booking cancel
     */
    private function buildCancelEntireBookingXml(
        string $providerLocatorCode,
        string $providerCode
    ): string {
        $traceId      = 'cancel_' . uniqid();
        $univVersion  = $this->univVersion;
        $comVersion   = $this->comVersion;
        $targetBranch = $this->targetBranch;

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:univ="{$univVersion}"
    xmlns:com="{$comVersion}">

    <soapenv:Header/>

    <soapenv:Body>
        <univ:UniversalRecordCancelReq
            TraceId="{$traceId}"
            TargetBranch="{$targetBranch}"
            AuthorizedBy="user"
            UniversalRecordLocatorCode="{$providerLocatorCode}"
            Version="0">

            <com:BillingPointOfSaleInfo OriginApplication="uAPI"/>

        </univ:UniversalRecordCancelReq>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Build XML for specific segment cancel
     */
    private function buildCancelSegmentsXml(
        string $providerLocatorCode,
        array  $segments,
        string $providerCode
    ): string {
        $traceId      = 'cancel_seg_' . uniqid();
        $univVersion  = $this->univVersion;
        $comVersion   = $this->comVersion;
        $targetBranch = $this->targetBranch;

        // Build AirDelete commands for each segment
        $deleteCommands = '';
        foreach ($segments as $segment) {
            $segmentKey         = $segment['key'];
            $bookingTravelerRef = $segment['booking_traveler_ref'];

            $deleteCommands .= <<<XML

            <univ:UniversalModifyCmd>
                <univ:AirDelete
                    Key="{$segmentKey}"
                    Element="Segment"
                    ReservationLocatorCode="{$providerLocatorCode}">
                    <com:BookingTravelerRef Key="{$bookingTravelerRef}"/>
                </univ:AirDelete>
            </univ:UniversalModifyCmd>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:univ="{$univVersion}"
    xmlns:com="{$comVersion}">

    <soapenv:Header/>

    <soapenv:Body>
        <univ:UniversalRecordModifyReq
            TraceId="{$traceId}"
            TargetBranch="{$targetBranch}"
            AuthorizedBy="user"
            Version="0">

            <com:BillingPointOfSaleInfo OriginApplication="uAPI"/>
            {$deleteCommands}
        </univ:UniversalRecordModifyReq>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  RESPONSE PARSERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Parse UniversalRecordCancelRsp response
     */
    private function parseCancelResponse(string $xmlString): array
    {
        try {
            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement($xmlString);
            libxml_clear_errors();

            $xml->registerXPathNamespace('SOAP',  'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('univ',  $this->univVersion);
            $xml->registerXPathNamespace('com',   $this->comVersion);

            // SOAP Fault check
            $fault = $xml->xpath('//SOAP:Fault');
            if (!empty($fault)) {
                $f = $fault[0];
                return [
                    'success'      => false,
                    'fault_code'   => (string) ($f->faultcode ?? ''),
                    'fault_string' => trim((string) ($f->faultstring ?? '')),
                ];
            }

            // Parse ProviderReservationStatus
            $statusNodes = $xml->xpath('//univ:ProviderReservationStatus');
            $statuses    = [];

            foreach ($statusNodes as $node) {
                $attr       = $node->attributes();
                $cancelInfo = '';
                $ciNodes    = $node->xpath('.//univ:CancelInfo');
                if (!empty($ciNodes)) {
                    $cancelInfo = trim((string) $ciNodes[0]);
                }

                $statuses[] = [
                    'provider_code'  => (string) ($attr['ProviderCode'] ?? ''),
                    'locator_code'   => (string) ($attr['LocatorCode'] ?? ''),
                    'cancelled'      => filter_var((string) ($attr['Cancelled'] ?? 'false'), FILTER_VALIDATE_BOOLEAN),
                    'cancel_info'    => $cancelInfo,
                    'create_date'    => (string) ($attr['CreateDate'] ?? ''),
                    'modified_date'  => (string) ($attr['ModifiedDate'] ?? ''),
                ];
            }

            $allCancelled = !empty($statuses) && collect($statuses)->every(fn($s) => $s['cancelled']);

            return [
                'success'   => $allCancelled,
                'cancelled' => $allCancelled,
                'statuses'  => $statuses,
                'message'   => $allCancelled ? 'Booking cancelled successfully' : 'Cancellation may have failed',
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse UniversalRecordModifyRsp response (segment cancel)
     */
    private function parseModifyResponse(string $xmlString): array
    {
        try {
            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement($xmlString);
            libxml_clear_errors();

            $xml->registerXPathNamespace('SOAP',  'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('univ',  $this->univVersion);
            $xml->registerXPathNamespace('com',   $this->comVersion);
            $xml->registerXPathNamespace('air',   $this->airVersion);

            // SOAP Fault check
            $fault = $xml->xpath('//SOAP:Fault');
            if (!empty($fault)) {
                $f = $fault[0];
                return [
                    'success'      => false,
                    'fault_code'   => (string) ($f->faultcode ?? ''),
                    'fault_string' => trim((string) ($f->faultstring ?? '')),
                ];
            }

            // Check UniversalRecordModifyRsp returned
            $rspNodes = $xml->xpath('//univ:UniversalRecordModifyRsp');
            if (empty($rspNodes)) {
                return ['success' => false, 'error' => 'No modify response found'];
            }

            // Check remaining air segments
            $remainingSegments = [];
            foreach ($xml->xpath('//air:AirSegment') as $seg) {
                $attr                = $seg->attributes();
                $remainingSegments[] = [
                    'key'            => (string) ($attr['Key'] ?? ''),
                    'flight_number'  => (string) ($attr['FlightNumber'] ?? ''),
                    'carrier'        => (string) ($attr['Carrier'] ?? ''),
                    'origin'         => (string) ($attr['Origin'] ?? ''),
                    'destination'    => (string) ($attr['Destination'] ?? ''),
                    'status'         => (string) ($attr['Status'] ?? ''),
                    'departure_time' => (string) ($attr['DepartureTime'] ?? ''),
                ];
            }

            return [
                'success'            => true,
                'cancelled'          => true,
                'message'            => 'Segment(s) cancelled successfully',
                'remaining_segments' => $remainingSegments,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
