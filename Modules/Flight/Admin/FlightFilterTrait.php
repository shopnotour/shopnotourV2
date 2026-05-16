<?php

namespace Modules\Flight\Admin;

trait FlightFilterTrait
{
    use FlightFormatHelperTrait;

    protected function applyBackendFilters($flights, $request)
    {
        if (empty($flights)) return $flights;

        $filtered = $flights;

        // Price Range Filter
        if ($request->has('price_range')) {
            $filtered = $this->filterByPriceRange($filtered, $request->input('price_range'));
        }

        // Airline Filter
        if ($request->has('airline_codes') && is_array($request->input('airline_codes'))) {
            $filtered = $this->filterByAirlines($filtered, $request->input('airline_codes'));
        }

        // Stops Filter
        if ($request->has('stop_type') && !empty($request->input('stop_type'))) {
            $filtered = $this->filterByStops($filtered, $request->input('stop_type'));
        }

        // Time Slots Filter
        if ($request->has('time_slots') && !empty($request->input('time_slots'))) {
            $filtered = $this->filterByTimeSlots($filtered, $request->input('time_slots'));
        }

        // Sorting
        if ($request->has('orderby')) {
            $filtered = $this->sortFlights($filtered, $request->input('orderby'));
        }

        return array_values($filtered);
    }

    private function filterByPriceRange($flights, $priceRange)
    {
        $priceRange = explode(';', $priceRange);
        if (count($priceRange) != 2) return $flights;

        $minPrice = (float)$priceRange[0];
        $maxPrice = (float)$priceRange[1];

        return array_filter($flights, function ($flight) use ($minPrice, $maxPrice) {
            $price = $flight['price'] ?? 0;
            return $price >= $minPrice && $price <= $maxPrice;
        });
    }

    private function filterByAirlines($flights, $selectedAirlineCodes)
    {
        return array_filter($flights, function ($flight) use ($selectedAirlineCodes) {
            $airlineCode = $flight['airline_code'] ?? '';
            return in_array($airlineCode, $selectedAirlineCodes);
        });
    }

    private function filterByStops($flights, $stopType)
    {
        return array_filter($flights, function ($flight) use ($stopType) {
            $originalData = $flight['original_data'] ?? null;
            if (!$originalData || !isset($originalData->itineraries)) {
                return false;
            }

            $itineraries = $originalData->itineraries;
            if (empty($itineraries)) return false;

            $firstItinerary = is_object($itineraries[0]) ? $itineraries[0] : (object)$itineraries[0];
            $segments = $firstItinerary->segments ?? [];

            if (is_object($segments)) {
                $segments = (array)$segments;
            }

            $stopsCount = max(count($segments) - 1, 0);

            switch ($stopType) {
                case 'non-stop':
                    return $stopsCount === 0;
                case 'one-stop':
                    return $stopsCount === 1;
                case 'two-stop':
                    return $stopsCount >= 2;
                default:
                    return true;
            }
        });
    }

    private function filterByTimeSlots($flights, $timeSlots)
    {
        $selectedSlots = is_array($timeSlots) ? $timeSlots : explode(',', $timeSlots);

        return array_filter($flights, function ($flight) use ($selectedSlots) {
            $originalData = $flight['original_data'] ?? null;
            if (!$originalData) return false;

            $itineraries = is_object($originalData)
                ? ($originalData->itineraries ?? null)
                : ($originalData['itineraries'] ?? null);

            if (empty($itineraries)) return false;

            $firstItinerary = is_array($itineraries[0])
                ? $itineraries[0]
                : (array)$itineraries[0];

            $segments = $firstItinerary['segments'] ?? ($firstItinerary->segments ?? []);
            if (empty($segments)) return false;

            $firstSegment = is_array($segments[0])
                ? $segments[0]
                : (array)$segments[0];

            $departureAt = $firstSegment['departure']['at']
                ?? ($firstSegment['departure']->at ?? null);

            if (empty($departureAt)) return false;

            try {
                $dateTime = new \DateTime($departureAt);
                $hour = (int)$dateTime->format('H');
            } catch (\Exception $e) {
                return false;
            }

            foreach ($selectedSlots as $slot) {
                $slotParts = explode('-', trim($slot));
                if (count($slotParts) !== 2) continue;

                $start = (int)$slotParts[0];
                $end = (int)$slotParts[1];

                if ($end == 0) $end = 24;

                if ($hour >= $start && $hour < $end) {
                    return true;
                }
            }

            return false;
        });
    }

    private function sortFlights($flights, $orderby)
    {
        usort($flights, function ($a, $b) use ($orderby) {
            switch ($orderby) {
                case 'price_low_high':
                    return ($a['price'] ?? 0) <=> ($b['price'] ?? 0);

                case 'price_high_low':
                    return ($b['price'] ?? 0) <=> ($a['price'] ?? 0);

                case 'duration_low_high':
                    $aDuration = $this->parseDurationToMinutes($a['duration'] ?? '');
                    $bDuration = $this->parseDurationToMinutes($b['duration'] ?? '');
                    return $aDuration <=> $bDuration;

                case 'duration_high_low':
                    $aDuration = $this->parseDurationToMinutes($a['duration'] ?? '');
                    $bDuration = $this->parseDurationToMinutes($b['duration'] ?? '');
                    return $bDuration <=> $aDuration;

                default:
                    return 0;
            }
        });

        return $flights;
    }
}
