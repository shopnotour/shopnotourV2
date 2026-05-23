<?php

namespace Modules\Flight\Service\TravelPort;

class TravelPortXmlBuildService
{
    private $targetBranch;
    public $maxSolutions;
    private $airVersion;
    private $comVersion;

    public string $traceId = '';
    public array $bookingTravelerRefs = [];

    public function __construct()
    {
        $this->targetBranch = env('TRAVELPORT_TARGET_BRANCH');
        $this->maxSolutions = (int) env('TRAVELPORT_MAX_SOLUTIONS', 50);

        $schemaVersion   = env('TRAVELPORT_SCHEMA_VERSION', 'v52_0');
        $this->airVersion = "http://www.travelport.com/schema/air_{$schemaVersion}";
        $this->comVersion = "http://www.travelport.com/schema/common_{$schemaVersion}";
    }

    public function buildXmlRequest(array $searchData): string
    {

        if (session()->has('travelport_trace_id')) {
            $this->traceId = session('travelport_trace_id');
        } else {
            $this->traceId = bin2hex(random_bytes(16));
            session(['travelport_trace_id' => $this->traceId]);
        }
        $segments    = $searchData['segments'];
        $passengers  = $searchData['passengers'];
        $travelClass = $searchData['travel_class'] ?? 'ECONOMY';
        $airlineCodes = $searchData['airline_codes'] ?? null;

// string হলে array তে convert করো
        if (is_string($airlineCodes) && !empty($airlineCodes)) {
            $airlineCodes = array_filter(array_map('trim', explode(',', $airlineCodes)));
        }

        $searchLegs   = $this->buildSearchLegs($segments);
        $specialFare  = $searchData['special_fare'] ?? null;
        $passengerXml = $this->buildPassengers($passengers, $specialFare);
        $modifiers    = $this->buildAirSearchModifiers($airlineCodes, $travelClass);



        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header/>
    <SOAP-ENV:Body>
        <air:LowFareSearchReq xmlns:air="{$this->airVersion}" TraceId="{$this->traceId}"  AuthorizedBy="user" TargetBranch="{$this->targetBranch}" ReturnUpsellFare="true">
            <com:BillingPointOfSaleInfo xmlns:com="{$this->comVersion}" OriginApplication="UAPI"/>
{$searchLegs}
{$modifiers}
{$passengerXml}
        <air:AirPricingModifiers ETicketability="Required" FaresIndicator="PublicAndPrivateFares">
                <air:ExemptTaxes>
                    <air:TaxCategory>NQ</air:TaxCategory>
                </air:ExemptTaxes>
            </air:AirPricingModifiers>
        </air:LowFareSearchReq>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

        return $xml;
    }

    private function buildSearchLegs(array $segments): string
    {
        $searchLegs = '';

        // ✅ একবারই read করো
        $alternateDatesEnabled = env('FLIGHT_ALTERNATE_DATES_ENABLED', false);
        $range  = max(1, min(3, (int) env('FLIGHT_ALTERNATE_DATES_RANGE', 3)));
        $window = "P{$range}D";

        foreach ($segments as $segment) {
            $origin        = $segment['origin']['code'];
            $destination   = $segment['destination']['code'];
            $departureDate = $segment['departure_date'];
            $comVersion    = $this->comVersion;

            $searchLegs .= "        <air:SearchAirLeg>\n";
            $searchLegs .= "            <air:SearchOrigin>\n";
            $searchLegs .= "                <com:CityOrAirport xmlns:com=\"{$comVersion}\" Code=\"{$origin}\" PreferCity=\"true\"/>\n";
            $searchLegs .= "            </air:SearchOrigin>\n";
            $searchLegs .= "            <air:SearchDestination>\n";
            $searchLegs .= "                <com:CityOrAirport xmlns:com=\"{$comVersion}\" Code=\"{$destination}\" PreferCity=\"true\"/>\n";
            $searchLegs .= "            </air:SearchDestination>\n";

            if ($alternateDatesEnabled) {
                $searchLegs .= "            <air:SearchDepTime PreferredTime=\"{$departureDate}\" WindowBefore=\"{$window}\" WindowAfter=\"{$window}\"/>\n";
            } else {
                $searchLegs .= "            <air:SearchDepTime PreferredTime=\"{$departureDate}\"/>\n";
            }

            $searchLegs .= "        </air:SearchAirLeg>\n";
        }

        return $searchLegs;
    }

