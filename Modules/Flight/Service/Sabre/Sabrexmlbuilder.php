<?php

namespace Modules\Flight\Service\Sabre;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SabreXmlBuilder
{
    /**
     * Build complete CreatePassengerNameRecordRQ XML
     */
    public function buildPnrXml($booking, $passengers, $routes, $options = [])
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CreatePassengerNameRecordRQ/>');
        $xml->addAttribute('version', '2.5.0');
        $xml->addAttribute('xmlns', 'http://services.sabre.com/sp/reservation/v2_4');

        // 1. Travel Itinerary Add Info
        $this->addTravelItineraryInfo($xml, $booking, $passengers, $options);

        // 2. Air Book (Flight Segments)
        $this->addAirBook($xml, $routes, count($passengers));
//return $routes;
        // 3. Air Price
        $this->addAirPrice($xml, $passengers);

        // 4. Special Requests (Secure Flight, Advance Passenger, Services)
        $this->addSpecialRequests($xml, $passengers, $booking, $options);

        // 5. Ancillary Services (Optional: Baggage, Meals, Seats)
        if (!empty($options['ancillaries'])) {
            $this->addAncillaryServices($xml, $options['ancillaries'], $passengers);
        }

        // 6. Post Processing
        $this->addPostProcessing($xml, $options['received_from'] ?? 'SabreAPI');

