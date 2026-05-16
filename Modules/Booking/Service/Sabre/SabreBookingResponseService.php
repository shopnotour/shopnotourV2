<?php

namespace Modules\Booking\Service\Sabre;

use Illuminate\Support\Facades\Log;

class SabreBookingResponseService
{
    public function parseGetReservationResponse(array|string $response): array
    {
        try {
            if (is_string($response)) {
                $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            }

            $bookingId = $response['bookingId'] ?? '';
            if (empty($bookingId)) {
                return $this->buildError('bookingId not found in getPnr response');
            }

            $creation        = $response['creationDetails']   ?? [];
            $travelers       = $response['travelers']         ?? [];
            $flights         = $response['flights']           ?? [];
            $services        = $response['specialServices']   ?? [];
            $fares           = $response['fares']             ?? [];
            $fareOffer       = $response['fareOffers'][0]     ?? [];
            $fareOffers      = $response['fareOffers']        ?? [];
            $payments        = $response['payments']          ?? [];
            $fareRules       = $response['fareRules']         ?? [];
            $journeys        = $response['journeys']          ?? [];
            $contactInfo     = $response['contactInfo']       ?? [];
            $allSegments     = $response['allSegments']       ?? [];
            $flightTickets   = $response['flightTickets']     ?? [];
            $accountingItems = $response['accountingItems']   ?? [];
            $remarks         = $response['remarks']           ?? [];
            $futureTicketing = $response['futureTicketingPolicy'] ?? [];

            $createIso   = $this->toIso($creation['creationDate'] ?? '', $creation['creationTime'] ?? '00:00');
            $modifiedIso = $this->toIso(
                $creation['lastUpdateDate'] ?? $creation['creationDate'] ?? '',
                $creation['lastUpdateTime'] ?? $creation['creationTime'] ?? '00:00'
            );

            return [
                'success'           => true,
                'trace_id'          => (string)rand(1000, 9999),
                'transaction_id'    => strtoupper(md5($bookingId . microtime())),
                'response_time'     => 0,

                'booking_id'        => $bookingId,
                'start_date'        => $response['startDate']       ?? '',
                'end_date'          => $response['endDate']         ?? '',
                'is_cancelable'     => $response['isCancelable']    ?? false,
                'is_ticketed'       => $response['isTicketed']      ?? false,
                'timestamp'         => $response['timestamp']       ?? '',
                'booking_signature' => $response['bookingSignature']?? '',

                'contact_info' => [
                    'phones' => $contactInfo['phones'] ?? [],
                    'emails' => $contactInfo['emails'] ?? [],
                ],

                'universal_record' => [
                    'locator_code' => $response['request']['confirmationId'] ?? $bookingId,
                    'version'      => 0,
                    'status'       => 'Active',
                ],

                'provider_reservation' => [
                    'key'               => base64_encode($bookingId),
                    'provider_code'     => '1G',
                    'locator_code'      => $bookingId,
                    'create_date'       => $createIso,
                    'modified_date'     => $modifiedIso,
                    'host_create_date'  => $creation['creationDate']     ?? '',
                    'owning_pcc'        => $creation['userWorkPcc']      ?? '',
                    'home_pcc'          => $creation['userHomePcc']      ?? '',
                    'prime_host_id'     => $creation['primeHostId']      ?? '',
                    'number_of_updates' => (int)($creation['numberOfUpdates'] ?? 0),
                ],

                'supplier_locator' => $this->buildSupplierLocator($response, $flights),

                'air_reservation' => [
                    'locator_code'  => $response['request']['confirmationId'] ?? $bookingId,
                    'create_date'   => $createIso,
                    'modified_date' => $modifiedIso,
                ],

                'passenger'  => $this->buildPassenger($travelers[0] ?? []),
                'passengers' => $this->buildPassengers($travelers),
                'segments'   => $this->buildSegments($flights),
                'all_segments' => $this->buildAllSegments($allSegments),
                'journeys'   => $this->buildJourneys($journeys),
                'remarks'    => $this->buildRemarks($remarks),

                'messages' => ['warnings' => [], 'errors' => []],

                'action_status' => $this->buildActionStatus($services, $creation, $fares, $futureTicketing),

                'agency_info' => [
                    'action_type' => '',
                    'agent_code'  => $creation['creationUserSine'] ?? '',
                    'branch_code' => '',
                    'agency_code' => $creation['userWorkPcc']      ?? '',
                    'event_time'  => '',
                ],

                'special_services' => $this->buildSpecialServices($services),
                'pricing'          => $this->buildPricing($fares, $fareOffer, $fareOffers, $payments),
                'fare_rules'       => $this->buildFareRules($fareRules),
                'flight_tickets'   => $this->buildFlightTickets($flightTickets),
                'accounting_items' => $this->buildAccountingItems($accountingItems, $travelers),
            ];

        } catch (\Throwable $e) {
            Log::error('SabreBookingResponseService::parseGetReservationResponse', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return $this->buildError($e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SUPPLIER LOCATOR
    // ═══════════════════════════════════════════════════════════════════════

    private function buildSupplierLocator(array $response, array $flights): array
    {
        return [
            'supplier_code'    => $flights[0]['airlineCode']    ?? '',
            'locator_code'     => $flights[0]['confirmationId'] ?? '',
            'create_date_time' => '',
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PASSENGERS
    // ═══════════════════════════════════════════════════════════════════════

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
            'key'                 => base64_encode(($traveler['givenName'] ?? '') . ($traveler['surname'] ?? '')),
            'traveler_type'       => $traveler['passengerCode']     ?? 'ADT',
            'passenger_type'      => $traveler['type']              ?? 'ADULT',
            'name_association_id' => $traveler['nameAssociationId'] ?? '1',
            'gender'              => $this->shortGender($passport['gender'] ?? $secureFlight['gender'] ?? ''),
            'dob'                 => $passport['birthDate']   ?? $secureFlight['birthDate'] ?? '',
            'prefix'              => '',
            'first_name'          => $traveler['givenName']   ?? '',
            'last_name'           => $traveler['surname']     ?? '',
            'phone'               => $phone['number'] ?? '',
            'phone_label'         => $phone['label']  ?? '',
            'phone_country_code'  => '',
            'phone_location'      => '',
            'email'               => $traveler['emails'][0] ?? '',
            'emails'              => $traveler['emails']    ?? [],
            'phones'              => $traveler['phones']    ?? [],

            'passport_number'   => $passport['documentNumber']      ?? '',
            'passport_type'     => $passport['passportType']        ?? '',
            'passport_expiry'   => $passport['expiryDate']          ?? '',
            'passport_country'  => $passport['issuingCountryCode']  ?? '',
            'nationality'       => $passport['residenceCountryCode']?? '',
            'is_primary_holder' => $passport['isPrimaryDocumentHolder'] ?? false,

            'secure_flight' => $secureFlight ? [
                'given_name' => $secureFlight['givenName']  ?? '',
                'surname'    => $secureFlight['surname']    ?? '',
                'birth_date' => $secureFlight['birthDate']  ?? '',
                'gender'     => $secureFlight['gender']     ?? '',
            ] : null,

            'identity_documents' => $traveler['identityDocuments'] ?? [],
            'loyalty_programs'   => array_map(fn($lp) => [
                'supplier_code'  => $lp['supplierCode']  ?? null,
                'program_type'   => $lp['programType']   ?? null,
                'program_number' => $lp['programNumber'] ?? null,
                'receiver_code'  => $lp['receiverCode']  ?? null,
            ], $traveler['loyaltyPrograms'] ?? []),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SEGMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildSegments(array $flights): array
    {
        if (empty($flights)) return [];

        $segments = [];
        $groupMap = $this->calculateGroups($flights);

        foreach ($flights as $idx => $flight) {
            $depIso = $this->toIso($flight['departureDate'] ?? '', $flight['departureTime'] ?? '00:00:00');
            $arrIso = $this->toIso($flight['arrivalDate']   ?? '', $flight['arrivalTime']   ?? '00:00:00');

            // Connection layover
            $connection = null;
            if (isset($flights[$idx + 1]) && ($groupMap[$idx] ?? 0) === ($groupMap[$idx + 1] ?? 1)) {
                $nextDep = $this->toIso(
                    $flights[$idx+1]['departureDate'] ?? '',
                    $flights[$idx+1]['departureTime'] ?? '00:00:00'
                );
                try {
                    $diff = (new \DateTime($arrIso))->diff(new \DateTime($nextDep));
                    $connection = ['duration' => $diff->h * 60 + $diff->i + $diff->days * 1440];
                } catch (\Exception) {
                    $connection = ['duration' => 0];
                }
            }

            $sellMessages = [];
            if (!empty($flight['arrivalTerminalName']))   $sellMessages[] = 'ARRIVES '  . ($flight['toAirportCode']   ?? '') . ' ' . $flight['arrivalTerminalName'];
            if (!empty($flight['departureTerminalName'])) $sellMessages[] = 'DEPARTS '  . ($flight['fromAirportCode'] ?? '') . ' ' . $flight['departureTerminalName'];

            $segments[] = [
                'key'                    => base64_encode(($flight['itemId'] ?? '') . '-' . ($flight['flightNumber'] ?? '')),
                'item_id'                => $flight['itemId']               ?? '',
                'group'                  => $groupMap[$idx]                 ?? 0,
                'carrier'                => $flight['airlineCode']          ?? '',
                'airline_name'           => $flight['airlineName']          ?? '',
                'operating_carrier'      => $flight['operatingAirlineCode'] ?? '',
                'operating_airline_name' => $flight['operatingAirlineName'] ?? '',
                'operating_flight_number'=> (string)($flight['operatingFlightNumber'] ?? ''),
                'flight_number'          => (string)($flight['flightNumber']           ?? ''),
                'cabin_class'            => $this->titleCase($flight['cabinTypeName']   ?? ''),
                'cabin_type_code'        => $flight['cabinTypeCode']         ?? '',
                'class_of_service'       => $flight['bookingClass']          ?? '',
                'origin'                 => $flight['fromAirportCode']       ?? '',
                'destination'            => $flight['toAirportCode']         ?? '',
                'departure_time'         => $depIso,
                'arrival_time'           => $arrIso,
                'departure_terminal'     => $flight['departureTerminalName'] ?? '',
                'arrival_terminal'       => $flight['arrivalTerminalName']   ?? '',
                'arrival_gate'           => $flight['arrivalGate']           ?? '',
                'travel_time'            => (int)($flight['durationInMinutes'] ?? 0),
                'distance_miles'         => (int)($flight['distanceInMiles']  ?? 0),
                'equipment'              => $flight['aircraftTypeCode']       ?? '',
                'aircraft_name'          => $flight['aircraftTypeName']       ?? '',
                'status'                 => $flight['flightStatusCode']       ?? 'HK',
                'status_name'            => $flight['flightStatusName']       ?? '',
                'source_type'            => $flight['sourceType']             ?? '',
                'confirmation_id'        => $flight['confirmationId']         ?? '',
                'number_of_seats'        => (int)($flight['numberOfSeats']    ?? 1),
                'traveler_indices'       => $flight['travelerIndices']        ?? [1],
                'meals'                  => $flight['meals']                  ?? [],
                'identity_documents'     => $flight['identityDocuments']      ?? [],
                'is_past'                => $flight['isPast']                 ?? false,
                'marriage_group'         => '',
                'provider_code'          => '1G',
                'travel_order'           => $idx + 1,
                'provider_segment_order' => $idx + 1,
                'e_ticketability'        => 'Yes',
                'availability_source'    => $flight['bookingClass'] ?? '',
                'participant_level'      => 'Secure Sell',
                'connection'             => $connection,
                'sell_messages'          => $sellMessages,
            ];
        }

        return $segments;
    }

    private function calculateGroups(array $flights): array
    {
        $groups = [];
        $group  = 0;
        foreach ($flights as $idx => $flight) {
            if ($idx === 0) { $groups[$idx] = 0; continue; }
            if (($flight['fromAirportCode'] ?? '') !== ($flights[$idx-1]['toAirportCode'] ?? '')) $group++;
            $groups[$idx] = $group;
        }
        return $groups;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ALL SEGMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildAllSegments(array $allSegments): array
    {
        return array_map(fn($s) => [
            'id'                  => $s['id']                ?? '',
            'type'                => $s['type']              ?? '',
            'text'                => $s['text']              ?? '',
            'vendor_code'         => $s['vendorCode']        ?? '',
            'start_date'          => $s['startDate']         ?? '',
            'start_time'          => $s['startTime']         ?? '',
            'start_location_code' => $s['startLocationCode'] ?? '',
            'end_date'            => $s['endDate']           ?? '',
            'end_time'            => $s['endTime']           ?? '',
            'end_location_code'   => $s['endLocationCode']   ?? '',
        ], $allSegments);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  JOURNEYS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildJourneys(array $journeys): array
    {
        return array_map(fn($j) => [
            'first_airport_code' => $j['firstAirportCode'] ?? $j['first_airport_code'] ?? '',
            'last_airport_code'  => $j['lastAirportCode']  ?? $j['last_airport_code']  ?? '',
            'departure_date'     => $j['departureDate']    ?? $j['departure_date']     ?? '',
            'departure_time'     => $j['departureTime']    ?? $j['departure_time']     ?? '',
            'number_of_flights'  => $j['numberOfFlights']  ?? $j['number_of_flights']  ?? 1,
        ], $journeys);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REMARKS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildRemarks(array $remarks): array
    {
        return array_map(fn($r) => [
            'type' => $r['type'] ?? '',
            'text' => $r['text'] ?? '',
        ], $remarks);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SPECIAL SERVICES
    // ═══════════════════════════════════════════════════════════════════════

    private function buildSpecialServices(array $services): array
    {
        return array_map(fn($svc) => [
            'code'             => $svc['code']            ?? '',
            'name'             => $svc['name']            ?? '',
            'message'          => $svc['message']         ?? '',
            'status_code'      => $svc['statusCode']      ?? '',
            'status_name'      => $svc['statusName']      ?? '',
            'traveler_indices' => $svc['travelerIndices'] ?? [],
        ], $services);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTION STATUS
    // ═══════════════════════════════════════════════════════════════════════

    private function buildActionStatus(array $services, array $creation, array $fares = [], array $futureTicketing = []): array
    {
        return [
            'key'           => base64_encode('action_status'),
            'type'          => 'TAU',
            'ticket_date'   => $this->extractTicketingDeadline($services, $fares, $futureTicketing),
            'provider_code' => '1S',
        ];
    }

    private function extractTicketingDeadline(array $services, array $fares, array $futureTicketing = []): ?string
    {
        // Step 1: ADTK
        foreach ($services as $svc) {
            if (strtoupper($svc['code'] ?? '') !== 'ADTK') continue;
            $r = $this->parseDateFromMessage($svc['message'] ?? '');
            if ($r !== null) return $r;
        }

        // Step 2: OTHS keywords
        $keywords = [
            'ISSUE TICKET','TICKET BY','TICKETING BY','TICKETING REQUIRED','TKT BY',
            'LAST DAY TO TICKET','ADV PURCHASE BY','ISSUE BY','AUTO CANCEL',
            'CANCEL WITHOUT','VOID BY','PURCHASE BY','ADTK BY',
        ];
        foreach ($services as $svc) {
            if (strtoupper($svc['code'] ?? '') !== 'OTHS') continue;
            $msg = strtoupper(trim($svc['message'] ?? ''));
            $hit = false;
            foreach ($keywords as $kw) { if (str_contains($msg, $kw)) { $hit = true; break; } }
            if (!$hit) continue;
            $r = $this->parseDateFromMessage($svc['message'] ?? '');
            if ($r !== null) return $r;
        }

        // Step 3: purchaseDeadlineDate
        foreach ($fares as $fare) {
            $cd   = $fare['creationDetails'] ?? [];
            $date = $cd['purchaseDeadlineDate'] ?? '';
            $time = $cd['purchaseDeadlineTime'] ?? '23:59';
            if (!empty($date)) return $date . 'T' . $time . ':00.000+06:00';
        }

        // Step 4: futureTicketingPolicy
        if (!empty($futureTicketing['ticketingDate'])) {
            return $futureTicketing['ticketingDate'] . 'T' . ($futureTicketing['ticketingTime'] ?? '23:59') . ':00.000+06:00';
        }

        return null;
    }

    private function parseDateFromMessage(string $message): ?string
    {
        $msg = strtoupper(trim($message));

        $months = [
            'JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06',
            'JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12',
        ];

        // Pattern 1: 09APR26 AT 0949 GMT / 08APR26 1205GMT
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\s+(?:AT\s+)?(\d{4})\s*(GMT|UTC|Z)\b/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], $m[3], $months);
            $time = substr($m[4],0,2).':'.substr($m[4],2,2);
            if ($date) return \Carbon\Carbon::parse($date.'T'.$time.':00+00:00')->setTimezone('Asia/Dhaka')->format('Y-m-d\TH:i:s.000P');
        }

        // Pattern 2: 09APR26 1937DAC
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\s+(\d{4})([A-Z]{3})\b/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], $m[3], $months);
            $time = substr($m[4],0,2).':'.substr($m[4],2,2);
            $tz   = strtoupper($m[5]);
            if ($date) {
                if (in_array($tz, ['GMT','UTC'])) return \Carbon\Carbon::parse($date.'T'.$time.':00+00:00')->setTimezone('Asia/Dhaka')->format('Y-m-d\TH:i:s.000P');
                return $date.'T'.$time.':00.000+06:00';
            }
        }

        // Pattern 3: 13APR 1700 DAC LT
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{4})\s*([A-Z]{3})\s*(LT|TIME\s+ZONE)?\b/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], date('Y'), $months);
            $time = substr($m[3],0,2).':'.substr($m[3],2,2);
            $tz   = strtoupper($m[4]);
            if ($date) {
                if (in_array($tz, ['GMT','UTC'])) return \Carbon\Carbon::parse($date.'T'.$time.':00+00:00')->setTimezone('Asia/Dhaka')->format('Y-m-d\TH:i:s.000P');
                return $date.'T'.$time.':00.000+06:00';
            }
        }

        // Pattern 4: 08APR26/1200
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\/(\d{4})/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], $m[3], $months);
            $time = substr($m[4],0,2).':'.substr($m[4],2,2);
            if ($date) return $date.'T'.$time.':00.000+00:00';
        }

        // Pattern 5: date only
        if (preg_match('/(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], $m[3], $months);
            if ($date) return $date.'T23:59:00.000+00:00';
        }

        // Pattern 6: BY DAC21APR26/1006
        if (preg_match('/BY\s+[A-Z]{3}(\d{1,2})(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)(\d{2,4})\/(\d{4})/i', $msg, $m)) {
            $date = $this->buildIsoDate($m[1], $m[2], $m[3], $months);
            $time = substr($m[4],0,2).':'.substr($m[4],2,2);
            if ($date) return $date.'T'.$time.':00.000+06:00';
        }

        return null;
    }

    private function buildIsoDate(string $day, string $mon, string $year, array $months): string
    {
        $mon = strtoupper($mon);
        if (!isset($months[$mon])) return '';
        if (strlen($year) === 2) $year = (int)$year >= 50 ? '19'.$year : '20'.$year;
        return $year.'-'.$months[$mon].'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FARE RULES
    // ═══════════════════════════════════════════════════════════════════════

    private function buildFareRules(array $fareRules): array
    {
        return array_map(function ($rule) {
            return [
                'origin'           => $rule['originAirportCode']      ?? $rule['origin']        ?? '',
                'destination'      => $rule['destinationAirportCode'] ?? $rule['destination']   ?? '',
                'airline'          => $rule['owningAirlineCode']       ?? $rule['airline']       ?? '',
                'passenger_code'   => $rule['passengerCode']           ?? '',
                'is_refundable'    => $rule['isRefundable']            ?? $rule['is_refundable'] ?? false,
                'is_changeable'    => $rule['isChangeable']            ?? $rule['is_changeable'] ?? false,
                'is_cancelable'    => $rule['isCancelable']            ?? $rule['is_cancelable'] ?? false,

                'refund_penalties' => array_map(fn($p) => [
                    'applicability'    => $p['applicability']          ?? '',
                    'conditions_apply' => $p['conditionsApply']        ?? false,
                    'has_no_show_cost' => $p['hasNoShowCost']          ?? false,
                    'penalty_amount'   => $p['penalty']['amount']       ?? $p['penalty_amount']   ?? '0',
                    'penalty_currency' => $p['penalty']['currencyCode'] ?? $p['penalty_currency'] ?? '',
                    'no_show_amount'   => $p['noShowPenalty']['amount'] ?? $p['no_show_amount']   ?? null,
                    'no_show_currency' => $p['noShowPenalty']['currencyCode'] ?? $p['no_show_currency'] ?? null,
                ], $rule['refundPenalties'] ?? $rule['cancelPenalties'] ?? []),

                'exchange_penalties' => array_map(fn($p) => [
                    'applicability'    => $p['applicability']          ?? '',
                    'conditions_apply' => $p['conditionsApply']        ?? false,
                    'has_no_show_cost' => $p['hasNoShowCost']          ?? false,
                    'penalty_amount'   => $p['penalty']['amount']       ?? $p['penalty_amount']   ?? '0',
                    'penalty_currency' => $p['penalty']['currencyCode'] ?? $p['penalty_currency'] ?? '',
                    'no_show_amount'   => $p['noShowPenalty']['amount'] ?? $p['no_show_amount']   ?? null,
                    'no_show_currency' => $p['noShowPenalty']['currencyCode'] ?? $p['no_show_currency'] ?? null,
                ], $rule['exchangePenalties'] ?? $rule['exchange_penalties'] ?? []),
            ];
        }, $fareRules);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  FLIGHT TICKETS  (exchange-aware, multi-coupon)
    //
    //  Fix 1: flightCoupons + allCoupons merge
    //  Fix 2: partial exchange detection
    //  Fix 3: is_exchanged যেকোনো coupon এ E/R/V থাকলে
    // ═══════════════════════════════════════════════════════════════════════

    private function buildFlightTickets(array $flightTickets): array
    {
        if (empty($flightTickets)) return [];

        return array_map(function ($t) {

            // ── Coupon merge ──────────────────────────────────────────────
            // flightCoupons = active/current coupons (itemId সহ)
            // allCoupons    = সব coupon (exchanged গুলোও)
            // Merge: flightCoupons priority, allCoupons থেকে missing গুলো add
            $flightCoupons = $t['flightCoupons'] ?? [];
            $allCoupons    = $t['allCoupons']    ?? [];

            $fcItemIds = array_filter(array_column($flightCoupons, 'itemId'));

            foreach ($allCoupons as $ac) {
                $acItemId = $ac['itemId'] ?? null;
                // itemId নেই বা flightCoupons এ নেই → add করো
                if ($acItemId === null || !in_array($acItemId, $fcItemIds)) {
                    // duplicate check — same status+itemId ইতিমধ্যে আছে কিনা
                    $exists = collect($flightCoupons)->some(fn($fc) =>
                        ($fc['itemId'] ?? null) === $acItemId &&
                        ($fc['couponStatusCode'] ?? '') === ($ac['couponStatusCode'] ?? '')
                    );
                    if (!$exists) $flightCoupons[] = $ac;
                }
            }

            $rawCoupons = !empty($flightCoupons) ? $flightCoupons : $allCoupons;

            $coupons = array_map(fn($c) => [
                'item_id'            => $c['itemId']          ?? null,
                'coupon_status'      => $c['couponStatus']     ?? null,
                'coupon_status_code' => $c['couponStatusCode'] ?? null,
            ], $rawCoupons);

            // ── Exchange detection ────────────────────────────────────────
            $exchangeCodes = ['E','R','V'];
            $flownCodes    = ['F','B'];  // F=Flown, B=Boarded/Used
            $activeCodes   = ['I','O'];  // I=Not Flown, O=Open

            $hasExchange  = collect($coupons)->some(fn($c) => in_array($c['coupon_status_code'] ?? '', $exchangeCodes));
            $hasFlown     = collect($coupons)->some(fn($c) => in_array($c['coupon_status_code'] ?? '', $flownCodes));
            $hasNotFlown  = collect($coupons)->some(fn($c) => in_array($c['coupon_status_code'] ?? '', $activeCodes));

            // Fully exchanged: সব coupon exchanged
            $allExchanged = !empty($coupons) && collect($coupons)->every(fn($c) => in_array($c['coupon_status_code'] ?? '', $exchangeCodes));

            // Partial exchange: কিছু flown, কিছু exchanged (CCXWQH এর মতো)
            $isPartialExchange = $hasExchange && ($hasFlown || $hasNotFlown) && !$allExchanged;

            $isExchanged = $hasExchange; // যেকোনো exchange coupon আছে

            // ── Commission ───────────────────────────────────────────────
            $commAmt  = $t['commission']['commissionAmount']     ?? null;
            $commCurr = $t['commission']['currencyCode']         ?? null;
            $commPct  = $t['commission']['commissionPercentage'] ?? null;

            return [
                'number'                => $t['number']           ?? null,
                'date'                  => $t['date']             ?? null,
                'airline_code'          => $t['airlineCode']      ?? null,
                'agency_iata'           => $t['agencyIataNumber'] ?? null,
                'traveler_index'        => $t['travelerIndex']    ?? null,
                'ticket_status'         => $t['ticketStatusName'] ?? null,
                'ticket_status_code'    => $t['ticketStatusCode'] ?? null,
                'ticketing_pcc'         => $t['ticketingPcc']     ?? null,
                'is_exchanged'          => $isExchanged,
                'is_partial_exchange'   => $isPartialExchange,
                'all_exchanged'         => $allExchanged,

                'payment' => isset($t['payment']) ? [
                    'subtotal' => $t['payment']['subtotal']     ?? null,
                    'taxes'    => $t['payment']['taxes']        ?? null,
                    'total'    => $t['payment']['total']        ?? null,
                    'currency' => $t['payment']['currencyCode'] ?? null,
                ] : null,

                'flight_coupons'         => $coupons,
                'commission_amount'      => $commAmt,
                'commission_currency'    => $commCurr,
                'commission_percentage'  => $commPct,
            ];
        }, $flightTickets);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACCOUNTING ITEMS
    // ═══════════════════════════════════════════════════════════════════════

    // ═══════════════════════════════════════════════════════════════════════
    //  ACCOUNTING ITEMS
    //  — passenger_name যোগ করা হয়েছে (travelers থেকে)
    //  — original_ticket_ref: exchange এর ক্ষেত্রে text এ -a থেকে বের করা
    //  — is_exchange: new ticket নাকি original
    // ═══════════════════════════════════════════════════════════════════════

    private function buildAccountingItems(array $accountingItems, array $travelers = []): array
    {
        return array_map(function ($a) use ($travelers) {
            // Passenger name: travelerIndices[0] থেকে
            $tIdx     = ($a['travelerIndices'][0] ?? 1) - 1;
            $traveler = $travelers[$tIdx] ?? null;
            $paxName  = $traveler
                ? trim(($traveler['givenName'] ?? '') . ' ' . ($traveler['surname'] ?? ''))
                : null;

            // Exchange reference: text field এ -a{ticketNumber} pattern
            $originalTicketRef = null;
            $isExchange        = false;
            $text = $a['text'] ?? '';
            if (!empty($text) && preg_match('/-a(\d{10,})/i', $text, $m)) {
                $originalTicketRef = $m[1];
                $isExchange        = true;
            }

            // Creation type normalize
            $creationType = $a['creationType'] ?? null;
            if ($isExchange && empty($creationType)) {
                $creationType = 'Exchange';
            }

            return [
                'ticket_number'      => $a['ticketNumber']        ?? null,
                'airline_code'       => $a['airlineCode']         ?? null,
                'fare_amount'        => $a['fareAmount']          ?? null,
                'tax_amount'         => $a['taxAmount']           ?? null,
                'fare_application'   => $a['fareApplicationType'] ?? null,
                'form_of_payment'    => $a['formOfPaymentType']   ?? null,
                'creation_type'      => $creationType,
                'tariff_basis'       => $a['tariffBasisType']     ?? null,
                'traveler_indices'   => $a['travelerIndices']     ?? [],
                'traveler_index'     => ($a['travelerIndices'][0] ?? null),
                'passenger_name'     => $paxName,
                'commission_amount'  => $a['commission']['commissionAmount'] ?? null,
                'is_exchange'        => $isExchange,
                'original_ticket_ref'=> $originalTicketRef,
                'text'               => $text,
            ];
        }, $accountingItems);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PRICING
    //
    //  Fix 1: baggagePieces array format (totalWeightInKilograms নেই)
    //  Fix 2: PQR fare total missing — subtotal+taxes থেকে calculate
    //  Fix 3: fareConstruction এ checkedBaggageAllowance pieces only
    // ═══════════════════════════════════════════════════════════════════════

    private function buildPricing(array $fares, array $fareOffer, array $fareOffers, array $payments): array
    {
        $grandTotal = $payments['flightTotals'][0] ?? [];

        // ── Baggage helper: totalWeight অথবা baggagePieces[0] থেকে নাও ──
        $getBagKg = function(?array $allowance): int {
            if (empty($allowance)) return 0;
            return (int)(
                $allowance['totalWeightInKilograms']
                ?? $allowance['baggagePieces'][0]['maximumWeightInKilograms']
                ?? 0
            );
        };

        // ── Grand totals for overview tile ──
        $checkedKg = $getBagKg($fareOffer['checkedBaggageAllowance'] ?? null);
        $cabinKg   = $getBagKg($fareOffer['cabinBaggageAllowance']   ?? null);

        // ── Fare breakdowns ───────────────────────────────────────────────
        $fareBreakdowns = [];
        foreach ($fares as $fare) {
            $totals = $fare['totals'] ?? [];

            // Fix: total missing → subtotal + taxes
            $fTotal = $totals['total'] ?? null;
            if ($fTotal === null && isset($totals['subtotal'], $totals['taxes'])) {
                $fTotal = (string)(floatval($totals['subtotal']) + floatval($totals['taxes']));
            }
            $fTotal = $fTotal ?? '0';

            $fareBreakdowns[] = [
                'record_id'            => $fare['recordId']             ?? '',
                'record_type_code'     => $fare['recordTypeCode']       ?? 'PQ',
                'record_type_name'     => $fare['recordTypeName']       ?? '',
                'pricing_type_code'    => $fare['pricingTypeCode']      ?? '',
                'pricing_type_name'    => $fare['pricingTypeName']      ?? '',
                'pricing_status_code'  => $fare['pricingStatusCode']    ?? '',
                'pricing_status_name'  => $fare['pricingStatusName']    ?? '',
                'traveler_indices'     => $fare['travelerIndices']       ?? [],
                'traveler_type'        => $fare['requestedTravelerType'] ?? '',
                'priced_traveler_type' => $fare['pricedTravelerType']    ?? '',
                'is_negotiated'        => $fare['isNegotiatedFare']      ?? false,
                'validating_carrier'   => $fare['airlineCode']           ?? '',
                'fare_calculation'     => $fare['fareCalculationLine']   ?? '',
                'subtotal'             => $totals['subtotal']            ?? '0',
                'taxes'                => $totals['taxes']               ?? '0',
                'total'                => $fTotal,
                'currency'             => $totals['currencyCode']        ?? '',
                'original_total'       => $fare['originalTotalValues']['total']        ?? '0',
                'original_currency'    => $fare['originalTotalValues']['currencyCode'] ?? '',

                'tax_breakdown' => array_map(fn($t) => [
                    'code'     => $t['taxCode']             ?? '',
                    'amount'   => $t['taxAmount']['amount'] ?? '0',
                    'currency' => $t['taxAmount']['currencyCode'] ?? '',
                    'is_paid'  => $t['isPaid']              ?? false,
                ], $fare['taxBreakdown'] ?? []),

                'fare_construction' => array_map(fn($fc) => [
                    'fare_basis'         => $fc['fareBasisCode']    ?? '',
                    'brand_fare_code'    => $fc['brandFareCode']    ?? '',
                    'brand_fare_name'    => $fc['brandFareName']    ?? '',
                    'brand_program_code' => $fc['brandProgramCode'] ?? '',
                    'brand_program_name' => $fc['brandProgramName'] ?? '',
                    'is_current'         => $fc['isCurrentItinerary'] ?? false,
                    'base_amount'        => $fc['baseRate']['amount']       ?? '0',
                    'base_currency'      => $fc['baseRate']['currencyCode'] ?? '',
                    // Fix: baggagePieces format
                    'checked_bag_pieces' => $fc['checkedBaggageAllowance']['numberOfPieces']
                        ?? $fc['checkedBaggageAllowance']['maximumPieces']
                            ?? null,
                    'checked_bag_kg'     => $getBagKg($fc['checkedBaggageAllowance'] ?? null)
                        ?: $getBagKg($fareOffer['checkedBaggageAllowance'] ?? null),
                    'cabin_bag_kg'       => $getBagKg($fareOffer['cabinBaggageAllowance'] ?? null),
                ], $fare['fareConstruction'] ?? []),

                'commission' => [
                    'percentage' => $fare['commission']['commissionPercentage'] ?? null,
                    'amount'     => $fare['commission']['commissionAmount']     ?? null,
                ],

                'creation_details' => [
                    'user_sine' => $fare['creationDetails']['creationUserSine'] ?? '',
                    'date'      => $fare['creationDetails']['creationDate']     ?? '',
                    'time'      => $fare['creationDetails']['creationTime']     ?? '',
                    'work_pcc'  => $fare['creationDetails']['userWorkPcc']      ?? '',
                    'home_pcc'  => $fare['creationDetails']['userHomePcc']      ?? '',
                ],
            ];
        }

        // ── Extra baggage charges ─────────────────────────────────────────
        $checkedBaggageCharges = [];
        foreach ($fareOffers as $fo) {
            foreach ($fo['checkedBaggageCharges'] ?? [] as $charge) {
                $checkedBaggageCharges[] = [
                    'max_weight_kg'   => $charge['maximumWeightInKilograms']  ?? null,
                    'max_weight_lbs'  => $charge['maximumWeightInPounds']     ?? null,
                    'max_size_cm'     => $charge['maximumSizeInCentimeters']  ?? null,
                    'max_size_inches' => $charge['maximumSizeInInches']       ?? null,
                    'pieces'          => $charge['numberOfPieces']            ?? 1,
                    'special_item'    => $charge['specialItemDescription']    ?? null,
                    'fee_amount'      => $charge['fee']['amount']             ?? '0',
                    'fee_currency'    => $charge['fee']['currencyCode']       ?? '',
                ];
            }
        }

        // ── Per-leg baggage ───────────────────────────────────────────────
        $perLegBaggage = [];
        foreach ($fareOffers as $foIdx => $fo) {
            // এই leg এর extra paid charges
            $legExtraCharges = [];
            foreach ($fo['checkedBaggageCharges'] ?? [] as $charge) {
                $legExtraCharges[] = [
                    'max_weight_kg'   => $charge['maximumWeightInKilograms']  ?? null,
                    'max_weight_lbs'  => $charge['maximumWeightInPounds']     ?? null,
                    'max_size_cm'     => $charge['maximumSizeInCentimeters']  ?? null,
                    'max_size_inches' => $charge['maximumSizeInInches']       ?? null,
                    'pieces'          => $charge['numberOfPieces']            ?? 1,
                    'special_item'    => $charge['specialItemDescription']    ?? null,
                    'fee_amount'      => $charge['fee']['amount']             ?? '0',
                    'fee_currency'    => $charge['fee']['currencyCode']       ?? '',
                ];
            }

            $perLegBaggage[] = [
                'leg_index'          => $foIdx,
                'traveler_indices'   => $fo['travelerIndices']   ?? [],
                'flight_item_ids'    => array_map(fn($f) => $f['itemId'] ?? '', $fo['flights'] ?? []),
                'checked_bag_kg'     => $getBagKg($fo['checkedBaggageAllowance'] ?? null),
                'checked_bag_pieces' => $fo['checkedBaggageAllowance']['numberOfPieces']
                    ?? $fo['checkedBaggageAllowance']['maximumPieces']
                        ?? null,
                'cabin_bag_kg'       => $getBagKg($fo['cabinBaggageAllowance'] ?? null),
                'cabin_bag_pieces'   => $fo['cabinBaggageAllowance']['numberOfPieces']
                    ?? $fo['cabinBaggageAllowance']['maximumPieces']
                        ?? null,
                'extra_charges'      => $legExtraCharges,
            ];
        }

        return [
            'grand_total' => [
                'subtotal' => $grandTotal['subtotal']     ?? '0',
                'taxes'    => $grandTotal['taxes']        ?? '0',
                'total'    => $grandTotal['total']        ?? '0',
                'currency' => $grandTotal['currencyCode'] ?? '',
            ],
            'fare_breakdowns'         => $fareBreakdowns,
            'per_leg_baggage'         => $perLegBaggage,
            'checked_bag_kg'          => $checkedKg,
            'cabin_bag_kg'            => $cabinKg,
            'checked_baggage_charges' => $checkedBaggageCharges,
            'traveler_indices'        => $fareOffer['travelerIndices'] ?? [],
            'fare_offer_flights'      => array_map(fn($f) => $f['itemId'] ?? '', $fareOffer['flights'] ?? []),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function toIso(string $date, string $time): string
    {
        if (empty($date)) return '';
        if (strlen($time) === 5) $time .= ':00';
        return $date . 'T' . substr($time, 0, 8) . '.000+00:00';
    }

    private function shortGender(string $gender): string
    {
        return match (strtoupper($gender)) {
            'MALE'   => 'M',
            'FEMALE' => 'F',
            default  => $gender,
        };
    }

    private function titleCase(string $str): string
    {
        return ucfirst(strtolower($str));
    }

    private function buildError(string $message): array
    {
        return ['success' => false, 'error' => $message];
    }
}