    // private function buildPassengers(array $passengers): string
    // {
    //     $passengerXml = '';
    //     $index = 1;

    //     foreach ($passengers['breakdown'] as $passenger) {
    //         $code = match($passenger['type']) {
    //             'adult'  => 'ADT',
    //             'child'  => 'CNN',
    //             'infant' => 'INF',
    //             default  => 'ADT',
    //         };

    //         $ageAttr = '';
    //         if ($passenger['type'] === 'child' && isset($passenger['age'])) {
    //             $ageAttr = " Age=\"{$passenger['age']}\"";
    //         } elseif ($passenger['type'] === 'infant') {
    //             $ageAttr = ' Age="1"';
    //         }

    //         $bookingTravelerRef = base64_encode("BookingTraveler{$index}");

    //         // ✅ এই line add করো — AirPrice এও same ref দরকার
    //         $this->bookingTravelerRefs[$code . '_' . $index] = $bookingTravelerRef;

    //         $passengerXml .= "        <com:SearchPassenger xmlns:com=\"{$this->comVersion}\" Code=\"{$code}\"{$ageAttr} BookingTravelerRef=\"{$bookingTravelerRef}\"/>\n";

    //         $index++;
    //     }

    //     // ✅ Session এ save করো (optional, কিন্তু debug এ কাজে আসে)
    //     session(['travelport_traveler_refs' => $this->bookingTravelerRefs]);

    //     return $passengerXml;
    // }

    private function buildPassengers(array $passengers, ?string $specialFare = null): string
    {
        $passengerXml = '';
        $index = 1;

        foreach ($passengers['breakdown'] as $passenger) {

            // ✅ Adult এর জন্য special fare check
            if ($passenger['type'] === 'adult') {
                $code = $this->resolvePassengerCode($specialFare);
            } else {
                $code = match($passenger['type']) {
                    'child'  => 'CNN',
                    'infant' => 'INF',
                    default  => 'ADT',
                };
            }

            $ageAttr = '';
            if ($passenger['type'] === 'child' && isset($passenger['age'])) {
                $ageAttr = " Age=\"{$passenger['age']}\"";
            } elseif ($passenger['type'] === 'infant') {
                $ageAttr = ' Age="1"';
            }

            $bookingTravelerRef = base64_encode("BookingTraveler{$index}");
            $this->bookingTravelerRefs[$code . '_' . $index] = $bookingTravelerRef;

            $passengerXml .= "        <com:SearchPassenger xmlns:com=\"{$this->comVersion}\" Code=\"{$code}\"{$ageAttr} BookingTravelerRef=\"{$bookingTravelerRef}\"/>\n";

            $index++;
        }

        session(['travelport_traveler_refs' => $this->bookingTravelerRefs]);

        return $passengerXml;
    }