        return $this->formatXml($xml);
    }

    /**
     * Add Travel Itinerary Add Info section
     */

    protected function addTravelItineraryInfo($xml, $booking, $passengers, $options)
    {
        $addInfo = $xml->addChild('TravelItineraryAddInfo');

        // Agency Info
        $agencyInfo = $addInfo->addChild('AgencyInfo');
        $ticketing = $agencyInfo->addChild('Ticketing');
        $ticketing->addAttribute('TicketType', $options['ticket_type'] ?? '7TAW');
        $xml->addAttribute('targetCity', $options['target_city'] ?? config('sabre.credentials.pcc', '27YK'));
        $xml->addAttribute('haltOnAirPriceError', 'true');
        // Customer Info
        $customerInfo = $addInfo->addChild('CustomerInfo');

        // ✅ STEP 1: First separate adults and infants
        $adults = [];
        $children = [];
        $infants = [];

        foreach ($passengers as $passenger) {
//            $type = $this->getPassengerType($passenger);
            $type = $passenger->traveler_type;

            if (in_array($type, ['INFANT', 'INS'])) {
                $infants[] = $passenger;
            } elseif (in_array($type, ['CHILD', 'CNN'])) {
                $children[] = $passenger;
            } else {
                $adults[] = $passenger;
            }
        }

        // ✅ STEP 2: Calculate NameNumbers
        // Adults first: 1.1, 2.1, 3.1
        // Children next: 4.1, 5.1
        // Infants: attached to adults (1.2, 2.2 etc)

        $nameNumberCounter = 1;
        $infantAssignments = []; // Track which infant belongs to which adult

        // Contact Numbers - প্রথম adult এর phone
        $contactNumbers = $customerInfo->addChild('ContactNumbers');
        $firstAdult = $adults[0] ?? $children[0] ?? null;

        if ($firstAdult) {
            $phone = $this->cleanPhone($firstAdult->phone ?? $booking->phone ?? '');
            if ($phone) {
                $contactNumber = $contactNumbers->addChild('ContactNumber');
                $contactNumber->addAttribute('LocationCode', $routes[0]->departure_iata_code ?? 'DAC');
                $contactNumber->addAttribute('NameNumber', '1.1');
                $contactNumber->addAttribute('Phone', $phone);
                $contactNumber->addAttribute('PhoneUseType', 'M');

            }

            // Email
            $email = $booking->email ?? 'shopnotour@gmail.com';
//            $email = $firstAdult->email ?? $booking->email ?? '';
            if ($email) {
                $emailNode = $customerInfo->addChild('Email');
                $emailNode->addAttribute('Address', $email);
                $emailNode->addAttribute('NameNumber', '1.1');
            }
        }

        // ✅ STEP 3: Add Adults
        foreach ($adults as $index => $adult) {
            $nameNumber = $nameNumberCounter . '.1';

            $personName = $customerInfo->addChild('PersonName');
            $personName->addAttribute('NameNumber', $nameNumber);
            $personName->addAttribute('PassengerType', 'ADT');

            $personName->addChild('GivenName', htmlspecialchars($adult->first_name));
            $personName->addChild('Surname', htmlspecialchars($adult->last_name));

            // ✅ Associate infant with this adult (if available)
            if (!empty($infants)) {
                $infant = array_shift($infants); // Get first unassigned infant
                $infantAssignments[$nameNumberCounter] = $infant;
            }

            $nameNumberCounter++;
        }

        // ✅ STEP 4: Add Children
        foreach ($children as $child) {
            $nameNumber = $nameNumberCounter . '.1';

            $personName = $customerInfo->addChild('PersonName');
            $personName->addAttribute('NameNumber', $nameNumber);
            $personName->addAttribute('PassengerType', $this->getPassengerType($child));

            $personName->addChild('GivenName', htmlspecialchars($child->first_name));
            $personName->addChild('Surname', htmlspecialchars($child->last_name));

            $nameNumberCounter++;
        }

        // ✅ STEP 5: Add Infants (with adult association)
        foreach ($infantAssignments as $adultNumber => $infant) {
            $nameNumber = $adultNumber . '.2'; // .2 means associated infant

            $personName = $customerInfo->addChild('PersonName');
            $personName->addAttribute('NameNumber', $nameNumber);
            $personName->addAttribute('PassengerType', 'INF');
            $personName->addAttribute('Infant', 'true');

            // ✅ CRITICAL: Associate with parent
            $personName->addAttribute('WithInfant', 'true');

            $personName->addChild('GivenName', htmlspecialchars($infant->first_name));
            $personName->addChild('Surname', htmlspecialchars($infant->last_name));
        }

        // ✅ STEP 6: Handle remaining infants (if more infants than adults)
        if (!empty($infants)) {
            Log::warning('More infants than adults - some infants cannot be assigned', [
                'remaining_infants' => count($infants)
            ]);

            // Option 1: Treat them as INS (infant with seat)
            // Option 2: Return error
            // For now, we'll add them as INS
            foreach ($infants as $infant) {
                $nameNumber = $nameNumberCounter . '.1';

                $personName = $customerInfo->addChild('PersonName');
                $personName->addAttribute('NameNumber', $nameNumber);
                $personName->addAttribute('PassengerType', 'INS'); // Infant with seat
                $personName->addAttribute('Infant', 'true');

                $personName->addChild('GivenName', htmlspecialchars($infant->first_name));
                $personName->addChild('Surname', htmlspecialchars($infant->last_name));

                $nameNumberCounter++;
            }
        }
    }
