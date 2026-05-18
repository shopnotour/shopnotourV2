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

            $ur     = $universalRecord[0];
            $this->registerNamespaces($ur);
            $urAttr = $ur->attributes();

            // ── Core Parsing ──────────────────────────────────────────────
            $providerInfo    = $this->parseProviderReservationInfo($xml);
            $supplierLocator = $this->parseSupplierLocator($xml);
            $airReservation  = $this->parseAirReservation($xml);
            $travelers       = $this->parseBookingTravelers($ur);
            $flights         = $this->parseFlights($xml, $supplierLocator['locator_code'] ?? '');
            $pricing         = $this->parsePricing($xml, $flights);
            $agencyInfo      = $this->parseAgencyInfo($ur);
            $remarks         = $this->parseRemarks($ur);
            $actionStatus    = $this->parseActionStatus($ur, $xml, $remarks);

            // ── Derived ───────────────────────────────────────────────────
            $journeys    = $this->buildJourneys($flights);
            $allSegments = $this->buildAllSegments($flights);
            $contactInfo = $this->buildContactInfo($travelers);
            $specialSvcs = $this->buildSpecialServices($travelers);

            $startDate = $flights[0]['departureDate'] ?? '';
            $endDate   = !empty($flights) ? end($flights)['arrivalDate'] : '';

            $providerLocatorCode = $providerInfo['locator_code'] ?? '';

            // ── Plating carrier (for ticket airline_code fallback) ─────────
            $platingCarrier = $this->parsePlatingCarrier($xml);

            return [
                'success'           => true,
                'trace_id'          => (string) rand(1000, 9999),
                'transaction_id'    => strtoupper(md5($providerLocatorCode . microtime())),
                'response_time'     => 0,

                'booking_id'        => $providerLocatorCode,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'is_cancelable'     => true,
                'is_ticketed'       => $this->checkTicketed($xml),
                'timestamp'         => now()->toIso8601String(),
                'booking_signature' => hash('sha256', $providerLocatorCode . microtime()),

                'contact_info' => $contactInfo,

                'universal_record' => [
                    'locator_code'    => $providerLocatorCode,
                    'ur_locator_code' => (string) ($urAttr['LocatorCode'] ?? ''),
                    'version'         => (int) ($urAttr['Version'] ?? 0),
                    'status'          => (string) ($urAttr['Status'] ?? 'Active'),
                ],

                'provider_reservation' => [
                    'key'               => $providerInfo['key']              ?? '',
                    'provider_code'     => $providerInfo['provider_code']    ?? '1G',
                    'locator_code'      => $providerLocatorCode,
                    'create_date'       => $providerInfo['create_date']      ?? '',
                    'modified_date'     => $providerInfo['modified_date']    ?? '',
                    'host_create_date'  => $providerInfo['host_create_date'] ?? '',
                    'owning_pcc'        => $providerInfo['owning_pcc']       ?? '',
                    'home_pcc'          => $providerInfo['owning_pcc']       ?? '',
                    'prime_host_id'     => '1G',
                    'number_of_updates' => 0,
                    'display_details'   => $providerInfo['details']          ?? [],
                ],

                'supplier_locator' => [
                    'supplier_code'    => $supplierLocator['supplier_code']    ?? '',
                    'locator_code'     => $supplierLocator['locator_code']     ?? '',
                    'create_date_time' => $supplierLocator['create_date_time'] ?? '',
                ],

                'air_reservation' => [
                    'locator_code'  => $providerLocatorCode,
                    'create_date'   => $airReservation['create_date']   ?? '',
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

                'special_services' => $specialSvcs,
                'pricing'          => $pricing,
                'fare_rules'       => $this->buildFareRules($xml),
                'flight_tickets'   => $this->parseFlightTickets($xml, $platingCarrier),
                'accounting_items' => [],

                // ── TravelPort extra (Sabre তে নেই, পরে কাজে লাগতে পারে) ──
                '_tp_extra' => [
                    'osi_remarks'          => $this->parseOSI($ur),
                    'action_status_remark' => $this->parseActionStatusRemark($ur),
                    'purge_date'           => $providerInfo['details']['p_n_r_purge_date'] ?? '',
                    'iata_number'          => $providerInfo['details']['creating_agency_i_a_t_a'] ?? '',
                    'creating_agent_duty'  => $providerInfo['details']['creating_agent_duty'] ?? '',
                ],
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

    // ═══════════════════════════════════════════════════════════════════════
    //  FLIGHT TICKETS  (Sabre buildFlightTickets এর মতো — exchange-aware)
    // ═══════════════════════════════════════════════════════════════════════

    private function parseFlightTickets(SimpleXMLElement $xml, string $platingCarrier = ''): array
    {
        $tickets = [];

        // ── Step 1: Traveler Key → Index map ──────────────────────────────
        $travelerKeyMap = [];
        foreach ($this->safeXpath($xml, '//com:BookingTraveler') as $idx => $t) {
            $attr = $t->attributes();
            $key  = (string) ($attr['Key'] ?? '');
            if ($key) {
                $travelerKeyMap[$key] = $idx + 1; // 1-based
            }
        }

        // ── Step 2: AirPricingInfo Key → pricing data map ──────────────────
        $pricingMap = [];
        foreach ($this->safeXpath($xml, '//air:AirPricingInfo') as $pn) {
            $pna = $pn->attributes();
            $key = (string) ($pna['Key'] ?? '');
            if ($key) {
                $totalStr   = (string) ($pna['ApproximateTotalPrice'] ?? $pna['TotalPrice'] ?? '');
                $taxStr     = (string) ($pna['Taxes'] ?? '');
                $baseStr    = (string) ($pna['ApproximateBasePrice'] ?? $pna['EquivalentBasePrice'] ?? '');
                $pricingMap[$key] = [
                    'total'    => $this->extractAmount($totalStr),
                    'taxes'    => $this->extractAmount($taxStr),
                    'subtotal' => $this->extractAmount($baseStr),
                    'currency' => $this->extractCurrency($totalStr ?: 'BDT0'),
                ];
            }
        }

        // ── Step 3: TicketingModifiers PlatingCarrier map ──────────────────
        // key = TicketingModifiersRef Key → PlatingCarrier
        $tmCarrierMap = [];
        foreach ($this->safeXpath($xml, '//air:TicketingModifiers') as $tm) {
            $tma = $tm->attributes();
            $key = (string) ($tma['Key'] ?? '');
            if ($key) {
                $tmCarrierMap[$key] = (string) ($tma['PlatingCarrier'] ?? $platingCarrier);
            }
        }

        // ── Step 4: SSR TKNE → coupon map (segmentRef → coupon info) ──────
        // FreeText format: "9979412260059C1" → ticketNumber + coupon
        $tkneMap = []; // travelerRef → [ ['ticket_number'=>..., 'coupon_number'=>..., 'segment_ref'=>...], ... ]
        foreach ($this->safeXpath($xml, '//com:BookingTraveler') as $t) {
            $tAttr       = $t->attributes();
            $travelerRef = (string) ($tAttr['Key'] ?? '');

            foreach ($this->safeXpath($t, './/com:SSR') as $ssr) {
                $sAttr = $ssr->attributes();
                if ((string) ($sAttr['Type'] ?? '') !== 'TKNE') continue;

                $freeText   = (string) ($sAttr['FreeText'] ?? '');
                $segmentRef = (string) ($sAttr['SegmentRef'] ?? '');

                // e.g. "9979412260059C1" — last char(s) after C is coupon number
                if (preg_match('/^(\d+)C(\d+)$/', $freeText, $m)) {
                    $tkneMap[$travelerRef][] = [
                        'ticket_number' => $m[1],
                        'coupon_number' => (int) $m[2],
                        'segment_ref'   => $segmentRef,
                        'status'        => (string) ($sAttr['Status'] ?? 'HK'),
                    ];
                }
            }
        }

        // ── Step 5: TicketInfo → main ticket data ──────────────────────────
        foreach ($this->safeXpath($xml, '//air:TicketInfo') as $ticket) {
            $attr        = $ticket->attributes();
            $travelerRef = (string) ($attr['BookingTravelerRef'] ?? '');
            $ticketNum   = (string) ($attr['Number'] ?? '');
            $issueDate   = (string) ($attr['TicketIssueDate'] ?? '');
            $pricingRef  = (string) ($attr['AirPricingInfoRef'] ?? '');

            // Name
            $nameNodes = $this->safeXpath($ticket, './/com:Name');
            $passengerName = [];
            if (!empty($nameNodes)) {
                $na = $nameNodes[0]->attributes();
                $passengerName = [
                    'first' => (string) ($na['First'] ?? ''),
                    'last'  => (string) ($na['Last']  ?? ''),
                ];
            }

            // Traveler index (1-based, Sabre মতো)
            $travelerIndex = $travelerKeyMap[$travelerRef] ?? null;

            // Ticket status — TravelPort: S=Issued, N=Issued, V=Voided, R=Refunded, E=Exchanged
            $rawStatus = strtoupper((string) ($attr['Status'] ?? ''));
            $statusName = match ($rawStatus) {
                'S', 'N' => 'Issued',
                'V'      => 'Voided',
                'R'      => 'Refunded',
                'E'      => 'Exchanged',
                'X'      => 'Expired',
                default  => 'Issued',
            };
            $statusCode = match ($rawStatus) {
                'S', 'N' => 'TE',
                'V'      => 'TV',
                'R'      => 'TR',
                'E'      => 'TX',
                'X'      => 'TX',
                default  => 'TE',
            };

            // Airline code — TicketingModifiersRef → PlatingCarrier
            $tmRefKey   = (string) ($attr['TicketingModifiersRef'] ?? '');
            $airlineCode = $tmCarrierMap[$tmRefKey] ?? $platingCarrier;

            // Payment — AirPricingInfoRef থেকে match
            $payment = null;
            if (!empty($pricingRef) && isset($pricingMap[$pricingRef])) {
                $pm = $pricingMap[$pricingRef];
                $payment = [
                    'subtotal' => $pm['subtotal'],
                    'taxes'    => $pm['taxes'],
                    'total'    => $pm['total'],
                    'currency' => $pm['currency'],
                ];
            }

            // Flight coupons — SSR TKNE থেকে (Sabre flightCoupons এর মতো)
            $flightCoupons = [];
            foreach ($tkneMap[$travelerRef] ?? [] as $tkne) {
                if ($tkne['ticket_number'] !== $ticketNum) continue;

                // Coupon status: HK = Not Flown (I), TKNE status থেকে map করো
                $couponStatusCode = 'I'; // Not Flown (default for issued tickets)
                $couponStatusName = 'Not Flown';

                $flightCoupons[] = [
                    'item_id'            => (string) $tkne['coupon_number'],
                    'coupon_status'      => $couponStatusName,
                    'coupon_status_code' => $couponStatusCode,
                    // TravelPort extra
                    '_segment_ref'       => $tkne['segment_ref'],
                    '_tkne_status'       => $tkne['status'],
                ];
            }

            // ── Exchange detection (Sabre buildFlightTickets এর মতো) ──────
            $exchangeCodes = ['E', 'R', 'V'];
            $activeCodes   = ['I', 'O', 'F', 'B'];

            // Ticket level: E/R/V status মানে exchanged/refunded/voided
            $isExchanged       = in_array($rawStatus, ['E', 'R', 'V']);
            $allExchanged      = $isExchanged; // ticket level এ সব coupon effectively exchanged
            $isPartialExchange = false;

            // Coupon level check
            if (!empty($flightCoupons)) {
                $hasExchangeCoupon = collect($flightCoupons)->some(
                    fn($c) => in_array($c['coupon_status_code'] ?? '', $exchangeCodes)
                );
                $allCouponsExchanged = collect($flightCoupons)->every(
                    fn($c) => in_array($c['coupon_status_code'] ?? '', $exchangeCodes)
                );
                $hasActiveCoupon = collect($flightCoupons)->some(
                    fn($c) => in_array($c['coupon_status_code'] ?? '', $activeCodes)
                );

                if ($hasExchangeCoupon) {
                    $isExchanged       = true;
                    $allExchanged      = $allCouponsExchanged;
                    $isPartialExchange = $hasExchangeCoupon && $hasActiveCoupon && !$allCouponsExchanged;
                }
            }

            // Issue date — শুধু date part (Sabre এর 'date' key এর মতো)
            $issueDateFormatted = !empty($issueDate) ? substr($issueDate, 0, 10) : null;

            // Commission — TravelPort TicketInfo এ নেই, null রাখো
            // (accounting_items এ পরে add করা যাবে)
            $commissionAmount   = null;
            $commissionCurrency = null;
            $commissionPct      = null;

            // TicketingModifiers থেকে commission নেওয়ার চেষ্টা
            foreach ($this->safeXpath($xml, '//air:TicketingModifiers') as $tm) {
                $tma = $tm->attributes();
                if ((string) ($tma['Key'] ?? '') === $tmRefKey) {
                    $commNodes = $this->safeXpath($tm, './/com:Commission');
                    if (!empty($commNodes)) {
                        $ca                 = $commNodes[0]->attributes();
                        $commissionPct      = (string) ($ca['Percentage'] ?? null);
                        $commissionAmount   = null; // percentage থেকে amount calculate করতে payment লাগবে
                        $commissionCurrency = $payment['currency'] ?? null;

                        // Commission amount = base * percentage / 100
                        if ($commissionPct && isset($payment['subtotal'])) {
                            $commissionAmount = number_format(
                                (float)$payment['subtotal'] * (float)$commissionPct / 100,
                                2, '.', ''
                            );
                        }
                    }
                    break;
                }
            }

            $tickets[] = [
                // ── Sabre compatible keys ──────────────────────────────────
                'number'              => $ticketNum,
                'date'                => $issueDateFormatted,
                'airline_code'        => $airlineCode,
                'agency_iata'         => (string) ($attr['IATANumber'] ?? ''),
                'traveler_index'      => $travelerIndex,
                'ticket_status'       => $statusName,
                'ticket_status_code'  => $statusCode,
                'ticketing_pcc'       => (string) ($attr['TicketingAgentSignOn'] ?? ''),
                'is_exchanged'        => $isExchanged,
                'is_partial_exchange' => $isPartialExchange,
                'all_exchanged'       => $allExchanged,
                'payment'             => $payment,
                'flight_coupons'      => $flightCoupons,
                'commission_amount'   => $commissionAmount,
                'commission_currency' => $commissionCurrency,
                'commission_percentage' => $commissionPct,

                // ── TravelPort extra (পরে কাজে লাগতে পারে) ──────────────
                '_tp_extra' => [
                    'passenger_name'   => $passengerName,
                    'traveler_ref'     => $travelerRef,
                    'pricing_info_ref' => $pricingRef,
                    'raw_status'       => $rawStatus,
                    'country_code'     => (string) ($attr['CountryCode'] ?? ''),
                ],
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
            'key'              => (string) ($attr['Key']          ?? ''),
            'provider_code'    => (string) ($attr['ProviderCode'] ?? '1G'),
            'locator_code'     => (string) ($attr['LocatorCode']  ?? ''),
            'create_date'      => (string) ($attr['CreateDate']   ?? ''),
            'modified_date'    => (string) ($attr['ModifiedDate'] ?? ''),
            'host_create_date' => (string) ($attr['HostCreateDate'] ?? ''),
            'owning_pcc'       => (string) ($attr['OwningPCC']    ?? ''),
            'details'          => $details,
        ];
    }

    private function parseSupplierLocator(SimpleXMLElement $xml): array
    {
        $nodes = $this->safeXpath($xml, '//com:SupplierLocator');
        if (empty($nodes)) return [];

        $attr = $nodes[0]->attributes();
        return [
            'supplier_code'    => (string) ($attr['SupplierCode']        ?? ''),
            'locator_code'     => (string) ($attr['SupplierLocatorCode'] ?? ''),
            'create_date_time' => (string) ($attr['CreateDateTime']      ?? ''),
        ];
    }

    private function parseAirReservation(SimpleXMLElement $xml): array
    {
        $nodes = $this->safeXpath($xml, '//air:AirReservation');
        if (empty($nodes)) return [];

        $attr = $nodes[0]->attributes();
        return [
            'locator_code'  => (string) ($attr['LocatorCode']  ?? ''),
            'create_date'   => (string) ($attr['CreateDate']   ?? ''),
            'modified_date' => (string) ($attr['ModifiedDate'] ?? ''),
        ];
    }

    private function parsePlatingCarrier(SimpleXMLElement $xml): string
    {
        $nodes = $this->safeXpath($xml, '//air:TicketingModifiers');
        if (empty($nodes)) return '';
        return (string) ($nodes[0]->attributes()['PlatingCarrier'] ?? '');
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
            $seats  = $this->parseSeatAssignments($t);
            $fqtvs  = $this->parseFQTV($t);

            $travelers[] = [
                'givenName'         => $name['first'],
                'surname'           => $name['last'],
                'prefix'            => $name['prefix'],
                'type'              => $this->mapTravelerType((string) ($attr['TravelerType'] ?? 'ADT')),
                'passengerCode'     => (string) ($attr['TravelerType'] ?? 'ADT'),
                'nameAssociationId' => (string) $index,
                'dob'               => (string) ($attr['DOB']    ?? ''),
                'gender'            => (string) ($attr['Gender'] ?? ''),
                'emails'            => array_column($emails, 'email'),
                'phones'            => array_map(fn($p) => [
                    'number'       => $p['number'],
                    'label'        => $p['type'] === 'Mobile' ? 'M' : 'H',
                    'country_code' => $p['country_code'],
                    'location'     => $p['location'],
                ], $phones),
                'identityDocuments' => $docs,
                'seatAssignments'   => $seats,
                'loyaltyPrograms'   => $fqtvs,
                '_travelerKey'      => (string) ($attr['Key'] ?? ''),
                '_ssrs'             => $ssrs,
            ];
            $index++;
        }

        return $travelers;
    }

    private function parseSeatAssignments(SimpleXMLElement $traveler): array
    {
        $seats = [];
        foreach ($this->safeXpath($traveler, './/com:AirSeatAssignment') as $sa) {
            $attr   = $sa->attributes();
            $seats[] = [
                'seat'               => (string) ($attr['Seat']           ?? ''),
                'status'             => (string) ($attr['Status']         ?? ''),
                'segment_ref'        => (string) ($attr['SegmentRef']     ?? ''),
                'flight_details_ref' => (string) ($attr['FlightDetailsRef'] ?? ''),
            ];
        }
        return $seats;
    }

    private function parseFQTV(SimpleXMLElement $traveler): array
    {
        // TravelPort FQTV SSR থেকে loyalty programs parse করো
        $programs = [];
        foreach ($this->safeXpath($traveler, './/com:SSR') as $ssr) {
            $attr = $ssr->attributes();
            $type = (string) ($attr['Type'] ?? '');
            if ($type !== 'FQTV') continue;

            $freeText = (string) ($attr['FreeText'] ?? '');
            $carrier  = (string) ($attr['Carrier']  ?? '');

            // FreeText format: "EK/1234567890" or "EK1234567890"
            $programNumber = '';
            if (preg_match('/([A-Z]{2})\/(.+)/', $freeText, $m)) {
                $carrier       = $m[1];
                $programNumber = $m[2];
            } elseif (preg_match('/([A-Z]{2})(.+)/', $freeText, $m)) {
                $carrier       = $m[1];
                $programNumber = $m[2];
            } else {
                $programNumber = $freeText;
            }

            $programs[] = [
                'supplier_code'  => $carrier,
                'program_type'   => 'FFN',
                'program_number' => trim($programNumber),
                'receiver_code'  => $carrier,
            ];
        }
        return $programs;
    }

    private function buildPassengers(array $travelers): array
    {
        return array_map(fn($t) => $this->buildPassenger($t), $travelers);
    }

    private function buildPassenger(array $traveler): array
    {
        if (empty($traveler)) return [];

        $phone        = $traveler['phones'][0] ?? [];
        $passport     = null;
        $secureFlight = null;

        foreach ($traveler['identityDocuments'] ?? [] as $doc) {
            $type = $doc['documentType'] ?? '';
            if ($type === 'PASSPORT' && $passport === null)                         $passport     = $doc;
            if ($type === 'SECURE_FLIGHT_PASSENGER_DATA' && $secureFlight === null) $secureFlight = $doc;
        }

        return [
            // ── Sabre compatible keys ──────────────────────────────────────
            'key'                 => $traveler['_travelerKey'] ?? base64_encode(($traveler['givenName'] ?? '') . ($traveler['surname'] ?? '')),
            'traveler_type'       => $traveler['passengerCode']     ?? 'ADT',
            'passenger_type'      => $traveler['type']              ?? 'ADULT',
            'name_association_id' => $traveler['nameAssociationId'] ?? '1',
            'gender'              => $this->shortGender($traveler['gender'] ?? $passport['gender'] ?? ''),
            'dob'                 => $traveler['dob'] ?? $passport['birthDate'] ?? '',
            'prefix'              => $traveler['prefix'] ?? '',
            'first_name'          => $traveler['givenName'] ?? '',
            'last_name'           => $traveler['surname']   ?? '',
            'phone'               => $phone['number'] ?? '',
            'phone_label'         => $phone['label']  ?? '',
            'phone_country_code'  => $phone['country_code'] ?? '',
            'phone_location'      => $phone['location']     ?? '',
            'email'               => $traveler['emails'][0] ?? '',
            'emails'              => $traveler['emails']    ?? [],
            'phones'              => $traveler['phones']    ?? [],

            'passport_number'     => $passport['documentNumber']       ?? '',
            'passport_type'       => 'Passport',
            'passport_expiry'     => $passport['expiryDate']           ?? '',
            'passport_country'    => $passport['issuingCountryCode']   ?? '',
            'nationality'         => $passport['residenceCountryCode'] ?? '',
            'is_primary_holder'   => $passport['isPrimaryDocumentHolder'] ?? false,

            'secure_flight' => $secureFlight ? [
                'given_name' => $secureFlight['givenName'] ?? '',
                'surname'    => $secureFlight['surname']   ?? '',
                'birth_date' => $secureFlight['birthDate'] ?? '',
                'gender'     => $secureFlight['gender']    ?? '',
            ] : null,

            'identity_documents' => $traveler['identityDocuments'] ?? [],

            // Sabre এ loyaltyPrograms আছে, TravelPort FQTV SSR থেকে
            'loyalty_programs' => array_map(fn($lp) => [
                'supplier_code'  => $lp['supplier_code']  ?? null,
                'program_type'   => $lp['program_type']   ?? null,
                'program_number' => $lp['program_number'] ?? null,
                'receiver_code'  => $lp['receiver_code']  ?? null,
            ], $traveler['loyaltyPrograms'] ?? []),

            // ── TravelPort extra ───────────────────────────────────────────
            '_tp_extra' => [
                'seat_assignments' => $traveler['seatAssignments'] ?? [],
                'ssrs'             => $traveler['_ssrs']           ?? [],
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FLIGHTS
    // ═══════════════════════════════════════════════════════════════════════

    private function parseFlights(SimpleXMLElement $xml, string $supplierLocatorCode): array
    {
        $flights       = [];
        $index         = 10;
        $travelerCount = count($this->safeXpath($xml, '//com:BookingTraveler'));

        foreach ($this->safeXpath($xml, '//air:AirSegment') as $seg) {
            $this->registerNamespaces($seg);
            $attr    = $seg->attributes();
            $fdNodes = $this->safeXpath($seg, './/air:FlightDetails');
            $fdAttr  = !empty($fdNodes) ? $fdNodes[0]->attributes() : null;

            $depTime = (string) ($attr['DepartureTime'] ?? '');
            $arrTime = (string) ($attr['ArrivalTime']   ?? '');

            // Sell messages — terminal info
            $sellMessages = [];
            foreach ($this->safeXpath($seg, './/com:SellMessage') as $sm) {
                $msg = trim((string) $sm);
                if (str_contains($msg, 'TERMINAL')) {
                    $sellMessages[] = $msg;
                }
            }

            // Connection
            $connDuration = null;
            foreach ($this->safeXpath($seg, './/air:Connection') as $conn) {
                $connDuration = (int) ($conn->attributes()['Duration'] ?? 0);
            }

            // Meals from SSR
            $meals = $this->parseMealsForSegment($xml, (string) ($attr['Key'] ?? ''));

            $flights[] = [
                'itemId'                => (string) $index,
                'confirmationId'        => $supplierLocatorCode,
                'sourceType'            => 'ATPCO',
                'flightNumber'          => (int) ($attr['FlightNumber'] ?? 0),
                'airlineCode'           => (string) ($attr['Carrier'] ?? ''),
                'airlineName'           => $this->getAirlineName((string) ($attr['Carrier'] ?? '')),
                'operatingFlightNumber' => (int) ($attr['FlightNumber'] ?? 0),
                'operatingAirlineCode'  => (string) ($attr['Carrier'] ?? ''),
                'operatingAirlineName'  => $this->getAirlineName((string) ($attr['Carrier'] ?? '')),
                'fromAirportCode'       => (string) ($attr['Origin']      ?? ''),
                'toAirportCode'         => (string) ($attr['Destination'] ?? ''),
                'departureDate'         => $this->extractDate($depTime),
                'departureTime'         => $this->extractTime($depTime),
                'arrivalDate'           => $this->extractDate($arrTime),
                'arrivalTime'           => $this->extractTime($arrTime),
                'departureTerminalName' => $fdAttr ? (string) ($fdAttr['OriginTerminal']      ?? '') : '',
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
                'distanceInMiles'       => (int) ($attr['Distance']   ?? 0),
                'travelerIndices'       => range(1, max(1, $travelerCount)),
                'identityDocuments'     => [],
                'sellMessages'          => $sellMessages,
                'isPast'                => false,
                '_segmentKey'           => (string) ($attr['Key']         ?? ''),
                '_group'                => (int)    ($attr['Group']        ?? 0),
                '_travelOrder'          => (int)    ($attr['TravelOrder']  ?? $index - 9),
                '_marriageGroup'        => (string) ($attr['MarriageGroup'] ?? ''),
                '_providerCode'         => (string) ($attr['ProviderCode'] ?? '1G'),
                '_status'               => (string) ($attr['Status']       ?? 'HK'),
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
            'B' => 'Breakfast', 'L' => 'Lunch',   'D' => 'Dinner',
            'S' => 'Snack',     'M' => 'Meal',     'V' => 'Vegetarian Meal',
            'K' => 'Kosher',    'H' => 'Hindu Meal',
        ];

        foreach ($this->safeXpath($xml, '//com:SSR') as $ssr) {
            $attr = $ssr->attributes();
            $type = (string) ($attr['Type'] ?? '');
            if (in_array($type, ['MEAL','VGML','HNML','KSML','VLML']) || str_starts_with($type, 'ML')) {
                $code    = substr($type, 0, 1);
                $meals[] = [
                    'code'        => $code,
                    'description' => $mealCodes[$code] ?? $type,
                ];
            }
        }

        return $meals;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SEGMENTS (Sabre buildSegments এর মতো)
    // ═══════════════════════════════════════════════════════════════════════

    private function buildSegments(array $flights, array $travelers): array
    {
        $segments = [];

        // Identity docs map from travelers
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

            // Connection layover (Sabre buildSegments এর মতো)
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
                        $arr  = new \DateTime($flight['_arrTimeRaw']);
                        $dep  = new \DateTime($flights[$idx + 1]['_depTimeRaw']);
                        $diff = $arr->diff($dep);
                        $connection = ['duration' => $diff->h * 60 + $diff->i + $diff->days * 1440];
                    } catch (\Exception) {
                        $connection = ['duration' => 0];
                    }
                }
            }

            $segments[] = [
                // ── Sabre compatible keys ──────────────────────────────────
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
                'confirmation_id'        => $flight['confirmationId'],
                'number_of_seats'        => $flight['numberOfSeats'],
                'traveler_indices'       => $flight['travelerIndices'],
                'meals'                  => $flight['meals'],
                'identity_documents'     => $allIdentityDocs,
                'is_past'                => $flight['isPast'],
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
    //  ALL SEGMENTS (Sabre buildAllSegments এর মতো)
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
    //  JOURNEYS (Sabre buildJourneys এর মতো)
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
    //  PRICING  (Sabre buildPricing এর মতো)
    // ═══════════════════════════════════════════════════════════════════════

    private function parsePricing(SimpleXMLElement $xml, array $flights): array
    {
        $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
        if (empty($pricingNodes)) {
            return $this->emptyPricing();
        }

        $firstAttr = $pricingNodes[0]->attributes();
        $currency  = $this->extractCurrency(
            (string) ($firstAttr['ApproximateTotalPrice'] ?? $firstAttr['TotalPrice'] ?? 'BDT0')
        );

        // ── Grand total: Payment element থেকে (actual paid amount) ─────────
        $grandTotal  = 0.0;
        $grandTaxes  = 0.0;
        $grandBase   = 0.0;

        // First try Payment element (most accurate)
        $paymentNodes = $this->safeXpath($xml, '//com:Payment');
        if (!empty($paymentNodes)) {
            foreach ($paymentNodes as $pn) {
                $pa          = $pn->attributes();
                $paidStr     = (string) ($pa['ApproximateAmount'] ?? $pa['Amount'] ?? '');
                if (!empty($paidStr)) {
                    $grandTotal += (float) $this->extractAmount($paidStr);
                    $currency    = $this->extractCurrency($paidStr);
                }
            }
        }

        // Fallback: sum from AirPricingInfo
        if ($grandTotal == 0) {
            foreach ($pricingNodes as $pn) {
                $pna         = $pn->attributes();
                $grandTotal += (float) $this->extractAmount((string) ($pna['ApproximateTotalPrice'] ?? $pna['TotalPrice'] ?? '0'));
            }
        }

        // Taxes & base from AirPricingInfo (Payment element এ নেই)
        foreach ($pricingNodes as $pn) {
            $pna        = $pn->attributes();
            $grandTaxes += (float) $this->extractAmount((string) ($pna['Taxes'] ?? '0'));
            $grandBase  += (float) $this->extractAmount((string) ($pna['ApproximateBasePrice'] ?? '0'));
        }

        // ── Plating carrier ──────────────────────────────────────────────
        $tmNodes        = $this->safeXpath($xml, '//air:TicketingModifiers');
        $platingCarrier = '';
        if (!empty($tmNodes)) {
            $platingCarrier = (string) ($tmNodes[0]->attributes()['PlatingCarrier'] ?? '');
        }

        // ── Fare breakdowns (Sabre fareBreakdowns এর মতো) ───────────────
        $fareBreakdowns = [];
        foreach ($pricingNodes as $pn) {
            $pna = $pn->attributes();

            $ptNodes = $this->safeXpath($pn, './/air:PassengerType');
            $paxType = !empty($ptNodes) ? (string) ($ptNodes[0]->attributes()['Code'] ?? 'ADT') : 'ADT';

            $ptotal    = (string) ($pna['ApproximateTotalPrice'] ?? $pna['TotalPrice'] ?? 'BDT0');
            $pbase     = (string) ($pna['ApproximateBasePrice']  ?? $pna['EquivalentBasePrice'] ?? $pna['BasePrice'] ?? 'BDT0');
            $ptaxes    = (string) ($pna['Taxes'] ?? 'BDT0');
            $pcurrency = $this->extractCurrency($ptotal);

            $ptaxBreakdown = [];
            foreach ($this->safeXpath($pn, './/air:TaxInfo') as $tax) {
                $ta              = $tax->attributes();
                $ptaxBreakdown[] = [
                    'code'     => (string) ($ta['Category'] ?? ''),
                    'amount'   => $this->extractAmount((string) ($ta['Amount'] ?? '0')),
                    'currency' => $this->extractCurrency((string) ($ta['Amount'] ?? '')),
                    'is_paid'  => false,
                ];
            }

            $pFareConstruction = [];
            foreach ($this->safeXpath($pn, './/air:FareInfo') as $fi) {
                $fia      = $fi->attributes();
                $bagKg    = 0;
                $bagPcs   = null;

                // MaxWeight
                $mwNodes = $this->safeXpath($fi, './/air:MaxWeight');
                if (!empty($mwNodes)) {
                    $mwa = $mwNodes[0]->attributes();
                    if (strtolower((string) ($mwa['Unit'] ?? '')) === 'kilograms') {
                        $bagKg = (int) ($mwa['Value'] ?? 0);
                    }
                }

                // NumberOfPieces
                $npNodes = $this->safeXpath($fi, './/air:NumberOfPieces');
                if (!empty($npNodes)) {
                    $bagPcs = (int) ((string) $npNodes[0]);
                }

                // Brand info
                $brandId   = '';
                $brandName = '';
                $brandNodes = $this->safeXpath($fi, './/air:Brand');
                if (!empty($brandNodes)) {
                    $ba        = $brandNodes[0]->attributes();
                    $brandId   = (string) ($ba['BrandID'] ?? '');
                    $brandName = (string) ($ba['Name']    ?? '');
                }

                // Endorsements
                $endorsements = [];
                foreach ($this->safeXpath($fi, './/com:Endorsement') as $end) {
                    $ea = $end->attributes();
                    $endorsements[] = (string) ($ea['Value'] ?? '');
                }

                $pFareConstruction[] = [
                    'fare_basis'         => (string) ($fia['FareBasis'] ?? ''),
                    'brand_fare_code'    => $brandId,
                    'brand_fare_name'    => $brandName,
                    'brand_program_code' => '',
                    'brand_program_name' => '',
                    'is_current'         => true,
                    'base_amount'        => $this->extractAmount((string) ($fia['Amount'] ?? '0')),
                    'base_currency'      => $this->extractCurrency((string) ($fia['Amount'] ?? '')),
                    'checked_bag_kg'     => $bagKg,
                    'checked_bag_pieces' => $bagPcs,
                    'cabin_bag_kg'       => null,
                    // TravelPort extra
                    '_endorsements'      => $endorsements,
                    '_not_valid_before'  => (string) ($fia['NotValidBefore'] ?? ''),
                    '_not_valid_after'   => (string) ($fia['NotValidAfter']  ?? ''),
                    '_private_fare'      => (string) ($fia['PrivateFare']    ?? ''),
                ];
            }

            // FareCalc
            $pFareCalcNodes = $this->safeXpath($pn, './/air:FareCalc');
            $pFareCalc      = !empty($pFareCalcNodes) ? (string) $pFareCalcNodes[0] : '';

            // Commission from TicketingModifiers
            $commPct    = null;
            $commAmount = null;
            if (!empty($tmNodes)) {
                $commNodes = $this->safeXpath($tmNodes[0], './/com:Commission');
                if (!empty($commNodes)) {
                    $ca      = $commNodes[0]->attributes();
                    $commPct = (string) ($ca['Percentage'] ?? null);
                    if ($commPct) {
                        $baseAmt    = (float) $this->extractAmount($pbase);
                        $commAmount = number_format($baseAmt * (float)$commPct / 100, 2, '.', '');
                    }
                }
            }

            // Ticketed status
            $isTicketed = (string) ($pna['Ticketed'] ?? 'false') === 'true';

            // Change/Cancel penalties (for fare_rules, also stored here)
            $changePenalty = null;
            $cancelPenalty = null;
            $cpNodes = $this->safeXpath($pn, './/air:ChangePenalty');
            if (!empty($cpNodes)) {
                $amtNodes     = $this->safeXpath($cpNodes[0], './/air:Amount');
                $changePenalty = !empty($amtNodes) ? $this->extractAmount((string) $amtNodes[0]) : '0';
                $cpCurr        = !empty($amtNodes) ? $this->extractCurrency((string) $amtNodes[0]) : 'BDT';
            }
            $ccNodes = $this->safeXpath($pn, './/air:CancelPenalty');
            if (!empty($ccNodes)) {
                $amtNodes     = $this->safeXpath($ccNodes[0], './/air:Amount');
                $cancelPenalty = !empty($amtNodes) ? $this->extractAmount((string) $amtNodes[0]) : '0';
                $ccCurr        = !empty($amtNodes) ? $this->extractCurrency((string) $amtNodes[0]) : 'BDT';
            }

            $fareBreakdowns[] = [
                // ── Sabre compatible ──────────────────────────────────────
                'record_id'            => (string) (count($fareBreakdowns) + 1),
                'record_type_code'     => 'PQ',
                'record_type_name'     => 'Price Quote',
                'pricing_type_code'    => 'S',
                'pricing_type_name'    => 'System',
                'pricing_status_code'  => $isTicketed ? 'T' : 'A',
                'pricing_status_name'  => $isTicketed ? 'Ticketed' : 'Active',
                'traveler_indices'     => [],
                'traveler_type'        => $paxType,
                'priced_traveler_type' => $paxType,
                'is_negotiated'        => false,
                'validating_carrier'   => $platingCarrier,
                'fare_calculation'     => $pFareCalc,
                'subtotal'             => $this->extractAmount($pbase),
                'taxes'                => $this->extractAmount($ptaxes),
                'total'                => $this->extractAmount($ptotal),
                'currency'             => $pcurrency,
                'original_total'       => $this->extractAmount($ptotal),
                'original_currency'    => $pcurrency,
                'tax_breakdown'        => $ptaxBreakdown,
                'fare_construction'    => $pFareConstruction,
                'commission' => [
                    'percentage' => $commPct,
                    'amount'     => $commAmount,
                ],
                'creation_details' => [],

                // ── TravelPort extra ──────────────────────────────────────
                '_tp_extra' => [
                    'pricing_method'        => (string) ($pna['PricingMethod']  ?? ''),
                    'refundable'            => (string) ($pna['Refundable']     ?? 'false') === 'true',
                    'exchangeable'          => (string) ($pna['Exchangeable']   ?? 'false') === 'true',
                    'latest_ticketing_time' => (string) ($pna['LatestTicketingTime'] ?? ''),
                    'true_last_date_to_ticket' => (string) ($pna['TrueLastDateToTicket'] ?? ''),
                    'change_penalty'        => $changePenalty,
                    'change_penalty_currency' => $cpCurr ?? null,
                    'cancel_penalty'        => $cancelPenalty,
                    'cancel_penalty_currency' => $ccCurr ?? null,
                ],
            ];
        }

        // ── Baggage: first FareInfo থেকে ────────────────────────────────
        $checkedBagKg  = 0;
        $checkedBagPcs = null;
        $fareInfoNodes = $this->safeXpath($pricingNodes[0], './/air:FareInfo');
        if (!empty($fareInfoNodes)) {
            $mw = $this->safeXpath($fareInfoNodes[0], './/air:MaxWeight');
            if (!empty($mw)) {
                $mwa = $mw[0]->attributes();
                if (strtolower((string) ($mwa['Unit'] ?? '')) === 'kilograms') {
                    $checkedBagKg = (int) ($mwa['Value'] ?? 0);
                }
            }
            $np = $this->safeXpath($fareInfoNodes[0], './/air:NumberOfPieces');
            if (!empty($np)) {
                $checkedBagPcs = (int) ((string) $np[0]);
            }
        }

        $perLegBaggage    = $this->buildPerLegBaggage($flights, $checkedBagKg, $checkedBagPcs);
        $fareOfferFlights = array_map(fn($f) => $f['itemId'], $flights);
        $travelerCount    = count($this->safeXpath($xml, '//com:BookingTraveler'));

        return [
            'grand_total' => [
                'subtotal' => (string) (int) $grandBase,
                'taxes'    => (string) (int) $grandTaxes,
                'total'    => (string) (int) $grandTotal,
                'currency' => $currency,
            ],
            'fare_breakdowns'         => $fareBreakdowns,
            'per_leg_baggage'         => $perLegBaggage,
            'checked_bag_kg'          => $checkedBagKg,
            'cabin_bag_kg'            => 7,
            'checked_baggage_charges' => [],
            'traveler_indices'        => range(1, max(1, $travelerCount)),
            'fare_offer_flights'      => $fareOfferFlights,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PER LEG BAGGAGE (Sabre buildPricing perLegBaggage এর মতো)
    // ═══════════════════════════════════════════════════════════════════════

    private function buildPerLegBaggage(array $flights, int $checkedBagKg, ?int $checkedBagPcs): array
    {
        if (empty($flights)) return [];

        // Group by journey (group field)
        $groups = [];
        foreach ($flights as $f) {
            $gk = (string) $f['_group'];
            $groups[$gk][] = $f;
        }

        $legs = [];
        foreach (array_values($groups) as $legIdx => $group) {
            $legs[] = [
                'leg_index'          => $legIdx,
                'traveler_indices'   => $group[0]['travelerIndices'] ?? [1],
                'flight_item_ids'    => array_map(fn($f) => $f['itemId'], $group),
                'checked_bag_kg'     => $checkedBagKg,
                'checked_bag_pieces' => $checkedBagPcs,
                'cabin_bag_kg'       => 7,
                'cabin_bag_pieces'   => null,
                'extra_charges'      => [],
            ];
        }

        return $legs;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FARE RULES (Sabre buildFareRules এর মতো)
    // ═══════════════════════════════════════════════════════════════════════

    private function buildFareRules(SimpleXMLElement $xml): array
    {
        $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
        if (empty($pricingNodes)) return [];

        $tmNodes = $this->safeXpath($xml, '//air:TicketingModifiers');
        $airline = !empty($tmNodes) ? (string) ($tmNodes[0]->attributes()['PlatingCarrier'] ?? '') : '';

        $fareInfoNodes = $this->safeXpath($pricingNodes[0], './/air:FareInfo');
        $ruleOrigin    = '';
        $ruleDest      = '';
        if (!empty($fareInfoNodes)) {
            $fia        = $fareInfoNodes[0]->attributes();
            $ruleOrigin = (string) ($fia['Origin']      ?? '');
            $ruleDest   = (string) ($fia['Destination'] ?? '');
        }

        $rules = [];
        foreach ($pricingNodes as $p) {
            $attr = $p->attributes();

            $isRefundable   = (string) ($attr['Refundable']   ?? 'false') === 'true';
            $isExchangeable = (string) ($attr['Exchangeable'] ?? 'false') === 'true';

            $ptNodes = $this->safeXpath($p, './/air:PassengerType');
            $paxType = !empty($ptNodes) ? (string) ($ptNodes[0]->attributes()['Code'] ?? 'ADT') : 'ADT';

            // Refund (cancel) penalties
            $refundPenalties = [];
            foreach ($this->safeXpath($p, './/air:CancelPenalty') as $cp) {
                $cpa      = $cp->attributes();
                $applic   = (string) ($cpa['PenaltyApplies'] ?? 'Before Departure');
                $amtNodes = $this->safeXpath($cp, './/air:Amount');
                $amount   = !empty($amtNodes) ? $this->extractAmount((string) $amtNodes[0]) : '0';
                $curr     = !empty($amtNodes) ? $this->extractCurrency((string) $amtNodes[0]) : 'BDT';

                $refundPenalties[] = [
                    'applicability'    => strtoupper(str_replace(' ', '_', $applic)),
                    'conditions_apply' => false,
                    'has_no_show_cost' => false,
                    'penalty_amount'   => $this->cleanAmount($amount),
                    'penalty_currency' => $curr,
                    'no_show_amount'   => '0',
                    'no_show_currency' => $curr,
                ];
            }

            // Exchange (change) penalties
            $exchangePenalties = [];
            foreach ($this->safeXpath($p, './/air:ChangePenalty') as $cp) {
                $cpa      = $cp->attributes();
                $applic   = (string) ($cpa['PenaltyApplies'] ?? 'Anytime');
                $amtNodes = $this->safeXpath($cp, './/air:Amount');
                $amount   = !empty($amtNodes) ? $this->extractAmount((string) $amtNodes[0]) : '0';
                $curr     = !empty($amtNodes) ? $this->extractCurrency((string) $amtNodes[0]) : 'BDT';

                $exchangePenalties[] = [
                    'applicability'    => strtoupper(str_replace(' ', '_', $applic)),
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
                'is_cancelable'      => $isRefundable,
                'refund_penalties'   => $refundPenalties,
                'exchange_penalties' => $exchangePenalties,
            ];
        }

        return $rules;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTION STATUS (Sabre buildActionStatus এর মতো — multi-priority)
    // ═══════════════════════════════════════════════════════════════════════

    private function parseActionStatus(SimpleXMLElement $ur, SimpleXMLElement $xml, array $remarks): array
    {
        $ticketDate = '';

        // ── Priority 1: ADTK Remark থেকে parse ───────────────────────────
        foreach ($remarks as $remark) {
            $text = strtoupper($remark['text'] ?? '');
            if (!str_contains($text, 'ADTK')) continue;

            $parsed = $this->parseDateFromRemark($text);
            if ($parsed !== null) {
                $ticketDate = $parsed;
                break;
            }
        }

        // ── Priority 2: ActionStatus.TicketDate ───────────────────────────
        if (empty($ticketDate)) {
            $nodes = $this->safeXpath($ur, './/com:ActionStatus');
            if (!empty($nodes)) {
                $attr       = $nodes[0]->attributes();
                $ticketDate = (string) ($attr['TicketDate'] ?? '');
            }
        }

        // ── Priority 3: AirPricingInfo.LatestTicketingTime ────────────────
        if (empty($ticketDate)) {
            $pricingNodes = $this->safeXpath($xml, '//air:AirPricingInfo');
            if (!empty($pricingNodes)) {
                $pna        = $pricingNodes[0]->attributes();
                $ticketDate = (string) ($pna['LatestTicketingTime']    ?? $pna['TrueLastDateToTicket'] ?? '');
            }
        }

        // Key, type, provider from ActionStatus node
        $key          = '';
        $type         = 'TAU';
        $providerCode = '1G';

        $nodes = $this->safeXpath($ur, './/com:ActionStatus');
        if (!empty($nodes)) {
            $attr         = $nodes[0]->attributes();
            $key          = (string) ($attr['Key']          ?? '');
            $type         = (string) ($attr['Type']         ?? 'TAU');
            $providerCode = (string) ($attr['ProviderCode'] ?? '1G');
        }

        return [
            'key'           => $key,
            'type'          => $type,
            'ticket_date'   => $ticketDate,
            'provider_code' => $providerCode,
        ];
    }

    /**
     * ADTK remark থেকে ticketing deadline parse — Sabre parseDateFromMessage এর মতো
     */
    private function parseDateFromRemark(string $text): ?string
    {
        $text = strtoupper(trim($text));

        $months = [
            'JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04',
            'MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08',
            'SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12',
        ];

        // Pattern A: BY 18MAY 2222 DAC  (or similar)
        if (preg_match('/BY\s+(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})?\s+(\d{4})[A-Z]{3}/i', $text, $m)) {
            $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mon   = $months[strtoupper($m[2])] ?? '01';
            $year  = !empty($m[3]) ? (strlen($m[3]) === 2 ? '20'.$m[3] : $m[3]) : now()->year;
            $hour  = substr($m[4], 0, 2);
            $min   = substr($m[4], 2, 2);
            return "{$year}-{$mon}-{$day}T{$hour}:{$min}:00.000+06:00";
        }

        // Pattern B: GBYDAC22APR26/1101 or DAC22APR26/1101
        if (preg_match('/[A-Z]{3}(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\/(\d{4})/i', $text, $m)) {
            $day  = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mon  = $months[strtoupper($m[2])] ?? '01';
            $year = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];
            $hour = substr($m[4], 0, 2);
            $min  = substr($m[4], 2, 2);
            return "{$year}-{$mon}-{$day}T{$hour}:{$min}:00.000+06:00";
        }

        // Pattern C: 18MAY26 2222
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\s+(\d{4})/i', $text, $m)) {
            $day  = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mon  = $months[strtoupper($m[2])] ?? '01';
            $year = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];
            $hour = substr($m[4], 0, 2);
            $min  = substr($m[4], 2, 2);
            return "{$year}-{$mon}-{$day}T{$hour}:{$min}:00.000+06:00";
        }

        // Pattern D: date only 18MAY26
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})/i', $text, $m)) {
            $day  = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mon  = $months[strtoupper($m[2])] ?? '01';
            $year = strlen($m[3]) === 2 ? '20'.$m[3] : $m[3];
            return "{$year}-{$mon}-{$day}T23:59:00.000+06:00";
        }

        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  AGENCY INFO
    // ═══════════════════════════════════════════════════════════════════════

    private function parseAgencyInfo(SimpleXMLElement $ur): array
    {
        $nodes = $this->safeXpath($ur, './/com:AgencyInfo/com:AgentAction');
        if (empty($nodes)) {
            return [
                'action_type' => '', 'agent_code' => '',
                'branch_code' => '', 'agency_code' => '', 'event_time' => '',
            ];
        }

        $attr = $nodes[0]->attributes();
        return [
            'action_type' => (string) ($attr['ActionType'] ?? ''),
            'agent_code'  => (string) ($attr['AgentCode']  ?? ''),
            'branch_code' => (string) ($attr['BranchCode'] ?? ''),
            'agency_code' => (string) ($attr['AgencyCode'] ?? ''),
            'event_time'  => (string) ($attr['EventTime']  ?? ''),
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
                'type'      => (string) ($attr['TypeInGds'] ?? ''),
                'text'      => !empty($dataNodes) ? (string) $dataNodes[0] : '',
                // TravelPort extra
                '_category' => (string) ($attr['Category']     ?? ''),
                '_supplier' => (string) ($attr['SupplierCode'] ?? ''),
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

    private function parseOSI(SimpleXMLElement $ur): array
    {
        $osi = [];
        foreach ($this->safeXpath($ur, './/com:OSI') as $o) {
            $attr  = $o->attributes();
            $osi[] = [
                'carrier' => (string) ($attr['Carrier'] ?? ''),
                'text'    => (string) ($attr['Text']    ?? ''),
            ];
        }
        return $osi;
    }

    private function parseActionStatusRemark(SimpleXMLElement $ur): string
    {
        $nodes = $this->safeXpath($ur, './/com:ActionStatus/com:Remark');
        return !empty($nodes) ? (string) $nodes[0] : '';
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CONTACT INFO + SPECIAL SERVICES (Sabre buildSpecialServices এর মতো)
    // ═══════════════════════════════════════════════════════════════════════

    private function buildContactInfo(array $travelers): array
    {
        $phones = [];
        $emails = [];
        foreach ($travelers as $t) {
            foreach ($t['phones'] as $p) {
                $entry = $p['number'] . '-' . $p['label'] . '-1.1';
                if (!in_array($entry, $phones)) $phones[] = $entry;
            }
            foreach ($t['emails'] as $e) {
                if (!in_array($e, $emails)) $emails[] = $e;
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
                    'code'             => 'CTCE',
                    'name'             => 'Passenger contact information e-mail address',
                    'message'          => '/' . $email,
                    'status_code'      => 'HK',
                    'status_name'      => 'Confirmed',
                    'traveler_indices' => [$travelerIndex],
                ];
            }

            foreach ($traveler['phones'] as $phone) {
                $services[] = [
                    'code'             => 'CTCM',
                    'name'             => 'Passenger contact information mobile phone number',
                    'message'          => '/' . $phone['number'],
                    'status_code'      => 'HK',
                    'status_name'      => 'Confirmed',
                    'traveler_indices' => [$travelerIndex],
                ];
            }

            foreach ($traveler['identityDocuments'] as $doc) {
                if (($doc['documentType'] ?? '') !== 'PASSPORT') continue;

                $services[] = [
                    'code'             => 'DOCS',
                    'name'             => 'API-Passenger Travel Document',
                    'message'          => sprintf('/P/%s/%s/%s/%s/%s/%s/%s/%s',
                        $doc['issuingCountryCode']  ?? '',
                        $doc['documentNumber']      ?? '',
                        $doc['residenceCountryCode'] ?? '',
                        $this->formatDocDate($doc['birthDate']   ?? ''),
                        $this->shortGender($doc['gender'] ?? ''),
                        $this->formatDocDate($doc['expiryDate']  ?? ''),
                        $doc['surname']    ?? '',
                        $doc['givenName']  ?? ''
                    ),
                    'status_code'      => 'HK',
                    'status_name'      => 'Confirmed',
                    'traveler_indices' => [$travelerIndex],
                ];
            }
        }
        return $services;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  IDENTITY DOCUMENTS (SSR DOCS থেকে parse)
    // ═══════════════════════════════════════════════════════════════════════

    private function parseIdentityDocuments(array $ssrs, array $name, $travelerAttr): array
    {
        $docs = [];
        foreach ($ssrs as $ssr) {
            if ($ssr['type'] !== 'DOCS') continue;

            $freeText = ltrim($ssr['free_text'], ' ');
            $parts    = explode('/', $freeText);

            if (count($parts) < 8) continue;
            if (trim($parts[0] ?? '') === 'DB') continue;

            $docs[] = [
                'documentNumber'           => $parts[2] ?? '',
                'documentType'             => 'PASSPORT',
                'passportType'             => 'Passport',
                'expiryDate'               => $this->parseDocDate($parts[6] ?? ''),
                'issuingCountryCode'       => $parts[1] ?? '',
                'residenceCountryCode'     => $parts[3] ?? '',
                'givenName'                => trim($parts[8] ?? $name['first']),
                'surname'                  => trim($parts[7] ?? $name['last']),
                'birthDate'                => isset($travelerAttr['DOB']) ? (string) $travelerAttr['DOB'] : '',
                'gender'                   => $this->mapGender((string) ($travelerAttr['Gender'] ?? '')),
                'isPrimaryDocumentHolder'  => false,
                'itemId'                   => md5($ssr['free_text']),
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
            'first'  => (string) ($attr['First']  ?? ''),
            'middle' => (string) ($attr['Middle'] ?? ''),
            'last'   => (string) ($attr['Last']   ?? ''),
        ];
    }

    private function parsePhones(SimpleXMLElement $element): array
    {
        $phones = [];
        foreach ($this->safeXpath($element, './/com:PhoneNumber') as $p) {
            $attr     = $p->attributes();
            $phones[] = [
                'type'         => (string) ($attr['Type']        ?? ''),
                'number'       => (string) ($attr['Number']      ?? ''),
                'country_code' => (string) ($attr['CountryCode'] ?? ''),
                'location'     => (string) ($attr['Location']    ?? ''),
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
                'type'  => (string) ($attr['Type']    ?? ''),
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
                'type'        => (string) ($attr['Type']      ?? ''),
                'status'      => (string) ($attr['Status']    ?? ''),
                'free_text'   => (string) ($attr['FreeText']  ?? ''),
                'carrier'     => (string) ($attr['Carrier']   ?? ''),
                'segment_ref' => (string) ($attr['SegmentRef'] ?? ''),
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
            'fault_code'   => (string) ($fault->faultcode   ?? ''),
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
            ['"',        '"',        "'",         "'"],
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

    private function extractTime(string $datetime): string
    {
        return strlen($datetime) >= 19 ? substr($datetime, 11, 8) : '';
    }

    private function extractAmount(string $value): string
    {
        return preg_replace('/^[A-Z]{3}/', '', $value);
    }

    private function cleanAmount(string $value): string
    {
        if (str_contains($value, '.')) {
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
            'JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04',
            'MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08',
            'SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12',
        ];
        $day   = substr($date, 0, 2);
        $month = $months[strtoupper(substr($date, 2, 3))] ?? '01';
        $year  = '20' . substr($date, 5, 2);
        return "{$year}-{$month}-{$day}";
    }

    private function formatDocDate(string $date): string
    {
        if (empty($date)) return '';
        $months = [
            '01'=>'JAN','02'=>'FEB','03'=>'MAR','04'=>'APR',
            '05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AUG',
            '09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DEC',
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
            'MALE',   'M' => 'M',
            'FEMALE', 'F' => 'F',
            default       => $g,
        };
    }

    private function mapFlightStatus(string $s): string
    {
        return match ($s) {
            'HK', 'KK' => 'Confirmed',
            'NN'       => 'Need',
            'UC'       => 'Unable to Confirm',
            'HX'       => 'Cancelled',
            default    => 'Confirmed',
        };
    }

    private function getAircraftName(string $code): string
    {
        return match (strtoupper($code)) {
            '789'        => 'BOEING 787-9',
            '788'        => 'BOEING 787-8',
            '787'        => 'BOEING 787',
            '77W', '777' => 'BOEING 777',
            '773'        => 'BOEING 777-300',
            '772'        => 'BOEING 777-200',
            '76W', '767' => 'BOEING 767',
            '752', '757' => 'BOEING 757',
            '744', '747' => 'BOEING 747',
            '738', '73H' => 'BOEING 737-800',
            '737'        => 'BOEING 737',
            '320', '32A' => 'AIRBUS A320',
            '32N'        => 'AIRBUS A320NEO',
            '321', '32B' => 'AIRBUS A321',
            '32Q'        => 'AIRBUS A321NEO',
            '319'        => 'AIRBUS A319',
            '333'        => 'AIRBUS A330-300',
            '332'        => 'AIRBUS A330-200',
            '351'        => 'AIRBUS A350-900',
            '388'        => 'AIRBUS A380-800',
            'DH8'        => 'DEHAVILLAND DASH 8',
            'DH4'        => 'DEHAVILLAND DASH 400',
            'AT7'        => 'ATR 72',
            'AT4'        => 'ATR 42',
            'CR9'        => 'CANADAIR REGIONAL JET 900',
            'CR7'        => 'CANADAIR REGIONAL JET 700',
            'E90'        => 'EMBRAER 190',
            'E75'        => 'EMBRAER 175',
            'E95'        => 'EMBRAER 195',
            default      => $code,
        };
    }

    private function getAirlineName(string $code): string
    {
        return match ($code) {
            'AI'  => 'AIR INDIA',
            'GF'  => 'GULF AIR',
            'BG'  => 'BIMAN BANGLADESH',
            'EK'  => 'EMIRATES',
            'QR'  => 'QATAR AIRWAYS',
            'EY'  => 'ETIHAD AIRWAYS',
            'TK'  => 'TURKISH AIRLINES',
            'SQ'  => 'SINGAPORE AIRLINES',
            'BS'  => 'US-BANGLA AIRLINES',
            'VQ'  => 'NOVOAIR',
            'OB'  => 'REGENT AIRWAYS',
            'FZ'  => 'FLYDUBAI',
            'G8'  => 'GO FIRST',
            '6E'  => 'INDIGO',
            'SG'  => 'SPICEJET',
            'KU'  => 'KUWAIT AIRWAYS',
            'WY'  => 'OMAN AIR',
            'SV'  => 'SAUDI ARABIAN AIRLINES',
            'XY' => 'FLYNAS',
            'PK'  => 'PAKISTAN INTERNATIONAL',
            'MH'  => 'MALAYSIA AIRLINES',
            'TG'  => 'THAI AIRWAYS',
            'CX'  => 'CATHAY PACIFIC',
            'BA'  => 'BRITISH AIRWAYS',
            'LH'  => 'LUFTHANSA',
            'AF'  => 'AIR FRANCE',
            'KL'  => 'KLM',
            default => $code,
        };
    }

    private function buildError(string $message): array
    {
        return ['success' => false, 'error' => $message];
    }
}
