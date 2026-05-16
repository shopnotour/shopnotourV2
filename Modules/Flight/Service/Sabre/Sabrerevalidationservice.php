<?php

namespace Modules\Flight\Service\Sabre;



    use Modules\Booking\Models\Booking;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

class Sabrerevalidationservice
{
    private SabreRevalidationBuilder $builder;
    private string $apiUrl;
    private string $accessToken;

    public function __construct()
    {
        $this->builder = new Sabrerevalidationbuilder();
        $this->apiUrl = config('sabre.base_url') . 'v6.1.0/shop/flights/revalidate';
    }

    /**
     * Revalidate booking fare with Sabre
     *
     * @param Booking $booking
     * @param string|null $pcc Pseudo City Code (optional)
     * @return array
     */
    public function revalidate(Booking $booking, ?string $pcc = null): array
    {
        try {
            // Load relationships
            $passengers = $booking->passengers;
            $routes = $booking->routes;
//dd($routes);
            // Validate data
           $result= $this->builder->build($booking, $passengers, $routes, $pcc);
           return $result;
           dd($result);
            $validationErrors = $this->builder->validate();

            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validationErrors
                ];
            }

            // Build payload
            $payload = $this->builder->build($booking, $passengers, $routes, $pcc);

            // Log request for debugging
            Log::info('Sabre Revalidation Request', [
                'booking_id' => $booking->id,
                'journey_summary' => $this->builder->getJourneySummary(),
                'payload' => $payload
            ]);

            // Make API request
            $response = $this->makeApiRequest($payload);

            // Log response
            Log::info('Sabre Revalidation Response', [
                'booking_id' => $booking->id,
                'response' => $response
            ]);

            return $this->processResponse($response, $booking);

        } catch (\Exception $e) {
            Log::error('Sabre Revalidation Error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make API request to Sabre
     */
    private function makeApiRequest(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, $payload);

        if ($response->failed()) {
            throw new \Exception('Sabre API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get or refresh access token
     */
    private function getAccessToken(): string
    {
        // Implement token management logic
        // This is a placeholder - implement actual token logic
        return $this->accessToken ?? config('sabre.access_token');
    }

    /**
     * Process Sabre response and extract fare information
     */
    private function processResponse(array $response, Booking $booking): array
    {
        // Check for errors
        if (isset($response['Errors'])) {
            return [
                'success' => false,
                'error' => 'Sabre returned errors',
                'errors' => $response['Errors']
            ];
        }

        // Extract itinerary options
        $itineraries = $response['PricedItineraries'] ?? [];

        if (empty($itineraries)) {
            return [
                'success' => false,
                'error' => 'No itineraries found'
            ];
        }

        // Find matching itinerary or get best option
        $selectedItinerary = $this->findMatchingItinerary($itineraries, $booking);

        return [
            'success' => true,
            'original_price' => $booking->sub_total,
            'current_price' => $this->extractTotalFare($selectedItinerary),
            'price_difference' => $this->calculatePriceDifference($booking, $selectedItinerary),
            'fare_details' => $this->extractFareDetails($selectedItinerary),
            'itinerary' => $selectedItinerary,
            'all_options' => count($itineraries),
            'validated_at' => now()->toIso8601String()
        ];
    }

    /**
     * Find itinerary matching original booking
     */
    private function findMatchingItinerary(array $itineraries, Booking $booking): array
    {
        // For now, return first itinerary
        // TODO: Implement matching logic based on flight numbers, times, etc.
        return $itineraries[0];
    }

    /**
     * Extract total fare from itinerary
     */
    private function extractTotalFare(array $itinerary): float
    {
        $airItineraryPricingInfo = $itinerary['AirItineraryPricingInfo'] ?? [];
        $itinTotalFare = $airItineraryPricingInfo['ItinTotalFare'] ?? [];

        return (float)($itinTotalFare['TotalFare']['Amount'] ?? 0);
    }

    /**
     * Calculate price difference
     */
    private function calculatePriceDifference(Booking $booking, array $itinerary): array
    {
        $originalPrice = (float)$booking->sub_total;
        $currentPrice = $this->extractTotalFare($itinerary);
        $difference = $currentPrice - $originalPrice;

        return [
            'amount' => abs($difference),
            'percentage' => $originalPrice > 0 ? ($difference / $originalPrice) * 100 : 0,
            'status' => $difference > 0 ? 'increased' : ($difference < 0 ? 'decreased' : 'unchanged'),
            'original' => $originalPrice,
            'current' => $currentPrice
        ];
    }

    /**
     * Extract detailed fare breakdown
     */
    private function extractFareDetails(array $itinerary): array
    {
        $pricingInfo = $itinerary['AirItineraryPricingInfo'] ?? [];
        $ptcFareBreakdowns = $pricingInfo['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? [];

        $fareDetails = [];

        foreach ($ptcFareBreakdowns as $breakdown) {
            $passengerType = $breakdown['PassengerTypeQuantity']['Code'] ?? 'ADT';
            $quantity = $breakdown['PassengerTypeQuantity']['Quantity'] ?? 1;
            $passengerFare = $breakdown['PassengerFare'] ?? [];

            $fareDetails[] = [
                'passenger_type' => $passengerType,
                'quantity' => $quantity,
                'base_fare' => (float)($passengerFare['BaseFare']['Amount'] ?? 0),
                'taxes' => (float)($passengerFare['Taxes']['TotalTax']['Amount'] ?? 0),
                'total' => (float)($passengerFare['TotalFare']['Amount'] ?? 0),
                'currency' => $passengerFare['TotalFare']['CurrencyCode'] ?? 'USD'
            ];
        }

        return $fareDetails;
    }

    /**
     * Quick validation check without full processing
     */
    public function quickValidate(Booking $booking): array
    {
        $passengers = $booking->passengers;
        $routes = $booking->bookingRoutes;

        $this->builder->build($booking, $passengers, $routes);
        $errors = $this->builder->validate();

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'journey_summary' => empty($errors) ? $this->builder->getJourneySummary() : []
        ];
    }
}
