<?php

namespace Modules\Flight\Service\AirArabia;


class AirArabiaXmlBuildService
{
    // ==========================================
    // Config — .env থেকে নেওয়া
    // ==========================================

    private string $username;
    private string $password;
    private string $agentCode;
    private string $defaultCurrency;
    private string $defaultCountry;
    private string $defaultStation;



    public function __construct()
    {
        $this->username        = config('services.air_arabia.username');
        $this->password        = config('services.air_arabia.password');
        $this->agentCode       = config('services.air_arabia.agent_code');
        $this->defaultCurrency = config('services.air_arabia.default_currency', 'BDT');
        $this->defaultCountry  = config('services.air_arabia.default_country',  'BD');
        $this->defaultStation  = config('services.air_arabia.default_station',  'DAC');
    }

    // ==========================================
    // REST Search Payload (JSON) — Step 2
    // ==========================================

    /**
     * FlightSearchService থেকে আসা searchData দিয়ে
     * Air Arabia REST search এর JSON payload তৈরি করে।
     *
     * @param array $searchData — FlightSearchService::prepareSearchData() এর output
     * @return array            — JSON payload (Http::post() তে সরাসরি দেওয়া যাবে)
     */
    public function buildSearchPayload(array $searchData): array
    {
        $segments   = $searchData['segments'];
        $passengers = $searchData['passengers'];
        $tripType   = $searchData['trip_type'];
        $currency   = $searchData['currency'] ?? $this->defaultCurrency;
        $country    = $searchData['country']  ?? $this->defaultCountry;
        $station    = $searchData['station']  ?? $this->defaultStation;

        $isReturn    = strtolower($tripType) === 'round';
        $searchOnds  = $this->buildSearchOnds($segments);
        $cutoverTime = now()->subDay()->format('Y-m-d\TH:i:s'); // আজকের আগের দিন

        return [
            'searchOnds'   => $searchOnds,
            'paxCounts'    => $this->buildPaxCounts($passengers),
            'isReturn'       => $isReturn,   // ✅ isReturn → return
            'currencyCode' => $currency,
            'cabinClass'   => $this->mapCabinClass($searchData['travel_class'] ?? 'ECONOMY'),
            'metaData'     => [
                'agentCode'     => $this->agentCode,
                'country'       => $country,
                'station'       => $station,
                'salesChannel'  => 'OTA',  // Header এ OTA আছে, body তেও OTA রাখুন
                'otherMetaData' => [
                    // ✅ FLIGHT_CUTOVER_TIME যোগ করুন
//                    ['metaDataKey' => 'FLIGHT_CUTOVER_TIME', 'metaDataValue' => $cutoverTime],
                    // ✅ SKIP_OND_MERGE → true করুন
                    ['metaDataKey' => 'SKIP_OND_MERGE', 'metaDataValue' => 'false'],
                ],
            ],
        ];
    }

    /**
     * Journey type header এর জন্য।
     */
    public function getJourneyType(string $tripType): string
    {
        return strtolower($tripType) === 'round' ? 'RETURN' : 'ONEWAY';
    }

    // ==========================================
    // SOAP: Get Price XML — Step 3
    // ==========================================

    /**
     * getPrice SOAP request XML তৈরি করে।
     *
     * @param array  $segments   — প্রতিটি item এ থাকবে:
     *                             rph, flightNumber, departureDateTime, arrivalDateTime,
     *                             origin, originTerminal, destination, destTerminal,
     *                             airlineCode, returnFlag (optional)
     * @param string $direction  — 'OneWay' | 'Return'
     * @param int    $adults     — যাত্রী সংখ্যা
     */


