<?php

namespace Modules\Api\Sabre;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SabreApisServiceClass
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


    /**
     * Get Sabre auth token
     */
//    private function getAuthToken(): string
//    {
//        return Cache::remember(
//            'sabre_api_token',
//            now()->addMinutes(config('sabre.token_cache_minutes')),
//            function () {
//                try {
//                    $credentials = base64_encode(
//                        config('sabre.rest.client_id') . ':' . config('sabre.rest.client_secret')
//                    );
//
//                    $response = Http::timeout(60)
//                        ->connectTimeout(30)
//                        ->retry(3, 1000)
//                        ->withOptions([
//                            'verify' => !app()->environment('local'),
//                        ])
//                        ->asForm()
//                        ->withHeaders([
//                            'Authorization' => "Basic {$credentials}",
//                            'Accept' => 'application/json',
//                        ])
//                        ->post(config('sabre.rest.base_url') . '/v3/auth/token', [
//                            'grant_type' => 'password',
//                            'username' => config('sabre.rest.username'),
//                            'password' => config('sabre.rest.password'),
//                        ]);
//
//                    if ($response->successful()) {
//                        $data = $response->json();
//
//                        Log::info('Sabre Auth Success', [
//                            'expires_in' => $data['expires_in'] ?? 0,
//                        ]);
//
//                        return $data['access_token'] ?? null;
//                    }
//
//                    Log::error('Sabre Auth Failed', [
//                        'status' => $response->status(),
//                        'body' => $response->body(),
//                    ]);
//
//                    throw new \Exception('Sabre authentication failed');
//
//                } catch (\Exception $e) {
//                    Log::error('Sabre Auth Exception', [
//                        'message' => $e->getMessage(),
//                    ]);
//                    throw $e;
//                }
//            }
//        );
//    }

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

    /**
     * Price check API call (BargainFinderMax)
     * Endpoint: /v3.2.0/shop/flights
     */
    public function priceCheck($payload)
    {
        $token = $this->getAuthToken();

        $endpoint = config('sabre.rest.base_url') . '/v3/shop/flights/revalidate';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($endpoint, $payload);

        \Log::info('Sabre Price Check Request', [
            'endpoint' => $endpoint,
            'response_status' => $response->status(),
        ]);

        if ($response->failed()) {
            throw new \Exception('Sabre API Error: ' . $response->body());
        }

        return $response->json();
    }

    public function ndcOfferPrice($offerId, $offerItemIds): array
    {
        $token    = $this->getAuthToken();
        $endpoint = config('sabre.rest.base_url') . '/v1/offers/price';

        // ✅ সবসময় flat string array নিশ্চিত করুন
        $flatIds = [];
        array_walk_recursive($offerItemIds, function($id) use (&$flatIds) {
            if (is_string($id)) $flatIds[] = $id;
        });

        $payload = [
            'query' => [
                [
                    'offerItemId' => $flatIds
                ]
            ]
        ];

        \Log::channel('daily')->info('NDC Offer Price Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post($endpoint, $payload);

        \Log::channel('daily')->info('NDC Offer Price Response: ' . json_encode($response->json(), JSON_PRETTY_PRINT));

        $messages = $response->json()['messages'] ?? [];
        $hasError  = collect($messages)->contains('type', 'ERROR');

        if ($response->failed() || $hasError) {
            $errorMsg = collect($messages)->firstWhere('type', 'ERROR')['message'] ?? 'Unknown error';
            throw new \Exception('Sabre NDC Offer Price Error: ' . $errorMsg);
        }

        return $response->json();
    }

    public function ndcOrderCreate(array $payload): array
    {
        $token    = $this->getAuthToken();
        $endpoint = $this->baseUrl . '/v1/orders/create';

        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        $data = $response->json();

        Log::info('NDC Order Create Response', $data ?? []);

        // ✅ errors array check
        if (!empty($data['errors'])) {
            $errorMsg = collect($data['errors'])->pluck('message')->implode(', ');
            throw new \Exception('NDC Order Error: ' . $errorMsg);
        }

        // ✅ messages array check
        if (!empty($data['messages'])) {
            foreach ($data['messages'] as $msg) {
                if (($msg['type'] ?? '') === 'ERROR') {
                    throw new \Exception($msg['message'] ?? 'NDC Order Error');
                }
            }
        }

        return $data;
    }
}
