<?php
////
////namespace Modules\Flight\Service;
////
////use Modules\Flight\Models\FlightApi;
////use Modules\Flight\Models\FlightCallingStructure;
////use Modules\Flight\Service\AirArabia\AirArabiaFlightService;
////use Modules\Flight\Service\Sabre\SabreFlightService;
////use Modules\Flight\Service\TravelPort\TravelPortFlightService;
////
////class FlightSearchService
////{
////    private SegmentProcessor $segmentProcessor;
////    private PassengerProcessor $passengerProcessor;
////    private FlightDiscountService $discountService;
////    private FlightChargesService $chargesService;
////
////    public function __construct(
////        SegmentProcessor      $segmentProcessor,
////        PassengerProcessor    $passengerProcessor,
////        FlightDiscountService $discountService,
////        FlightChargesService  $chargesService
////    )
////    {
////        $this->segmentProcessor = $segmentProcessor;
////        $this->passengerProcessor = $passengerProcessor;
////        $this->discountService = $discountService;
////        $this->chargesService = $chargesService;
////    }
////
////    /**
////     * Search flights from all active providers
////     */
////    public function search(array $validated)
////    {
////        $searchData = $this->prepareSearchData($validated);
////
////        // Get active APIs
////        $flightApis = FlightApi::where('status', 'active')
////            ->where('is_enabled', 1)
////            ->orderBy('priority', 'asc')
////            ->get();
////
////        if ($flightApis->isEmpty()) {
////            return [
////                'success' => false,
////                'error' => 'No active flight API configured',
////                'flights' => [],
////            ];
////        }
////
////        $allFlights = [];
////        $errors = [];
////
////        foreach ($flightApis as $api) {
////            try {
////                $from = $searchData['route_from'];
////                $to = $searchData['route_to'];
////                $userAirlines = $searchData['user_airline_codes'] ?? null;
////
////                // এই API এর জন্য calling structure config খোঁজো
////                $callingConfig = FlightCallingStructure::getConfigForRouteAndGds($from, $to, $api->provider);
////                $callingAirlines = $callingConfig?->airline_codes ?? [];
////
////                // ── Airline filter decide করো ──────────────────
////                if ($userAirlines) {
////                    // User airline select করেছে
////                    if (!empty($callingAirlines)) {
////                        // Calling config আছে → intersection check
////                        $airlineCodes = array_values(array_intersect($userAirlines, $callingAirlines));
////
////                        // এই API তে user এর কোনো airline নেই → skip
////                        if (empty($airlineCodes)) {
////                            $errors[$api->provider] = 'Skipped: airline not configured for this route';
////                            continue;
////                        }
////                    } else {
////                        // Calling config নেই → স্বাধীন, user airlines দিয়ে search
////                        $airlineCodes = $userAirlines;
////                    }
////                } else {
////                    // User কোনো airline select করেনি
////                    // Calling config এর airlines, না থাকলে null (সব airlines)
////                    $airlineCodes = !empty($callingAirlines) ? $callingAirlines : null;
////                }
////                // ───────────────────────────────────────────────
////
////                $apiSearchData = array_merge($searchData, ['airline_codes' => $airlineCodes]);
////
////                $result = $this->searchByProvider($api->provider, $apiSearchData);
////
////                if ($result['success'] && !empty($result['flights'])) {
////                    foreach ($result['flights'] as &$flight) {
////                        $flight['provider'] = $api->provider;
////                    }
////                    unset($flight);
////                    $allFlights = array_merge($allFlights, $result['flights']);
////                } else {
////                    $errors[$api->provider] = $result['error'] ?? 'No flights';
////                }
////
////            } catch (\Exception $e) {
////                $errors[$api->provider] = $e->getMessage();
////            }
////        }
////
////        // Sort by price
////        usort($allFlights, function ($a, $b) {
////            return (float)($a['price']['total'] ?? 0) <=> (float)($b['price']['total'] ?? 0);
////        });
////
////        return [
////            'success' => !empty($allFlights),
////            'flights' => $allFlights,
////            'total_results' => count($allFlights),
////            'currency' => 'BDT',
////            'errors' => $errors,
////        ];
////    }
////
////    /**
////     * Prepare base search data.
////     * airline_codes এখানে set হয় না — loop এ per-API হয়।
////     */
////    private function prepareSearchData(array $validated): array
////    {
////        $segments = $this->segmentProcessor->processSegments(
////            $validated['segments'],
////            $validated['trip_type'],
////            $validated['return_date'] ?? null
////        );
////
////        $passengers = $this->passengerProcessor->processPassengers($validated);
////
////        $firstSegment = $segments[0];
////
////        return [
////            'trip_type' => $validated['trip_type'],
////            'segments' => $segments,
////            'passengers' => $passengers,
////            'travel_class' => $validated['travel_class'],
////            'route_from' => $firstSegment['origin']['code'] ?? null,
////            'route_to' => $firstSegment['destination']['code'] ?? null,
////            // User manually airline filter করলে (UI থেকে)
////            'user_airline_codes' => $validated['airline_codes'] ?? null,
////        ];
////    }
////
////    /**
////     * Search by provider
////     */
////    private function searchByProvider(string $provider, array $searchData): array
////    {
////
////        return match (strtolower($provider)) {
////            'travelport' => $this->searchTravelPort($searchData),
////            'sabre' => $this->searchSabre($searchData),
////            'air_arabia' => $this->searchAirArabia($searchData),
////            default => ['success' => false, 'flights' => [], 'error' => 'Unknown provider'],
////        };
////    }
////
////    private function searchTravelPort(array $searchData): array
////    {
////        $service = new TravelPortFlightService($this->discountService, $this->chargesService);
////        return $service->search($searchData);
////    }
////
////    private function searchSabre(array $searchData): array
////    {
////        $service = new SabreFlightService($this->discountService, $this->chargesService);
////        return $service->search($searchData);
////    }
////
////    private function searchAirArabia(array $searchData): array
////    {
////        $service = new AirArabiaFlightService($this->discountService, $this->chargesService);
////        return $service->search($searchData);
////    }
////}
//
//
//namespace Modules\Flight\Service;
//
//use Modules\Flight\Models\FlightApi;
//use Modules\Flight\Models\FlightCallingStructure;
//use Modules\Flight\Service\AirArabia\AirArabiaResponseParser;
//use Modules\Flight\Service\Sabre\SabreFlightService;
//use Modules\Flight\Service\TravelPort\TravelPortFlightService;
//use Modules\Flight\Service\AirArabia\AirArabiaFlightService;
//
//// ← নতুন
//
//class FlightSearchService
//{
//    private SegmentProcessor $segmentProcessor;
//    private PassengerProcessor $passengerProcessor;
//    private FlightDiscountService $discountService;
//    private FlightChargesService $chargesService;
//
//    public function __construct(
//        SegmentProcessor      $segmentProcessor,
//        PassengerProcessor    $passengerProcessor,
//        FlightDiscountService $discountService,
//        FlightChargesService  $chargesService
//    )
//    {
//        $this->segmentProcessor = $segmentProcessor;
//        $this->passengerProcessor = $passengerProcessor;
//        $this->discountService = $discountService;
//        $this->chargesService = $chargesService;
//    }
//
//
//    public function searchStream(array $validated, callable $onFlight): void
//    {
//        $searchData = $this->prepareSearchData($validated);
//
//        $flightApis = FlightApi::where('status', 'active')
//            ->where('is_enabled', 1)
//            ->orderBy('priority', 'asc')
//            ->get();
//
//        if ($flightApis->isEmpty()) return;
//
//        foreach ($flightApis as $api) {
//            try {
//                $from = $searchData['route_from'];
//                $to   = $searchData['route_to'];
//                $userAirlines = $searchData['user_airline_codes'] ?? null;
//
//                $callingConfig  = FlightCallingStructure::getConfigForRouteAndGds($from, $to, $api->provider);
//                $callingAirlines = $callingConfig?->airline_codes ?? [];
//
//                if ($userAirlines) {
//                    if (!empty($callingAirlines)) {
//                        $airlineCodes = array_values(array_intersect($userAirlines, $callingAirlines));
//                        if (empty($airlineCodes)) continue;
//                    } else {
//                        $airlineCodes = $userAirlines;
//                    }
//                } else {
//                    $airlineCodes = !empty($callingAirlines) ? $callingAirlines : null;
//                }
//
//                $apiSearchData = array_merge($searchData, ['airline_codes' => $airlineCodes]);
//                $result = $this->searchByProvider($api->provider, $apiSearchData);
//
//                if ($result['success'] && !empty($result['flights'])) {
//                    // ✅ এই GDS এর flights আসামাত্র callback দিয়ে পাঠান
//                    foreach ($result['flights'] as $flight) {
//                        $flight['provider'] = $api->provider;
//                        $onFlight($flight);
//                    }
//                }
//
//            } catch (\Exception $e) {
//                // silently skip
//            }
//        }
//    }
//    /**
//     * Search flights from all active providers
//     */
//    public function search(array $validated): array
//    {
////        dd($validated);
//        $searchData = $this->prepareSearchData($validated);
//
//        $flightApis = FlightApi::where('status', 'active')
//            ->where('is_enabled', 1)
//            ->orderBy('priority', 'asc')
//            ->get();
////        dd($flightApis);
////        return $flightApis;
//        if ($flightApis->isEmpty()) {
//            return [
//                'success' => false,
//                'error' => 'No active flight API configured',
//                'flights' => [],
//            ];
//        }
//
//        $allFlights = [];
//        $errors = [];
//
//        foreach ($flightApis as $api) {
//            try {
//                $from = $searchData['route_from'];
//                $to = $searchData['route_to'];
//                $userAirlines = $searchData['user_airline_codes'] ?? null;
//
//                $callingConfig = FlightCallingStructure::getConfigForRouteAndGds($from, $to, $api->provider);
//                $callingAirlines = $callingConfig?->airline_codes ?? [];
//
//                // ── Airline filter ──────────────────────────────
//                if ($userAirlines) {
//                    if (!empty($callingAirlines)) {
//                        $airlineCodes = array_values(array_intersect($userAirlines, $callingAirlines));
//                        if (empty($airlineCodes)) {
//                            $errors[$api->provider] = 'Skipped: airline not configured for this route';
//                            continue;
//                        }
//                    } else {
//                        $airlineCodes = $userAirlines;
//                    }
//                } else {
//                    $airlineCodes = !empty($callingAirlines) ? $callingAirlines : null;
//                }
//                // ────────────────────────────────────────────────
//
//                $apiSearchData = array_merge($searchData, ['airline_codes' => $airlineCodes]);
//
//                $result = $this->searchByProvider($api->provider, $apiSearchData);
////return $result;
//                if ($result['success'] && !empty($result['flights'])) {
//                    foreach ($result['flights'] as &$flight) {
//                        $flight['provider'] = $api->provider;
//                    }
//                    unset($flight);
//                    $allFlights = array_merge($allFlights, $result['flights']);
//                } else {
//                    $errors[$api->provider] = $result['error'] ?? 'No flights';
//                }
//
//            } catch (\Exception $e) {
//                $errors[$api->provider] = $e->getMessage();
//            }
//        }
//
//        // Sort by price
//        usort($allFlights, fn($a, $b) => (float)($a['price']['total'] ?? 0) <=> (float)($b['price']['total'] ?? 0)
//        );
//
//        return [
//            'success' => !empty($allFlights),
//            'flights' => $allFlights,
//            'total_results' => count($allFlights),
//            'currency' => 'BDT',
//            'errors' => $errors,
//        ];
//    }
//
//    /**
//     * Prepare base search data
//     */
//    private function prepareSearchData(array $validated): array
//    {
//        $segments = $this->segmentProcessor->processSegments(
//            $validated['segments'],
//            $validated['trip_type'],
//            $validated['return_date'] ?? null
//        );
//
//        $passengers = $this->passengerProcessor->processPassengers($validated);
//        $firstSegment = $segments[0];
//
//        return [
//            'trip_type' => $validated['trip_type'],
//            'segments' => $segments,
//            'passengers' => $passengers,
//            'travel_class' => $validated['travel_class'],
//            'route_from' => $firstSegment['origin']['code'] ?? null,
//            'route_to' => $firstSegment['destination']['code'] ?? null,
//            'user_airline_codes' => $validated['airline_codes'] ?? null,
//        ];
//    }
//
//    /**
//     * Search by provider — Air Arabia যোগ করা হয়েছে
//     */
//    private function searchByProvider(string $provider, array $searchData): array
//    {
//        return match (strtolower($provider)) {
//            'travelport' => $this->searchTravelPort($searchData),
//            'sabre' => $this->searchSabre($searchData),
//            'airarabia' => $this->searchAirArabia($searchData), // ← নতুন
//            default => ['success' => false, 'flights' => [], 'error' => 'Unknown provider: ' . $provider],
//        };
//    }
//
//    private function searchTravelPort(array $searchData): array
//    {
//        $service = new TravelPortFlightService($this->discountService, $this->chargesService);
//        return $service->search($searchData);
//    }
//
//    private function searchSabre(array $searchData): array
//    {
//
//        $service = new SabreFlightService($this->discountService, $this->chargesService);
//        return $service->search($searchData);
//    }
//
//    // ← নতুন method
//    private function searchAirArabia(array $searchData): array
//    {
////        dd('sarowar');
//        $service = new AirArabiaResponseParser($this->discountService, $this->chargesService);
//        return $service->search($searchData);
//    }
//}


