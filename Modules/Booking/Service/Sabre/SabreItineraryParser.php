<?php

namespace Modules\Booking\Service\Sabre;

use Illuminate\Support\Facades\Log;

/**
 * Parses Sabre revalidation response and checks price against session price.
 *
 * Usage:
 *   $parser = new SabreItineraryParser($sabreResponse, $flight);
 *   $result = $parser->checkAndPrepare();
 *
 * Returns:
 *   ['status' => 'ok',       'data' => [...]]
 *   ['status' => 'mismatch', 'new_price' => 12345]
 *   ['status' => 'error',    'message' => '...']
 */
class SabreItineraryParser
{
    protected ?array $response;
    protected array  $flight;

    // Price tolerance — 1 BDT পর্যন্ত পার্থক্য ignore করবে (floating point)
    protected float $tolerance = 1.0;

    public function __construct(?array $response, array $flight)
    {
        $this->response = $response;
        $this->flight   = $flight;
    }

    // ─────────────────────────────────────────────
    // Entry point
    // ─────────────────────────────────────────────

    public function checkAndPrepare(): array
    {
        // ── Step 1: Response আছে কিনা ──────────────────
        if (empty($this->response)) {
            Log::error('Sabre Revalidate: empty response');
            return $this->error('Sabre থেকে কোনো response আসেনি।');
        }

        $root = $this->response['groupedItineraryResponse'] ?? null;

        if (!$root) {
            Log::error('Sabre Revalidate: missing groupedItineraryResponse', $this->response);
            return $this->error('Invalid response structure।');
        }

        // ── Step 2: Itinerary পাওয়া গেছে কিনা ─────────
        $itineraryCount = $root['statistics']['itineraryCount'] ?? 0;

        if ($itineraryCount < 1) {
            Log::warning('Sabre Revalidate: itineraryCount = 0 — flight no longer available');
            return $this->error('এই ফ্লাইটটি আর উপলব্ধ নেই।');
        }

        // ── Step 3: currentItinerary খুঁজে বের করো ─────
        $itinerary = $this->findCurrentItinerary($root);

        if (!$itinerary) {
            Log::warning('Sabre Revalidate: currentItinerary not found');
            return $this->error('Revalidated itinerary খুঁজে পাওয়া যায়নি।');
        }

        // ── Step 4: revalidated flag check ──────────────
        $pricingInfo = $itinerary['pricingInformation'][0] ?? null;

        if (!$pricingInfo) {
            return $this->error('Pricing information পাওয়া যায়নি।');
        }

        $revalidated = $pricingInfo['revalidated'] ?? false;

        if (!$revalidated) {
            Log::warning('Sabre Revalidate: revalidated flag is false');
            return $this->error('Flight revalidation সফল হয়নি।');
        }

        // ── Step 5: নতুন price বের করো ─────────────────
        $fare         = $pricingInfo['fare'] ?? [];
        $totalFare    = $fare['totalFare']   ?? [];
        $newApiPrice  = (float) ($totalFare['totalPrice'] ?? 0);

        if ($newApiPrice <= 0) {
            return $this->error('Response এ valid price পাওয়া যায়নি।');
        }

        // ── Step 6: পুরনো price এর সাথে compare ────────
        $oldApiPrice = (float) ($this->flight['price']['api_subtotal'] ?? 0);

        Log::info('Sabre Revalidate: price check', [
            'old_price' => $oldApiPrice,
            'new_price' => $newApiPrice,
            'diff'      => abs($newApiPrice - $oldApiPrice),
        ]);

        if (abs($newApiPrice - $oldApiPrice) > $this->tolerance) {
            return [
                'status'    => 'mismatch',
                'new_price' => $newApiPrice,
                'old_price' => $oldApiPrice,
            ];
        }

        // ── Step 7: সব ঠিক আছে — data prepare করো ─────
        return [
            'status' => 'ok',
            'data'   => $this->prepareVerifiedData($fare, $root),
        ];
    }

    // ─────────────────────────────────────────────
    // currentItinerary খুঁজে বের করো
    // ─────────────────────────────────────────────

    protected function findCurrentItinerary(array $root): ?array
    {
        $groups = $root['itineraryGroups'] ?? [];

        foreach ($groups as $group) {
            $itineraries = $group['itineraries'] ?? [];
            foreach ($itineraries as $itin) {
                if (!empty($itin['currentItinerary'])) {
                    return $itin;
                }
            }
        }

        // Fallback: currentItinerary flag না থাকলে প্রথম itinerary নাও
        return $groups[0]['itineraries'][0] ?? null;
    }

    // ─────────────────────────────────────────────
    // Verified data prepare
    // ─────────────────────────────────────────────

    protected function prepareVerifiedData(array $fare, array $root): array
    {
        $totalFare   = $fare['totalFare']          ?? [];
        $passengerInfo = $fare['passengerInfoList'][0]['passengerInfo'] ?? [];

        return [
            // Price info
            'total_price'          => $totalFare['totalPrice']         ?? 0,
            'total_tax'            => $totalFare['totalTaxAmount']      ?? 0,
            'base_fare_amount'     => $totalFare['baseFareAmount']      ?? 0,
            'base_fare_currency'   => $totalFare['baseFareCurrency']    ?? 'USD',
            'equivalent_amount'    => $totalFare['equivalentAmount']    ?? 0,
            'equivalent_currency'  => $totalFare['equivalentCurrency']  ?? 'BDT',

            // Carrier / fare info
            'validating_carrier'   => $fare['validatingCarrierCode']    ?? '',
            'last_ticket_date'     => $fare['lastTicketDate']           ?? '',
            'last_ticket_time'     => $fare['lastTicketTime']           ?? '',
            'eTicketable'          => $fare['eTicketable']              ?? true,
            'vita'                 => $fare['vita']                     ?? false,

            // Refundable check
            'refundable'           => !($passengerInfo['nonRefundable'] ?? true),

            // Baggage
            'baggage'              => $this->extractBaggage($passengerInfo, $root),

            // Penalties
            'penalties'            => $passengerInfo['penaltiesInfo']['penalties'] ?? [],

            // Exchange rate
            'exchange_rate'        => $fare['passengerInfoList'][0]['passengerInfo']['currencyConversion']['exchangeRateUsed']
                ?? $this->flight['passengers'][0]['exchange_rate']
                    ?? null,

            // Raw fare for further processing
            'raw_fare'             => $fare,
        ];
    }

    // ─────────────────────────────────────────────
    // Baggage extract
    // ─────────────────────────────────────────────

    protected function extractBaggage(array $passengerInfo, array $root): array
    {
        $baggageAllowanceDescs = [];
        foreach ($root['baggageAllowanceDescs'] ?? [] as $desc) {
            $baggageAllowanceDescs[$desc['id']] = $desc;
        }

        $result = [];
        foreach ($passengerInfo['baggageInformation'] ?? [] as $bag) {
            $ref     = $bag['allowance']['ref'] ?? null;
            $allowance = $ref ? ($baggageAllowanceDescs[$ref] ?? []) : [];

            $result[] = [
                'airline'        => $bag['airlineCode']    ?? '',
                'provision_type' => $bag['provisionType']  ?? '',
                'weight'         => $allowance['weight']   ?? null,
                'unit'           => $allowance['unit']     ?? 'kg',
                'piece_count'    => $allowance['pieceCount'] ?? null,
            ];
        }

        return $result;
    }

    // ─────────────────────────────────────────────
    // Error helper
    // ─────────────────────────────────────────────

    protected function error(string $message): array
    {
        return [
            'status'  => 'error',
            'message' => $message,
        ];
    }
}
