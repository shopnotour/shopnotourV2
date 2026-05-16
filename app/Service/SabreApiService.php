<?php

namespace App\Service;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SabreApiService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $username;
    protected string $password;
    protected string $soapUsername;
    protected string $organization;
    protected string $domain = 'DEFAULT';
    protected int $tokenCacheMinutes;
    protected string $endpoint = 'https://webservices.cert.platform.sabre.com/websvc';

    public function __construct()
    {

        $this->clientId     = env('SABRE_CLIENT_ID');
        $this->clientSecret = env('SABRE_CLIENT_SECRET');
        $this->username     = env('SABRE_USERNAME');
        $this->password     = env('SABRE_PASSWORD');
        $this->soapUsername = env('SABRE_USERNAME_SOAP');
        $this->organization = env('ORGANIZATION');
        $this->baseUrl      = rtrim(env('SABRE_BASE_URL', 'https://api.cert.platform.sabre.com'), '/');
        $this->tokenCacheMinutes = env('SABRE_TOKEN_CACHE_MINUTES', 14);
    }

    public function getAuthToken(): ?string
    {
        return Cache::remember('sabre_api_token', now()->addMinutes($this->tokenCacheMinutes), function () {

            $cookieString = config('sabre.cookie_string', '');
            try {
                $response = Http::timeout(60)
                    ->connectTimeout(30)
                    ->retry(3, 1000)
                    ->withOptions([
                        'verify' => !app()->environment('local'), // Local e SSL verify off
                    ])
                    ->asForm()
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->withHeaders([
                        'Cookie' => $cookieString,
                        'Accept' => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/v3/auth/token", [
                        'grant_type' => 'password',
                        'username'   => $this->username,
                        'password'   => $this->password,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('Sabre Auth Success', ['token_length' => strlen($data['access_token'] ?? '')]);
                    return $data['access_token'] ?? null;
                }

                Log::error('Sabre Auth Failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'headers' => $response->headers(),
                ]);

                return null;

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Sabre Connection Error - Check Network/VPN', [
                    'message' => $e->getMessage(),
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error('Sabre Auth Exception', [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                ]);
                return null;
            }
        });
    }

    public function revalidateItinerary(array $payload): ?array
    {
        // Sabre Bargain Finder Max / Low Fare Search endpoint —
        // VerificationItinCallLogic = B দিলে এটাই revalidation হিসেবে কাজ করে
        $endpoint = '/v5/shop/flights/revalidate';

        $response = $this->request($endpoint, $payload, 'POST');

        Log::info('🔄 Sabre Revalidate Response', [
            'itins_found' => isset($response['groupedItineraryResponse']['statistics']['itineraryCount'])
                ? $response['groupedItineraryResponse']['statistics']['itineraryCount']
                : 'N/A',
        ]);

        return $response; // null on failure (request() already logs errors)
    }