namespace Modules\Flight\Service;

use Illuminate\Support\Facades\Log;
use Modules\Flight\Models\FlightApi;
use Modules\Flight\Models\FlightCallingStructure;
use Modules\Flight\Service\AirArabia\AirArabiaResponseParser;
use Modules\Flight\Service\Sabre\SabreFlightService;
use Modules\Flight\Service\TravelPort\TravelPortFlightService;
use Modules\Flight\Service\AirArabia\AirArabiaFlightService;

// ← নতুন

class FlightSearchService
{
    private SegmentProcessor $segmentProcessor;
    private PassengerProcessor $passengerProcessor;
    private FlightDiscountService $discountService;
    private FlightChargesService $chargesService;

    public function __construct(
        SegmentProcessor      $segmentProcessor,
        PassengerProcessor    $passengerProcessor,
        FlightDiscountService $discountService,
        FlightChargesService  $chargesService
    )
    {
        $this->segmentProcessor = $segmentProcessor;
        $this->passengerProcessor = $passengerProcessor;
        $this->discountService = $discountService;
        $this->chargesService = $chargesService;
    }


    public function searchStream(array $validated, callable $onFlight): void
    {
        $searchData = $this->prepareSearchData($validated);

        $flightApis = FlightApi::where('status', 'active')
            ->where('is_enabled', 1)
            ->orderBy('priority', 'asc')
            ->get();

        if ($flightApis->isEmpty()) return;

        foreach ($flightApis as $api) {
            try {
                $from = $searchData['route_from'];
                $to = $searchData['route_to'];
                $userAirlines = $searchData['user_airline_codes'] ?? null;

                $callingConfig = FlightCallingStructure::getConfigForRouteAndGds($from, $to, $api->provider);
                $callingAirlines = $callingConfig?->airline_codes ?? [];

                if ($userAirlines) {
                    if (!empty($callingAirlines)) {
                        $airlineCodes = array_values(array_intersect($userAirlines, $callingAirlines));
                        if (empty($airlineCodes)) continue;
                    } else {
                        $airlineCodes = $userAirlines;
                    }
                } else {
                    $airlineCodes = !empty($callingAirlines) ? $callingAirlines : null;
                }

                $apiSearchData = array_merge($searchData, ['airline_codes' => $airlineCodes]);
                $result = $this->searchByProvider($api->provider, $apiSearchData);

                if ($result['success'] && !empty($result['flights'])) {
                    // ✅ এই GDS এর flights আসামাত্র callback দিয়ে পাঠান
                    foreach ($result['flights'] as $flight) {
                        $flight['provider'] = $api->provider;
                        $onFlight($flight);
                    }
                }

            } catch (\Exception $e) {
                Log::warning('Flight search failed', [
                    'provider' => $api->provider,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Search flights from all active providers
     */
    public function search(array $validated): array
    {
//        dd($validated);
        $searchData = $this->prepareSearchData($validated);

        $flightApis = FlightApi::where('status', 'active')
            ->where('is_enabled', 1)
            ->orderBy('priority', 'asc')
            ->get();
//        dd($flightApis);
//        return $flightApis;
        if ($flightApis->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No active flight API configured',
                'flights' => [],
            ];
        }

        $allFlights = [];
        $errors = [];

        foreach ($flightApis as $api) {
            try {
                $from = $searchData['route_from'];
                $to = $searchData['route_to'];
                $userAirlines = $searchData['user_airline_codes'] ?? null;

                $callingConfig = FlightCallingStructure::getConfigForRouteAndGds($from, $to, $api->provider);
                $callingAirlines = $callingConfig?->airline_codes ?? [];

                // ── Airline filter ──────────────────────────────
                if ($userAirlines) {
                    if (!empty($callingAirlines)) {
                        $airlineCodes = array_values(array_intersect($userAirlines, $callingAirlines));
                        if (empty($airlineCodes)) {
                            $errors[$api->provider] = 'Skipped: airline not configured for this route';
                            continue;
                        }
                    } else {
                        $airlineCodes = $userAirlines;
                    }
                } else {
                    $airlineCodes = !empty($callingAirlines) ? $callingAirlines : null;
                }
                // ────────────────────────────────────────────────

                $apiSearchData = array_merge($searchData, ['airline_codes' => $airlineCodes]);

                $result = $this->searchByProvider($api->provider, $apiSearchData);
//return $result;
                if ($result['success'] && !empty($result['flights'])) {
                    foreach ($result['flights'] as &$flight) {
                        $flight['provider'] = $api->provider;
                    }
                    unset($flight);
                    $allFlights = array_merge($allFlights, $result['flights']);
                } else {
                    $errors[$api->provider] = $result['error'] ?? 'No flights';
                }

            } catch (\Exception $e) {
                Log::warning('Flight search failed', [
                    'provider' => $api->provider,
                    'error' => $e->getMessage(),
                ]);
                $errors[$api->provider] = $e->getMessage();
            }
        }

        // Sort by price
        // usort($allFlights, fn($a, $b) => (float)($a['price']['total'] ?? 0) <=> (float)($b['price']['total'] ?? 0)
        // );

        $this->sortFlights($allFlights, $searchData['airline_codes'] ?? null);

        return [
            'success' => !empty($allFlights),
            'flights' => $allFlights,
            'total_results' => count($allFlights),
            'currency' => 'BDT',
            'errors' => $errors,
        ];
    }


    /**
     * ✅ Sort flights:
     * - User airline দিলে → সেই airline আগে, তারপর price
     * - না দিলে → validating_carrier অনুযায়ী group, তারপর price
     */
    private function sortFlights(array &$flights, $userAirlines = null): void
    {
        // ✅ User airline array তে convert করো
        $preferredAirlines = [];
        if (!empty($userAirlines)) {
            $preferredAirlines = is_array($userAirlines)
                ? array_map('strtoupper', $userAirlines)
                : array_map('trim', explode(',', strtoupper($userAirlines)));
        }

        usort($flights, function ($a, $b) use ($preferredAirlines) {

            $aCarrier = strtoupper($a['validating_carrier'] ?? '');
            $bCarrier = strtoupper($b['validating_carrier'] ?? '');

            // ✅ User airline দিলে সেটা আগে
            if (!empty($preferredAirlines)) {
                $aPreferred = in_array($aCarrier, $preferredAirlines) ? 0 : 1;
                $bPreferred = in_array($bCarrier, $preferredAirlines) ? 0 : 1;

                if ($aPreferred !== $bPreferred) {
                    return $aPreferred <=> $bPreferred;
                }
            }

            // ✅ Same carrier হলে price sort
            if ($aCarrier === $bCarrier) {
                return (float)($a['price']['total'] ?? 0)
                    <=> (float)($b['price']['total'] ?? 0);
            }

            // ✅ আলাদা carrier হলে price sort
            return (float)($a['price']['total'] ?? 0)
                <=> (float)($b['price']['total'] ?? 0);
        });
    }

    /**
     * Prepare base search data
     */
    private function prepareSearchData(array $validated): array
    {
        $segments = $this->segmentProcessor->processSegments(
            $validated['segments'],
            $validated['trip_type'],
            $validated['return_date'] ?? null
        );

        $passengers = $this->passengerProcessor->processPassengers($validated);
        $firstSegment = $segments[0];

        return [
            'trip_type' => $validated['trip_type'],
            'segments' => $segments,
            'passengers' => $passengers,
            'travel_class' => $validated['travel_class'],
            'route_from' => $firstSegment['origin']['code'] ?? null,
            'route_to' => $firstSegment['destination']['code'] ?? null,
            'user_airline_codes' => $validated['airline_codes'] ?? null,
        ];
    }

    /**
     * Search by provider — Air Arabia যোগ করা হয়েছে
     */
    private function searchByProvider(string $provider, array $searchData): array
    {
        return match (strtolower($provider)) {
            'travelport' => $this->searchTravelPort($searchData),
            'sabre' => $this->searchSabre($searchData),
            'airarabia' => $this->searchAirArabia($searchData), // ← নতুন
            default => ['success' => false, 'flights' => [], 'error' => 'Unknown provider: ' . $provider],
        };
    }

    private function searchTravelPort(array $searchData): array
    {
        $service = new TravelPortFlightService($this->discountService, $this->chargesService);
        return $service->search($searchData);
    }

    private function searchSabre(array $searchData): array
    {

        $service = new SabreFlightService($this->discountService, $this->chargesService);
        return $service->search($searchData);
    }

    private function searchAirArabia(array $searchData): array
    {
        $service = new AirArabiaFlightService($this->discountService, $this->chargesService);
        return $service->search($searchData);
    }
}
