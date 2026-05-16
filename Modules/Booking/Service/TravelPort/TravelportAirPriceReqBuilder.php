<?php

namespace Modules\Booking\Service\TravelPort;

/**
 * Travelport uAPI AirPriceReq Builder
 * Builds AirPriceReq XML from parsed flight data (selected_flight session)
 *
 * Usage:
 *   $selectedFlight = session('selected_flight');
 *   $builder = new TravelportAirPriceReqBuilder($selectedFlight);
 *   $xml = $builder->build();
 */
class TravelportAirPriceReqBuilder
{
    protected array $data;
    protected string $targetBranch;
    protected string $traceId;
    protected string $airVersion;
    protected string $comVersion;

    public function __construct(array $selectedFlight, string $targetBranch = '', string $traceId = '')
    {
        $this->data         = $selectedFlight;
        $this->targetBranch = $targetBranch ?: env('TRAVELPORT_TARGET_BRANCH', 'P3088249');
        $this->traceId      = $traceId ?: session('travelport_trace_id') ?: 'price-chk-' . uniqid();

        $schemaVersion    = env('TRAVELPORT_SCHEMA_VERSION', 'v52_0');
        $this->airVersion = "http://www.travelport.com/schema/air_{$schemaVersion}";
        $this->comVersion = "http://www.travelport.com/schema/common_{$schemaVersion}";
    }

    // -------------------------------------------------------------------------
    // Public entry point
    // -------------------------------------------------------------------------

    public function build(): string
    {
        $segments      = $this->buildSelectedSegments();
        $segmentXml    = $this->renderSegments($segments);
        $passengerXml  = $this->renderPassengers();
        $pricingCmdXml = $this->renderPricingCommand($segments);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP:Header/>
  <SOAP:Body>
    <air:AirPriceReq
      xmlns:air="{$this->airVersion}"
      xmlns:com="{$this->comVersion}"
      AuthorizedBy="user"
      TargetBranch="{$this->targetBranch}"
      TraceId="{$this->traceId}"
      FareRuleType="none">

      <com:BillingPointOfSaleInfo OriginApplication="uAPI"/>

      <air:AirItinerary>
{$segmentXml}
      </air:AirItinerary>

{$passengerXml}

{$pricingCmdXml}

      <com:FormOfPayment Type="Credit"/>

    </air:AirPriceReq>
  </SOAP:Body>
</SOAP:Envelope>
XML;
    }

    // -------------------------------------------------------------------------
    // Step 1 — Find selected segments from legs
    // -------------------------------------------------------------------------

    protected function buildSelectedSegments(): array
    {
        $selectedSegments = [];
        $seenKeys = [];

        foreach ($this->data['legs'] as $leg) {
            foreach ($leg['segments'] as $seg) {
                if (!isset($seenKeys[$seg['key']])) {
                    $seenKeys[$seg['key']] = true;
                    $selectedSegments[] = $seg;
                }
            }
        }

        return $selectedSegments;
    }

    // -------------------------------------------------------------------------
    // Step 2 — Render AirSegment XML
    // FIX: Connection="true" added for non-last segments within the same Group
    // -------------------------------------------------------------------------