//    protected function addTravelItineraryInfo($xml, $booking, $passengers, $options)
//    {
//        $addInfo = $xml->addChild('TravelItineraryAddInfo');
//
//        // Agency Info
//        $agencyInfo = $addInfo->addChild('AgencyInfo');
//        $ticketing = $agencyInfo->addChild('Ticketing');
//        $ticketing->addAttribute('TicketType', $options['ticket_type'] ?? '7TAW');
//
//        // Customer Info
//        $customerInfo = $addInfo->addChild('CustomerInfo');
//
//        // Contact Numbers
//        $contactNumbers = $customerInfo->addChild('ContactNumbers');
//        $firstPassenger = $passengers[0];
//
//        $phone = $this->cleanPhone($firstPassenger->phone ?? $booking->phone ?? '');
//        if ($phone) {
//            $contactNumber = $contactNumbers->addChild('ContactNumber');
//            $contactNumber->addAttribute('NameNumber', '1.1');
//            $contactNumber->addAttribute('Phone', $phone);
//            $contactNumber->addAttribute('PhoneUseType', 'M');
//        }
//
//        // Email (Optional)
//        $email = $firstPassenger->email ?? $booking->email ?? '';
//        if ($email) {
//            $emailNode = $customerInfo->addChild('Email');
//            $emailNode->addAttribute('Address', $email);
//            $emailNode->addAttribute('NameNumber', '1.1');
//        }
//
//        // Person Names
//        $infantCounter = 0;
//        foreach ($passengers as $index => $passenger) {
//            $nameNumber = ($index + 1) . '.1';
//            $passengerType = $this->getPassengerType($passenger);
//
//            // Check if this is an infant
//            $isInfant = in_array($passengerType, ['INF', 'INS']);
//
//            $personName = $customerInfo->addChild('PersonName');
//            $personName->addAttribute('NameNumber', $nameNumber);
//            $personName->addAttribute('PassengerType', $passengerType);
//
//            if ($isInfant) {
//                $personName->addAttribute('Infant', 'true');
//                $infantCounter++;
//            }
//
//            $personName->addChild('GivenName', htmlspecialchars($passenger->first_name));
//            $personName->addChild('Surname', htmlspecialchars($passenger->last_name));
//        }
//    }

    /**
     * Add AirBook section (Flight Segments)
     */
    protected function addAirBook($xml, $routes, $totalPassengers)
    {
//        $routes = is_array($routes) ? $routes : $routes->toArray();
        $airBook = $xml->addChild('AirBook');
        $haltCodes = ['NO', 'NN', 'UC', 'US', 'UN', 'LL', 'HL', 'HX', 'WL'];
        foreach ($haltCodes as $code) {
            $haltOnStatus = $airBook->addChild('HaltOnStatus');
            $haltOnStatus->addAttribute('Code', $code);  // ✅ Attribute, not child
        }
        // Origin Destination Information
        $originDest = $airBook->addChild('OriginDestinationInformation');

        foreach ($routes as $index => $route) {
            $segment = $originDest->addChild('FlightSegment');

            // Attributes
            $departureDateTime = Carbon::parse($route->departure_at)->format('Y-m-d\TH:i:s');
            $segment->addAttribute('DepartureDateTime', $departureDateTime);
            $segment->addAttribute('FlightNumber', $this->extractFlightNumber($route->flight_number));
//            $segment->addAttribute('FlightNumber',$route->flight_number);
            $segment->addAttribute('NumberInParty', (string)$totalPassengers);
            $segment->addAttribute('ResBookDesigCode', $route->class ?? 'Y');
            $segment->addAttribute('Status', 'NN');

            // Destination Location
            $destLocation = $segment->addChild('DestinationLocation');
            $destLocation->addAttribute('LocationCode', $route->arrival_iata_code   );

            // Marketing Airline
            $marketingAirline = $segment->addChild('MarketingAirline');
            $marketingAirline->addAttribute('Code', $route->carrier_code);
            $marketingAirline->addAttribute('FlightNumber', $this->extractFlightNumber($route->flight_number));

            // Marriage Group (Connection Logic)
            $marriageGroup = $this->determineMarriageGroup($routes, $index);
            if ($marriageGroup) {
                $segment->addAttribute('MarriageGrp', $marriageGroup);
            }

            // Origin Location
            $originLocation = $segment->addChild('OriginLocation');
            $originLocation->addAttribute('LocationCode', $route->departure_iata_code);
        }

        // Redisplay Reservation
        $redisplay = $airBook->addChild('RedisplayReservation');
        $redisplay->addAttribute('NumAttempts', '2');
        $redisplay->addAttribute('WaitInterval', '300');
    }

    /**
     * Add Air Price section
     */
    protected function addAirPrice($xml, $passengers)
    {
        $airPrice = $xml->addChild('AirPrice');
        $priceRequest = $airPrice->addChild('PriceRequestInformation');
        $priceRequest->addAttribute('Retain', 'true');

        $optionalQualifiers = $priceRequest->addChild('OptionalQualifiers');
        $pricingQualifiers = $optionalQualifiers->addChild('PricingQualifiers');

        // Count passenger types
        $passengerCounts = $this->countPassengerTypes($passengers);

        foreach ($passengerCounts as $type => $count) {
            if ($count > 0) {
                $passengerType = $pricingQualifiers->addChild('PassengerType');
                $passengerType->addAttribute('Code', $type);
                $passengerType->addAttribute('Quantity', (string)$count);
            }
        }
    }

    /**
     * Add Special Requests (Secure Flight, APIS, SSR)
     */
    protected function addSpecialRequests($xml, $passengers, $booking, $options)
    {
        $specialReq = $xml->addChild('SpecialReqDetails');
        $specialService = $specialReq->addChild('SpecialService');
        $specialServiceInfo = $specialService->addChild('SpecialServiceInfo');

        $infantAssociations = []; // Track infant-adult associations

        foreach ($passengers as $index => $passenger) {
            $nameNumber = ($index + 1) . '.1';
            $passengerType = $this->getPassengerType($passenger);
            $isInfant = in_array($passengerType, ['INF', 'INS']);

            // 1. Secure Flight (TSA Required)
            $secureFlight = $specialServiceInfo->addChild('SecureFlight');
            $secureFlight->addAttribute('SegmentNumber', 'A');

            $personName = $secureFlight->addChild('PersonName');
            $personName->addAttribute('NameNumber', $nameNumber);
            $personName->addAttribute('DateOfBirth', $this->formatDateOfBirth($passenger->dob ?? null, $passengerType));
            $personName->addAttribute('Gender', $this->getGender($passenger, $isInfant));

            $personName->addChild('GivenName', htmlspecialchars($passenger->first_name));
            $personName->addChild('Surname', htmlspecialchars($passenger->last_name));

            $vendorPrefs = $secureFlight->addChild('VendorPrefs');
            $airline = $vendorPrefs->addChild('Airline');
            $airline->addAttribute('Hosted', 'false');
            // 2. Advance Passenger (APIS - Passport Info)
            if (!empty($passenger->passport_number)) {
                $advPassenger = $specialServiceInfo->addChild('AdvancePassenger');
                $advPassenger->addAttribute('SegmentNumber', 'A');

                $document = $advPassenger->addChild('Document');
                $document->addAttribute('Number', $passenger->passport_number);
                $document->addAttribute('ExpirationDate', $this->formatPassportExpiry($passenger->passport_expiry_date ?? null));
                $document->addAttribute('Type', 'P');
                $document->addAttribute('IssueCountry', $passenger->country ?? 'BD');
                $document->addAttribute('NationalityCountry', $passenger->country ?? 'BD');

                $personNameAPIS = $advPassenger->addChild('PersonName');
                $personNameAPIS->addAttribute('NameNumber', $nameNumber);
                $personNameAPIS->addChild('GivenName', htmlspecialchars($passenger->first_name));
                $personNameAPIS->addChild('Surname', htmlspecialchars($passenger->last_name));
                $personNameAPIS->addAttribute('Gender', $this->getGender($passenger, $isInfant));
                $personNameAPIS->addAttribute('DateOfBirth', $this->formatDateOfBirth($passenger->dob ?? null, $passengerType));
            }

            // 3. Infant Association (INFT SSR)
            if ($isInfant) {
                // Find the accompanying adult (usually the first adult)
                $adultNameNumber = '1.1'; // Default to first passenger
                foreach ($passengers as $adultIndex => $adultPassenger) {
                    $adultType = $this->getPassengerType($adultPassenger);
                    if ($adultType === 'ADT') {
                        $adultNameNumber = ($adultIndex + 1) . '.1';
                        break;
                    }
                }

                $service = $specialServiceInfo->addChild('Service');
                $service->addAttribute('SegmentNumber', 'A');
                $service->addAttribute('SSR_Code', 'INFT');

                $personNameINFT = $service->addChild('PersonName');
                $personNameINFT->addAttribute('NameNumber', $adultNameNumber); // Associate with adult

                $dobFormatted = $this->formatInfantDob($passenger->dob ?? null);
                $service->addChild('Text', htmlspecialchars("{$passenger->last_name}/{$passenger->first_name}/{$dobFormatted}"));
            }

            // 4. Contact Info SSRs (CTCM, CTCE)
            if ($index === 0) { // First passenger only
                $phone = $this->cleanPhone($passenger->phone ?? $booking->phone ?? '');
                if ($phone) {
                    $serviceCTCM = $specialServiceInfo->addChild('Service');
                    $serviceCTCM->addAttribute('SegmentNumber', 'A');
                    $serviceCTCM->addAttribute('SSR_Code', 'CTCM');
                    $personNameCTCM = $serviceCTCM->addChild('PersonName');
                    $personNameCTCM->addAttribute('NameNumber', $nameNumber);
                    $serviceCTCM->addChild('Text', $phone);
                }

                $email = $booking->email ?? 'shopnotour@gmail.com';
                if ($email) {
                    $serviceCTCE = $specialServiceInfo->addChild('Service');
                    $serviceCTCE->addAttribute('SegmentNumber', 'A');
                    $serviceCTCE->addAttribute('SSR_Code', 'CTCE');
                    $personNameCTCE = $serviceCTCE->addChild('PersonName');
                    $personNameCTCE->addAttribute('NameNumber', $nameNumber);
                    $serviceCTCE->addChild('Text', str_replace('@', '//', $email));
                }
            }
        }
    }

    /**
     * Add Ancillary Services (Baggage, Meals, Seats)
     */
    protected function addAncillaryServices($xml, $ancillaries, $passengers)
    {
        // This will be added to SpecialServiceInfo
        $specialReq = $xml->xpath('//SpecialServiceInfo')[0];

        foreach ($ancillaries as $ancillary) {
            $service = $specialReq->addChild('Service');
            $service->addAttribute('SegmentNumber', $ancillary['segment'] ?? 'A');
            $service->addAttribute('SSR_Code', $ancillary['ssr_code']); // e.g., EXBG, MEAL, SEAT

            if (!empty($ancillary['passenger_name_number'])) {
                $personName = $service->addChild('PersonName');
                $personName->addAttribute('NameNumber', $ancillary['passenger_name_number']);
            }

            if (!empty($ancillary['text'])) {
                $service->addChild('Text', htmlspecialchars($ancillary['text']));
            }
        }
    }

    /**
     * Add Post Processing section
     */
    protected function addPostProcessing($xml, $receivedFrom)
    {
        $postProcessing = $xml->addChild('PostProcessing');

        $redisplay = $postProcessing->addChild('RedisplayReservation');
        $redisplay->addAttribute('waitInterval', '100');
        $redisplay->addAttribute('returnExtendedPriceQuote', 'true');

        $endTransaction = $postProcessing->addChild('EndTransaction');
        $source = $endTransaction->addChild('Source');
        $source->addAttribute('ReceivedFrom', $receivedFrom);
    }

    /**
     * Helper: Extract flight number from string (e.g., "AA123" -> "123")
     */
    protected function extractFlightNumber($flightNumber)
    {
        return preg_replace('/[^0-9]/', '', $flightNumber);
    }

    /**
     * Helper: Determine marriage group for connection detection
     */
    protected function determineMarriageGroup($routes, $currentIndex)
    {
        if ($currentIndex === 0) {
            return 'O'; // First segment always starts new group
        }

        $prevRoute = $routes[$currentIndex - 1];
        $currentRoute = $routes[$currentIndex];

        $prevArrival = Carbon::parse($prevRoute->arrival_at);
        $currDeparture = Carbon::parse($currentRoute->departure_at);
        $hoursDiff = $prevArrival->diffInHours($currDeparture);

        // Same airport AND < 24 hours = Connection (I)
        if ($currentRoute->departure_iata_code === $prevRoute->arrival_iata_code && $hoursDiff < 24) {
            return 'I';
        }

        return 'O'; // New journey leg
    }

    /**
     * Helper: Get passenger type from passenger data
     */
    protected function getPassengerType($passenger)
    {
        $meta = is_array($passenger->meta) ? $passenger->meta : json_decode($passenger->meta ?? '[]', true);

        // Check if passenger_type_code exists in meta
        if (!empty($meta['passenger_type_code'])) {
            return strtoupper($meta['passenger_type_code']);
        }

        // Fallback to DOB-based detection
        if (!empty($passenger->dob)) {
            $age = Carbon::parse($passenger->dob)->age;
            if ($age < 2) return 'INF';
            if ($age < 12) return 'CHD';
        }

        return 'ADT';
    }

    /**
     * Helper: Count passengers by type
     */
    protected function countPassengerTypes($passengers)
    {
        $counts = ['ADT' => 0, 'CHD' => 0, 'INF' => 0];

        foreach ($passengers as $passenger) {
            $type = $this->getPassengerType($passenger);
            if (isset($counts[$type])) {
                $counts[$type]++;
            } else {
                $counts[$type] = 1;
            }
        }

        return $counts;
    }

    /**
     * Helper: Format date of birth for Secure Flight
     */
    protected function formatDateOfBirth($dob, $passengerType)
    {
        if (!empty($dob)) {
            try {
                return Carbon::parse($dob)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback
            }
        }

        // Default DOB based on passenger type
        $now = Carbon::now();
        if ($passengerType === 'INF') {
            return $now->subMonths(12)->format('Y-m-d');
        } elseif ($passengerType === 'CHD') {
            return $now->subYears(8)->format('Y-m-d');
        }

        return $now->subYears(30)->format('Y-m-d');
    }

    /**
     * Helper: Format infant DOB for INFT SSR (DDMMMYY format)
     */
    protected function formatInfantDob($dob)
    {
        if (!empty($dob)) {
            try {
                return Carbon::parse($dob)->format('dMy'); // e.g., 12JAN20
            } catch (\Exception $e) {
                // Fallback
            }
        }

        return Carbon::now()->subMonths(12)->format('dMy');
    }

    /**
     * Helper: Format passport expiry date
     */
    protected function formatPassportExpiry($expiry)
    {
        if (!empty($expiry)) {
            try {
                $parsed = Carbon::parse($expiry);
                // ✅ Validate it's a future date
                if ($parsed->isFuture()) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Fallback to default
            }
        }

        // ✅ Default: 5 years from now
        return Carbon::now()->addYears(5)->format('Y-m-d');
    }

    /**
     * Helper: Get gender from passenger data
     */
    protected function getGender($passenger, $isInfant = false)
    {
        $gender = strtoupper($passenger->gender ?? 'M');

        if ($isInfant) {
            return $gender === 'F' ? 'FI' : 'MI'; // Female Infant / Male Infant
        }

        return $gender === 'F' ? 'F' : 'M';
    }

    /**
     * Helper: Clean phone number
     */
    protected function cleanPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Format XML with proper indentation
     */
    protected function formatXml($xml)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Build simplified XML for testing (without ancillaries)
     */
    public function buildSimplePnrXml($booking, $passengers, $routes)
    {
        return $this->buildPnrXml($booking, $passengers, $routes, [
            'ticket_type' => '7TAW',
            'target_city' => config('sabre.credentials.pcc', '27YK'), // ✅ ADD THIS
            'received_from' => 'SabreAPIsTools', // ✅ UPDATE THIS
            'ancillaries' => []
        ]);
    }
}
