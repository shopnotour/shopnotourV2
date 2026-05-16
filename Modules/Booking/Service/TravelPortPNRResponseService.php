<?php

namespace Modules\Booking\Service;

use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;

class TravelPortPNRResponseService
{

    public function handlePnrResponse(array $response, $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'success' => false,
                'error' => 'Booking not found',
                'booking_id' => $bookingId
            ];
        }

        // ✅ Check for successful PNR creation
        if ($response['success']) {
            // ✅ Get PNR from universal_record (it's an array, not object)
            $pnr = $response['universal_record']['locator_code'] ?? null;
            $providerPnr = $response['provider_reservation']['locator_code'] ?? null;
            $supplierPnr = $response['supplier_locator']['locator_code'] ?? null;
            $airReservationLocator = $response['air_reservation']['locator_code'] ?? null;

            if (!$pnr) {
                return [
                    'success' => false,
                    'error' => 'PNR creation failed: No PNR ID returned',
                    'booking_code' => $booking->code
                ];
            }

            // ✅ Update booking with complete PNR data
//            $booking->update([
//                'pnr_id' => $providerPnr, // Universal Record Locator: 333IHY
//                'confirmed_at' => now(),
//                'status' => 'booked',

                // ✅ Additional PNR references
//                'provider_pnr' => $providerPnr, // Provider Locator: DW8F75
//                'supplier_pnr' => $supplierPnr, // Supplier Locator: KVOEIB
//                'air_reservation_locator' => $airReservationLocator, // Air Locator: 333II1
//                'booking_system' => 'travelport',
//                'gds_pcc' => $response['provider_reservation']['owning_pcc'] ?? null, // 3YE4
//            ]);

            $booking->update([
                'pnr_id'       => $providerPnr ?? '',
                'booking_date' => \Carbon\Carbon::parse($response['action_status']['ticket_date'])->format('Y-m-d H:i:s'),
                'status'       => 'booked',
                'confirmed_at' => now(),
                'pnr_raw_data' => json_encode($response),
            ]);

            $booking->passengers()->update([
                'pnr' => $providerPnr ?? '',
                'status'=> 'booked',
            ]);

            // ✅ Update passengers with traveler keys
//            $this->updatePassengerData($booking, $response);

            // ✅ Update segments with confirmation data
//            $this->updateSegmentData($booking, $response);

            Log::info('PNR created successfully', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'universal_pnr' => $pnr,
                'provider_pnr' => $providerPnr,
                'supplier_pnr' => $supplierPnr,
                'trace_id' => $response['trace_id'] ?? null,
                'transaction_id' => $response['transaction_id'] ?? null
            ]);

            return [
                'success' => true,
                'pnr' => $pnr,
                'provider_pnr' => $providerPnr,
                'supplier_pnr' => $supplierPnr,
                'booking_code' => $booking->code,
                'booking_id' => $booking->id,
                'universal_record' => $response['universal_record'],
                'supplier_locator' => $response['supplier_locator'],
                'message' => 'Booking confirmed successfully'
            ];
        }

        // ✅ Handle error response
        return [
            'success' => false,
            'error' => $response['error'] ?? 'PNR creation failed',
            'booking_code' => $booking->code ?? null,
            'booking_id' => $booking->id ?? null
        ];
    }

    /**
     * ✅ Update passenger data with PNR response
     */
    private function updatePassengerData(Booking $booking, array $response)
    {
        if (empty($response['passenger'])) {
            return;
        }

        $passengerData = $response['passenger'];

        // Get first passenger (or loop if multiple passengers in future)
        $passenger = $booking->passengers()->first();

        if ($passenger) {
            $passenger->update([
                'traveler_key' => $passengerData['key'] ?? null,
                'pnr_passenger_type' => $passengerData['traveler_type'] ?? null,
                'pnr_confirmed_at' => now()
            ]);
        }
    }

    /**
     * ✅ Update segment data with confirmation codes
     */
    private function updateSegmentData(Booking $booking, array $response)
    {
        if (empty($response['segments'])) {
            return;
        }

        $segments = $response['segments'];

        // Store segment confirmation data in booking metadata or separate table
        $segmentConfirmations = [];

        foreach ($segments as $index => $segment) {
            $segmentConfirmations[] = [
                'segment_index' => $index,
                'key' => $segment['key'] ?? null,
                'carrier' => $segment['carrier'] ?? null,
                'flight_number' => $segment['flight_number'] ?? null,
                'origin' => $segment['origin'] ?? null,
                'destination' => $segment['destination'] ?? null,
                'departure_time' => $segment['departure_time'] ?? null,
                'arrival_time' => $segment['arrival_time'] ?? null,
                'status' => $segment['status'] ?? null,
                'confirmation_code' => $segment['confirmation_code'] ?? null
            ];
        }

        // Update booking with segment confirmations
        $booking->update([
            'segment_confirmations' => json_encode($segmentConfirmations)
        ]);
    }

    /**
     * Parse Travelport AirCreateReservationRsp XML to Array
     */
    public function parseTravelportBookingResponse($xmlResponse)
    {
        // Load XML
        $xml = simplexml_load_string($xmlResponse);

        // ✅ FIX: Register namespaces matching your XML exactly
        $xml->registerXPathNamespace('SOAP', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('universal', 'http://www.travelport.com/schema/universal_v52_0');
        $xml->registerXPathNamespace('common_v52_0', 'http://www.travelport.com/schema/common_v52_0');
        $xml->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v52_0');

        // Get response root
        $response = $xml->xpath('//universal:AirCreateReservationRsp')[0];

        $result = [
            'success' => true,
            'trace_id' => (string)$response['TraceId'],
            'transaction_id' => (string)$response['TransactionId'],
            'response_time' => (int)$response['ResponseTime'],

            // Universal Record
            'universal_record' => [
                'locator_code' => '',
                'version' => 0,
                'status' => '',
            ],

            // Provider Reservation
            'provider_reservation' => [
                'key' => '',
                'provider_code' => '',
                'locator_code' => '',
                'create_date' => '',
                'modified_date' => '',
                'host_create_date' => '',
                'owning_pcc' => '',
            ],

            // Supplier Locator (Airline PNR)
            'supplier_locator' => [
                'supplier_code' => '',
                'locator_code' => '',
                'create_date_time' => '',
            ],

            // Air Reservation
            'air_reservation' => [
                'locator_code' => '',
                'create_date' => '',
                'modified_date' => '',
            ],

            // Passenger
            'passenger' => [
                'key' => '',
                'traveler_type' => '',
                'gender' => '',
                'dob' => '',
                'prefix' => '',
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'phone_country_code' => '',
                'phone_location' => '',
                'email' => '',
            ],

            // Flight Segments
            'segments' => [],

            // Remarks
            'remarks' => [],

            // Warnings/Errors
            'messages' => [
                'warnings' => [],
                'errors' => [],
            ],

            // Action Status
            'action_status' => [
                'key' => '',
                'type' => '',
                'ticket_date' => '',
                'provider_code' => '',
            ],

            // Agency Info
            'agency_info' => [
                'action_type' => '',
                'agent_code' => '',
                'branch_code' => '',
                'agency_code' => '',
                'event_time' => '',
            ],
        ];

        // Parse Response Messages (Warnings/Errors)
        $messages = $response->xpath('.//common_v52_0:ResponseMessage');
        foreach ($messages as $message) {
            $msg = [
                'code' => (string)$message['Code'],
                'type' => (string)$message['Type'],
                'provider_code' => (string)$message['ProviderCode'],
                'message' => trim((string)$message),
            ];

            if (strtolower($msg['type']) === 'error') {
                $result['messages']['errors'][] = $msg;
            } else {
                $result['messages']['warnings'][] = $msg;
            }
        }

        // Parse Universal Record
        $ur = $response->xpath('.//universal:UniversalRecord')[0] ?? null;
        if ($ur) {
            $result['universal_record'] = [
                'locator_code' => (string)$ur['LocatorCode'],
                'version' => (int)$ur['Version'],
                'status' => (string)$ur['Status'],
            ];

            // Parse Passenger
            $traveler = $ur->xpath('.//common_v52_0:BookingTraveler')[0] ?? null;
            if ($traveler) {
                $travelerName = $traveler->xpath('.//common_v52_0:BookingTravelerName')[0];
                $phone = $traveler->xpath('.//common_v52_0:PhoneNumber')[0];
                $email = $traveler->xpath('.//common_v52_0:Email')[0];

                $result['passenger'] = [
                    'key' => (string)$traveler['Key'],
                    'traveler_type' => (string)$traveler['TravelerType'],
                    'gender' => (string)$traveler['Gender'],
                    'dob' => (string)$traveler['DOB'],
                    'prefix' => (string)$travelerName['Prefix'],
                    'first_name' => (string)$travelerName['First'],
                    'last_name' => (string)$travelerName['Last'],
                    'phone' => $phone ? (string)$phone['Number'] : '',
                    'phone_country_code' => $phone ? (string)$phone['CountryCode'] : '',
                    'phone_location' => $phone ? (string)$phone['Location'] : '',
                    'email' => $email ? (string)$email['EmailID'] : '',
                ];
            }

            // Parse Action Status
            $actionStatus = $ur->xpath('.//common_v52_0:ActionStatus')[0] ?? null;
            if ($actionStatus) {
                $result['action_status'] = [
                    'key' => (string)$actionStatus['Key'],
                    'type' => (string)$actionStatus['Type'],
                    'ticket_date' => (string)$actionStatus['TicketDate'],
                    'provider_code' => (string)$actionStatus['ProviderCode'],
                ];
            }

            // Parse Provider Reservation Info
            $providerInfo = $ur->xpath('.//universal:ProviderReservationInfo')[0] ?? null;
            if ($providerInfo) {
                $result['provider_reservation'] = [
                    'key' => (string)$providerInfo['Key'],
                    'provider_code' => (string)$providerInfo['ProviderCode'],
                    'locator_code' => (string)$providerInfo['LocatorCode'],
                    'create_date' => (string)$providerInfo['CreateDate'],
                    'modified_date' => (string)$providerInfo['ModifiedDate'],
                    'host_create_date' => (string)$providerInfo['HostCreateDate'],
                    'owning_pcc' => (string)$providerInfo['OwningPCC'],
                ];
            }

            // Parse Air Reservation
            $airReservation = $ur->xpath('.//air:AirReservation')[0] ?? null;
            if ($airReservation) {
                $result['air_reservation'] = [
                    'locator_code' => (string)$airReservation['LocatorCode'],
                    'create_date' => (string)$airReservation['CreateDate'],
                    'modified_date' => (string)$airReservation['ModifiedDate'],
                ];

                // Parse Supplier Locator (Airline PNR) - inside AirReservation
                $supplierLocator = $airReservation->xpath('.//common_v52_0:SupplierLocator')[0] ?? null;
                if ($supplierLocator) {
                    $result['supplier_locator'] = [
                        'supplier_code' => (string)$supplierLocator['SupplierCode'],
                        'locator_code' => (string)$supplierLocator['SupplierLocatorCode'],
                        'create_date_time' => (string)$supplierLocator['CreateDateTime'],
                    ];
                }

                // Parse Flight Segments - from AirReservation
                $segments = $airReservation->xpath('.//air:AirSegment');
                foreach ($segments as $segment) {
                    $flightDetails = $segment->xpath('.//air:FlightDetails')[0] ?? null;
                    $connection = $segment->xpath('.//air:Connection')[0] ?? null;
                    $sellMessages = $segment->xpath('.//common_v52_0:SellMessage');

                    $segmentData = [
                        'key' => (string)$segment['Key'],
                        'group' => (int)$segment['Group'],
                        'carrier' => (string)$segment['Carrier'],
                        'flight_number' => (string)$segment['FlightNumber'],
                        'cabin_class' => (string)$segment['CabinClass'],
                        'class_of_service' => (string)$segment['ClassOfService'],
                        'origin' => (string)$segment['Origin'],
                        'destination' => (string)$segment['Destination'],
                        'departure_time' => (string)$segment['DepartureTime'],
                        'arrival_time' => (string)$segment['ArrivalTime'],
                        'travel_time' => (int)$segment['TravelTime'],
                        'equipment' => (string)$segment['Equipment'],
                        'status' => (string)$segment['Status'],
                        'marriage_group' => (string)$segment['MarriageGroup'],
                        'provider_code' => (string)$segment['ProviderCode'],
                        'travel_order' => (int)$segment['TravelOrder'],
                        'provider_segment_order' => (int)$segment['ProviderSegmentOrder'],
                        'e_ticketability' => (string)$segment['ETicketability'],
                        'availability_source' => (string)$segment['AvailabilitySource'],
                        'participant_level' => (string)$segment['ParticipantLevel'],
                        'connection' => $connection ? [
                            'duration' => (int)$connection['Duration'],
                        ] : null,
                        'sell_messages' => [],
                    ];

                    // Add sell messages
                    foreach ($sellMessages as $msg) {
                        $segmentData['sell_messages'][] = trim((string)$msg);
                    }

                    $result['segments'][] = $segmentData;
                }
            }

            // Parse Remarks
            $remarks = $ur->xpath('.//common_v52_0:GeneralRemark');
            foreach ($remarks as $remark) {
                $remarkData = $remark->xpath('.//common_v52_0:RemarkData')[0] ?? null;
                $result['remarks'][] = [
                    'key' => (string)$remark['Key'],
                    'type' => (string)$remark['TypeInGds'],
                    'text' => $remarkData ? trim((string)$remarkData) : '',
                    'create_date' => (string)$remark['CreateDate'],
                ];
            }

            // Parse Agency Info
            $agencyInfo = $ur->xpath('.//common_v52_0:AgencyInfo')[0] ?? null;
            if ($agencyInfo) {
                $agentAction = $agencyInfo->xpath('.//common_v52_0:AgentAction')[0] ?? null;
                if ($agentAction) {
                    $result['agency_info'] = [
                        'action_type' => (string)$agentAction['ActionType'],
                        'agent_code' => (string)$agentAction['AgentCode'],
                        'branch_code' => (string)$agentAction['BranchCode'],
                        'agency_code' => (string)$agentAction['AgencyCode'],
                        'event_time' => (string)$agentAction['EventTime'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Get a clean summary of the booking
     */
    public function getBookingSummary($parsedResponse)
    {
        return [
            'pnr' => $parsedResponse['universal_record']['locator_code'],
            'gds_locator' => $parsedResponse['provider_reservation']['locator_code'],
            'airline_pnr' => $parsedResponse['supplier_locator']['locator_code'],
            'status' => $parsedResponse['universal_record']['status'],
            'passenger_name' => $parsedResponse['passenger']['prefix'] . ' ' .
                $parsedResponse['passenger']['first_name'] . ' ' .
                $parsedResponse['passenger']['last_name'],
            'passenger_email' => $parsedResponse['passenger']['email'],
            'passenger_phone' => '+' . $parsedResponse['passenger']['phone_country_code'] .
                ' ' . $parsedResponse['passenger']['phone'],
            'segments_count' => count($parsedResponse['segments']),
            'has_warnings' => count($parsedResponse['messages']['warnings']) > 0,
            'has_errors' => count($parsedResponse['messages']['errors']) > 0,
            'ticket_date' => $parsedResponse['action_status']['ticket_date'],
        ];
    }
}