    /**
     * ✅ Special fare — Sabre এর মতোই same logic
     */
    private function resolvePassengerCode(?string $specialFare): string
    {
        if (empty($specialFare)) return 'ADT';

        $fareMap = [
            'student' => ['enabled' => env('FLIGHT_STUDENT_FARE_ENABLED', false), 'code' => 'STU'],
            'group'   => ['enabled' => env('FLIGHT_GROUP_FARE_ENABLED',   false), 'code' => 'GRP'],
            'senior'  => ['enabled' => env('FLIGHT_SENIOR_FARE_ENABLED',  false), 'code' => 'SNN'],
            'seaman'  => ['enabled' => env('FLIGHT_SEAMAN_FARE_ENABLED',  false), 'code' => 'SEA'],
        ];

        $key = strtolower($specialFare);

        if (!isset($fareMap[$key]) || !$fareMap[$key]['enabled']) {
            return 'ADT';
        }

        return $fareMap[$key]['code'];
    }

//    private function buildPassengers(array $passengers): string
//    {
//        $passengerXml = '';
//
//        foreach ($passengers['breakdown'] as $passenger) {
//            $code = match($passenger['type']) {
//                'adult'  => 'ADT',
//                'child'  => 'CNN',
//                'infant' => 'INF',
//                default  => 'ADT',
//            };
//
//            $ageAttr = '';
//            if ($passenger['type'] === 'child' && isset($passenger['age'])) {
//                $ageAttr = " Age=\"{$passenger['age']}\"";
//            } elseif ($passenger['type'] === 'infant') {
//                $ageAttr = ' Age="1"';
//            }
//
//            $passengerXml .= "        <com:SearchPassenger xmlns:com=\"{$this->comVersion}\" Code=\"{$code}\"{$ageAttr}/>\n";
//        }
//
//        return $passengerXml;
//    }

//     private function buildAirSearchModifiers(?array $airlineCodes, string $travelClass): string
//     {
//         $modifiers = <<<MOD
//         <air:AirSearchModifiers MaxSolutions="{$this->maxSolutions}">
//             <air:PreferredProviders>
//                 <com:Provider xmlns:com="{$this->comVersion}" Code="1G"/>
//             </air:PreferredProviders>
// MOD;

//         // Permitted Carriers (optional)
//         if (!empty($airlineCodes)) {
//             if (is_string($airlineCodes)) {
//                 $airlineCodes = array_filter(array_map('trim', explode(',', $airlineCodes)));
//             }

//             if (count($airlineCodes) > 0) {
//                 $modifiers .= "\n            <air:PermittedCarriers>";
//                 foreach ($airlineCodes as $code) {
//                     $code       = strtoupper(trim($code));
//                     $modifiers .= "\n                <com:Carrier xmlns:com=\"{$this->comVersion}\" Code=\"{$code}\"/>";
//                 }
//                 $modifiers .= "\n            </air:PermittedCarriers>";
//             }
//         }

//         // Preferred Cabin Class
//         $modifiers .= "\n            <air:PreferredCabins>";
//         $modifiers .= "\n                <com:CabinClass xmlns:com=\"{$this->comVersion}\" Type=\"{$travelClass}\"/>";
//         $modifiers .= "\n            </air:PreferredCabins>";
//         $modifiers .= "\n        </air:AirSearchModifiers>";

//         return $modifiers;
//     }

    private function buildAirSearchModifiers(?array $airlineCodes, string $travelClass): string
    {
        $modifiers = <<<MOD
        <air:AirSearchModifiers MaxSolutions="{$this->maxSolutions}">
            <air:PreferredProviders>
                <com:Provider xmlns:com="{$this->comVersion}" Code="1G"/>
            </air:PreferredProviders>
MOD;

        // Permitted Carriers (optional)
        if (!empty($airlineCodes)) {
            if (is_string($airlineCodes)) {
                $airlineCodes = array_filter(array_map('trim', explode(',', $airlineCodes)));
            }

            if (count($airlineCodes) > 0) {
                $modifiers .= "\n            <air:PermittedCarriers>";
                foreach ($airlineCodes as $code) {
                    $code       = strtoupper(trim($code));
                    $modifiers .= "\n                <com:Carrier xmlns:com=\"{$this->comVersion}\" Code=\"{$code}\"/>";
                }
                $modifiers .= "\n            </air:PermittedCarriers>";
            }
        }

        // ✅ Cabin Class — default Economy, client দিলে সেটা
        $mappedCabin = $this->mapCabinClass($travelClass);
        $modifiers .= "\n            <air:PreferredCabins>";
        $modifiers .= "\n                <com:CabinClass xmlns:com=\"{$this->comVersion}\" Type=\"{$mappedCabin}\"/>";
        $modifiers .= "\n            </air:PreferredCabins>";
        $modifiers .= "\n        </air:AirSearchModifiers>";

        return $modifiers;
    }
    private function mapCabinClass(string $travelClass): string
    {
        $mapping = [
            'ECONOMY'         => 'Economy',
            'PREMIUM_ECONOMY' => 'PremiumEconomy',
            'BUSINESS'        => 'Business',
            'FIRST'           => 'First',
        ];

        return $mapping[strtoupper($travelClass)] ?? 'Economy';
    }
}
