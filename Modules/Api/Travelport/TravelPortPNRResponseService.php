<?php

namespace Modules\Api\Travelport;


use Illuminate\Support\Facades\Log;

class TravelPortPNRResponseService
{
    /**
     * Parse TravelPort getPnr() response → Sabre-compatible format.
     * Keys match SabreBookingResponseService::parseGetReservationResponse() output exactly.
     */
    public function parseGetReservationResponse(array|string $response): array
    {
        try {
            if (is_string($response)) {
                $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            }
            if (!($response['success'] ?? false)) {
                return $this->buildError('TravelPort response indicates failure');
            }
            $bookingId   = $response['bookingId'] ?? '';
            if (empty($bookingId)) {
                return $this->buildError('bookingId not found in TravelPort response');
            }
            $creation      = $response['creationDetails'] ?? [];
            $travelers     = $response['travelers'] ?? [];
            $flights       = $response['flights'] ?? [];
            $services      = $response['specialServices'] ?? [];
            $fares         = $response['fares'] ?? [];
            $fareOffer     = $response['fareOffers'][0] ?? [];
            $fareOffers    = $response['fareOffers'] ?? [];
            $payments      = $response['payments'] ?? [];
            $fareRules     = $response['fareRules'] ?? [];
            $journeys      = $response['journeys'] ?? [];
            $contactInfo   = $response['contactInfo'] ?? [];
            $allSegments   = $response['allSegments'] ?? [];
            $createIso   = $this->toIso($creation['creationDate'] ?? '', $creation['creationTime'] ?? '00:00');
            $modifiedIso = $this->toIso(
                $creation['lastUpdateDate'] ?? $creation['creationDate'] ?? '',
                $creation['lastUpdateTime'] ?? $creation['creationTime'] ?? '00:00'
            );
            return [
                'success'          => true,
                'trace_id'         => (string) rand(1000, 9999),
                'transaction_id'   => strtoupper(md5($bookingId . microtime())),
                'response_time'    => 0,
                // ── Booking meta ─────────────────────────────────────────
                'booking_id'        => $bookingId,
                'start_date'        => $response['startDate'] ?? '',
                'end_date'          => $response['endDate'] ?? '',
                'is_cancelable'     => $response['isCancelable'] ?? false,
                'is_ticketed'       => $response['isTicketed'] ?? false,
                'timestamp'         => $response['timestamp'] ?? '',
                'booking_signature' => $response['bookingSignature'] ?? '',
                // ── Contact info ─────────────────────────────────────────
                'contact_info' => [
                    'phones' => $contactInfo['phones'] ?? [],
                    'emails' => $contactInfo['emails'] ?? [],
                ],
                // ── Universal record ─────────────────────────────────────
                'universal_record' => [
                    'locator_code' => $bookingId,
                    'version'      => 0,
                    'status'       => 'Active',
                ],
                // ── Provider reservation ─────────────────────────────────
                'provider_reservation' => [
                    'key'               => base64_encode($bookingId),
                    'provider_code'     => $creation['primeHostId'] ?? '1G',
                    'locator_code'      => $bookingId,
                    'create_date'       => $createIso,
                    'modified_date'     => $modifiedIso,
                    'host_create_date'  => $creation['creationDate'] ?? '',
                    'owning_pcc'        => $creation['userWorkPcc'] ?? '',
                    'home_pcc'          => $creation['userHomePcc'] ?? '',
                    'prime_host_id'     => $creation['primeHostId'] ?? '',
                    'number_of_updates' => (int) ($creation['numberOfUpdates'] ?? 0),
                ],
                // ── Supplier locator ─────────────────────────────────────
                'supplier_locator' => [
                    'supplier_code'    => $flights[0]['airlineCode'] ?? '',
                    'locator_code'     => $flights[0]['confirmationId'] ?? '',
                    'create_date_time' => $createIso,
                ],
                // ── Air reservation ──────────────────────────────────────
                'air_reservation' => [
                    'locator_code'  => $bookingId,
                    'create_date'   => $createIso,
                    'modified_date' => $modifiedIso,
                ],
                // ── Passengers ───────────────────────────────────────────
                'passenger'  => $this->buildPassenger($travelers[0] ?? []),
                'passengers' => $this->buildPassengers($travelers),
                // ── Segments ─────────────────────────────────────────────
                'segments'     => $this->buildSegments($flights),
                'all_segments' => $this->buildAllSegments($allSegments),
                // ── Journeys ─────────────────────────────────────────────
                'journeys' => $this->buildJourneys($journeys),
                'remarks'  => [],
                'messages' => [
                    'warnings' => [],
                    'errors'   => [],
                ],
                // ── Action status ────────────────────────────────────────
                'action_status' => $this->buildActionStatus($services, $creation),
                // ── Agency info ──────────────────────────────────────────
                'agency_info' => [
                    'action_type' => '',
                    'agent_code'  => $creation['creationUserSine'] ?? '',
                    'branch_code' => '',
                    'agency_code' => $creation['userWorkPcc'] ?? '',
                    'event_time'  => '',
                ],
                // ── Special services / SSR ───────────────────────────────
                'special_services' => $this->buildSpecialServices($services),
                // ── Pricing ──────────────────────────────────────────────
                'pricing' => $this->buildPricing($fares, $fareOffer, $fareOffers, $payments),
                // ── Fare rules + penalties ───────────────────────────────
                'fare_rules' => $this->buildFareRules($fareRules),
            ];
        } catch (\Throwable $e) {
        Log::error('TravelPortBookingResponseService::parseGetReservationResponse', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);
        return $this->buildError($e->getMessage());
    }
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
        $phone    = $traveler['phones'][0] ?? [];
        $passport = null;
        $secureFlightData = null;
        foreach ($traveler['identityDocuments'] ?? [] as $doc) {
            if (($doc['documentType'] ?? '') === 'PASSPORT' && $passport === null) {
                $passport = $doc;
            }
            if (($doc['documentType'] ?? '') === 'SECURE_FLIGHT_PASSENGER_DATA' && $secureFlightData === null) {
                $secureFlightData = $doc;
            }
        }
        return [
            'key'                => base64_encode(($traveler['givenName'] ?? '') . ($traveler['surname'] ?? '')),
            'traveler_type'      => $traveler['passengerCode'] ?? 'ADT',
            'passenger_type'     => $traveler['type'] ?? 'ADULT',
            'name_association_id'=> $traveler['nameAssociationId'] ?? '1',
            'gender'             => $this->shortGender($passport['gender'] ?? $secureFlightData['gender'] ?? ''),
            'dob'                => $passport['birthDate'] ?? $secureFlightData['birthDate'] ?? '',
            'prefix'             => '',
            'first_name'         => $traveler['givenName'] ?? '',
            'last_name'          => $traveler['surname'] ?? '',
            'phone'              => $phone['number'] ?? '',
            'phone_label'        => $phone['label'] ?? '',
            'phone_country_code' => '',
            'phone_location'     => '',
            'email'              => $traveler['emails'][0] ?? '',
            'emails'             => $traveler['emails'] ?? [],
            'phones'             => $traveler['phones'] ?? [],
            // Passport
            'passport_number'    => $passport['documentNumber'] ?? '',
            'passport_type'      => $passport['passportType'] ?? '',
            'passport_expiry'    => $passport['expiryDate'] ?? '',
            'passport_country'   => $passport['issuingCountryCode'] ?? '',
            'nationality'        => $passport['residenceCountryCode'] ?? '',
            'is_primary_holder'  => $passport['isPrimaryDocumentHolder'] ?? false,
            // Secure flight
            'secure_flight' => $secureFlightData ? [
                'given_name' => $secureFlightData['givenName'] ?? '',
                'surname'    => $secureFlightData['surname'] ?? '',
                'birth_date' => $secureFlightData['birthDate'] ?? '',
                'gender'     => $secureFlightData['gender'] ?? '',
            ] : null,
            'identity_documents' => $traveler['identityDocuments'] ?? [],
            // ── Extra keys present in TravelPort but not in Sabre ────────
            'traveler_key' => $traveler['_travelerKey'] ?? '',   // extra
        ];
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  SEGMENTS
    // ═══════════════════════════════════════════════════════════════════════
    private function buildSegments(array $flights): array
    {
        $segments = [];
        $groupMap = $this->calculateGroups($flights);
        foreach ($flights as $idx => $flight) {
            $depIso = $this->toIso($flight['departureDate'] ?? '', $flight['departureTime'] ?? '00:00:00');
            $arrIso = $this->toIso($flight['arrivalDate'] ?? '', $flight['arrivalTime'] ?? '00:00:00');
            // Connection layover
            $connection = null;
            if (isset($flights[$idx + 1]) && $groupMap[$idx] === $groupMap[$idx + 1]) {
                $nextDep = $this->toIso(
                    $flights[$idx + 1]['departureDate'] ?? '',
                    $flights[$idx + 1]['departureTime'] ?? '00:00:00'
                );
                try {
                    $diff       = (new \DateTime($arrIso))->diff(new \DateTime($nextDep));
                    $connection = ['duration' => $diff->h * 60 + $diff->i + $diff->days * 1440];
                } catch (\Exception) {
                    $connection = ['duration' => 0];
                }
            }
            // Terminal sell messages
            $sellMessages = [];
            if (!empty($flight['arrivalTerminalName'])) {
                $sellMessages[] = 'ARRIVES ' . ($flight['toAirportCode'] ?? '') . ' ' . $flight['arrivalTerminalName'];
            }
            if (!empty($flight['departureTerminalName'])) {
                $sellMessages[] = 'DEPARTS ' . ($flight['fromAirportCode'] ?? '') . ' ' . $flight['departureTerminalName'];
            }
            $segments[] = [
                'key'                     => base64_encode($flight['itemId'] . '-' . $flight['flightNumber']),
                'item_id'                 => $flight['itemId'] ?? '',
                'group'                   => $groupMap[$idx],
                'carrier'                 => $flight['airlineCode'] ?? '',
                'airline_name'            => $flight['airlineName'] ?? '',
                'operating_carrier'       => $flight['operatingAirlineCode'] ?? '',
                'operating_airline_name'  => $flight['operatingAirlineName'] ?? '',
                'operating_flight_number' => (string) ($flight['operatingFlightNumber'] ?? ''),
                'flight_number'           => (string) ($flight['flightNumber'] ?? ''),
                'cabin_class'             => $this->titleCase($flight['cabinTypeName'] ?? ''),
                'cabin_type_code'         => $flight['cabinTypeCode'] ?? '',
                'class_of_service'        => $flight['bookingClass'] ?? '',
                'origin'                  => $flight['fromAirportCode'] ?? '',
                'destination'             => $flight['toAirportCode'] ?? '',
                'departure_time'          => $depIso,
                'arrival_time'            => $arrIso,
                'departure_terminal'      => $flight['departureTerminalName'] ?? '',
                'arrival_terminal'        => $flight['arrivalTerminalName'] ?? '',
                'arrival_gate'            => $flight['arrivalGate'] ?? '',
                'travel_time'             => (int) ($flight['durationInMinutes'] ?? 0),
                'distance_miles'          => (int) ($flight['distanceInMiles'] ?? 0),
                'equipment'               => $flight['aircraftTypeCode'] ?? '',
                'aircraft_name'           => $flight['aircraftTypeName'] ?? '',
                'status'                  => $flight['flightStatusCode'] ?? 'HK',
                'status_name'             => $flight['flightStatusName'] ?? '',
                'source_type'             => $flight['sourceType'] ?? '',
                'confirmation_id'         => $flight['confirmationId'] ?? '',
                'number_of_seats'         => (int) ($flight['numberOfSeats'] ?? 1),
                'traveler_indices'        => $flight['travelerIndices'] ?? [1],
                'meals'                   => $flight['meals'] ?? [],
                'identity_documents'      => $flight['identityDocuments'] ?? [],
                'marriage_group'          => '',
                'provider_code'           => '1G',
                'travel_order'            => $idx + 1,
                'provider_segment_order'  => $idx + 1,
                'e_ticketability'         => 'Yes',
                'availability_source'     => $flight['bookingClass'] ?? '',
                'participant_level'       => 'Secure Sell',
                'connection'              => $connection,
                'sell_messages'           => $sellMessages,
                // ── Extra keys present in TravelPort response ─────────────
                'segment_key' => $flight['_segmentKey'] ?? '',  // extra
            ];
        }
        return $segments;
    }
    private function calculateGroups(array $flights): array
    {
        $groups = [];
        $group  = 0;
        foreach ($flights as $idx => $flight) {
            if ($idx === 0) {
                $groups[$idx] = 0;
                continue;
            }
            if (($flight['fromAirportCode'] ?? '') !== ($flights[$idx - 1]['toAirportCode'] ?? '')) {
                $group++;
            }
            $groups[$idx] = $group;
        }
        return $groups;
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  ALL SEGMENTS (raw)
    // ═══════════════════════════════════════════════════════════════════════
    private function buildAllSegments(array $allSegments): array
    {
        return array_map(fn($seg) => [
            'id'                  => $seg['id'] ?? '',
            'type'                => $seg['type'] ?? '',
            'text'                => $seg['text'] ?? '',
            'vendor_code'         => $seg['vendorCode'] ?? '',
            'start_date'          => $seg['startDate'] ?? '',
            'start_time'          => $seg['startTime'] ?? '',
            'start_location_code' => $seg['startLocationCode'] ?? '',
            'end_date'            => $seg['endDate'] ?? '',
            'end_time'            => $seg['endTime'] ?? '',
            'end_location_code'   => $seg['endLocationCode'] ?? '',
        ], $allSegments);
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  JOURNEYS
    // ═══════════════════════════════════════════════════════════════════════
    private function buildJourneys(array $journeys): array
    {
        return array_map(fn($j) => [
            'first_airport_code' => $j['firstAirportCode'] ?? '',
            'last_airport_code'  => $j['lastAirportCode'] ?? '',
            'departure_date'     => $j['departureDate'] ?? '',
            'departure_time'     => $j['departureTime'] ?? '',
            'number_of_flights'  => (int) ($j['numberOfFlights'] ?? 0),
        ], $journeys);
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  SPECIAL SERVICES (SSR)
    // ═══════════════════════════════════════════════════════════════════════
    private function buildSpecialServices(array $services): array
    {
        return array_map(fn($svc) => [
            'code'             => $svc['code'] ?? '',
            'name'             => $svc['name'] ?? '',
            'message'          => $svc['message'] ?? '',
            'status_code'      => $svc['statusCode'] ?? '',
            'status_name'      => $svc['statusName'] ?? '',
            'traveler_indices' => $svc['travelerIndices'] ?? [],
        ], $services);
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  ACTION STATUS
    // ═══════════════════════════════════════════════════════════════════════
    private function buildActionStatus(array $services, array $creation): array
    {
        $ticketDate = '';
        foreach ($services as $svc) {
            if (($svc['code'] ?? '') !== 'OTHS') {
                continue;
            }
            $msg = $svc['message'] ?? '';
            if (preg_match('/BY\\s+(\\d{2}[A-Z]{3}\\d{2})\\s+(\\d{4})GMT/i', $msg, $m)) {
                $date = $this->parseDateShort($m[1]);
                $time = substr($m[2], 0, 2) . ':' . substr($m[2], 2, 2);
                if ($date) {
                    $ticketDate = $date . 'T' . $time . ':00.000+00:00';
                    break;
                }
            }
        }
        if (empty($ticketDate)) {
            $fallback   = date('Y-m-d', strtotime(($creation['creationDate'] ?? date('Y-m-d')) . ' +3 days'));
            $ticketDate = $fallback . 'T' . ($creation['creationTime'] ?? '23:59') . ':00.000+06:00';
        }
        return [
            'key'           => base64_encode('action_status'),
            'type'          => 'TAU',
            'ticket_date'   => $ticketDate,
            'provider_code' => '1G',
        ];
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  FARE RULES + PENALTIES
    // ═══════════════════════════════════════════════════════════════════════
    private function buildFareRules(array $fareRules): array
    {
        return array_map(function ($rule) {
            return [
                'origin'           => $rule['originAirportCode'] ?? '',
                'destination'      => $rule['destinationAirportCode'] ?? '',
                'airline'          => $rule['owningAirlineCode'] ?? '',
                'passenger_code'   => $rule['passengerCode'] ?? '',
                'is_refundable'    => $rule['isRefundable'] ?? false,
                'is_changeable'    => $rule['isChangeable'] ?? false,
                'refund_penalties' => array_map(fn($p) => [
                    'applicability'    => $p['applicability'] ?? '',
                    'conditions_apply' => $p['conditionsApply'] ?? false,
                    'has_no_show_cost' => $p['hasNoShowCost'] ?? false,
                    'penalty_amount'   => $p['penalty']['amount'] ?? '0',
                    'penalty_currency' => $p['penalty']['currencyCode'] ?? '',
                    'no_show_amount'   => $p['noShowPenalty']['amount'] ?? '0',
                    'no_show_currency' => $p['noShowPenalty']['currencyCode'] ?? '',
                ], $rule['refundPenalties'] ?? []),
                'exchange_penalties' => array_map(fn($p) => [
                    'applicability'    => $p['applicability'] ?? '',
                    'conditions_apply' => $p['conditionsApply'] ?? false,
                    'has_no_show_cost' => $p['hasNoShowCost'] ?? false,
                    'penalty_amount'   => $p['penalty']['amount'] ?? '0',
                    'penalty_currency' => $p['penalty']['currencyCode'] ?? '',
                    'no_show_amount'   => $p['noShowPenalty']['amount'] ?? '0',
                    'no_show_currency' => $p['noShowPenalty']['currencyCode'] ?? '',
                ], $rule['exchangePenalties'] ?? []),
            ];
        }, $fareRules);
    }
    // ═══════════════════════════════════════════════════════════════════════
    //  PRICING
    // ═══════════════════════════════════════════════════════════════════════
    private function buildPricing(array $fares, array $fareOffer, array $fareOffers, array $payments): array
    {
        $grandTotal = $payments['flightTotals'][0] ?? [];
        $fareBreakdowns = [];
        foreach ($fares as $fare) {
            $totals = $fare['totals'] ?? [];
            $fareBreakdowns[] = [
                'record_id'            => $fare['recordId'] ?? '',
                'record_type_code'     => $fare['recordTypeCode'] ?? '',
                'record_type_name'     => $fare['recordTypeName'] ?? '',
                'pricing_type_code'    => $fare['pricingTypeCode'] ?? '',
                'pricing_type_name'    => $fare['pricingTypeName'] ?? '',
                'pricing_status_code'  => $fare['pricingStatusCode'] ?? '',
                'pricing_status_name'  => $fare['pricingStatusName'] ?? '',
                'traveler_indices'     => $fare['travelerIndices'] ?? [],
                'traveler_type'        => $fare['requestedTravelerType'] ?? '',
                'priced_traveler_type' => $fare['pricedTravelerType'] ?? '',
                'is_negotiated'        => $fare['isNegotiatedFare'] ?? false,
                'validating_carrier'   => $fare['airlineCode'] ?? '',
                'fare_calculation'     => $fare['fareCalculationLine'] ?? '',
                'subtotal'             => $totals['subtotal'] ?? '0',
                'taxes'                => $totals['taxes'] ?? '0',
                'total'                => $totals['total'] ?? '0',
                'currency'             => $totals['currencyCode'] ?? '',
                'original_total'       => $fare['originalTotalValues']['total'] ?? '0',
                'original_currency'    => $fare['originalTotalValues']['currencyCode'] ?? '',
                'tax_breakdown' => array_map(fn($t) => [
                    'code'     => $t['taxCode'] ?? '',
                    'amount'   => $t['taxAmount']['amount'] ?? '0',
                    'currency' => $t['taxAmount']['currencyCode'] ?? '',
                ], $fare['taxBreakdown'] ?? []),
                'fare_construction' => array_map(fn($fc) => [
                    'fare_basis'          => $fc['fareBasisCode'] ?? '',
                    'brand_fare_code'     => $fc['brandFareCode'] ?? '',
                    'brand_fare_name'     => $fc['brandFareName'] ?? '',
                    'brand_program_code'  => $fc['brandProgramCode'] ?? '',
                    'brand_program_name'  => $fc['brandProgramName'] ?? '',
                    'is_current'          => $fc['isCurrentItinerary'] ?? false,
                    'base_amount'         => $fc['baseRate']['amount'] ?? '0',
                    'base_currency'       => $fc['baseRate']['currencyCode'] ?? '',
                    'checked_bag_kg'      => $fc['checkedBaggageAllowance']['totalWeightInKilograms'] ?? 0,
                ], $fare['fareConstruction'] ?? []),
                'creation_details' => [
                    'user_sine' => $fare['creationDetails']['creationUserSine'] ?? '',
                    'date'      => $fare['creationDetails']['creationDate'] ?? '',
                    'time'      => $fare['creationDetails']['creationTime'] ?? '',
                    'work_pcc'  => $fare['creationDetails']['userWorkPcc'] ?? '',
                    'home_pcc'  => $fare['creationDetails']['userHomePcc'] ?? '',
                ],
            ];
        }
        // Baggage charges from all fareOffers
        $checkedBaggageCharges = [];
        foreach ($fareOffers as $fo) {
            foreach ($fo['checkedBaggageCharges'] ?? [] as $charge) {
                $checkedBaggageCharges[] = [
                    'max_weight_kg'    => $charge['maximumWeightInKilograms'] ?? null,
                    'max_weight_lbs'   => $charge['maximumWeightInPounds'] ?? null,
                    'max_size_cm'      => $charge['maximumSizeInCentimeters'] ?? null,
                    'max_size_inches'  => $charge['maximumSizeInInches'] ?? null,
                    'pieces'           => $charge['numberOfPieces'] ?? 1,
                    'special_item'     => $charge['specialItemDescription'] ?? null,
                    'fee_amount'       => $charge['fee']['amount'] ?? '0',
                    'fee_currency'     => $charge['fee']['currencyCode'] ?? '',
                ];
            }
        }
        return [
            'grand_total' => [
                'subtotal' => $grandTotal['subtotal'] ?? '0',
                'taxes'    => $grandTotal['taxes'] ?? '0',
                'total'    => $grandTotal['total'] ?? '0',
                'currency' => $grandTotal['currencyCode'] ?? '',
            ],
            'fare_breakdowns'          => $fareBreakdowns,
            'checked_bag_kg'           => $fareOffer['checkedBaggageAllowance']['totalWeightInKilograms'] ?? 0,
            'cabin_bag_kg'             => $fareOffer['cabinBaggageAllowance']['totalWeightInKilograms'] ?? 0,
            'checked_baggage_charges'  => $checkedBaggageCharges,
            'traveler_indices'         => $fareOffer['travelerIndices'] ?? [],
            'fare_offer_flights'       => array_map(fn($f) => $f['itemId'] ?? '', $fareOffer['flights'] ?? []),
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
    private function parseDateShort(string $date): string
    {
        $months = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12',
        ];
        if (preg_match('/^(\\d{2})([A-Z]{3})(\\d{2})$/i', strtoupper($date), $m)) {
            $year = (int) $m[3] >= 50 ? '19' . $m[3] : '20' . $m[3];
            return $year . '-' . ($months[$m[2]] ?? '01') . '-' . $m[1];
        }
        return '';
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