//    public function getAuthToken(): ?string
//    {
//        return Cache::remember('sabre_api_token', now()->addMinutes($this->tokenCacheMinutes), function () {
//
//            // This is the cookie string from your auth cURL command.
//            $cookieString = 'visid_incap_2768617=FvdLoVQQR16OnFg7c/FodLbJEGkAAAAAQUIPAAAAAAANYerE9S36GBRUVm+jkpwN; incap_ses_932_2768614=QNYRO/tP40i1sCs3EiHvDECJEWkAAAAAaliwr+y5UOSwvyij9Wz6dQ==; incap_ses_988_2768614=RCffepdT7Ucdp1Lo2BS2DVDEEWkAAAAAOudaT0ZKhfG3DP6ysZVoUg==; nlbi_2768614=par0clJ7TWsOOw+SRh9LCAAAAABD+Un7fyoiqsHC0mDceCBa; visid_incap_2768614=12ZsCQGTTmCz6iWlwu17yehAEGkAAAAAQUIPAAAAAAB+i0b9GYec/qca3+DRsXYF';
//
//
//            $response = Http::asForm()
//                // Basic Auth uses the Client ID and Client Secret
//                ->withBasicAuth($this->clientId, $this->clientSecret)
//                ->withHeaders([
//                    'Cookie' => $cookieString
//                ])
//                // The URL is v3 as per your cURL command
//                ->post("{$this->baseUrl}/v3/auth/token", [
//                    // The body contains the 'password' grant type and user credentials
//                    'grant_type' => 'password',
//                    'username'   => $this->username,
//                    'password'   => $this->password,
//                ]);
//
//            if ($response->successful()) {
//                $data = $response->json();
//                Log::info('Sabre Auth Success', $data);
//                return $data['access_token'] ?? null;
//            }
//
//            Log::error('Sabre Auth Failed', [
//                'status' => $response->status(),
//                'body'   => $response->body(),
//            ]);
//
//            return null;
//        });
//    }

    /**
     * Send a request to the Sabre API with authentication.
     * Updated to accept custom headers.
     */
     public function request(string $endpoint, array $data = [], string $method = 'POST', array $headers = [])
    {
        $token = $this->getAuthToken();
        if (!$token) {
            throw new \Exception("Failed to retrieve Sabre token.");
        }

        $url = "{$this->baseUrl}{$endpoint}";

        $request = Http::withToken($token)
            ->acceptJson()
            ->retry(3, 1000)
            ->withHeaders($headers); // ✅ সবসময় chain করা, empty হলেও সমস্যা নেই

        $response = $request->{strtolower($method)}($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Sabre API Request Failed", [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
            'body'     => $response->body(),
        ]);

        throw new \Exception("Sabre API Request Failed. Status: " . $response->status() . " Body: " . $response->body());
    }

    public function getPnr(string $confirmationId)
    {
        $endpoint = "/v1/trip/orders/getBooking";

        // Body তে confirmationId পাঠাতে হবে
        $data = [
            'confirmationId' => $confirmationId
        ];

        $response = $this->request($endpoint, $data, 'POST'); // POST method

        if (!$response) {
            throw new \Exception("Failed to retrieve PNR: {$confirmationId}");
        }

        return $response;
    }

    public function lowFareSearch(array $payload)
    {
        // Sabre endpoint for Bargain Finder Max / Low Fare Search
        $endpoint = '/v5/offers/shop';

        // Always POST with JSON body
        return $this->request($endpoint, $payload, 'POST');
    }

    /**
     * Create a booking/reservation using Sabre API
     * UPDATED based on your new cURL command.
     */
    public function createBookingPnr(array $payload)
    {
        // Sabre endpoint for creating reservations
        $endpoint = '/v2.4.0/passenger/records?mode=create';

        // Headers from the new cURL command
        $headers = [
            'Content-Type'    => 'application/json',
            'Conversation-ID' => '2021.01.DevStudio',
            'Diagnostics'     => 'client'
        ];

        // Payload from the new cURL command.
        // Note: The $bookingData parameter is not used, as the payload is hardcoded from the cURL.
        // $payload = [
        //     "CreatePassengerNameRecordRQ" => [
        //         "version" => "2.4.0",
        //         "TravelItineraryAddInfo" => [
        //             "AgencyInfo" => [
        //                 "Ticketing" => [
        //                     "TicketType" => "7TAW"
        //                 ]
        //             ],
        //             "CustomerInfo" => [
        //                 "ContactNumbers" => [
        //                     "ContactNumber" => [
        //                         [
        //                             "Phone" => "74991234567",
        //                             "PhoneUseType" => "A"
        //                         ]
        //                     ]
        //                 ],
        //                 "PersonName" => [
        //                     [
        //                         "NameNumber" => "1.1",
        //                         "GivenName" => "MAX",
        //                         "Surname" => "POWER"
        //                     ]
        //                 ]
        //             ]
        //         ],
        //         "AirBook" => [
        //             "OriginDestinationInformation" => [
        //                 "FlightSegment" => [
        //                     [
        //                         "DepartureDateTime" => "2025-12-10T00:00:00",
        //                         "FlightNumber" => "203",
        //                         "NumberInParty" => "1",
        //                         "ResBookDesigCode" => "Y",
        //                         "Status" => "NN",
        //                         "DestinationLocation" => [
        //                             "LocationCode" => "LAS"
        //                         ],
        //                         "MarketingAirline" => [
        //                             "Code" => "UA",
        //                             "FlightNumber" => "203"
        //                         ],
        //                         "OriginLocation" => [
        //                             "LocationCode" => "ORD"
        //                         ]
        //                     ]
        //                 ]
        //             ]
        //         ],
        //         "MiscSegment" => [
        //             "OriginLocation" => [
        //                 "LocationCode" => "LAX"
        //             ],
        //             "Text" => "OTH MISCELLANEOUS SEGMENT",
        //             "VendorPrefs" => [
        //                 "Airline" => [
        //                     "Code" => "B6"
        //                 ]
        //             ],
        //             "DepartureDateTime" => "09-08",
        //             "NumberInParty" => 1,
        //             "Status" => "GK",
        //             "Type" => "OTH"
        //         ],
        //         "PostProcessing" => [
        //             "EndTransaction" => [
        //                 "Source" => [
        //                     "ReceivedFrom" => "API"
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];

        // Send the request with the new endpoint, payload, method, and headers
        return $this->request($endpoint, $payload, 'POST', $headers);
    }


    public function buildTicketRecords($booking,$passengers)
    {
        $records = [];
        $recordNumber = 0;

        // ✅ Group passengers by type
        $groupedPassengers = $passengers->groupBy(function($passenger) {
            return $this->getPassengerTypeCode($passenger);
        });

        foreach ($groupedPassengers as $typeCode => $passengersOfType) {
            $records[] = [
                'Number' => $recordNumber,
                'Reissue' => false
            ];
            $recordNumber++;
        }

        // If no grouping needed, just one record for all
        if (empty($records)) {
            $records[] = [
                'Number' => 1,
                'Reissue' => false
            ];
        }

        $ticketRequest = [
            'AirTicketRQ' => [
                'DesignatePrinter' => [
                    'Printers' => [
                        'Ticket' => [
                            'CountryCode' => 'BD'
                        ]
                    ]
                ],
                'Itinerary' => [
                    'ID' => $booking->pnr_id
                ],
                'Ticketing' => [
                    [
                        'PricingQualifiers' => [
                            'PriceQuote' => [
                                ['Record' => $records]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $recordNumber;
    }

    public function getPassengerTypeCode($passenger)
    {
        $travelerType = strtoupper($passenger->traveler_type ?? $passenger->passenger_type_code ?? '');

        // Check if already has correct code
        if (in_array($travelerType, ['ADT', 'CNN', 'INF'])) {
            return $travelerType;
        }

        // Check age for child classification
        if ($passenger->dob) {
            $age = \Carbon\Carbon::parse($passenger->dob)->age;

            if ($age >= 12) {
                return 'ADT'; // Adult
            } elseif ($age >= 5) {
                return 'CNN'; // Child 5-12 (C07)
            } elseif ($age >= 2) {
                return 'CNN'; // Child 2-5 (C03)
            } else {
                return 'INF'; // Infant 0-2
            }
        }

        // Fallback to traveler_type
        switch ($travelerType) {
            case 'ADULT':
                return 'ADT';
            case 'CHILD':
                return 'CNN';
            case 'INFANT':
                return 'INF';
            default:
                return 'ADT';
        }
    }

    public function callSabreTicketAPI($request)
    {
        // Get Sabre token
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post(config($this->baseUrl) . '/v1.2.0/air/ticket', $request);

        if (!$response->successful()) {
            Log::error('❌ Sabre API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Sabre API Error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Retrieve booking details from Sabre
     */
    public function getBooking(string $recordLocator)
    {
        $endpoint = "/v1/passenger/records/{$recordLocator}";

        // Pass an empty array for data and specify 'GET' method
        return $this->request($endpoint, [], 'GET');
    }

    /**
     * Cancel a booking in Sabre
     */
    public function cancelBookings(string $recordLocator)
    {
//        return 'sarowar';
        $endpoint = "/v1/passenger/records/{$recordLocator}";

        $cancelData = [
            'requestType' => 'Cancel',
            'actionCode' => 'CANCELLED'
        ];
dd($cancelData);
        // Specify 'PUT' method
        return $this->request($endpoint, $cancelData, 'PUT');
    }

    public function cancelBooking(string $recordLocator): array
    {
        $endpoint = "/v1/trip/orders/cancelBooking";

        $payload = [
            'confirmationId'       => $recordLocator,
            'retrieveBooking'      => true,
            'cancelAll'            => true,
            'errorHandlingPolicy'  => 'ALLOW_PARTIAL_CANCEL',
        ];

        $response = $this->request($endpoint, $payload, 'POST');
//dd($response);
        Log::info('🛑 Sabre cancelBooking Response', [
            'pnr'      => $recordLocator,
            'response' => $response,
        ]);

        return $response ?? [];
    }

    /**
     * Issue ticket for a booking
     */
    public function issueTicket(string $recordLocator, array $ticketData = [])
    {
        $endpoint = "/v1/passenger/records/{$recordLocator}/tickets";

        return $this->request($endpoint, $ticketData, 'POST');
    }

    /**
     * Issue E-Ticket using AirTicketRQ
     * Returns ticket number(s) after successful issuance
     */
    /**
     * Issue E-Ticket using AirTicketRQ
     * Refactored to handle multiple Price Quotes
     */

  public function issueTicketSoap($pnr, $pccTkt, $ptrta, $priceQuotes, $countryCode = 'BD')
{
//    return $priceQuotes;
    // ------------------------------
    // STEP 1: REST TOKEN
    // ------------------------------
    $token = $this->getAuthToken();
    if (!$token) {
        return ['error' => 'Unable to generate REST token'];
    }

    $endpoint = "https://api.cert.platform.sabre.com/v1.3.0/air/ticket";

    // ------------------------------
    // STEP 2: BUILD PRICE QUOTE XML
    // ------------------------------
    $priceQuoteXml = "";
    for ($i = 1; $i <= $priceQuotes; $i++) {
//        $pq = trim($pq);
//        if ($pq === "") continue;

        $priceQuoteXml .= <<<XML
            <PriceQuote>
                <Record Number="{$i}" Reissue="false"/>
            </PriceQuote>
        XML;
    }
//      dd($priceQuoteXml);
//    foreach ($priceQuotes as $pq) {
//        $pq = trim($pq);
//        if ($pq === "") continue;
//
//        $priceQuoteXml .= <<<XML
//            <PriceQuote>
//                <Record Number="{$pq}" Reissue="false"/>
//            </PriceQuote>
//        XML;
//    }

    // fallback — always at least PQ=1
//    if ($priceQuoteXml === "") {
//        $priceQuoteXml = <<<XML
//            <PriceQuote>
//                <Record Number="1" Reissue="false"/>
//            </PriceQuote>
//        XML;
//    }

    // ------------------------------
    // STEP 3: BUILD TICKETING REQUEST
    // ------------------------------
    $xmlPayload = <<<XML
<AirTicketRQ targetCity="{$pccTkt}" version="1.3.0" xmlns="http://services.sabre.com/sp/air/ticket/v1_3">

    <DesignatePrinter>
        <Printers>
            <Hardcopy LNIATA="{$ptrta}"/>
            <Ticket CountryCode="{$countryCode}"/>
        </Printers>
    </DesignatePrinter>

    <Itinerary ID="{$pnr}"/>

    <Ticketing>
        <FOP_Qualifiers>
            <BasicFOP Type="CA"/>
        </FOP_Qualifiers>

        <MiscQualifiers>
            <Commission Percent="0"/>
        </MiscQualifiers>

        <PricingQualifiers>
            {$priceQuoteXml}
        </PricingQualifiers>
    </Ticketing>

    <PostProcessing acceptNegotiatedFare="true" acceptPriceChanges="true" actionOnPQExpired="Q">
        <EndTransaction>
            <Source ReceivedFrom="API"/>
        </EndTransaction>
    </PostProcessing>

</AirTicketRQ>
XML;
//dd($xmlPayload);
    // ------------------------------
    // STEP 4: SEND REQUEST
    // ------------------------------
    $response = Http::withHeaders([
        "Content-Type"   => "application/xml",
        "Accept"         => "application/xml",
        "Authorization"  => "Bearer {$token}",
        "Conversation-ID" => uniqid("conv_"),
    ])->withBody($xmlPayload, "application/xml")
      ->post($endpoint);


       $body = $response->body();
//dd($body);

//// Then try to parse
//      $xml = simplexml_load_string($body);
//      dd($xml); // Check the structure

      // ------------------------------
    // STEP 5: EXTRACT Ticket Numbers Only
    // ------------------------------
    // ------------------------------
// STEP 5: PARSE XML RESPONSE PROPERLY
// ------------------------------

      try {
          $xml = simplexml_load_string($body);

          if (!isset($xml->Summary)) {
              return null;
          }

          $ticketData = [];

          // Iterate through all Summary nodes
          foreach ($xml->Summary as $summary) {
              $ticketData[] = [
                  'ticket_number' => (string)$summary->DocumentNumber,
                  'first_name' => (string)$summary->FirstName,
                  'last_name' => (string)$summary->LastName,
                  'pnr' => (string)$summary->Reservation,
                  'total_amount' => (string)$summary->TotalAmount,
                  'currency_code' => (string)$summary->TotalAmount['currencyCode'],
                  'issue_date_time' => (string)$summary->LocalIssueDateTime,
                  'issuing_location' => (string)$summary->IssuingLocation,
                  'document_type' => (string)$summary->DocumentType,
              ];
          }

//dd($ticketData);
          return $ticketData;

//          foreach ($summaries as $summary) {
//              $ticketData[] = [
//                  'ticket_number' => (string)$summary->DocumentNumber,
//                  'first_name' => (string)$summary->FirstName,
//                  'last_name' => (string)$summary->LastName,
//                  'full_name' => trim((string)$summary->FirstName . ' ' . (string)$summary->LastName),
//                  'document_type' => (string)$summary->DocumentType,
//                  'pnr' => (string)$summary->Reservation,
//                  'issue_date_time' => (string)$summary->LocalIssueDateTime,
//                  'issuing_location' => (string)$summary->IssuingLocation,
//                  'total_amount' => (string)$summary->TotalAmount,
//                  'currency_code' => (string)$summary->TotalAmount['currencyCode'] ?? 'BDT',
//              ];
//          }

//          return $ticketData;
//dd($ticketData);
      } catch (\Exception $e) {
          \Log::error('Ticket parsing error: ' . $e->getMessage());
          return null;
      }
//    preg_match_all('/<DocumentNumber>(\d+)<\/DocumentNumber>/', $body, $matches);
//
//    $ticketNumbers = $matches[1] ?? [];
//
//
//    // ------------------------------
//    // STEP 6: RETURN ONLY TICKET NUMBER ARRAY
//    // ------------------------------
//    return $ticketNumbers;


}








    public function createSoapSession(): ?string
    {
        $xmlPayload = <<<XML
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP-ENV:Header>
                <MessageHeader xmlns="http://www.ebxml.org/namespaces/messageHeader">
                    <From><PartyId>Agency</PartyId></From>
                    <To><PartyId>Sabre_API</PartyId></To>
                    <ConversationId>2021.01.DevStudio</ConversationId>
                    <Action>SessionCreateRQ</Action>
                </MessageHeader>
                <Security xmlns="http://schemas.xmlsoap.org/ws/2002/12/secext">
                    <UsernameToken>
                        <Username>{$this->soapUsername}</Username>
                        <Password>{$this->password}</Password>
                        <Organization>{$this->organization}</Organization>
                        <ClientId>{$this->clientId}</ClientId>
                        <ClientSecret>{$this->clientSecret}</ClientSecret>
                        <Domain>{$this->domain}</Domain>
                    </UsernameToken>
                </Security>
            </SOAP-ENV:Header>
            <SOAP-ENV:Body>
                <SessionCreateRQ returnContextID="true" Version="2.0.0"
                    xmlns="http://www.opentravel.org/OTA/2002/11"/>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>
        XML;

        $response = Http::withHeaders(['Content-Type' => 'text/xml'])
            ->withBody($xmlPayload, 'text/xml')
            ->post($this->endpoint);

        if (!$response->successful()) {
            Log::error('Sabre SessionCreateRQ failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to create SOAP session with Sabre.');
        }

        $body = $response->body();
        $xml = simplexml_load_string($body);


        $xml->registerXPathNamespace('wsse', 'http://schemas.xmlsoap.org/ws/2002/12/secext');
        $tokenNode = $xml->xpath('//wsse:BinarySecurityToken');
        $token = $tokenNode ? (string)$tokenNode[0] : null;

        if (!$token) {
            throw new \Exception('No BinarySecurityToken found in SOAP response.');
        }

        // ✅ Clean escaped characters
        $token = str_replace(['\\/', '\\.'], ['/', '.'], $token);

        // ✅ Cache token for reuse (15 min)
        Cache::put('sabre_soap_token', $token, now()->addMinutes(15));

        Log::info('Sabre SOAP session created successfully', [
            'token_preview' => substr($token, 0, 60) . '...',
        ]);

        return $token;
    }
    public function getReservation(string $recordLocator)
    {
        $securityToken = \Cache::get('sabre_soap_token') ?? $this->createSoapSession();
        $securityToken = str_replace(['\\/', '\\.'], ['/', '.'], $securityToken);
        $endpoint = 'https://webservices.cert.platform.sabre.com/websvc';
        $conversationId = '2021.01.DevStudio';

        $xmlPayload = <<<XML
                    <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
                    <SOAP-ENV:Header>
                        <MessageHeader xmlns="http://www.ebxml.org/namespaces/messageHeader">
                        <From><PartyId>Agency</PartyId></From>
                        <To><PartyId>Sabre_API</PartyId></To>
                        <ConversationId>{$conversationId}</ConversationId>
                        <Action>GetReservationRQ</Action>
                        </MessageHeader>
                        <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
                        <wsse:BinarySecurityToken>{$securityToken}</wsse:BinarySecurityToken>
                        </wsse:Security>
                    </SOAP-ENV:Header>

                    <SOAP-ENV:Body>
                        <GetReservationRQ xmlns="http://webservices.sabre.com/pnrbuilder/v1_19" Version="1.19.0">
                        <Locator>{$recordLocator}</Locator>
                        <RequestType>Stateful</RequestType>
                        <ReturnOptions>
                            <SubjectAreas>
                            <SubjectArea>PRICE_QUOTE</SubjectArea>
                            </SubjectAreas>
                            <ViewName>Default</ViewName>
                            <ResponseFormat>STL</ResponseFormat>
                        </ReturnOptions>
                        </GetReservationRQ>
                    </SOAP-ENV:Body>
                    </SOAP-ENV:Envelope>
                    XML;

        $response = \Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
        ])->withBody($xmlPayload, 'text/xml')->post($endpoint);

        return [
            'xml' => $response->body(),
            'pnr' => $recordLocator,
        ];
    }




public function createPriceQuote( $recordLocator, $routes, $passengers)
{
    // ---- Step 1: Load the PNR ----
    $this->getReservation($recordLocator);

    // ---- Step 2: SOAP Session Token ----
    $securityToken = \Cache::get('sabre_soap_token') ?? $this->createSoapSession();
    $securityToken = str_replace(['\\/', '\\.'], ['/', '.'], $securityToken);

    // ---- Step 3: SOAP Metadata ----
    $endpoint = 'https://webservices.cert.platform.sabre.com/websvc';
    $conversationId = '2021.01.DevStudio';
    $messageId = uniqid();

    // ---- Step 4: Build Flight Segments XML ----
    $flightSegmentsXML = "<OriginDestinationInformation>";

    foreach ($routes as $route) {

        $arrival     = date('Y-m-d\TH:i:s', strtotime($route['arrival_at']));
        $departure   = date('Y-m-d\TH:i:s', strtotime($route['departure_at']));
        $flightNo    = $route['flight_number'];
        $carrier     = $route['carrier_code'];
        $origin      = $route['departure_iata_code'];
        $destination = $route['arrival_iata_code'];

        // REAL BOOKING CLASS
        $fareClass = $route['class'] ?? 'Y';  // e.g. N, Y, Q

        $flightSegmentsXML .= <<<XML
            <FlightSegment
                ArrivalDateTime="{$arrival}"
                DepartureDateTime="{$departure}"
                FlightNumber="{$flightNo}"
                ResBookDesigCode="{$fareClass}">

                <DestinationLocation LocationCode="{$destination}"/>
                <MarketingCarrier Code="{$carrier}" FlightNumber="{$flightNo}"/>
                <OriginLocation LocationCode="{$origin}"/>
            </FlightSegment>
        XML;
    }

    $flightSegmentsXML .= "</OriginDestinationInformation>";

    // ---- Step 5: Build Passenger PQ XML ----
    $paxCount = [];

    foreach ($passengers as $p) {
        $code = $p['passenger_type_code']; // ADT / C07 etc

        if (!isset($paxCount[$code])) {
            $paxCount[$code] = 0;
        }
        $paxCount[$code]++;
    }

    $passengerXML = "";
    foreach ($paxCount as $code => $qty) {
        $passengerXML .= '<PassengerType Code="'.$code.'" Quantity="'.$qty.'"/>';
    }

    // ---- Step 6: Build Full SOAP XML Request ----
    $xmlPayload = <<<XML
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header>
        <MessageHeader xmlns="http://www.ebxml.org/namespaces/messageHeader">
            <From><PartyId>Agency</PartyId></From>
            <To><PartyId>Sabre_API</PartyId></To>
            <ConversationId>{$conversationId}</ConversationId>
            <Action>OTA_AirPriceLLSRQ</Action>
            <MessageData>
                <MessageId>{$messageId}</MessageId>
                <Timestamp>{date('c')}</Timestamp>
            </MessageData>
        </MessageHeader>

        <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
            <wsse:BinarySecurityToken>{$securityToken}</wsse:BinarySecurityToken>
        </wsse:Security>
    </SOAP-ENV:Header>

    <SOAP-ENV:Body>
        <OTA_AirPriceRQ
            xmlns="http://webservices.sabre.com/sabreXML/2011/10"
            ReturnHostCommand="true"
            Version="2.17.0">

            {$flightSegmentsXML}

            <PriceRequestInformation Retain="true">
                <OptionalQualifiers>

                    <FlightQualifiers>
                        <VendorPrefs>
                            <Airline Code="GF"/>
                        </VendorPrefs>
                    </FlightQualifiers>

                    <PricingQualifiers>
                        {$passengerXML}
                    </PricingQualifiers>

                </OptionalQualifiers>
            </PriceRequestInformation>

        </OTA_AirPriceRQ>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;

    // ---- Step 7: Send Request ----
    $response = \Http::withHeaders([
        'Content-Type' => 'text/xml; charset=utf-8',
    ])->withBody($xmlPayload, 'text/xml')->post($endpoint);

    $body = $response->body();

 // ---- Step 9: Extract Host Command (Useful for debugging) ----
preg_match('/<stl:HostCommand[^>]*>(.*?)<\/stl:HostCommand>/s', $body, $hostMatch);
$hostCommand = $hostMatch[1] ?? null;

// ---- Step 10: PQ Extraction (Final version) ----
preg_match_all('/SolutionSequenceNmbr\s*=\s*"(\d+)"/m', $body, $matches);
$found = $matches[1] ?? [];

if (empty($found)) {
    $priceQuotes = [1]; // fallback, Sabre GF/BS always gives 1 PQ
} else {
    $priceQuotes = array_values(array_unique($found));
}

// ---- Step 11: Final Response Struct ----
return [
    'success'       => $response->successful(),
    'status_code'   => $response->status(),
    'host_command'  => $hostCommand,        // Example: WPAGF¥P2ADT/1C07¥RQ
    'price_quotes'  => $priceQuotes,        // Example: [1]
    'pq_count'      => count($priceQuotes), // Example: 1
    'request_xml'   => $xmlPayload,
    'response_xml'  => $body,
];




}



    public function endTransaction(string $from = 'DEV-API'): array
    {
        $token = \Cache::get('sabre_soap_token') ?? $this->createSoapSession();
        $token = trim(str_replace(["\n", "\r"], '', $token));

        $endpoint = 'https://webservices.cert.platform.sabre.com/websvc';
        $conversationId = '2021.01.DevStudio';
        $messageId = uniqid('ET_');
        $timestamp = gmdate('c');

        $xmlPayload = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
  <soapenv:Header>

    <eb:MessageHeader xmlns:eb="http://www.ebxml.org/namespaces/messageHeader">
      <eb:From><eb:PartyId>Agency</eb:PartyId></eb:From>
      <eb:To><eb:PartyId>Sabre_API</eb:PartyId></eb:To>

      <eb:ConversationId>{$conversationId}</eb:ConversationId>
      <eb:Action>EndTransactionLLSRQ</eb:Action>

      <eb:MessageData>
        <eb:MessageId>{$messageId}</eb:MessageId>
        <eb:Timestamp>{$timestamp}</eb:Timestamp>
      </eb:MessageData>
    </eb:MessageHeader>

    <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
      <wsse:BinarySecurityToken>{$token}</wsse:BinarySecurityToken>
    </wsse:Security>

  </soapenv:Header>

  <soapenv:Body>
    <EndTransactionRQ xmlns="http://webservices.sabre.com/sabreXML/2011/10" Version="2.0.0">
        <EndTransaction Ind="true"/>
        <Source ReceivedFrom="{$from}"/>
    </EndTransactionRQ>
  </soapenv:Body>
</soapenv:Envelope>
XML;

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
        ])->withBody($xmlPayload, 'text/xml')->post($endpoint);
        $body = $response->body();

        return [
            'raw_request'  => $xmlPayload,
            'raw_response' => $body,
            'success'      => str_contains($body, '<stl:Success'),
        ];
    }

    public function assignPrinter()
    {
        $token = Cache::get('sabre_soap_token');

        if (!$token) {
            $token = $this->createSoapSession();
        }

        $token = trim($token);
        $country = "BD";
        $timestamp = gmdate('c');
        $messageId = uniqid("MSG");

        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Header>
        <eb:MessageHeader xmlns:eb="http://www.ebxml.org/namespaces/messageHeader">
            <eb:From><eb:PartyId>Agency</eb:PartyId></eb:From>
            <eb:To><eb:PartyId>Sabre_API</eb:PartyId></eb:To>
            <eb:ConversationId>2021.01.DevStudio</eb:ConversationId>
            <eb:Action>DesignatePrinterLLSRQ</eb:Action>
            <eb:MessageData>
                <eb:MessageId>{$messageId}</eb:MessageId>
                <eb:Timestamp>{$timestamp}</eb:Timestamp>
            </eb:MessageData>
        </eb:MessageHeader>

        <wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext">
            <wsse:BinarySecurityToken>{$token}</wsse:BinarySecurityToken>
        </wsse:Security>
    </soapenv:Header>

    <soapenv:Body>
        <DesignatePrinterRQ ReturnHostCommand="true" Version="2.0.2"
            xmlns="http://webservices.sabre.com/sabreXML/2011/10">

            <Printers>
                <Ticket CountryCode="{$country}"/>
            </Printers>

        </DesignatePrinterRQ>
    </soapenv:Body>
</soapenv:Envelope>
XML;


        $response = Http::withHeaders([
            "Content-Type" => "text/xml"
        ])->withBody($xml, "text/xml")
            ->post("https://webservices.cert.platform.sabre.com/websvc");

        $body = $response->body();

        // 🔥 Universal regex to catch ALL possible LNIATA formats
        if (preg_match('/HostCommand[^>]*LNIATA="([^"]+)"/i', $body, $matches)) {
            return trim($matches[1]);
        }

        // 🔥 fallback regex
        if (preg_match('/LNIATA="([^"]+)"/i', $body, $matches2)) {
            return trim($matches2[1]);
        }

        return null;
    }
}
