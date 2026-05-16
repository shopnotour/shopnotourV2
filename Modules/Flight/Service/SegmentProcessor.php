<?php

namespace Modules\Flight\Service;

use Illuminate\Support\Collection;
use Modules\Flight\Models\Airport;

class SegmentProcessor
{
    public function processSegments(array $segments, string $tripType, ?string $returnDate = null): array
    {

        // Round trip: Create return segment automatically
        if ($tripType === 'round' && $returnDate && count($segments) === 1) {
            $segments = $this->createRoundTripSegments($segments[0], $returnDate);
        }
        // Step 1: Extract unique airport IDs
        $airportIds = $this->extractAirportIds($segments);

        // Step 2: Fetch airports (single query - efficient)
        $airports = $this->getAirports($airportIds);

        // Step 3: Return standardized format (NOT API specific)
        return $this->prepareStandardFormat($segments, $airports);
    }

    /**
     * Create round trip segments (outbound + return)
     */
    private function createRoundTripSegments(array $outboundSegment, string $returnDate): array
    {
        return [
            // Outbound segment
            $outboundSegment,

            // Return segment (reversed)
            [
                'from' => $outboundSegment['to'],
                'to' => $outboundSegment['from'],
                'departure' => $returnDate,
            ]
        ];
    }

    private function extractAirportIds(array $segments): array
    {
        return collect($segments)
            ->flatMap(fn($segment) => [$segment['from'], $segment['to']])
            ->unique()
            ->values()
            ->toArray();
    }
    private function getAirports(array $ids): Collection
    {
        return Airport::whereIn('id', $ids)
            ->get(['id', 'code', 'name', 'address'])
            ->keyBy('id');
    }

    /**
     * Format segments for API
     */

    private function prepareStandardFormat(array $segments, Collection $airports): array
    {
        return collect($segments)->map(function($segment) use ($airports) {
            $originAirport = $airports[$segment['from']];
            $destinationAirport = $airports[$segment['to']];

            return [
                'departure_date' => $segment['departure'],
                'origin' => [
                    'id' => $originAirport->id,
                    'code' => $originAirport->code,
                    'name' => $originAirport->name,
                    'address' => $originAirport->address,
                ],
                'destination' => [
                    'id' => $destinationAirport->id,
                    'code' => $destinationAirport->code,
                    'name' => $destinationAirport->name,
                    'address' => $destinationAirport->address,
                ],
            ];
        })->toArray();
    }

    /**
     * Validate segments structure
     */
    public function validateSegments(array $segments): bool
    {
        foreach ($segments as $segment) {
            if (!isset($segment['from']) || !isset($segment['to']) || !isset($segment['departure'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get total number of segments
     */
    public function getSegmentCount(array $segments): int
    {
        return count($segments);
    }

    public function validateRoundTrip(string $tripType, ?string $returnDate, array $segments): ?string
    {
        if ($tripType === 'round') {
            if (!$returnDate) {
                return 'Return date is required for round trips';
            }

            if (count($segments) !== 1) {
                return 'Round trip should have only one outbound segment';
            }

            if (strtotime($returnDate) <= strtotime($segments[0]['departure'])) {
                return 'Return date must be after departure date';
            }
        }

        return null; // No errors
    }
//    private function formatForAPI(array $segments, Collection $airports): array
//    {
//        return collect($segments)->map(function($segment) use ($airports) {
//            $originAirport = $airports[$segment['from']];
//            $destinationAirport = $airports[$segment['to']];
//
//            return [
//                'DepartureDateTime' => $this->formatDepartureDate($segment['departure']),
//                'OriginLocation' => [
//                    'LocationCode' => $originAirport->code,
//                ],
//                'DestinationLocation' => [
//                    'LocationCode' => $destinationAirport->code,
//                ],
//            ];
//        })->toArray();
//    }

    /**
     * Format departure date for API
     */
//    private function formatDepartureDate(string $date): string
//    {
//        return date('Y-m-d\TH:i:s', strtotime($date));
//        // Output: 2025-12-13T00:00:00
//    }

    /**
     * Get segment details with airport info (optional - if needed)
     */
//    public function getSegmentDetails(array $segments): array
//    {
//        $airportIds = $this->extractAirportIds($segments);
//        $airports = $this->getAirports($airportIds);
//
//        return collect($segments)->map(function($segment) use ($airports) {
//            $originAirport = $airports[$segment['from']];
//            $destinationAirport = $airports[$segment['to']];
//
//            return [
//                'departure_date' => $segment['departure'],
//                'origin' => [
//                    'id' => $originAirport->id,
//                    'code' => $originAirport->code,
//                    'name' => $originAirport->name,
//                    'address' => $originAirport->address,
//                ],
//                'destination' => [
//                    'id' => $destinationAirport->id,
//                    'code' => $destinationAirport->code,
//                    'name' => $destinationAirport->name,
//                    'address' => $destinationAirport->address,
//                ],
//            ];
//        })->toArray();
//    }
}
