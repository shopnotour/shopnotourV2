<?php

namespace Modules\Booking\Service;

use SimpleXMLElement;
use Illuminate\Support\Facades\Log;

class TravelPortPNRResponseParser
{
    /**
     * Parse PNR Retrieve Response → same structure as SabreBookingResponseService
     */
    public function parseRetrieveResponse(string $xmlString): array
    {
        try {
            $xmlString = $this->cleanXmlString($xmlString);

            if (empty($xmlString)) {
                return $this->buildError('Empty XML response');
            }

            libxml_use_internal_errors(true);
            $xml = new SimpleXMLElement($xmlString);
            libxml_clear_errors();

            $this->registerNamespaces($xml);

            // Check SOAP Fault
            $soapFault = $this->safeXpath($xml, '//SOAP:Fault');
            if (!empty($soapFault)) {
                return $this->parseSoapFault($soapFault[0]);
            }

            $universalRecord = $this->safeXpath($xml, '//univ:UniversalRecord');
            if (empty($universalRecord)) {
                return $this->buildError('UniversalRecord not found');
            }

            $ur = $universalRecord[0];
            $this->registerNamespaces($ur);
            $urAttr = $ur->attributes();

            // ── Core Parsing ─────────────────────────────────────────────
            $providerInfo    = $this->parseProviderReservationInfo($xml);
            $supplierLocator = $this->parseSupplierLocator($xml);
            $airReservation  = $this->parseAirReservation($xml);
            $travelers       = $this->parseBookingTravelers($ur);
            $flights         = $this->parseFlights($xml, $supplierLocator['locator_code'] ?? '');
            $pricing         = $this->parsePricing($xml, $flights);
            $agencyInfo      = $this->parseAgencyInfo($ur);
            $remarks         = $this->parseRemarks($ur);
            $actionStatus = $this->parseActionStatus($ur, $xml, $remarks);

            // ── Derived ──────────────────────────────────────────────────
            $journeys     = $this->buildJourneys($flights);
            $allSegments  = $this->buildAllSegments($flights);
            $contactInfo  = $this->buildContactInfo($travelers);
            $specialSvcs  = $this->buildSpecialServices($travelers);

            $startDate = $flights[0]['departureDate'] ?? '';
            $endDate   = !empty($flights) ? end($flights)['arrivalDate'] : '';

            // ── FIX: Use ProviderReservationInfo LocatorCode as booking_id ──
            $providerLocatorCode = $providerInfo['locator_code'] ?? '';

            return [
                'success'            => true,
                'trace_id'           => (string) rand(1000, 9999),
                'transaction_id'     => strtoupper(md5($providerLocatorCode . microtime())),
                'response_time'      => 0,

                'booking_id'         => $providerLocatorCode,
                'start_date'         => $startDate,
                'end_date'           => $endDate,
                'is_cancelable'      => true,
                'is_ticketed'        => $this->checkTicketed($xml),
                'timestamp'          => now()->toIso8601String(),
                'booking_signature'  => hash('sha256', $providerLocatorCode . microtime()),

                'contact_info'       => $contactInfo,

                // ── FIX: universal_record uses ProviderReservationInfo LocatorCode ──
                'universal_record' => [
                    'locator_code'     => $providerLocatorCode,        // আগের মতোই (blade এ কোনো change নেই)
                    'ur_locator_code' => (string)($urAttr['LocatorCode'] ?? ''), // ✅ নতুন key
                    'version'          => 0,
                    'status'           => 'Active',
                ],

                'provider_reservation' => [
                    'key'              => $providerInfo['key'] ?? '',
                    'provider_code'    => $providerInfo['provider_code'] ?? '1G',
                    'locator_code'     => $providerLocatorCode,
                    'create_date'      => $providerInfo['create_date'] ?? '',
                    'modified_date'    => $providerInfo['modified_date'] ?? '',
                    'host_create_date' => $providerInfo['host_create_date'] ?? '',
                    'owning_pcc'       => $providerInfo['owning_pcc'] ?? '',
                    'home_pcc'         => $providerInfo['owning_pcc'] ?? '',
                    'prime_host_id'    => '1G',
                    'number_of_updates'=> 0,
                    'display_details'  => $providerInfo['details'] ?? [],
                ],

                'supplier_locator' => [
                    'supplier_code'    => $supplierLocator['supplier_code'] ?? '',
                    'locator_code'     => $supplierLocator['locator_code'] ?? '',
                    'create_date_time' => $supplierLocator['create_date_time'] ?? '',
                ],

                // ── FIX: air_reservation uses ProviderReservationInfo LocatorCode ──
                'air_reservation' => [
                    'locator_code'  => $providerLocatorCode,
                    'create_date'   => $airReservation['create_date'] ?? '',
                    'modified_date' => $airReservation['modified_date'] ?? '',
                ],

                'passenger'  => $this->buildPassenger($travelers[0] ?? []),
                'passengers' => $this->buildPassengers($travelers),

                'segments'     => $this->buildSegments($flights, $travelers),
                'all_segments' => $allSegments,
                'journeys'     => $journeys,

                'remarks' => $remarks,

                'messages' => [
                    'warnings' => $this->parseWarnings($xml),
                    'errors'   => [],
                ],

                'action_status' => $actionStatus,
                'agency_info'   => $agencyInfo,

                'special_services'  => $specialSvcs,
                'pricing'           => $pricing,
                'fare_rules'        => $this->buildFareRules($xml),
                'flight_tickets'    => $this->parseFlightTickets($xml),
                'accounting_items'  => [],
            ];

        } catch (\Exception $e) {
            Log::error('TravelPortPNRResponseParser error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->buildError($e->getMessage());
        }
    }


    private function parseFlightTickets(SimpleXMLElement $xml): array
    {
        $tickets = [];

        // ── Step 1: Traveler Key → Index map ──────────────────────────
        $travelerKeyMap = [];
        foreach ($this->safeXpath($xml, '//com:BookingTraveler') as $idx => $t) {
            $attr = $t->attributes();
            $key  = (string) ($attr['Key'] ?? '');
            if ($key) {
                $travelerKeyMap[$key] = $idx + 1; // 1-based
            }
        }

        // ── Step 2: SSR TKNE → coupon map (travelerRef → coupons[]) ───
        // FreeText format: "7799412104183C1" → ticket number + coupon
        $tkneMap = []; // travelerRef → [ ['number'=>..., 'coupon'=>...], ... ]
        foreach ($this->safeXpath($xml, '//com:BookingTraveler') as $t) {
            $tAttr       = $t->attributes();
            $travelerRef = (string) ($tAttr['Key'] ?? '');

            foreach ($this->safeXpath($t, './/com:SSR') as $ssr) {
                $sAttr = $ssr->attributes();
                if ((string) ($sAttr['Type'] ?? '') !== 'TKNE') continue;

                $freeText = (string) ($sAttr['FreeText'] ?? '');
                // e.g. "7799412104183C1" — last char is coupon number
                if (preg_match('/^(\d+)C(\d+)$/', $freeText, $m)) {
                    $tkneMap[$travelerRef][] = [
                        'ticket_number' => $m[1],
                        'coupon_number' => (int) $m[2],
                    ];
                }
            }
        }

        // ── Step 3: TicketInfo → main ticket data ──────────────────────
        foreach ($this->safeXpath($xml, '//air:TicketInfo') as $ticket) {
            $attr        = $ticket->attributes();
            $travelerRef = (string) ($attr['BookingTravelerRef'] ?? '');
            $ticketNum   = (string) ($attr['Number'] ?? '');
            $issueDate   = (string) ($attr['TicketIssueDate'] ?? '');

            // Name
            $nameNodes = $this->safeXpath($ticket, './/com:Name');
            $name      = [];
            if (!empty($nameNodes)) {
                $na   = $nameNodes[0]->attributes();
                $name = [
                    'first' => (string) ($na['First'] ?? ''),
                    'last'  => (string) ($na['Last'] ?? ''),
                ];
            }

            // Traveler index
            $travelerIndex = $travelerKeyMap[$travelerRef] ?? null;

            // Status — Travelport TicketInfo Status="N" মানে issued
            $rawStatus   = (string) ($attr['Status'] ?? '');
            $statusName  = match ($rawStatus) {
                'N', 'n' => 'Issued',
                'V'      => 'Voided',
                'R'      => 'Refunded',
                'E'      => 'Exchanged',
                default  => 'Issued',
            };
            $statusCode  = match ($rawStatus) {
                'N', 'n' => 'TE',
                'V'      => 'TV',
                'R'      => 'TR',
                'E'      => 'TX',
                default  => 'TE',
            };

            // Flight coupons — SSR TKNE থেকে
            $flightCoupons = [];
            foreach ($tkneMap[$travelerRef] ?? [] as $tkne) {
                if ($tkne['ticket_number'] === $ticketNum) {
                    $flightCoupons[] = [
                        'item_id'            => (string) $tkne['coupon_number'],
                        'coupon_status'      => 'Not Flown',
                        'coupon_status_code' => 'I',
                    ];
                }
            }

            // Issue date format — Sabre এর মতো শুধু date part
            $issueDateFormatted = $issueDate ? substr($issueDate, 0, 10) : null;

            $tickets[] = [
                'number'             => $ticketNum,
                'date'               => $issueDateFormatted,    // ← Sabre এর 'date' key এর মতো
                'airline_code'       => (string) ($attr['Carrier'] ?? 'BS'),
                'agency_iata'        => (string) ($attr['IATANumber'] ?? ''),
                'traveler_index'     => $travelerIndex,          // ← Blade match এর জন্য
                'ticket_status'      => $statusName,             // ← 'Issued'
                'ticket_status_code' => $statusCode,             // ← 'TE'
                'ticketing_pcc'      => (string) ($attr['TicketingAgentSignOn'] ?? ''),

                'payment' => [
                    'subtotal' => null,
                    'taxes'    => null,
                    'total'    => null,
                    'currency' => null,
                ],

                'flight_coupons' => $flightCoupons,              // ← Sabre এর মতো same structure

                // Extra (blade এ দরকার না হলেও রাখা ভালো)
                'passenger_name' => $name,
            ];
        }

        return $tickets;
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  PROVIDER / SUPPLIER / AIR RESERVATION
    // ═══════════════════════════════════════════════════════════════════════

    private function parseProviderReservationInfo(SimpleXMLElement $xml): array
    {
        $nodes = $this->safeXpath($xml, '//univ:ProviderReservationInfo');
        if (empty($nodes)) return [];

        $attr    = $nodes[0]->attributes();
        $details = [];
        foreach ($this->safeXpath($nodes[0], './/univ:DisplayDetail') as $d) {
            $da = $d->attributes();
            $details[$this->toSnakeCase((string) ($da['Name'] ?? ''))] = (string) ($da['Value'] ?? '');
        }

        return [
            'key'             => (string) ($attr['Key'] ?? ''),
            'provider_code'   => (string) ($attr['ProviderCode'] ?? '1G'),
            'locator_code'    => (string) ($attr['LocatorCode'] ?? ''),
            'create_date'     => (string) ($attr['CreateDate'] ?? ''),
            'modified_date'   => (string) ($attr['ModifiedDate'] ?? ''),
            'host_create_date'=> (string) ($attr['HostCreateDate'] ?? ''),
            'owning_pcc'      => (string) ($attr['OwningPCC'] ?? ''),
            'details'         => $details,
        ];
    }

    private function parseSupplierLocator(SimpleXMLElement $xml): array
    {
        $nodes = $this->safeXpath($xml, '//com:SupplierLocator');
        if (empty($nodes)) return [];

        $attr = $nodes[0]->attributes();
        return [
            'supplier_code'    => (string) ($attr['SupplierCode'] ?? ''),
            'locator_code'     => (string) ($attr['SupplierLocatorCode'] ?? ''),
            'create_date_time' => (string) ($attr['CreateDateTime'] ?? ''),
        ];
    }

    private function parseAirReservation(SimpleXMLElement $xml): array
    {
        $nodes = $this->safeXpath($xml, '//air:AirReservation');
        if (empty($nodes)) return [];

        $attr = $nodes[0]->attributes();
        return [
            'locator_code'  => (string) ($attr['LocatorCode'] ?? ''),
            'create_date'   => (string) ($attr['CreateDate'] ?? ''),
            'modified_date' => (string) ($attr['ModifiedDate'] ?? ''),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TRAVELERS
    // ═══════════════════════════════════════════════════════════════════════

    private function parseBookingTravelers(SimpleXMLElement $ur): array
    {
        $travelers = [];
        $index     = 1;

        foreach ($this->safeXpath($ur, './/com:BookingTraveler') as $t) {
            $this->registerNamespaces($t);
            $attr   = $t->attributes();
            $name   = $this->parseTravelerName($t);
            $phones = $this->parsePhones($t);
            $emails = $this->parseEmails($t);
            $ssrs   = $this->parseSSRs($t);
            $docs   = $this->parseIdentityDocuments($ssrs, $name, $attr);

            $travelers[] = [
                'givenName'         => $name['first'],
                'surname'           => $name['last'],
                'prefix'            => $name['prefix'],
                'type'              => $this->mapTravelerType((string) ($attr['TravelerType'] ?? 'ADT')),
                'passengerCode'     => (string) ($attr['TravelerType'] ?? 'ADT'),
                'nameAssociationId' => (string) $index,
                'dob'               => (string) ($attr['DOB'] ?? ''),
                'gender'            => (string) ($attr['Gender'] ?? ''),
                'emails'            => array_column($emails, 'email'),
                'phones'            => array_map(fn($p) => [
                    'number' => $p['number'],
                    'label'  => $p['type'] === 'Mobile' ? 'M' : 'H',
                ], $phones),
                'identityDocuments' => $docs,
                '_travelerKey'      => (string) ($attr['Key'] ?? ''),
                '_ssrs'             => $ssrs,
            ];
            $index++;
        }

        return $travelers;
    }

    private function buildPassengers(array $travelers): array
    {
        return array_map(fn($t) => $this->buildPassenger($t), $travelers);
    }

    private function buildPassenger(array $traveler): array
    {
        if (empty($traveler)) return [];

        $phone          = $traveler['phones'][0] ?? [];
        $passport       = null;
        $secureFlightData = null;

        foreach ($traveler['identityDocuments'] ?? [] as $doc) {
            if (($doc['documentType'] ?? '') === 'PASSPORT' && !$passport) {
                $passport = $doc;
            }
            if (($doc['documentType'] ?? '') === 'SECURE_FLIGHT_PASSENGER_DATA' && !$secureFlightData) {
                $secureFlightData = $doc;
            }
        }

        return [
            'key'               => $traveler['_travelerKey'] ?? base64_encode(($traveler['givenName'] ?? '') . ($traveler['surname'] ?? '')),
            'traveler_type'     => $traveler['passengerCode'] ?? 'ADT',
            'passenger_type'    => $traveler['type'] ?? 'ADULT',
            'name_association_id' => $traveler['nameAssociationId'] ?? '1',
            'gender'            => $this->shortGender($traveler['gender'] ?? $passport['gender'] ?? ''),
            'dob'               => $traveler['dob'] ?? $passport['birthDate'] ?? '',
            'prefix'            => $traveler['prefix'] ?? '',
            'first_name'        => $traveler['givenName'] ?? '',
            'last_name'         => $traveler['surname'] ?? '',
            'phone'             => $phone['number'] ?? '',
            'phone_label'       => $phone['label'] ?? '',
            'phone_country_code'=> '',
            'phone_location'    => '',
            'email'             => $traveler['emails'][0] ?? '',
            'emails'            => $traveler['emails'] ?? [],
            'phones'            => $traveler['phones'] ?? [],
            'passport_number'   => $passport['documentNumber'] ?? '',
            'passport_type'     => 'Passport',
            'passport_expiry'   => $passport['expiryDate'] ?? '',
            'passport_country'  => $passport['issuingCountryCode'] ?? '',
            'nationality'       => $passport['residenceCountryCode'] ?? '',
            'is_primary_holder' => $passport['isPrimaryDocumentHolder'] ?? false,
            'secure_flight'     => $secureFlightData ? [
                'given_name' => $secureFlightData['givenName'] ?? '',
                'surname'    => $secureFlightData['surname'] ?? '',
                'birth_date' => $secureFlightData['birthDate'] ?? '',
                'gender'     => $secureFlightData['gender'] ?? '',
            ] : null,
            'identity_documents' => $traveler['identityDocuments'] ?? [],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FLIGHTS — root $xml + supplier locator code required
    // ═══════════════════════════════════════════════════════════════════════

    private function parseFlights(SimpleXMLElement $xml, string $supplierLocatorCode): array
    {
        $flights      = [];
        $index        = 10;
        $travelerCount = count($this->safeXpath($xml, '//com:BookingTraveler'));

        foreach ($this->safeXpath($xml, '//air:AirSegment') as $seg) {
            $this->registerNamespaces($seg);
            $attr    = $seg->attributes();
            $fdNodes = $this->safeXpath($seg, './/air:FlightDetails');
            $fdAttr  = !empty($fdNodes) ? $fdNodes[0]->attributes() : null;

            $depTime = (string) ($attr['DepartureTime'] ?? '');
            $arrTime = (string) ($attr['ArrivalTime'] ?? '');

            // ── Connection duration ────────────────────────────────────
            $connDuration = null;
            $connNodes    = $this->safeXpath($seg, './/air:Connection');
            if (!empty($connNodes)) {
                $connAttr     = $connNodes[0]->attributes();
                $connDuration = (int) ($connAttr['Duration'] ?? 0);
            }

            // ── Sell messages ──────────────────────────────────────────
            $sellMessages = [];
            foreach ($this->safeXpath($seg, './/com:SellMessage') as $sm) {
                $msg = trim((string) $sm);
                if (str_contains($msg, 'TERMINAL')) {
                    $sellMessages[] = $msg;
                }
            }

            // ── Meals from SSR ─────────────────────────────────────────
            $meals = $this->parseMealsForSegment($xml, (string) ($attr['Key'] ?? ''));

            $flights[] = [
                'itemId'                => (string) $index,
                // ── FIX: Use supplier locator code as confirmationId ──
                'confirmationId'        => $supplierLocatorCode,
                'sourceType'            => 'ATPCO',
                'flightNumber'          => (int) ($attr['FlightNumber'] ?? 0),
                'airlineCode'           => (string) ($attr['Carrier'] ?? ''),
                'airlineName'           => $this->getAirlineName((string) ($attr['Carrier'] ?? '')),
                'operatingFlightNumber' => (int) ($attr['FlightNumber'] ?? 0),
                'operatingAirlineCode'  => (string) ($attr['Carrier'] ?? ''),
                'operatingAirlineName'  => $this->getAirlineName((string) ($attr['Carrier'] ?? '')),
                'fromAirportCode'       => (string) ($attr['Origin'] ?? ''),
                'toAirportCode'         => (string) ($attr['Destination'] ?? ''),
                'departureDate'         => $this->extractDate($depTime),
                'departureTime'         => $this->extractTime($depTime),
                'arrivalDate'           => $this->extractDate($arrTime),
                'arrivalTime'           => $this->extractTime($arrTime),
                'departureTerminalName' => $fdAttr ? (string) ($fdAttr['OriginTerminal'] ?? '') : '',
                'arrivalTerminalName'   => $fdAttr ? (string) ($fdAttr['DestinationTerminal'] ?? '') : '',
                'numberOfSeats'         => $travelerCount,
                'cabinTypeName'         => ucfirst(strtolower((string) ($attr['CabinClass'] ?? 'Economy'))),
                'cabinTypeCode'         => 'Y',
                'aircraftTypeCode'      => (string) ($attr['Equipment'] ?? ''),
                'aircraftTypeName'      => $this->getAircraftName((string) ($attr['Equipment'] ?? '')),
                'bookingClass'          => (string) ($attr['ClassOfService'] ?? ''),
                'meals'                 => $meals,
                'flightStatusCode'      => (string) ($attr['Status'] ?? 'HK'),
                'flightStatusName'      => $this->mapFlightStatus((string) ($attr['Status'] ?? 'HK')),
                'durationInMinutes'     => (int) ($attr['TravelTime'] ?? 0),
                'distanceInMiles'       => (int) ($attr['Distance'] ?? 0),
                'travelerIndices'       => range(1, max(1, $travelerCount)),
                'identityDocuments'     => [],
                'sellMessages'          => $sellMessages,
                '_segmentKey'           => (string) ($attr['Key'] ?? ''),
                '_group'                => (int) ($attr['Group'] ?? 0),
                '_travelOrder'          => (int) ($attr['TravelOrder'] ?? $index - 9),
                '_marriageGroup'        => (string) ($attr['MarriageGroup'] ?? ''),
                '_providerCode'         => (string) ($attr['ProviderCode'] ?? '1G'),
                '_status'               => (string) ($attr['Status'] ?? 'HK'),
                '_depTimeRaw'           => $depTime,
                '_arrTimeRaw'           => $arrTime,
                '_connDuration'         => $connDuration,
                '_availabilitySource'   => (string) ($attr['AvailabilitySource'] ?? 'S'),
            ];
            $index++;
        }

        return $flights;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MEALS
    // ═══════════════════════════════════════════════════════════════════════

    private function parseMealsForSegment(SimpleXMLElement $xml, string $segmentKey): array
    {
        $meals     = [];
        $mealCodes = [
            'B' => 'Breakfast', 'L' => 'Lunch', 'D' => 'Dinner',
            'S' => 'Snack',     'M' => 'Meal',  'V' => 'Vegetarian Meal',
            'K' => 'Kosher',    'H' => 'Hindu Meal',
        ];

        foreach ($this->safeXpath($xml, '//com:SSR') as $ssr) {
            $attr = $ssr->attributes();
            $type = (string) ($attr['Type'] ?? '');
            if ($type === 'MEAL' || str_starts_with($type, 'ML') || $type === 'VGML' || $type === 'HNML') {
                $code        = substr($type, 0, 1);
                $meals[]     = [
                    'code'        => $code,
                    'description' => $mealCodes[$code] ?? $type,
                ];
            }
        }

        return $meals;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SEGMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildSegments(array $flights, array $travelers): array
    {
        $segments = [];

        // Build identity documents map from travelers
        $allIdentityDocs = [];
        foreach ($travelers as $traveler) {
            foreach ($traveler['identityDocuments'] ?? [] as $doc) {
                $allIdentityDocs[] = [
                    'itemId' => $doc['itemId'] ?? '',
                    'status' => 'Confirmed',
                ];
            }
        }

        foreach ($flights as $idx => $flight) {
            $connection = null;
            if ($flight['_connDuration'] !== null) {
                $connection = ['duration' => $flight['_connDuration']];
            } elseif (isset($flights[$idx + 1])) {
                $currKey = !empty($flight['_marriageGroup'])
                    ? $flight['_marriageGroup']
                    : (string) $flight['_group'];
                $nextKey = !empty($flights[$idx + 1]['_marriageGroup'])
                    ? $flights[$idx + 1]['_marriageGroup']
                    : (string) $flights[$idx + 1]['_group'];

                if ($currKey === $nextKey) {
                    try {
                        $arr        = new \DateTime($flight['_arrTimeRaw']);
                        $dep        = new \DateTime($flights[$idx + 1]['_depTimeRaw']);
                        $diff       = $arr->diff($dep);
                        $connection = ['duration' => $diff->h * 60 + $diff->i + $diff->days * 1440];
                    } catch (\Exception) {
                        $connection = ['duration' => 0];
                    }
                }
            }

            $segments[] = [
                'key'                    => $flight['_segmentKey'],
                'item_id'                => $flight['itemId'],
                'group'                  => $flight['_group'],
                'carrier'                => $flight['airlineCode'],
                'airline_name'           => $flight['airlineName'],
                'operating_carrier'      => $flight['operatingAirlineCode'],
                'operating_airline_name' => $flight['operatingAirlineName'],
                'operating_flight_number'=> (string) $flight['operatingFlightNumber'],
                'flight_number'          => (string) $flight['flightNumber'],
                'cabin_class'            => $flight['cabinTypeName'],
                'cabin_type_code'        => $flight['cabinTypeCode'],
                'class_of_service'       => $flight['bookingClass'],
                'origin'                 => $flight['fromAirportCode'],
                'destination'            => $flight['toAirportCode'],
                'departure_time'         => $flight['_depTimeRaw'],
                'arrival_time'           => $flight['_arrTimeRaw'],
                'departure_terminal'     => $flight['departureTerminalName'],
                'arrival_terminal'       => $flight['arrivalTerminalName'],
                'arrival_gate'           => '',
                'travel_time'            => $flight['durationInMinutes'],
                'distance_miles'         => $flight['distanceInMiles'],
                'equipment'              => $flight['aircraftTypeCode'],
                'aircraft_name'          => $flight['aircraftTypeName'],
                'status'                 => $flight['flightStatusCode'],
                'status_name'            => $flight['flightStatusName'],
                'source_type'            => $flight['sourceType'],
                // ── FIX: confirmation_id = supplier locator code ──
                'confirmation_id'        => $flight['confirmationId'],
                'number_of_seats'        => $flight['numberOfSeats'],
                'traveler_indices'       => $flight['travelerIndices'],
                'meals'                  => $flight['meals'],
                // ── FIX: identity_documents from travelers ──
                'identity_documents'     => $allIdentityDocs,
                'marriage_group'         => $flight['_marriageGroup'],
                'provider_code'          => $flight['_providerCode'],
                'travel_order'           => $flight['_travelOrder'],
                'provider_segment_order' => $flight['_travelOrder'],
                'e_ticketability'        => 'Yes',
                'availability_source'    => $flight['_availabilitySource'],
                'participant_level'      => 'Secure Sell',
                'connection'             => $connection,
                'sell_messages'          => $flight['sellMessages'],
            ];
        }

        return $segments;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ALL SEGMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildAllSegments(array $flights): array
    {
        return array_map(fn($f) => [
            'id'                  => $f['itemId'],
            'type'                => 'FLIGHT',
            'text'                => (string) $f['flightNumber'],
            'vendor_code'         => $f['airlineCode'],
            'start_date'          => $f['departureDate'],
            'start_time'          => $f['departureTime'],
            'start_location_code' => $f['fromAirportCode'],
            'end_date'            => $f['arrivalDate'],
            'end_time'            => $f['arrivalTime'],
            'end_location_code'   => $f['toAirportCode'],
        ], $flights);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  JOURNEYS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildJourneys(array $flights): array
    {
        if (empty($flights)) return [];

        $groups = [];
        foreach ($flights as $f) {
            $groupKey          = !empty($f['_marriageGroup']) ? $f['_marriageGroup'] : (string) $f['_group'];
            $groups[$groupKey][] = $f;
        }

        return array_values(array_map(function ($group) {
            $first = $group[0];
            $last  = end($group);
            return [
                'first_airport_code' => $first['fromAirportCode'],
                'last_airport_code'  => $last['toAirportCode'],
                'departure_date'     => $first['departureDate'],
                'departure_time'     => substr($first['departureTime'], 0, 5),
                'number_of_flights'  => count($group),
            ];
        }, $groups));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PRICING
    // ═══════════════════════════════════════════════════════════════════════

    private function parsePricing(SimpleXMLElement $xml, array $flights): array
    {
        $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
        if (empty($pricingNodes)) {
            return $this->emptyPricing();
        }

        $firstAttr = $pricingNodes[0]->attributes();
        $currency  = $this->extractCurrency((string) ($firstAttr['ApproximateTotalPrice'] ?? $firstAttr['TotalPrice'] ?? 'BDT0'));

        // Grand total
        $grandTotal = 0.0;
        $grandTaxes = 0.0;
        $grandBase  = 0.0;
        foreach ($pricingNodes as $pn) {
            $pna         = $pn->attributes();
            $grandTotal += (float) $this->extractAmount((string) ($pna['ApproximateTotalPrice'] ?? $pna['TotalPrice'] ?? '0'));
            $grandTaxes += (float) $this->extractAmount((string) ($pna['Taxes'] ?? '0'));
            $grandBase  += (float) $this->extractAmount((string) ($pna['ApproximateBasePrice'] ?? '0'));
        }

        // Plating carrier
        $tmNodes       = $this->safeXpath($xml, '//air:TicketingModifiers');
        $platingCarrier = '';
        if (!empty($tmNodes)) {
            $tma            = $tmNodes[0]->attributes();
            $platingCarrier = (string) ($tma['PlatingCarrier'] ?? '');
        }

        // Fare breakdowns
        $fareBreakdowns = [];
        foreach ($pricingNodes as $pn) {
            $pna = $pn->attributes();

            $ptNodes = $this->safeXpath($pn, './/air:PassengerType');
            $paxType = !empty($ptNodes) ? (string) ($ptNodes[0]->attributes()['Code'] ?? 'ADT') : 'ADT';

            $ptotal   = (string) ($pna['ApproximateTotalPrice'] ?? $pna['TotalPrice'] ?? 'BDT0');
            $pbase    = (string) ($pna['ApproximateBasePrice'] ?? $pna['EquivalentBasePrice'] ?? $pna['BasePrice'] ?? 'BDT0');
            $ptaxes   = (string) ($pna['Taxes'] ?? 'BDT0');
            $pcurrency = $this->extractCurrency($ptotal);

            $ptaxBreakdown = [];
            foreach ($this->safeXpath($pn, './/air:TaxInfo') as $tax) {
                $ta              = $tax->attributes();
                $ptaxBreakdown[] = [
                    'code'     => (string) ($ta['Category'] ?? ''),
                    'amount'   => $this->extractAmount((string) ($ta['Amount'] ?? '0')),
                    'currency' => $this->extractCurrency((string) ($ta['Amount'] ?? '')),
                ];
            }

            $pFareConstruction = [];
            foreach ($this->safeXpath($pn, './/air:FareInfo') as $fi) {
                $fia   = $fi->attributes();
                $bagKg = 0;
                $mw2   = $this->safeXpath($fi, './/air:MaxWeight');
                if (!empty($mw2)) {
                    $mwa2 = $mw2[0]->attributes();
                    if (strtolower((string) ($mwa2['Unit'] ?? '')) === 'kilograms') {
                        $bagKg = (int) ($mwa2['Value'] ?? 0);
                    }
                }

                $brandFareCode = '';
                $brandNodes    = $this->safeXpath($fi, './/air:Brand');
                if (!empty($brandNodes)) {
                    $ba            = $brandNodes[0]->attributes();
                    $brandFareCode = (string) ($ba['BrandID'] ?? '');
                }

                $pFareConstruction[] = [
                    'fare_basis'         => (string) ($fia['FareBasis'] ?? ''),
                    'brand_fare_code'    => $brandFareCode,
                    'brand_fare_name'    => '',
                    'brand_program_code' => '',
                    'brand_program_name' => '',
                    'is_current'         => true,
                    'base_amount'        => $this->extractAmount((string) ($fia['Amount'] ?? '0')),
                    'base_currency'      => $this->extractCurrency((string) ($fia['Amount'] ?? '')),
                    'checked_bag_kg'     => $bagKg,
                    'checked_bag_pieces' => null,
                    'cabin_bag_kg'       => null,
                ];
            }

            $pFareCalcNodes = $this->safeXpath($pn, './/air:FareCalc');
            $pFareCalc      = !empty($pFareCalcNodes) ? (string) $pFareCalcNodes[0] : '';

            $fareBreakdowns[] = [
                'record_id'           => (string) (count($fareBreakdowns) + 1),
                'record_type_code'    => 'PQ',
                'record_type_name'    => 'Price Quote',
                'pricing_type_code'   => 'S',
                'pricing_type_name'   => 'System',
                'pricing_status_code' => 'A',
                'pricing_status_name' => 'Active',
                'traveler_indices'    => [],
                'traveler_type'       => $paxType,
                'priced_traveler_type'=> $paxType,
                'is_negotiated'       => false,
                'validating_carrier'  => $platingCarrier,
                'fare_calculation'    => $pFareCalc,
                'subtotal'            => $this->extractAmount($pbase),
                'taxes'               => $this->extractAmount($ptaxes),
                'total'               => $this->extractAmount($ptotal),
                'currency'            => $pcurrency,
                'original_total'      => $this->extractAmount($ptotal),
                'original_currency'   => $pcurrency,
                'tax_breakdown'       => $ptaxBreakdown,
                'fare_construction'   => $pFareConstruction,
                'creation_details'    => [],
            ];
        }

        // Baggage
        $checkedBagKg   = 0;
        $fareInfoNodes  = $this->safeXpath($pricingNodes[0], './/air:FareInfo');
        if (!empty($fareInfoNodes)) {
            $mw = $this->safeXpath($fareInfoNodes[0], './/air:MaxWeight');
            if (!empty($mw)) {
                $mwa = $mw[0]->attributes();
                if (strtolower((string) ($mwa['Unit'] ?? '')) === 'kilograms') {
                    $checkedBagKg = (int) ($mwa['Value'] ?? 0);
                }
            }
        }

        $perLegBaggage  = $this->buildPerLegBaggage($flights, $checkedBagKg);
        $fareOfferFlights = array_map(fn($f) => $f['itemId'], $flights);
        $travelerCount  = count($this->safeXpath($xml, '//com:BookingTraveler'));

        return [
            'grand_total' => [
                'subtotal' => (string) (int) $grandBase,
                'taxes'    => (string) (int) $grandTaxes,
                'total'    => (string) (int) $grandTotal,
                'currency' => $currency,
            ],
            'fare_breakdowns'          => $fareBreakdowns,
            'per_leg_baggage'          => $perLegBaggage,
            'checked_bag_kg'           => $checkedBagKg,
            'cabin_bag_kg'             => 7,
            'checked_baggage_charges'  => [],
            'traveler_indices'         => range(1, max(1, $travelerCount)),
            'fare_offer_flights'       => $fareOfferFlights,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PER LEG BAGGAGE
    // ═══════════════════════════════════════════════════════════════════════

    private function buildPerLegBaggage(array $flights, int $checkedBagKg): array
    {
        if (empty($flights)) return [];

        $allItemIds     = array_map(fn($f) => $f['itemId'], $flights);
        $travelerIndices = $flights[0]['travelerIndices'] ?? [1];

        return [[
            'leg_index'          => 0,
            'traveler_indices'   => $travelerIndices,
            'flight_item_ids'    => $allItemIds,
            'checked_bag_kg'     => $checkedBagKg,
            'checked_bag_pieces' => null,
            'cabin_bag_kg'       => 7,
            'cabin_bag_pieces'   => null,
        ]];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FARE RULES
    // ═══════════════════════════════════════════════════════════════════════

    private function buildFareRules(SimpleXMLElement $xml): array
    {
        $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
        if (empty($pricingNodes)) return [];

        $tmNodes = $this->safeXpath($xml, '//air:TicketingModifiers');
        $airline = '';
        if (!empty($tmNodes)) {
            $tma     = $tmNodes[0]->attributes();
            $airline = (string) ($tma['PlatingCarrier'] ?? '');
        }

        // Origin/Destination from FareInfo
        $fareInfoNodes = $this->safeXpath($pricingNodes[0], './/air:FareInfo');
        $ruleOrigin    = '';
        $ruleDest      = '';
        if (!empty($fareInfoNodes)) {
            $fia       = $fareInfoNodes[0]->attributes();
            $ruleOrigin = (string) ($fia['Origin'] ?? '');
            $ruleDest   = (string) ($fia['Destination'] ?? '');
        }

        $rules = [];
        foreach ($pricingNodes as $p) {
            $attr = $p->attributes();

            $isRefundable   = (string) ($attr['Refundable'] ?? 'false') === 'true';
            $isExchangeable = (string) ($attr['Exchangeable'] ?? 'false') === 'true';

            $ptNodes = $this->safeXpath($p, './/air:PassengerType');
            $paxType = !empty($ptNodes) ? (string) ($ptNodes[0]->attributes()['Code'] ?? 'ADT') : 'ADT';

            $refundPenalties   = [];
            $exchangePenalties = [];

            $cancelPenalty = $this->safeXpath($p, './/air:CancelPenalty');
            if (!empty($cancelPenalty)) {
                $cpa         = $cancelPenalty[0]->attributes();
                $amountNodes = $this->safeXpath($cancelPenalty[0], './/air:Amount');
                $amount      = !empty($amountNodes) ? $this->extractAmount((string) $amountNodes[0]) : '0';
                $curr        = !empty($amountNodes) ? $this->extractCurrency((string) $amountNodes[0]) : 'BDT';
                $refundPenalties[] = [
                    'applicability'    => 'BEFORE_DEPARTURE',
                    'conditions_apply' => false,
                    'has_no_show_cost' => false,
                    'penalty_amount'   => $this->cleanAmount($amount),
                    'penalty_currency' => $curr,
                    'no_show_amount'   => '0',
                    'no_show_currency' => $curr,
                ];
            }

            $changePenalty = $this->safeXpath($p, './/air:ChangePenalty');
            if (!empty($changePenalty)) {
                $cpa         = $changePenalty[0]->attributes();
                $amountNodes = $this->safeXpath($changePenalty[0], './/air:Amount');
                $amount      = !empty($amountNodes) ? $this->extractAmount((string) $amountNodes[0]) : '0';
                $curr        = !empty($amountNodes) ? $this->extractCurrency((string) $amountNodes[0]) : 'BDT';
                $exchangePenalties[] = [
                    'applicability'    => 'BEFORE_DEPARTURE',
                    'conditions_apply' => false,
                    'has_no_show_cost' => false,
                    'penalty_amount'   => $this->cleanAmount($amount),
                    'penalty_currency' => $curr,
                    'no_show_amount'   => '0',
                    'no_show_currency' => $curr,
                ];
            }

            $rules[] = [
                'origin'             => $ruleOrigin,
                'destination'        => $ruleDest,
                'airline'            => $airline,
                'passenger_code'     => $paxType,
                'is_refundable'      => $isRefundable,
                'is_changeable'      => $isExchangeable,
                'refund_penalties'   => $refundPenalties,
                'exchange_penalties' => $exchangePenalties,
            ];
        }

        return $rules;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTION STATUS
    // ═══════════════════════════════════════════════════════════════════════

    private function parseActionStatus(SimpleXMLElement $ur, SimpleXMLElement $xml, array $remarks): array
    {
        $nodes = $this->safeXpath($ur, './/com:ActionStatus');

        $ticketDate = '';

        // ── Priority 1: ActionStatus.TicketDate ──────────────────────
        if (!empty($nodes)) {
            $attr       = $nodes[0]->attributes();
            $ticketDate = (string) ($attr['TicketDate'] ?? '');
        }

        // ── Priority 2: ADTK Remark (overrides ActionStatus) ─────────
        foreach ($remarks as $remark) {
            $text = strtoupper($remark['text']);
            if (!str_contains($text, 'ADTK')) continue;

            $months = [
                'JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04',
                'MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08',
                'SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12',
            ];

            // Pattern C: BY 24APR26 1204DAC
            if (preg_match('/BY\s+(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\s+(\d{4})[A-Z]{3}/i', $text, $m)) {
                $day        = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $monthNum   = $months[strtoupper($m[2])] ?? '01';
                $year       = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];
                $hour       = substr($m[4], 0, 2);
                $min        = substr($m[4], 2, 2);
                $ticketDate = "{$year}-{$monthNum}-{$day}T{$hour}:{$min}:00.000+06:00";
                break;
            }

            // Pattern A: GBYDAC22APR26/1101
            if (preg_match('/[A-Z]{3}(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\/(\d{4})/i', $text, $m)) {
                $day        = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $monthNum   = $months[strtoupper($m[2])] ?? '01';
                $year       = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];
                $hour       = substr($m[4], 0, 2);
                $min        = substr($m[4], 2, 2);
                $ticketDate = "{$year}-{$monthNum}-{$day}T{$hour}:{$min}:00.000+06:00";
                break;
            }

            // Pattern B: BY 22APR 1101 (no year)
            if (preg_match('/BY\s+(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{4})/i', $text, $m)) {
                $day        = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $monthNum   = $months[strtoupper($m[2])] ?? '01';
                $hour       = substr($m[3], 0, 2);
                $min        = substr($m[3], 2, 2);
                $year       = now()->year;
                $ticketDate = "{$year}-{$monthNum}-{$day}T{$hour}:{$min}:00.000+06:00";
                break;
            }
        }

        // ── Priority 3: AirPricingInfo.LatestTicketingTime ───────────
        if (empty($ticketDate)) {
            $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
            if (!empty($pricingNodes)) {
                $pna = $pricingNodes[0]->attributes();
                $ticketDate = (string) ($pna['LatestTicketingTime']
                    ?? $pna['TrueLastDateToTicket']
                    ?? '');
            }
        }

        $key          = '';
        $type         = 'TAU';
        $providerCode = '1G';

        if (!empty($nodes)) {
            $attr         = $nodes[0]->attributes();
            $key          = (string) ($attr['Key'] ?? '');
            $type         = (string) ($attr['Type'] ?? 'TAU');
            $providerCode = (string) ($attr['ProviderCode'] ?? '1G');
        }

        return [
            'key'           => $key,
            'type'          => $type,
            'ticket_date'   => $ticketDate,
            'provider_code' => $providerCode,
        ];
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  AGENCY INFO
    // ═══════════════════════════════════════════════════════════════════════

    private function parseAgencyInfo(SimpleXMLElement $ur): array
    {
        $nodes = $this->safeXpath($ur, './/com:AgencyInfo/com:AgentAction');
        if (empty($nodes)) return [
            'action_type' => '', 'agent_code' => '',
            'branch_code' => '', 'agency_code' => '', 'event_time' => '',
        ];

        $attr = $nodes[0]->attributes();
        return [
            'action_type' => (string) ($attr['ActionType'] ?? ''),
            'agent_code'  => (string) ($attr['AgentCode'] ?? ''),
            'branch_code' => (string) ($attr['BranchCode'] ?? ''),
            'agency_code' => (string) ($attr['AgencyCode'] ?? ''),
            'event_time'  => (string) ($attr['EventTime'] ?? ''),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REMARKS + WARNINGS
    // ═══════════════════════════════════════════════════════════════════════

    private function parseRemarks(SimpleXMLElement $ur): array
    {
        $remarks = [];
        foreach ($this->safeXpath($ur, './/com:GeneralRemark') as $r) {
            $attr      = $r->attributes();
            $dataNodes = $this->safeXpath($r, './/com:RemarkData');
            $remarks[] = [
                'type' => (string) ($attr['TypeInGds'] ?? ''),
                'text' => !empty($dataNodes) ? (string) $dataNodes[0] : '',
            ];
        }
        return $remarks;
    }

    private function parseWarnings(SimpleXMLElement $xml): array
    {
        $warnings = [];
        foreach ($this->safeXpath($xml, '//com:ResponseMessage') as $w) {
            $attr = $w->attributes();
            if ((string) ($attr['Type'] ?? '') === 'Warning') {
                $warnings[] = (string) $w;
            }
        }
        return $warnings;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CONTACT INFO + SPECIAL SERVICES
    // ═══════════════════════════════════════════════════════════════════════

    private function buildContactInfo(array $travelers): array
    {
        $phones = [];
        $emails = [];
        foreach ($travelers as $t) {
            foreach ($t['phones'] as $p) {
                $entry = $p['number'] . '-' . $p['label'] . '-1.1';
                if (!in_array($entry, $phones)) {
                    $phones[] = $entry;
                }
            }
            foreach ($t['emails'] as $e) {
                if (!in_array($e, $emails)) {
                    $emails[] = $e;
                }
            }
        }
        return ['phones' => $phones, 'emails' => $emails];
    }

    private function buildSpecialServices(array $travelers): array
    {
        $services = [];
        foreach ($travelers as $idx => $traveler) {
            $travelerIndex = $idx + 1;

            foreach ($traveler['emails'] as $email) {
                $services[] = [
                    'code'            => 'CTCE',
                    'name'            => 'Passenger contact information e-mail address',
                    'message'         => '/' . $email,
                    'status_code'     => 'HK',
                    'status_name'     => 'Confirmed',
                    'traveler_indices'=> [$travelerIndex],
                ];
            }

            foreach ($traveler['phones'] as $phone) {
                $services[] = [
                    'code'            => 'CTCM',
                    'name'            => 'Passenger contact information mobile phone number',
                    'message'         => '/' . $phone['number'],
                    'status_code'     => 'HK',
                    'status_name'     => 'Confirmed',
                    'traveler_indices'=> [$travelerIndex],
                ];
            }

            foreach ($traveler['identityDocuments'] as $doc) {
                if (($doc['documentType'] ?? '') === 'PASSPORT') {
                    $services[] = [
                        'code'            => 'DOCS',
                        'name'            => 'API-Passenger Travel Document',
                        'message'         => sprintf('/P/%s/%s/%s/%s/%s/%s/%s/%s',
                            $doc['issuingCountryCode'] ?? '',
                            $doc['documentNumber'] ?? '',
                            $doc['residenceCountryCode'] ?? '',
                            $this->formatDocDate($doc['birthDate'] ?? ''),
                            $this->shortGender($doc['gender'] ?? ''),
                            $this->formatDocDate($doc['expiryDate'] ?? ''),
                            $doc['surname'] ?? '',
                            $doc['givenName'] ?? ''
                        ),
                        'status_code'     => 'HK',
                        'status_name'     => 'Confirmed',
                        'traveler_indices'=> [$travelerIndex],
                    ];
                }
            }
        }
        return $services;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  IDENTITY DOCUMENTS (from SSR DOCS)
    // ═══════════════════════════════════════════════════════════════════════

    private function parseIdentityDocuments(array $ssrs, array $name, $travelerAttr): array
    {
        $docs = [];
        foreach ($ssrs as $ssr) {
            if ($ssr['type'] !== 'DOCS') continue;

            $freeText = ltrim($ssr['free_text'], ' ');
            $parts    = explode('/', $freeText);

            // DOCS format: P/CountryIssue/DocNum/CountryResidence/DOB/Gender/Expiry/Surname/GivenName
            if (count($parts) < 8) continue;

            // Skip DB (Date of Birth only) type SSR
            if (trim($parts[0] ?? '') === 'DB') continue;

            $docs[] = [
                'documentNumber'        => $parts[2] ?? '',
                'documentType'          => 'PASSPORT',
                'passportType'          => 'Passport',
                'expiryDate'            => $this->parseDocDate($parts[6] ?? ''),
                'issuingCountryCode'    => $parts[1] ?? '',
                'residenceCountryCode'  => $parts[3] ?? '',
                'givenName'             => trim($parts[8] ?? $name['first']),
                'surname'               => trim($parts[7] ?? $name['last']),
                'birthDate'             => isset($travelerAttr['DOB']) ? (string) $travelerAttr['DOB'] : '',
                'gender'                => $this->mapGender((string) ($travelerAttr['Gender'] ?? '')),
                'isPrimaryDocumentHolder' => false,
                'itemId'                => md5($ssr['free_text']),
            ];

            $docs[] = [
                'documentType' => 'SECURE_FLIGHT_PASSENGER_DATA',
                'givenName'    => trim($parts[8] ?? $name['first']),
                'surname'      => trim($parts[7] ?? $name['last']),
                'birthDate'    => isset($travelerAttr['DOB']) ? (string) $travelerAttr['DOB'] : '',
                'gender'       => $this->mapGender((string) ($travelerAttr['Gender'] ?? '')),
                'itemId'       => md5($ssr['free_text'] . '_sfpd'),
            ];
        }
        return $docs;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  XML HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function safeXpath(SimpleXMLElement $xml, string $path): array
    {
        $this->registerNamespaces($xml);
        $result = $xml->xpath($path);
        return ($result === false || $result === null) ? [] : $result;
    }

    private function parseTravelerName(SimpleXMLElement $traveler): array
    {
        $nodes = $this->safeXpath($traveler, './/com:BookingTravelerName');
        if (empty($nodes)) return ['prefix' => '', 'first' => '', 'middle' => '', 'last' => ''];

        $attr = $nodes[0]->attributes();
        return [
            'prefix' => (string) ($attr['Prefix'] ?? ''),
            'first'  => (string) ($attr['First'] ?? ''),
            'middle' => (string) ($attr['Middle'] ?? ''),
            'last'   => (string) ($attr['Last'] ?? ''),
        ];
    }

    private function parsePhones(SimpleXMLElement $element): array
    {
        $phones = [];
        foreach ($this->safeXpath($element, './/com:PhoneNumber') as $p) {
            $attr     = $p->attributes();
            $phones[] = [
                'type'   => (string) ($attr['Type'] ?? ''),
                'number' => (string) ($attr['Number'] ?? ''),
            ];
        }
        return $phones;
    }

    private function parseEmails(SimpleXMLElement $element): array
    {
        $emails = [];
        foreach ($this->safeXpath($element, './/com:Email') as $e) {
            $attr     = $e->attributes();
            $emails[] = [
                'type'  => (string) ($attr['Type'] ?? ''),
                'email' => (string) ($attr['EmailID'] ?? ''),
            ];
        }
        return $emails;
    }

    private function parseSSRs(SimpleXMLElement $traveler): array
    {
        $ssrs = [];
        foreach ($this->safeXpath($traveler, './/com:SSR') as $s) {
            $attr   = $s->attributes();
            $ssrs[] = [
                'type'      => (string) ($attr['Type'] ?? ''),
                'status'    => (string) ($attr['Status'] ?? ''),
                'free_text' => (string) ($attr['FreeText'] ?? ''),
                'carrier'   => (string) ($attr['Carrier'] ?? ''),
            ];
        }
        return $ssrs;
    }

    private function checkTicketed(SimpleXMLElement $xml): bool
    {
        return !empty($this->safeXpath($xml, '//air:TicketInfo'));
    }

    private function parseSoapFault(SimpleXMLElement $fault): array
    {
        return [
            'success'      => false,
            'soap_fault'   => true,
            'fault_code'   => (string) ($fault->faultcode ?? ''),
            'fault_string' => trim((string) ($fault->faultstring ?? '')),
        ];
    }

    private function cleanXmlString(string $xml): string
    {
        $xml = preg_replace('/^"""[\s]*/u', '', $xml);
        $xml = preg_replace('/[\s]*"""$/u', '', $xml);
        $xml = preg_replace('/[◀▶]/u', '', $xml);
        $xml = str_replace(
            ["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"],
            ['"', '"', "'", "'"],
            $xml
        );
        $xml = trim($xml, '"\'');
        $xml = trim($xml);

        if (!str_starts_with($xml, '<')) {
            $pos = strpos($xml, '<');
            if ($pos !== false) $xml = substr($xml, $pos);
        }

        return $xml;
    }

    private function registerNamespaces(SimpleXMLElement $xml): void
    {
        $xml->registerXPathNamespace('SOAP', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('univ', 'http://www.travelport.com/schema/universal_v52_0');
        $xml->registerXPathNamespace('com',  'http://www.travelport.com/schema/common_v52_0');
        $xml->registerXPathNamespace('air',  'http://www.travelport.com/schema/air_v52_0');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VALUE HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function extractDate(string $datetime): string
    {
        return strlen($datetime) >= 10 ? substr($datetime, 0, 10) : '';
    }

    private function extractTime(string $datetime, bool $withSeconds = true): string
    {
        if (strlen($datetime) < 19) return '';
        $time = substr($datetime, 11, 8);
        return $withSeconds ? $time : substr($time, 0, 5);
    }

    private function extractAmount(string $value): string
    {
        return preg_replace('/^[A-Z]{3}/', '', $value);
    }

    private function cleanAmount(string $value): string
    {
        if (strpos($value, '.') !== false) {
            $value = rtrim(rtrim($value, '0'), '.');
        }
        return $value;
    }

    private function extractCurrency(string $value): string
    {
        if (preg_match('/^([A-Z]{3})/', $value, $m)) return $m[1];
        return 'BDT';
    }

    private function parseDocDate(string $date): string
    {
        if (strlen($date) !== 7) return '';
        $months = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12',
        ];
        $day   = substr($date, 0, 2);
        $month = $months[strtoupper(substr($date, 2, 3))] ?? '01';
        $year  = '20' . substr($date, 5, 2);
        return "$year-$month-$day";
    }

    private function formatDocDate(string $date): string
    {
        if (empty($date)) return '';
        $months = [
            '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
            '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
            '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC',
        ];
        $parts = explode('-', $date);
        if (count($parts) !== 3) return '';
        return $parts[2] . ($months[$parts[1]] ?? 'JAN') . substr($parts[0], 2);
    }

    private function emptyPricing(): array
    {
        return [
            'grand_total'             => ['subtotal' => '0', 'taxes' => '0', 'total' => '0', 'currency' => 'BDT'],
            'fare_breakdowns'         => [],
            'per_leg_baggage'         => [],
            'checked_bag_kg'          => 0,
            'cabin_bag_kg'            => 7,
            'checked_baggage_charges' => [],
            'traveler_indices'        => [],
            'fare_offer_flights'      => [],
        ];
    }

    private function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    private function mapTravelerType(string $code): string
    {
        return match ($code) {
            'ADT'        => 'ADULT',
            'CNN', 'CHD' => 'CHILD',
            'INF'        => 'INFANT',
            default      => 'ADULT',
        };
    }

    private function mapGender(string $g): string
    {
        return match (strtoupper($g)) {
            'M' => 'MALE',
            'F' => 'FEMALE',
            default => 'MALE',
        };
    }

    private function shortGender(string $g): string
    {
        return match (strtoupper($g)) {
            'MALE', 'M'   => 'M',
            'FEMALE', 'F' => 'F',
            default       => $g,
        };
    }

    private function mapFlightStatus(string $s): string
    {
        return match ($s) {
            'HK', 'KK' => 'Confirmed',
            'NN'       => 'Need',
            default    => 'Confirmed',
        };
    }

    private function getAircraftName(string $code): string
    {
        return match (strtoupper($code)) {
            '789'       => 'BOEING 787-9',
            '788'       => 'BOEING 787-8',
            '77W', '777'=> 'BOEING 777',
            '738', '73H'=> 'BOEING 737-800',
            '320', '32A'=> 'AIRBUS A320',
            '32N'       => 'AIRBUS A320NEO',
            '321', '32B'=> 'AIRBUS A321',
            '32Q'       => 'AIRBUS A321NEO',
            '319'       => 'AIRBUS A319',
            '333'       => 'AIRBUS A330-300',
            '332'       => 'AIRBUS A330-200',
            '388'       => 'AIRBUS A380-800',
            'DH8'       => 'DEHAVILLAND DASH 8',
            'DH4'       => 'DEHAVILLAND DASH 400',
            'AT7'       => 'ATR 72',
            'AT4'       => 'ATR 42',
            'CR9'       => 'CANADAIR REGIONAL JET',
            'E90'       => 'EMBRAER 190',
            'E75'       => 'EMBRAER 175',
            default     => $code,
        };
    }

    private function getAirlineName(string $code): string
    {
        return match ($code) {
            'AI' => 'AIR INDIA',
            'GF' => 'GULF AIR',
            'BG' => 'BIMAN BANGLADESH',
            'EK' => 'EMIRATES',
            'QR' => 'QATAR AIRWAYS',
            'EY' => 'ETIHAD AIRWAYS',
            'TK' => 'TURKISH AIRLINES',
            'SQ' => 'SINGAPORE AIRLINES',
            'BS' => 'US-BANGLA AIRLINES',
            'VQ' => 'NOVOAIR',
            'OB' => 'REGENT AIRWAYS',
            'FZ' => 'FLYDUBAI',
            'G8' => 'GO FIRST',
            '6E' => 'INDIGO',
            'SG' => 'SPICEJET',
            default => $code,
        };
    }

    private function buildError(string $message): array
    {
        return ['success' => false, 'error' => $message];
    }
}