    protected function renderSegments(array $segments): string
    {
        $lines = [];

        // প্রতিটা Group এর শেষ segment এর key বের করো
        $lastInGroup = [];
        foreach ($segments as $seg) {
            $lastInGroup[(int) $seg['group']] = $seg['key'];
        }

        foreach ($segments as $seg) {
            $key           = htmlspecialchars($seg['key']);
            $group         = (int) $seg['group'];
            $carrier       = htmlspecialchars($seg['carrier']);
            $flightNum     = htmlspecialchars($seg['flight_number']);
            $origin        = htmlspecialchars($seg['departure']['airport_code']);
            $destination   = htmlspecialchars($seg['arrival']['airport_code']);
            $depTime       = htmlspecialchars($seg['departure']['date_time_zoon']);
            $arrTime       = htmlspecialchars($seg['arrival']['date_time_zoon']);
            $flightTime    = (int) $seg['duration'];
            $equipment     = htmlspecialchars($seg['aircraft']);
            $eTicket       = htmlspecialchars($seg['eTicketable'] ?? 'Yes');
            $providerCode  = htmlspecialchars($seg['provider_code'] ?? '1G');
            $bookingCode   = htmlspecialchars($seg['booking_info']['booking_code'] ?? 'O');
            $changeOfPlane = ($seg['change_of_plane'] ?? false) ? 'true' : 'false';
            $availSource   = htmlspecialchars($seg['availability_source'] ?? '');
            $availDisplay  = htmlspecialchars($seg['availability_display_type'] ?? '');
            $isCodeshare   = !empty($seg['is_codeshare']) && !empty($seg['operating_carrier']);

            preg_match('/Distance="(\d+)"/', $seg['xml'] ?? '', $dm);
            $distance = (int) ($dm[1] ?? 0);

            // শেষ segment কিনা চেক করো
            $isLastInGroup = ($lastInGroup[$group] === $seg['key']);

            // Attributes build করো — Connection attribute নেই, child element হবে
            $attrs  = "          Key=\"{$key}\"\n";
            $attrs .= "          Group=\"{$group}\"\n";
            $attrs .= "          Carrier=\"{$carrier}\"\n";
            $attrs .= "          FlightNumber=\"{$flightNum}\"\n";
            $attrs .= "          Origin=\"{$origin}\"\n";
            $attrs .= "          Destination=\"{$destination}\"\n";
            $attrs .= "          DepartureTime=\"{$depTime}\"\n";
            $attrs .= "          ArrivalTime=\"{$arrTime}\"\n";
            $attrs .= "          FlightTime=\"{$flightTime}\"\n";
            $attrs .= "          Distance=\"{$distance}\"\n";
            $attrs .= "          ETicketability=\"{$eTicket}\"\n";
            $attrs .= "          Equipment=\"{$equipment}\"\n";
            $attrs .= "          ChangeOfPlane=\"{$changeOfPlane}\"\n";
            $attrs .= "          ClassOfService=\"{$bookingCode}\"\n";
            $attrs .= "          ProviderCode=\"{$providerCode}\"";
            if ($availSource)  $attrs .= "\n          AvailabilitySource=\"{$availSource}\"";
            if ($availDisplay) $attrs .= "\n          AvailabilityDisplayType=\"{$availDisplay}\"";

            if ($isCodeshare) {
                $opCarrier   = htmlspecialchars($seg['operating_carrier']);
                $opFlightNum = htmlspecialchars($seg['operating_flight_number']);
                $line  = "        <air:AirSegment\n{$attrs}>\n";
                if (!$isLastInGroup) {
                    $line .= "          <air:Connection/>\n"; // ✅ child element
                }
                $line .= "          <air:CodeshareInfo OperatingCarrier=\"{$opCarrier}\" OperatingFlightNumber=\"{$opFlightNum}\"/>\n";
                $line .= "        </air:AirSegment>";
            } else {
                if (!$isLastInGroup) {
                    // Connecting segment — open tag + Connection child element
                    $line  = "        <air:AirSegment\n{$attrs}>\n";
                    $line .= "          <air:Connection/>\n"; // ✅ child element
                    $line .= "        </air:AirSegment>";
                } else {
                    // Direct / শেষ segment — self-closing
                    $line = "        <air:AirSegment\n{$attrs}/>";
                }
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Step 3 — Render SearchPassenger elements
    // FIX: Key attribute সরানো হয়েছে, শুধু BookingTravelerRef রাখা হয়েছে
    // -------------------------------------------------------------------------

    protected function renderPassengers(): string
    {
        $lines = [];
        $travelerIndex = 1;

        foreach ($this->data['passengers'] as $pax) {
            $type  = htmlspecialchars($pax['type']);
            $count = (int) $pax['count'];
            $age   = isset($pax['age']) && $pax['age'] !== null ? (int) $pax['age'] : null;

            for ($i = 0; $i < $count; $i++) {
                $ref     = base64_encode("BookingTraveler{$travelerIndex}");
                $ageAttr = $age !== null ? " Age=\"{$age}\"" : '';

                // ✅ FIX: Key attribute সরানো হয়েছে
                // Bryan এর মতে BookingTravelerRef যথেষ্ট, Key দরকার নেই
                $lines[] = "      <com:SearchPassenger"
                    . " xmlns:com=\"{$this->comVersion}\""
                    . " Code=\"{$type}\""
                    . $ageAttr
                    . " BookingTravelerRef=\"{$ref}\"/>";

                $travelerIndex++;
            }
        }

        return implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Step 4 — Render AirPricingCommand with PermittedBookingCodes per segment
    // -------------------------------------------------------------------------

    protected function renderPricingCommand(array $segments): string
    {
        $modifiers = [];

        foreach ($segments as $seg) {
            $segKey      = htmlspecialchars($seg['key']);
            $bookingCode = htmlspecialchars($seg['booking_info']['booking_code'] ?? '');

            if ($bookingCode) {
                $modifiers[] = "          <air:AirSegmentPricingModifiers AirSegmentRef=\"{$segKey}\">\n"
                    . "            <air:PermittedBookingCodes>\n"
                    . "              <air:BookingCode Code=\"{$bookingCode}\"/>\n"
                    . "            </air:PermittedBookingCodes>\n"
                    . "          </air:AirSegmentPricingModifiers>";
            }
        }

        if (empty($modifiers)) return '';

        $modifierXml = implode("\n", $modifiers);

        return "      <air:AirPricingCommand>\n{$modifierXml}\n      </air:AirPricingCommand>";
    }
}
