<?php

namespace Modules\Flight\Service\AirArabia;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Air Arabia One API — Laravel Service Class
 *
 * সম্পূর্ণ Flow:
 *  1. authenticate()             → accessToken + refreshToken নেওয়া (REST)
 *  2. searchFlights()            → ফ্লাইট খোঁজা (REST/JSON)
 *  3. getPrice()                 → মূল্য যাচাই → JSESSIONID + TransactionID সংরক্ষণ (SOAP)
 *  4. getPriceWithBundle()       → Bundle সহ মূল্য (SOAP) [optional]
 *  5. getBaggageDetails()        → ব্যাগেজ তথ্য (SOAP)
 *  6. getMealDetails()           → খাবার তথ্য (SOAP)
 *  7. getSeatMap()               → আসন মানচিত্র (SOAP)
 *  8. book()                     → বুকিং করা (SOAP)
 *  9. getReservationByPnr()      → PNR দিয়ে বুকিং দেখা (SOAP)
 * 10. modifyReservationPayment() → পেমেন্ট সহ বুকিং পরিবর্তন (SOAP)
 * 11. cancelBooking()            → বুকিং বাতিল (SOAP)
 */
class AirArabiaService
{
    // ==========================================
    // Config — সব .env থেকে নেওয়া হচ্ছে
    // ==========================================

    private string $authUrl;
    private string $searchUrl;
    private string $soapUrl;

    private string $login;
    private string $username;
    private string $password;
    private string $agentCode;

    private string $defaultCurrency;
    private string $defaultCountry;
    private string $defaultStation;
    private bool $sslVerify;
    private int $tokenTtlHours;

    // getPrice() থেকে নেওয়া — book() পর্যন্ত বহন করতে হবে
    private ?string $jsessionId = null;
    private ?string $transactionId = null;

    public function __construct()
    {
        $this->authUrl = config('services.air_arabia.auth_url');
        $this->searchUrl = config('services.air_arabia.search_url');
        $this->soapUrl = config('services.air_arabia.soap_url');

        $this->login = config('services.air_arabia.login');
        $this->username = config('services.air_arabia.username');
        $this->password = config('services.air_arabia.password');
        $this->agentCode = config('services.air_arabia.agent_code');

        $this->defaultCurrency = config('services.air_arabia.default_currency', 'BDT');
        $this->defaultCountry = config('services.air_arabia.default_country', 'BD');
        $this->defaultStation = config('services.air_arabia.default_station', 'DAC');
        $this->sslVerify = config('services.air_arabia.ssl_verify', false);
        $this->tokenTtlHours = config('services.air_arabia.token_ttl_hours', 23);
    }

    // ==========================================
    // STEP 1: Authentication (REST)
    // ==========================================

    /**
     * Login করে accessToken নেয়।
     * Token .env এর AIR_ARABIA_TOKEN_TTL_HOURS ঘণ্টা Cache এ থাকে।
     */

    public function authenticate(): string
    {
        if (Cache::has('air_arabia_access_token')) {
            return Cache::get('air_arabia_access_token');
        }

        $response = $this->httpClient()->timeout(30)->post($this->authUrl, [
            'login'    => $this->login,
            'password' => $this->password,
        ]);

        if ($response->failed()) {
            throw new Exception('AirArabia authentication failed. HTTP: ' . $response->status());
        }

        $accessToken = $response->json()['tokenPair']['accessToken'] ?? null;
        if (!$accessToken) throw new Exception('AirArabia: accessToken পাওয়া যায়নি।');

        Cache::put('air_arabia_access_token', $accessToken, now()->addHours($this->tokenTtlHours));
        Log::info('AirArabia Auth Success');

        return $accessToken;
    }

    // ==========================================
    // Get Flight
    // ==========================================

    public function getFlight($payload, $trip_type)
    {
        $token    = $this->authenticate();
        $response = $this->httpClient()
            ->withHeaders([
                'Authorization'        => 'Bearer ' . $token,
                'Content-Type'         => 'application/json',
                'X-AERO-SALES-CHANNEL' => 'OTA',
                'X-AERO-JOURNEY-TYPE'  => $trip_type,
                'X-AERO-USERID'        => $this->username,
                'X-AERO-AGENT-CODE'    => $this->agentCode,
            ])
            ->timeout(60)
            ->post($this->searchUrl, $payload);

        return $response; // ✅ শুধু response return করো — error check parser এ হবে
    }



    // ==========================================
    // Get Price
    // ==========================================

    public function getPrice(string $xml): string
    {
        Log::channel('daily')->info('AirArabia GetPrice Request: ' . $xml);

        $result = $this->sendSoapRequest('getPrice', $xml);
// ✅ XML formatted log
        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->loadXML($result['body']);
        Log::channel('daily')->info('AirArabia GetPrice Response: ' . PHP_EOL . $xml->saveXML());

        $this->jsessionId    = $this->extractJsessionId($result['headers']);
        $this->transactionId = $this->extractTransactionId($result['body']);

        if (!$this->jsessionId || !$this->transactionId) {
            Log::channel('daily')->error('AirArabia GetPrice: JSESSIONID বা TransactionID পাওয়া যায়নি।');
            throw new Exception('AirArabia: JSESSIONID বা TransactionID পাওয়া যায়নি।');
        }

        Log::channel('daily')->info('AirArabia GetPrice Session: jsessionId=' . $this->jsessionId . ' transactionId=' . $this->transactionId);

        return $result['body'];
    }