    /**
     * Flight object থেকে সরাসরি getPrice XML বানায়।
     * Controller → এই method → XML ready
     */
    public function buildGetPriceXmlFromFlight(array $flight): string
    {
        $segments  = $this->extractSegmentsFromFlight($flight);
        $direction = count($flight['legs']) > 1 ? 'Return' : 'OneWay';
        $paxCounts = $this->extractPaxCountsFromFlight($flight);

        // ✅ Connection = একই leg এ একাধিক segment (BOM→SHJ→BAH)
        // Return = আলাদা leg (SHJ→COK এবং COK→SHJ) — connection না
        $isConnection = count($segments) > 1
            && count(array_unique(array_column($segments, 'returnFlag'))) === 1;

        $segmentXml = $this->buildGetPriceSegmentXml($segments, $isConnection);
        $paxXml     = $this->buildPricePassengerXml($paxCounts);

        return $this->wrapInSoapEnvelope("
        <ns1:OTA_AirPriceRQ EchoToken=\"{$this->echoToken()}\"
            PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
            TimeStamp=\"{$this->timestamp()}\" Version=\"20061.00\">
            <ns1:POS>
                <ns1:Source TerminalID=\"TestUser/Test Runner\">
                    <ns1:RequestorID Type=\"4\" ID=\"{$this->username}\"/>
                    <ns1:BookingChannel Type=\"12\"/>
                </ns1:Source>
            </ns1:POS>
            <ns1:AirItinerary DirectionInd=\"{$direction}\">
                <ns1:OriginDestinationOptions>
                    {$segmentXml}
                </ns1:OriginDestinationOptions>
            </ns1:AirItinerary>
            <ns1:TravelerInfoSummary>
                <ns1:AirTravelerAvail>
                    {$paxXml}
                </ns1:AirTravelerAvail>
            </ns1:TravelerInfoSummary>
        </ns1:OTA_AirPriceRQ>
    ", 'ns1="http://www.opentravel.org/OTA/2003/05"');
    }

    private function extractSegmentsFromFlight(array $flight): array
    {
        $segments = [];
        foreach ($flight['legs'] as $leg) {
            foreach ($leg['segments'] as $seg) {
                $segments[] = [
                    'flightNumber'   => $seg['carrier'] . $seg['flight_number'],
                    'departureDateTime' => $seg['departure']['date'] . 'T' . $this->extractTimeComponent($seg['departure']['time'] ?? ''),
                    'arrivalDateTime'   => $seg['arrival']['date']   . 'T' . $this->extractTimeComponent($seg['arrival']['time']   ?? ''),
                    'origin'         => $seg['departure']['airport_code'],
                    'originTerminal' => $seg['departure']['terminal'] ?? '',
                    'destination'    => $seg['arrival']['airport_code'],
                    'destTerminal'   => $seg['arrival']['terminal']   ?? '',
                    'airlineCode'    => $seg['carrier'],
                    'rph'            => $seg['rph'] ?? '',
                    'returnFlag'     => $leg['leg_type'] === 'return' ? 'true' : 'false',
                    'segmentCode'    => $seg['segment_code'] ?? '',
                ];
            }
        }
        return $segments;
    }

    private function extractPaxCountsFromFlight(array $flight): array
    {
        $paxCounts = [];
        foreach ($flight['passengers'] as $pax) {
            if (($pax['count'] ?? 0) > 0) {
                // ✅ type normalize করুন — ADT/CHD/INF/CNN সব handle হবে
                $type = strtoupper($pax['type'] ?? 'ADT');

                // CNN → CHD (Air Arabia CHD ব্যবহার করে)
                if ($type === 'CNN' || str_starts_with($type, 'C0') || str_starts_with($type, 'C')) {
                    $type = 'CHD';
                }

                $paxCounts[] = [
                    'paxType' => $type,
                    'count'   => (int) $pax['count'],
                ];
            }
        }
        return $paxCounts;
    }
    public function buildGetPriceXml(array $flightOption, string $direction = 'OneWay', array $paxCounts = []): string
    {
        // flightOption এ flightSegments array আছে
        // connection হলে একাধিক segment একই option এ
        $segments    = $flightOption['flightSegments'];
        $isConnection = count($segments) > 1
            && count(array_unique(array_column($segments, 'segmentCode'))) > 1;

        $segmentXml = $this->buildGetPriceSegmentXml($segments, $isConnection);
        $paxXml     = $this->buildPricePassengerXml($paxCounts);

        return $this->wrapInSoapEnvelope("
        <ns1:OTA_AirPriceRQ EchoToken=\"{$this->echoToken()}\"
            PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
            TimeStamp=\"{$this->timestamp()}\" Version=\"20061.00\">
            <ns1:POS>
                <ns1:Source TerminalID=\"TestUser/Test Runner\">
                    <ns1:RequestorID Type=\"4\" ID=\"{$this->username}\"/>
                    <ns1:BookingChannel Type=\"12\"/>
                </ns1:Source>
            </ns1:POS>
            <ns1:AirItinerary DirectionInd=\"{$direction}\">
                <ns1:OriginDestinationOptions>
                    {$segmentXml}
                </ns1:OriginDestinationOptions>
            </ns1:AirItinerary>
            <ns1:TravelerInfoSummary>
                <ns1:AirTravelerAvail>
                    {$paxXml}
                </ns1:AirTravelerAvail>
            </ns1:TravelerInfoSummary>
        </ns1:OTA_AirPriceRQ>
    ", 'ns1="http://www.opentravel.org/OTA/2003/05"');
    }

    private function buildGetPriceSegmentXml(array $segments, bool $isConnection): string
    {
        // Connection: একই OND এর মধ্যে multiple segments (BOM→SHJ→COK)
        // Return: আলাদা আলাদা OND (SHJ→COK এবং COK→SHJ)
        // তাই isConnection false হলে সবসময় আলাদা OriginDestinationOption

        if ($isConnection) {
            $segmentsXml = '';
            foreach ($segments as $seg) {
                $segmentsXml .= "
            <ns1:FlightSegment
                ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                DepartureDateTime=\"{$seg['departureDateTime']}\"
                FlightNumber=\"{$seg['flightNumber']}\">
                <ns1:DepartureAirport LocationCode=\"{$seg['origin']}\"/>
                <ns1:ArrivalAirport LocationCode=\"{$seg['destination']}\"/>
                <ns1:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
            </ns1:FlightSegment>";
            }
            return "<ns1:OriginDestinationOption>{$segmentsXml}</ns1:OriginDestinationOption>";
        }

        // ✅ Direct বা Return — প্রতিটি segment আলাদা option এ
        $xml = '';
        foreach ($segments as $seg) {
            $xml .= "
        <ns1:OriginDestinationOption>
            <ns1:FlightSegment
                ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                DepartureDateTime=\"{$seg['departureDateTime']}\"
                FlightNumber=\"{$seg['flightNumber']}\">
                <ns1:DepartureAirport LocationCode=\"{$seg['origin']}\"/>
                <ns1:ArrivalAirport LocationCode=\"{$seg['destination']}\"/>
                <ns1:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
            </ns1:FlightSegment>
        </ns1:OriginDestinationOption>";
        }
        return $xml;
    }

    private function buildPricePassengerXml(array $paxCounts): string
    {
        $xml = '';
        foreach ($paxCounts as $pax) {
            if ($pax['count'] > 0) {
                // ✅ প্রতিটি pax type আলাদা line এ
                $xml .= "<ns1:PassengerTypeQuantity Code=\"{$pax['paxType']}\" Quantity=\"{$pax['count']}\"/>";
            }
        }
        return $xml;
    }

    // ==========================================
    // SOAP: Get Price with Bundle XML — Step 4
    // ==========================================

    public function buildGetPriceWithBundleXml(
        array  $flight,           // ← segments এর বদলে পুরো flight
        string $transactionId,
        string $outboundBundleId,
        string $inboundBundleId = ''
    ): string {
        $segments     = $this->extractSegmentsFromFlight($flight);
        $direction    = count($flight['legs']) > 1 ? 'Return' : 'OneWay';
        $paxCounts    = $this->extractPaxCountsFromFlight($flight);
        $isConnection = count($segments) > 1
            && count(array_unique(array_column($segments, 'returnFlag'))) === 1;

        $segmentXml = $this->buildGetPriceSegmentXml($segments, $isConnection);
        $paxXml     = $this->buildPricePassengerXml($paxCounts);

        $bundleXml = "<ns1:BundledServiceSelectionOptions>
        <ns1:OutBoundBunldedServiceId>{$outboundBundleId}</ns1:OutBoundBunldedServiceId>";
        if ($inboundBundleId) {
            $bundleXml .= "<ns1:InBoundBunldedServiceId>{$inboundBundleId}</ns1:InBoundBunldedServiceId>";
        }
        $bundleXml .= "</ns1:BundledServiceSelectionOptions>";

        return $this->wrapInSoapEnvelope("
        <ns1:OTA_AirPriceRQ EchoToken=\"{$this->echoToken()}\"
            PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
            TimeStamp=\"{$this->timestamp()}\" Version=\"20061.00\"
            TransactionIdentifier=\"{$transactionId}\">
            <ns1:POS>
                <ns1:Source TerminalID=\"TestUser/Test Runner\">
                    <ns1:RequestorID Type=\"4\" ID=\"{$this->username}\"/>
                    <ns1:BookingChannel Type=\"12\"/>
                </ns1:Source>
            </ns1:POS>
            <ns1:AirItinerary DirectionInd=\"{$direction}\">
                <ns1:OriginDestinationOptions>{$segmentXml}</ns1:OriginDestinationOptions>
            </ns1:AirItinerary>
            <ns1:TravelerInfoSummary>
                <ns1:AirTravelerAvail>
                    {$paxXml}
                </ns1:AirTravelerAvail>
            </ns1:TravelerInfoSummary>
            {$bundleXml}
        </ns1:OTA_AirPriceRQ>
    ", 'ns1="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Baggage Details XML — Step 5
    // ==========================================

    public function buildBaggageDetailsXml(array $segments, string $transactionId): string
    {
        $requestsXml = '';
        foreach ($segments as $seg) {
            $requestsXml .= "
                <ns:BaggageDetailsRequest TravelerRefNumberRPHs=\"\">
                    <ns:FlightSegmentInfo
                        ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                        DepartureDateTime=\"{$seg['departureDateTime']}\"
                        FlightNumber=\"{$seg['flightNumber']}\"
                        RPH=\"{$seg['rph']}\" returnFlag=\"false\">
                        <ns:DepartureAirport LocationCode=\"{$seg['origin']}\" Terminal=\"{$seg['originTerminal']}\"/>
                        <ns:ArrivalAirport LocationCode=\"{$seg['destination']}\" Terminal=\"{$seg['destTerminal']}\"/>
                        <ns:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
                    </ns:FlightSegmentInfo>
                </ns:BaggageDetailsRequest>";
        }

        return $this->wrapInSoapEnvelope("
            <ns:AA_OTA_AirBaggageDetailsRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TransactionIdentifier=\"{$transactionId}\" Version=\"20061.00\">
                <ns:POS>
                    <ns:Source TerminalID=\"TestUser/Test Runner\">
                        <ns:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns:BookingChannel Type=\"12\"/>
                    </ns:Source>
                </ns:POS>
                <ns:BaggageDetailsRequests>{$requestsXml}</ns:BaggageDetailsRequests>
            </ns:AA_OTA_AirBaggageDetailsRQ>
        ", 'ns="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Meal Details XML — Step 6
    // ==========================================

    public function buildMealDetailsXml(array $segments, string $transactionId): string
    {
        $requestsXml = '';
        foreach ($segments as $seg) {
            $requestsXml .= "
                <ns:MealDetailsRequest TravelerRefNumberRPHs=\"\">
                    <ns:FlightSegmentInfo
                        ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                        DepartureDateTime=\"{$seg['departureDateTime']}\"
                        FlightNumber=\"{$seg['flightNumber']}\"
                        RPH=\"{$seg['rph']}\" returnFlag=\"false\">
                        <ns:DepartureAirport LocationCode=\"{$seg['origin']}\" Terminal=\"{$seg['originTerminal']}\"/>
                        <ns:ArrivalAirport LocationCode=\"{$seg['destination']}\" Terminal=\"{$seg['destTerminal']}\"/>
                        <ns:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
                    </ns:FlightSegmentInfo>
                </ns:MealDetailsRequest>";
        }

        return $this->wrapInSoapEnvelope("
            <ns:AA_OTA_AirMealDetailsRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TransactionIdentifier=\"{$transactionId}\" Version=\"20061.00\">
                <ns:POS>
                    <ns:Source TerminalID=\"TestUser/Test Runner\">
                        <ns:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns:BookingChannel Type=\"12\"/>
                    </ns:Source>
                </ns:POS>
                <ns:MealDetailsRequests>{$requestsXml}</ns:MealDetailsRequests>
            </ns:AA_OTA_AirMealDetailsRQ>
        ", 'ns="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Seat Map XML — Step 7
    // ==========================================

    public function buildSeatMapXml(array $segments, string $transactionId): string
    {
        $segmentsXml = '';
        foreach ($segments as $seg) {
            $segmentsXml .= "
                <ns:FlightSegmentInfo
                    ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                    DepartureDateTime=\"{$seg['departureDateTime']}\"
                    FlightNumber=\"{$seg['flightNumber']}\"
                    RPH=\"{$seg['rph']}\" returnFlag=\"false\">
                    <ns:DepartureAirport LocationCode=\"{$seg['origin']}\" Terminal=\"{$seg['originTerminal']}\"/>
                    <ns:ArrivalAirport LocationCode=\"{$seg['destination']}\" Terminal=\"{$seg['destTerminal']}\"/>
                    <ns:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
                </ns:FlightSegmentInfo>";
        }

        return $this->wrapInSoapEnvelope("
            <ns:OTA_AirSeatMapRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TransactionIdentifier=\"{$transactionId}\" Version=\"20061.00\">
                <ns:POS>
                    <ns:Source TerminalID=\"TestUser/Test Runner\">
                        <ns:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns:BookingChannel Type=\"12\"/>
                    </ns:Source>
                </ns:POS>
                <ns:SeatMapRequests>
                    <ns:SeatMapRequest>{$segmentsXml}</ns:SeatMapRequest>
                </ns:SeatMapRequests>
            </ns:OTA_AirSeatMapRQ>
        ", 'ns="http://www.opentravel.org/OTA/2003/05"');
    }


    // ✅ এই method যোগ করুন buildBookXml() এর আগে

    /**
     * Booking model থেকে সরাসরি Book XML বানায়
     * AirArabiaBookingService::createPnr() এ call হবে
     */
    public function buildBookXmlFromBooking(
        array  $segments,
        array  $passengers,
        array  $contact,
        string $transactionId,
        float  $totalAmount,
        string $currency = 'BDT'
    ): string {
        // segments এ arrivalDateTime format check করো
        // BookingRoute থেকে আসা datetime: "2026-04-10 13:25:00"
        // XML এ দরকার: "2026-04-10T13:25:00"
        $segments = array_map(function ($seg) {
            $seg['departureDateTime'] = str_replace(' ', 'T', $seg['departureDateTime']);
            $seg['arrivalDateTime']   = str_replace(' ', 'T', $seg['arrivalDateTime']);
            return $seg;
        }, $segments);

        return $this->buildBookXml(
            $segments,
            $passengers,
            $contact,
            $transactionId,
            $totalAmount,
            $currency,
            false
        );
    }
    // ==========================================
    // SOAP: Book XML — Step 8
    // ==========================================

    /**
     * Book SOAP XML তৈরি করে।
     *
     * @param array  $segments       — RPH সহ segments
     * @param array  $passengers     — যাত্রীদের তথ্য (FlightSearchService passengers format)
     * @param array  $contact        — Contact info
     * @param string $transactionId  — getPrice থেকে পাওয়া
     * @param float  $totalAmount    — মোট মূল্য
     * @param string $currency       — মুদ্রা কোড
     * @param bool   $onHold         — true হলে পেমেন্ট ছাড়া Hold
     */
    public function buildBookXml(
        array  $segments,
        array  $passengers,
        array  $contact,
        string $transactionId,
        float  $totalAmount = 0,
        string $currency    = 'BDT',
        bool   $onHold      = false
    ): string {
        $segmentXml   = $this->buildSegmentXml($segments);
        $passengerXml = $this->buildPassengerXmlForBook($passengers);

        $fulfillmentXml = '';
        if (!$onHold && $totalAmount > 0) {
            $fulfillmentXml = "
                <ns2:Fulfillment>
                    <ns2:PaymentDetails>
                        <ns2:PaymentDetail>
                            <ns2:DirectBill>
                                <ns2:CompanyName Code=\"{$this->agentCode}\"/>
                            </ns2:DirectBill>
                            <ns2:PaymentAmount Amount=\"{$totalAmount}\" CurrencyCode=\"{$currency}\" DecimalPlaces=\"2\"/>
                        </ns2:PaymentDetail>
                    </ns2:PaymentDetails>
                </ns2:Fulfillment>";
        }

        return $this->wrapInSoapEnvelope("
            <ns2:OTA_AirBookRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TimeStamp=\"{$this->timestamp()}\"
                TransactionIdentifier=\"{$transactionId}\">
                <ns2:POS>
                    <ns2:Source TerminalID=\"TestUser/Test Runner\">
                        <ns2:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns2:BookingChannel Type=\"12\"/>
                    </ns2:Source>
                </ns2:POS>
                <ns2:AirItinerary>
                    <ns2:OriginDestinationOptions>{$segmentXml}</ns2:OriginDestinationOptions>
                </ns2:AirItinerary>
                <ns2:TravelerInfo>{$passengerXml}</ns2:TravelerInfo>
                {$fulfillmentXml}
            </ns2:OTA_AirBookRQ>
            <ns1:AAAirBookRQExt>
                <ns1:ContactInfo>
                    <ns1:PersonName>
                        <ns1:Title>{$contact['title']}</ns1:Title>
                        <ns1:FirstName>{$contact['firstName']}</ns1:FirstName>
                        <ns1:LastName>{$contact['lastName']}</ns1:LastName>
                    </ns1:PersonName>
                    <ns1:Telephone>
                        <ns1:PhoneNumber>{$contact['phone']}</ns1:PhoneNumber>
                        <ns1:CountryCode>{$contact['countryCode']}</ns1:CountryCode>
                    </ns1:Telephone>
                    <ns1:Email>{$contact['email']}</ns1:Email>
                    <ns1:Address>
                        <ns1:CountryName>
                            <ns1:CountryName>{$contact['country']}</ns1:CountryName>
                            <ns1:CountryCode>{$contact['countryIso']}</ns1:CountryCode>
                        </ns1:CountryName>
                        <ns1:CityName>{$contact['city']}</ns1:CityName>
                    </ns1:Address>
                </ns1:ContactInfo>
            </ns1:AAAirBookRQExt>
        ", 'ns1="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" ns2="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Get Reservation by PNR XML — Step 9
    // ==========================================

    public function buildGetReservationXml(string $pnr): string
    {
        return $this->wrapInSoapEnvelope("
            <ns2:OTA_ReadRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TimeStamp=\"{$this->timestamp()}\" Version=\"20061.00\">
                <ns2:POS>
                    <ns2:Source TerminalID=\"TestUser/Test Runner\">
                        <ns2:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns2:BookingChannel Type=\"12\"/>
                    </ns2:Source>
                </ns2:POS>
                <ns2:ReadRequests>
                    <ns2:ReadRequest>
                        <ns2:UniqueID ID=\"{$pnr}\" Type=\"14\"/>
                    </ns2:ReadRequest>
                </ns2:ReadRequests>
            </ns2:OTA_ReadRQ>
            <ns1:AAReadRQExt>
                <ns1:AALoadDataOptions>
                    <ns1:LoadTravelerInfo>true</ns1:LoadTravelerInfo>
                    <ns1:LoadAirItinery>true</ns1:LoadAirItinery>
                    <ns1:LoadPriceInfoTotals>true</ns1:LoadPriceInfoTotals>
                    <ns1:LoadFullFilment>true</ns1:LoadFullFilment>
                </ns1:AALoadDataOptions>
            </ns1:AAReadRQExt>
        ", 'ns1="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" ns2="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Modify Reservation (Payment) XML — Step 10
    // ==========================================

    public function buildModifyPaymentXml(
        string $pnr,
        string $transactionId,
        float  $amount,
        string $currency = 'BDT'
    ): string {
        return $this->wrapInSoapEnvelope("
            <ns8:OTA_AirBookModifyRQ EchoToken=\"{$this->echoToken()}\"
                SequenceNmbr=\"1\" TransactionIdentifier=\"{$transactionId}\" Version=\"20061.0\">
                <ns8:POS>
                    <ns8:Source>
                        <ns8:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns8:BookingChannel Type=\"12\"/>
                    </ns8:Source>
                </ns8:POS>
                <ns8:AirBookModifyRQ ModificationType=\"9\">
                    <ns8:Fulfillment>
                        <ns8:PaymentDetails>
                            <ns8:PaymentDetail>
                                <ns8:DirectBill>
                                    <ns8:CompanyName Code=\"{$this->agentCode}\"/>
                                </ns8:DirectBill>
                                <ns8:PaymentAmount Amount=\"{$amount}\" CurrencyCode=\"{$currency}\" DecimalPlaces=\"2\"/>
                            </ns8:PaymentDetail>
                        </ns8:PaymentDetails>
                    </ns8:Fulfillment>
                    <ns8:BookingReferenceID ID=\"{$pnr}\" Type=\"14\"/>
                </ns8:AirBookModifyRQ>
            </ns8:OTA_AirBookModifyRQ>
            <ns7:AAAirBookModifyRQExt>
                <ns7:AALoadDataOptions>
                    <ns7:LoadTravelerInfo>true</ns7:LoadTravelerInfo>
                    <ns7:LoadAirItinery>true</ns7:LoadAirItinery>
                    <ns7:LoadPriceInfoTotals>true</ns7:LoadPriceInfoTotals>
                    <ns7:LoadFullFilment>true</ns7:LoadFullFilment>
                </ns7:AALoadDataOptions>
            </ns7:AAAirBookModifyRQExt>
        ", 'ns7="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" ns8="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // SOAP: Cancel Booking XML — Step 11
    // ==========================================

    public function buildCancelXml(string $pnr): string
    {
        return $this->wrapInSoapEnvelope("
            <ns2:OTA_CancelRQ EchoToken=\"{$this->echoToken()}\"
                PrimaryLangID=\"en-us\" SequenceNmbr=\"1\"
                TimeStamp=\"{$this->timestamp()}\" Version=\"20061.00\">
                <ns2:POS>
                    <ns2:Source TerminalID=\"TestUser/Test Runner\">
                        <ns2:RequestorID ID=\"{$this->username}\" Type=\"4\"/>
                        <ns2:BookingChannel Type=\"12\"/>
                    </ns2:Source>
                </ns2:POS>
                <ns2:UniqueID ID=\"{$pnr}\" Type=\"14\"/>
            </ns2:OTA_CancelRQ>
        ", 'ns2="http://www.opentravel.org/OTA/2003/05"');
    }

    // ==========================================
    // Private Helpers
    // ==========================================

    /**
     * searchData segments → Air Arabia searchOnds format
     */
    private function buildSearchOnds(array $segments): array
    {
        $searchOnds = [];

        foreach ($segments as $segment) {
            $origin      = $segment['origin']['code'];
            $destination = $segment['destination']['code'];
            $date        = $segment['departure_date'];

            $searchOnds[] = [
                'origin'                => ['code' => $origin,      'locationType' => 'AIRPORT'],
                'destination'           => ['code' => $destination, 'locationType' => 'AIRPORT'],
                'searchStartDate'       => $date,
                'searchEndDate'         => $date,
                'preferredDate'         => $date,
                'bookingType'           => 'NORMAL',
                'cabinClass'            => 'Y',
                'ondRef'                => "{$origin}/{$destination}",
                'interlineQuoteDetails' => null,
            ];
        }

        return $searchOnds;
    }

    /**
     * searchData passengers → Air Arabia paxCounts format
     */
    private function buildPaxCounts(array $passengers): array
    {
        return [
            // ✅ Sample অনুযায়ী — count আগে, paxType পরে
            ['count' => $passengers['adults']   ?? 0, 'paxType' => 'ADT'],
            ['count' => $passengers['children'] ?? 0, 'paxType' => 'CHD'],
            ['count' => $passengers['infants']  ?? 0, 'paxType' => 'INF'],
        ];
    }

    /**
     * Travel class → Air Arabia cabin class code
     */
    private function mapCabinClass(string $travelClass): string
    {
        return match (strtoupper($travelClass)) {
            'BUSINESS'        => 'C',
            'FIRST'           => 'F',
            'PREMIUM_ECONOMY' => 'W',
            default           => 'Y', // ECONOMY
        };
    }

    /**
     * SOAP segment XML — getPrice, Book ইত্যাদিতে ব্যবহার হয়
     */
    private function buildSegmentXml(array $segments): string
    {
        $outbound = array_filter($segments, fn($s) => $s['returnFlag'] === 'false');
        $return   = array_filter($segments, fn($s) => $s['returnFlag'] === 'true');

        $xml = '';

        if (!empty($outbound)) {
            $xml .= '<ns2:OriginDestinationOption>';
            foreach ($outbound as $seg) {
                $xml .= "
            <ns2:FlightSegment
                ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                DepartureDateTime=\"{$seg['departureDateTime']}\"
                FlightNumber=\"{$seg['flightNumber']}\"
                RPH=\"{$seg['rph']}\" returnFlag=\"false\">
                <ns2:DepartureAirport LocationCode=\"{$seg['origin']}\" Terminal=\"{$seg['originTerminal']}\"/>
                <ns2:ArrivalAirport LocationCode=\"{$seg['destination']}\" Terminal=\"{$seg['destTerminal']}\"/>
                <ns2:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
            </ns2:FlightSegment>";
            }
            $xml .= '</ns2:OriginDestinationOption>';
        }

        if (!empty($return)) {
            $xml .= '<ns2:OriginDestinationOption>';
            foreach ($return as $seg) {
                $xml .= "
            <ns2:FlightSegment
                ArrivalDateTime=\"{$seg['arrivalDateTime']}\"
                DepartureDateTime=\"{$seg['departureDateTime']}\"
                FlightNumber=\"{$seg['flightNumber']}\"
                RPH=\"{$seg['rph']}\" returnFlag=\"false\">
                <ns2:DepartureAirport LocationCode=\"{$seg['origin']}\" Terminal=\"{$seg['originTerminal']}\"/>
                <ns2:ArrivalAirport LocationCode=\"{$seg['destination']}\" Terminal=\"{$seg['destTerminal']}\"/>
                <ns2:OperatingAirline Code=\"{$seg['airlineCode']}\"/>
            </ns2:FlightSegment>";
            }
            $xml .= '</ns2:OriginDestinationOption>';
        }

        return $xml;
    }

    /**
     * Book request এর জন্য passenger XML।
     * FlightSearchService passengers breakdown format থেকে তৈরি হয়।
     *
     * প্রতিটি passenger এ থাকবে:
     *   title, firstName, lastName, birthDate (YYYY-MM-DD),
     *   paxType (ADT/CHD/INF), phone, countryCode, nationality, rph (A1/A2...)
     */
    private function buildPassengerXmlForBook(array $passengers): string
    {
        $xml = '';
        foreach ($passengers as $pax) {
            $paxTypeCode = match(strtoupper($pax['paxType'] ?? 'ADT')) {
                'CHD'   => 'CHD',
                'INF'   => 'INF',
                default => 'ADT',
            };

            // ✅ BirthDate normalize — "1999-04-15 00:00:00" → "1999-04-15"
            $birthDate = date('Y-m-d', strtotime($pax['birthDate'] ?? '1990-01-01'));

            $xml .= "
        <ns2:AirTraveler BirthDate=\"{$birthDate}T00:00:00\" PassengerTypeCode=\"{$paxTypeCode}\">
                <ns2:PersonName>
                    <ns2:GivenName>{$pax['firstName']}</ns2:GivenName>
                    <ns2:Surname>{$pax['lastName']}</ns2:Surname>
                    <ns2:NameTitle>{$pax['title']}</ns2:NameTitle>
                </ns2:PersonName>
                <ns2:Telephone CountryAccessCode=\"{$pax['countryCode']}\" PhoneNumber=\"{$pax['phone']}\"/>
                <ns2:Address>
                    <ns2:CountryName Code=\"{$pax['nationality']}\"/>
                </ns2:Address>
                <ns2:Document DocHolderNationality=\"{$pax['nationality']}\">
                    <ns2:DocHolderName>{$pax['firstName']}</ns2:DocHolderName>
                </ns2:Document>
                <ns2:TravelerRefNumber RPH=\"{$pax['rph']}\"/>
            </ns2:AirTraveler>";
        }
        return $xml;
    }

    /**
     * WS-Security সহ SOAP Envelope
     */
    private function wrapInSoapEnvelope(string $bodyContent, string $nsDeclarations = ''): string
    {
        // ✅ "ns1="..." ns2="..."" → "xmlns:ns1="..." xmlns:ns2="...""
        $xmlnsDeclarations = $nsDeclarations
            ? preg_replace('/(\b\w+\b)=/', 'xmlns:$1=', $nsDeclarations)
            : '';

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <soap:Header>
        <wsse:Security soap:mustUnderstand="1"
            xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken
                wsu:Id="UsernameToken-1"
                xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <wsse:Username>{$this->username}</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">{$this->password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soap:Header>
    <soap:Body {$xmlnsDeclarations}>
        {$bodyContent}
    </soap:Body>
</soap:Envelope>
XML;
    }

    private function echoToken(): string { return uniqid('', true); }
    private function timestamp(): string { return now()->format('Y-m-d\TH:i:s'); }

    private function extractTimeComponent(string $time): string
    {
        preg_match('/(\d{2}:\d{2}:\d{2})/', $time, $m);
        return $m[1] ?? '00:00:00';
    }
}
