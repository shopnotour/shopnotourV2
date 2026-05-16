<?php
/**
 * Flight Validation Demo
 * 
 * This script demonstrates how to validate Sabre flight data
 * and identify invalid/NOOP flights from your dataset.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Modules\Flight\Helpers\FlightValidator;

// Example: Your Sabre raw data (groupedItineraryResponse)
$sabreRawData = [
    'scheduleDescs' => [
        [
            'id' => 1,
            'departure' => ['airport' => 'DAC', 'time' => '12:45:00+06:00'],
            'arrival' => ['airport' => 'CXB', 'time' => '13:50:00+06:00'],
            'carrier' => [
                'marketing' => 'BS',
                'marketingFlightNumber' => '149',
                'operating' => 'BS',
                'operatingFlightNumber' => '149',
            ]
        ],
        // Add more schedules...
    ],
    'itineraryGroups' => [
        // Your itinerary groups...
    ]
];

// Example: Your prepared flight data
$preparedFlights = [
    [
        'id' => 804048377,
        'airline_code' => 'BS',
        'price' => 4999,
        'flight_routes' => [
            [
                'segments' => [
                    [
                        'departure_iata_code' => 'DAC',
                        'departure_at' => '2025-11-12T10:30:00+06:00',
                        'arrival_iata_code' => 'CXB',
                        'arrival_at' => '2025-11-12T11:35:00+06:00',
                        'carrier_code' => 'BS',
                        'flight_number' => '145',
                    ]
                ]
            ]
        ],
        'alldata' => [/* ... */]
    ],
    // Example of NOOP flight (invalid)
    [
        'id' => 999999999,
        'airline_code' => 'NOOP',
        'price' => 0,
        'flight_routes' => [
            [
                'segments' => [
                    [
                        'departure_iata_code' => 'DAC',
                        'departure_at' => '2025-11-12T00:00:00+06:00',
                        'arrival_iata_code' => 'DAC', // Same as departure - invalid
                        'arrival_at' => '2025-11-12T00:00:00+06:00',
                        'carrier_code' => 'NOOP',
                        'flight_number' => 'NOOP',
                    ]
                ]
            ]
        ],
    ],
];

// ============================================
// VALIDATION EXAMPLE
// ============================================

echo "===== FLIGHT VALIDATION DEMO =====\n\n";

// 1. Validate flights
$validationResults = FlightValidator::validateFlights($sabreRawData, $preparedFlights);

// 2. Display summary
echo "SUMMARY:\n";
echo "--------\n";
echo "Total Flights: " . $validationResults['summary']['total_flights'] . "\n";
echo "Valid Flights: " . $validationResults['summary']['valid_flights'] . "\n";
echo "Invalid Flights: " . $validationResults['summary']['invalid_flights'] . "\n";
echo "Validation Rate: " . $validationResults['summary']['validation_rate'] . "%\n\n";

// 3. Display issues
if (!empty($validationResults['issues'])) {
    echo "ISSUES FOUND:\n";
    echo "-------------\n";
    foreach ($validationResults['issues'] as $issue) {
        echo sprintf(
            "[%s] Flight %s (Carrier: %s, Flight: %s) - %s\n",
            strtoupper($issue['severity']),
            $issue['flight_id'],
            $issue['carrier'] ?? 'N/A',
            $issue['flight_number'] ?? 'N/A',
            $issue['issue']
        );
    }
    echo "\n";
}

// 4. Display valid flights
echo "VALID FLIGHTS:\n";
echo "--------------\n";
foreach ($validationResults['valid'] as $flight) {
    $segment = $flight['flight_routes'][0]['segments'][0] ?? [];
    echo sprintf(
        "ID: %s | %s %s | %s → %s | Depart: %s | Price: %s\n",
        $flight['id'],
        $flight['airline_code'],
        $segment['flight_number'] ?? 'N/A',
        $segment['departure_iata_code'] ?? 'N/A',
        $segment['arrival_iata_code'] ?? 'N/A',
        $segment['departure_at'] ?? 'N/A',
        $flight['price'] ?? 'N/A'
    );
}
echo "\n";

// 5. Display invalid flights
if (!empty($validationResults['invalid'])) {
    echo "INVALID FLIGHTS (FILTERED OUT):\n";
    echo "--------------------------------\n";
    foreach ($validationResults['invalid'] as $flight) {
        $segment = $flight['flight_routes'][0]['segments'][0] ?? [];
        echo sprintf(
            "ID: %s | %s %s | %s → %s\n",
            $flight['id'],
            $flight['airline_code'] ?? 'UNKNOWN',
            $segment['flight_number'] ?? 'N/A',
            $segment['departure_iata_code'] ?? 'N/A',
            $segment['arrival_iata_code'] ?? 'N/A'
        );
    }
    echo "\n";
}

// ============================================
// QUICK CLEAN EXAMPLE
// ============================================

echo "\n===== QUICK CLEAN EXAMPLE =====\n\n";

$cleanedFlights = FlightValidator::cleanFlights($preparedFlights);

echo "Original count: " . count($preparedFlights) . "\n";
echo "Cleaned count: " . count($cleanedFlights) . "\n";
echo "Removed: " . (count($preparedFlights) - count($cleanedFlights)) . "\n\n";

// ============================================
// HTML SUMMARY EXAMPLE
// ============================================

echo "===== HTML SUMMARY =====\n\n";
$htmlSummary = FlightValidator::generateSummaryTable($validationResults);
echo $htmlSummary . "\n\n";

// ============================================
// SCHEDULE COMPARISON EXAMPLE
// ============================================

echo "\n===== SCHEDULE COMPARISON EXAMPLE =====\n\n";

// Known Bangladesh domestic schedules (your reference data)
$knownSchedules = [
    ['carrier' => 'BS', 'flight_number' => '145', 'departure' => 'DAC', 'arrival' => 'CXB'],
    ['carrier' => 'BS', 'flight_number' => '147', 'departure' => 'DAC', 'arrival' => 'CXB'],
    ['carrier' => 'BS', 'flight_number' => '149', 'departure' => 'DAC', 'arrival' => 'CXB'],
    ['carrier' => 'BG', 'flight_number' => '437', 'departure' => 'DAC', 'arrival' => 'CXB'],
    // Add more known schedules...
];

$comparison = FlightValidator::compareSchedules($knownSchedules, $sabreRawData['scheduleDescs']);

echo "Schedule Comparison Results:\n";
echo "Total Known Schedules: " . $comparison['stats']['total_known'] . "\n";
echo "Matched in Sabre: " . $comparison['stats']['matched'] . "\n";
echo "Discrepancies: " . $comparison['stats']['discrepancies'] . "\n";
echo "Missing from Sabre: " . $comparison['stats']['missing'] . "\n\n";

if (!empty($comparison['missing'])) {
    echo "MISSING SCHEDULES:\n";
    foreach ($comparison['missing'] as $missing) {
        echo sprintf("  - %s%s (%s)\n", $missing['carrier'], $missing['flight'], $missing['route']);
    }
    echo "\n";
}

if (!empty($comparison['discrepancies'])) {
    echo "DISCREPANCIES:\n";
    foreach ($comparison['discrepancies'] as $disc) {
        echo sprintf(
            "  - %s: Known=%s, Sabre=%s\n",
            $disc['flight'],
            $disc['known'],
            $disc['sabre']
        );
    }
}

echo "\n===== DEMO COMPLETE =====\n";