    // ==========================================
    // Get Price with Bundle
    // ==========================================

    public function getPriceWithBundle(
        array  $segments,
        string $outboundBundleId,
        string $inboundBundleId = '',
        string $direction = 'OneWay',
        array  $paxCounts = []
    ): string {
        $this->requireSession();
        $xml    = $this->xmlBuilder->buildGetPriceWithBundleXml(
            $segments, $this->transactionId,
            $outboundBundleId, $inboundBundleId,
            $direction, $paxCounts
        );
        $result = $this->sendSoapRequest('getPriceWithBundle', $xml, true);
        return $result['body'];
    }

    // ==========================================
    // Ancillaries
    // ==========================================

    public function getBaggageDetails(array $segments): string
    {
        $this->requireSession();
        $result = $this->sendSoapRequest('getBaggageDetails',
            $this->xmlBuilder->buildBaggageDetailsXml($segments, $this->transactionId), true);
        return $result['body'];
    }

    public function getMealDetails(array $segments): string
    {
        $this->requireSession();
        $result = $this->sendSoapRequest('getMealDetails',
            $this->xmlBuilder->buildMealDetailsXml($segments, $this->transactionId), true);
        return $result['body'];
    }

    public function getSeatMap(array $segments): string
    {
        $this->requireSession();
        $result = $this->sendSoapRequest('getSeatMap',
            $this->xmlBuilder->buildSeatMapXml($segments, $this->transactionId), true);
        return $result['body'];
    }

    // ==========================================
    // Book
    // ==========================================

    public function book(string $xml): string
    {
        $this->requireSession();

        Log::channel('daily')->info('AirArabia Book Request: ' . $xml);

        $result = $this->sendSoapRequest('book', $xml, true);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($result['body']);
        Log::channel('daily')->info('AirArabia Book Response: ' . PHP_EOL . $dom->saveXML());

        $this->resetSession();
        return $result['body'];
    }

    // ==========================================
    // PNR Operations
    // ==========================================

    public function getReservationByPnr(string $pnr): string
    {
        $result = $this->sendSoapRequest('getReservationByPnr',
            $this->xmlBuilder->buildGetReservationXml($pnr));
        return $result['body'];
    }

    public function modifyReservationPayment(
        string $pnr,
        float  $amount,
        string $currency = 'BDT',
        string $transId  = ''
    ): string {
        $txId = $transId ?: $this->transactionId;
        if (!$txId) throw new Exception('AirArabia: TransactionIdentifier দরকার।');

        $result = $this->sendSoapRequest('modifyReservation',
            $this->xmlBuilder->buildModifyPaymentXml($pnr, $txId, $amount, $currency));
        return $result['body'];
    }

    public function cancelBooking(string $pnr): string
    {
        $result = $this->sendSoapRequest('cancelBooking',
            $this->xmlBuilder->buildCancelXml($pnr));
        return $result['body'];
    }

    // ==========================================
    // Session
    // ==========================================

    public function setSession(string $jsessionId, string $transactionId): void
    {
        $this->jsessionId    = $jsessionId;
        $this->transactionId = $transactionId;
    }

    public function getSession(): array
    {
        return ['jsessionId' => $this->jsessionId, 'transactionId' => $this->transactionId];
    }

    public function clearTokenCache(): void
    {
        Cache::forget('air_arabia_access_token');
    }

    // ==========================================
    // Private Helpers
    // ==========================================

    private function httpClient()
    {
        $client = Http::timeout(30);
        return $this->sslVerify ? $client : $client->withoutVerifying();
    }

    private function sendSoapRequest(string $action, string $xml, bool $withSession = false): array
    {
        $headers = ['Content-Type' => 'text/xml; charset=UTF-8', 'SOAPAction' => '""'];
        if ($withSession && $this->jsessionId) {
            $headers['Cookie'] = 'JSESSIONID=' . $this->jsessionId;
        }

        $response = $this->httpClient()
            ->withHeaders($headers)
            ->withBody($xml, 'text/xml')
            ->post($this->soapUrl);

        if ($response->failed()) {
            Log::error("AirArabia {$action} Failed", ['status' => $response->status()]);
            throw new Exception("AirArabia {$action} failed. HTTP: " . $response->status());
        }

        Log::info("AirArabia {$action} called");
        return ['headers' => $response->headers(), 'body' => $response->body()];
    }

    private function extractJsessionId(array $headers): ?string
    {
        $setCookie = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? '';
        if (is_array($setCookie)) $setCookie = implode('; ', $setCookie);
        preg_match('/JSESSIONID=([^;,\s]+)/i', $setCookie, $matches);
        return $matches[1] ?? null;
    }

    private function extractTransactionId(string $body): ?string
    {
        preg_match('/TransactionIdentifier="([^"]+)"/i', $body, $matches);
        return $matches[1] ?? null;
    }

    private function requireSession(): void
    {
        if (!$this->jsessionId || !$this->transactionId) {
            throw new Exception('AirArabia: Session নেই। আগে getPrice() call করুন।');
        }
    }

    private function resetSession(): void
    {
        $this->jsessionId    = null;
        $this->transactionId = null;
    }
}
