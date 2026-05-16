<?php

namespace Modules\Flight\Service\Sabre;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SabreApiServices
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

//    public function __construct()
//    {
//        $this->baseUrl = config('sabre.base_url', 'https://api.cert.platform.sabre.com');
//        $this->username = config('sabre.credentials.username');
//        $this->password = config('sabre.credentials.password');
//        $this->pcc = config('sabre.credentials.pcc');
//    }

    public function __construct()
    {

        $this->clientId     = env('SABRE_CLIENT_ID');
        $this->clientSecret = env('SABRE_CLIENT_SECRET');
        $this->username     = env('SABRE_USERNAME');
        $this->password     = env('SABRE_PASSWORD');
        $this->soapUsername = env('SABRE_USERNAME_SOAP');
        $this->organization = env('ORGANIZATION');
        $this->baseUrl      = rtrim(env( 'https://webservices.cert.platform.sabre.com'), '/');
        $this->tokenCacheMinutes = env('SABRE_TOKEN_CACHE_MINUTES', 14);
    }
    private function getAuthToken(): string
    {
        return Cache::remember(
            'sabre_api_token',
            now()->addMinutes(config('sabre.token_cache_minutes')),
            function () {
                try {
                    // Encode client credentials
                    $credentials = base64_encode(
                        config('sabre.rest.client_id') . ':' . config('sabre.rest.client_secret')
                    );

                    $response = Http::timeout(60)
                        ->connectTimeout(30)
                        ->retry(3, 1000)
                        ->withOptions([
                            'verify' => !app()->environment('local'),
                        ])
                        ->asForm()
                        ->withHeaders([
                            'Authorization' => "Basic {$credentials}",
                            'Accept' => 'application/json',
                        ])
                        ->post(config('sabre.rest.base_url') . '/v3/auth/token', [
                            'grant_type' => 'password',
                            'username' => config('sabre.rest.username'),
                            'password' => config('sabre.rest.password'),
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        Log::info('Sabre Auth Success', [
                            'expires_in' => $data['expires_in'] ?? 0,
                        ]);

                        return $data['access_token'] ?? null;
                    }

                    Log::error('Sabre Auth Failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    throw new \Exception('Sabre authentication failed');

                } catch (\Exception $e) {
                    Log::error('Sabre Auth Exception', [
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }
        );
    }

    /**
     * ✅ Send XML Payload to Create PNR
     */
    /**
     * Create PNR with XML payload
     */
    public function createBookingPnrWithXml($xmlPayload)
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'AUTH_FAILED',
                    'message' => 'Could not obtain authentication token'
                ];
            }
//dd($xmlPayload);
            $endpoint = '/v2.4.0/passenger/records?mode=create';
            $pnrUrl = "{$this->baseUrl}{$endpoint}";
//            $pnrUrl = rtrim($this->baseUrl, ) .'/v2.4.0/passenger/records?mode=create';
            $targetCity = config('sabre.credentials.pcc', '27YK');

            Log::info('Sending PNR Request to Sabre', [
                'url' => $pnrUrl,
                'target_city' => $targetCity,
                'xml_length' => strlen($xmlPayload)
            ]);
//dd($pnrUrl,$targetCity,$xmlPayload);
            // ✅ THIS IS CORRECT FOR XML
//            $response = Http::timeout(120)
//                ->withHeaders([
//                    'Authorization' => 'Bearer '.$token,
//                    'Content-Type' => 'application/xml',  // ✅ XML content type
//                    'Accept' => 'application/json',
//                    'X-Sabre-PCC' => $targetCity,
//                ])
//                ->withBody($xmlPayload, 'application/xml')  // ✅ XML body
//                ->post($pnrUrl);
//            dd($response->body());

            $response = Http::withHeaders([
                "Content-Type"   => "application/xml",
                "Accept"         => "application/xml",
                "Authorization"  => "Bearer {$token}",
                "Conversation-ID" => uniqid("conv_"),
            ])->withBody($xmlPayload, "application/xml")
                ->post($endpoint);


            $body = $response->body();
            //   Log response
            Log::info('Sabre PNR Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            // Handle successful response
            if ($response->successful()) {
                $jsonResponse = $response->json();

                // Check if PNR was created
                if (isset($jsonResponse['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
                    $pnr = $jsonResponse['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];

                    Log::info('✅ PNR Created Successfully', [
                        'pnr' => $pnr
                    ]);

                    return [
                        'success' => true,
                        'pnr' => $pnr,
                        'data' => $jsonResponse
                    ];
                }

                // Success but no PNR
                Log::warning('Response successful but no PNR found', [
                    'response' => $jsonResponse
                ]);

                return [
                    'success' => false,
                    'error' => 'NO_PNR',
                    'message' => 'No PNR ID found in successful response',
                    'data' => $jsonResponse
                ];
            }

            // Handle error responses
            $errorResponse = $response->json() ?? [];

            Log::error('❌ Sabre PNR Creation Failed', [
                'status' => $response->status(),
                'error_response' => $errorResponse,
                'xml_sent' => $xmlPayload
            ]);

            // Extract readable error messages
            $errorMessages = $this->extractPnrErrors($errorResponse);

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => 'PNR_CREATION_FAILED',
                'messages' => $errorMessages,
                'data' => $errorResponse
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('❌ Connection Exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'CONNECTION_ERROR',
                'message' => 'Could not connect to Sabre API',
                'details' => $e->getMessage()
            ];

        } catch (\Exception $e) {
            Log::error('❌ Sabre PNR Exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => 'EXCEPTION',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract readable error messages from Sabre PNR response
     */
    private function extractPnrErrors($response)
    {
        $errors = [];

        // Handle empty response
        if (empty($response)) {
            return ['No response data available'];
        }

        // Handle direct error format
        if (isset($response['errorCode'])) {
            $code = $response['errorCode'] ?? 'UNKNOWN';
            $message = $response['message'] ?? 'No error message';
            $errors[] = "[$code] $message";
            return $errors;
        }

        // Handle CreatePassengerNameRecordRS format
        if (isset($response['CreatePassengerNameRecordRS']['ApplicationResults'])) {
            $appResults = $response['CreatePassengerNameRecordRS']['ApplicationResults'];

            // Extract errors
            if (isset($appResults['Error'])) {
                $errorArray = isset($appResults['Error'][0])
                    ? $appResults['Error']
                    : [$appResults['Error']];

                foreach ($errorArray as $error) {
                    if (isset($error['SystemSpecificResults'][0]['Message'])) {
                        $messages = isset($error['SystemSpecificResults'][0]['Message'][0])
                            ? $error['SystemSpecificResults'][0]['Message']
                            : [$error['SystemSpecificResults'][0]['Message']];

                        foreach ($messages as $msg) {
                            $code = $msg['code'] ?? 'UNKNOWN';
                            $content = $msg['content'] ?? 'No error message';
                            $errors[] = "[$code] $content";
                        }
                    }
                }
            }

            // Extract warnings
            if (isset($appResults['Warning'])) {
                $warningArray = isset($appResults['Warning'][0])
                    ? $appResults['Warning']
                    : [$appResults['Warning']];

                foreach ($warningArray as $warning) {
                    if (isset($warning['SystemSpecificResults'][0]['Message'])) {
                        $messages = isset($warning['SystemSpecificResults'][0]['Message'][0])
                            ? $warning['SystemSpecificResults'][0]['Message']
                            : [$warning['SystemSpecificResults'][0]['Message']];

                        foreach ($messages as $msg) {
                            $code = $msg['code'] ?? 'UNKNOWN';
                            $content = $msg['content'] ?? 'No warning message';

                            // Only include critical warnings
                            if (stripos($content, 'REQUIRED') !== false ||
                                stripos($content, 'INVALID') !== false ||
                                stripos($content, 'FAILED') !== false ||
                                stripos($content, 'ERROR') !== false ||
                                stripos($content, 'MISSING') !== false) {
                                $errors[] = "[WARNING-$code] $content";
                            }
                        }
                    }
                }
            }
        }

        return empty($errors) ? ['Unknown error occurred'] : $errors;
    }

    /**
     * ✅ Alternative: Send with XML Response (যদি XML response চান)
     */
    public function createBookingPnrWithXmlResponse($xmlPayload)
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                return null;
            }
//return $token;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml', // XML response
            ])->withBody($xmlPayload, 'application/xml')
                ->post($this->baseUrl . '/v2.4.0/passenger/records');
dd($response);
            if ($response->successful()) {
                // Parse XML response
                return simplexml_load_string($response->body());
            }

            Log::error('Sabre XML Response Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Sabre XML Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function buildTicketRecords($booking,$passengers)
    {
        $records = [];
        $recordNumber = 1;

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
        return $ticketRequest;
    }

    public function callSabreTicketAPI($request)
    {
        // Get Sabre token
        $token = $this->getSabreToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post(config('sabre.api_url') . '/v1.2.0/air/ticket', $request);

        if (!$response->successful()) {
            Log::error('❌ Sabre API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Sabre API Error: ' . $response->body());
        }

        return $response->json();
    }

    private function getPassengerTypeCode($passenger)
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

}
