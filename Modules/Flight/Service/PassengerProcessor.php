<?php

namespace Modules\Flight\Service;

class PassengerProcessor
{

    /**
     * Process and format passenger data
     * Returns standardized passenger format
     */
    public function processPassengers(array $requestData): array
    {
        $adults = (int) $requestData['adults'];
        $children = (int) ($requestData['children'] ?? 0);
        $infants = (int) ($requestData['infants'] ?? 0);
        $childrenAges = $requestData['children_ages'] ?? [];

        return [
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'children_ages' => $childrenAges,
            'total_passengers' => $this->getTotalCount($adults, $children, $infants),
            'breakdown' => $this->getPassengerBreakdown($adults, $children, $infants, $childrenAges),
        ];
    }

    /**
     * Get total passenger count
     */
    public function getTotalCount(int $adults, int $children, int $infants): int
    {
        return $adults + $children + $infants;
    }

    /**
     * Get detailed passenger breakdown
     */
    private function getPassengerBreakdown(int $adults, int $children, int $infants, array $childrenAges): array
    {
        $breakdown = [];

        // Add adults
        for ($i = 1; $i <= $adults; $i++) {
            $breakdown[] = [
                'type' => 'adult',
                'age' => null,
                'index' => $i,
            ];
        }

        // Add children with ages
        for ($i = 0; $i < $children; $i++) {
            $breakdown[] = [
                'type' => 'child',
                'age' => isset($childrenAges[$i]) ? (int) $childrenAges[$i] : null,
                'index' => $i + 1,
            ];
        }

        // Add infants
        for ($i = 1; $i <= $infants; $i++) {
            $breakdown[] = [
                'type' => 'infant',
                'age' => null,
                'index' => $i,
            ];
        }

        return $breakdown;
    }

    /**
     * Validate passenger data
     */
    public function validatePassengers(array $requestData): array
    {
        $errors = [];

        $adults = (int) ($requestData['adults'] ?? 0);
        $children = (int) ($requestData['children'] ?? 0);
        $infants = (int) ($requestData['infants'] ?? 0);
        $childrenAges = $requestData['children_ages'] ?? [];

        // Check minimum passengers
        if ($adults < 1) {
            $errors[] = 'At least 1 adult is required';
        }

        // Check infants vs adults
        if ($infants > $adults) {
            $errors[] = 'Number of infants cannot exceed number of adults';
        }

        // Check children ages array
        if ($children > 0 && count($childrenAges) !== $children) {
            $errors[] = 'Children ages count must match number of children';
        }

        // Validate each child age
        foreach ($childrenAges as $age) {
            $age = (int) $age;
            if ($age < 0 || $age > 12) {
                $errors[] = 'Child age must be between 0 and 12';
                break;
            }
        }

        // Check total passengers
        $total = $adults + $children + $infants;
        if ($total > 9) {
            $errors[] = 'Maximum 9 passengers allowed per booking';
        }

        return $errors;
    }

    /**
     * Get passenger count by type
     */
    public function getPassengerCountByType(array $passengers): array
    {
        return [
            'adults' => $passengers['adults'],
            'children' => $passengers['children'],
            'infants' => $passengers['infants'],
        ];
    }

    /**
     * Check if passenger data is valid
     */
    public function isValid(array $requestData): bool
    {
        return empty($this->validatePassengers($requestData));
    }

    /**
     * Get passenger summary text
     */
    public function getSummaryText(array $passengers): string
    {
        $parts = [];

        if ($passengers['adults'] > 0) {
            $parts[] = $passengers['adults'] . ' Adult' . ($passengers['adults'] > 1 ? 's' : '');
        }

        if ($passengers['children'] > 0) {
            $parts[] = $passengers['children'] . ' Child' . ($passengers['children'] > 1 ? 'ren' : '');
        }

        if ($passengers['infants'] > 0) {
            $parts[] = $passengers['infants'] . ' Infant' . ($passengers['infants'] > 1 ? 's' : '');
        }

        return implode(', ', $parts);
    }
}
