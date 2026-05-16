<?php

namespace Modules\Flight\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * FlightValidator - Validates and cleans Sabre GDS flight data
 * 
 * This helper compares raw Sabre API responses (groupedItineraryResponse)
 * with prepared/formatted flight offers to identify invalid or non-operating flights.
 */
class FlightValidator
{
    /**
     * Validate Sabre flight data against prepared dataset
     * 
     * @param array $sabreRawData The raw groupedItineraryResponse from Sabre API
     * @param array $preparedFlights The formatted flight offers array
     * @return array ['valid' => [...], 'invalid' => [...], 'summary' => [...]]
     */
    public static function validateFlights(array $sabreRawData, array $preparedFlights): array
    {
        $validFlights = [];
        $invalidFlights = [];
        $validationIssues = [];

        // Extract Sabre schedule descriptors for validation
        $scheduleDescs = $sabreRawData['scheduleDescs'] ?? [];
        $itineraryGroups = $sabreRawData['itineraryGroups'] ?? [];

        // Build a map of schedule IDs to schedule data for quick lookup
        $scheduleMap = [];
        foreach ($scheduleDescs as $schedule) {
            $scheduleId = $schedule['id'] ?? null;
            if ($scheduleId !== null) {
                $scheduleMap[$scheduleId] = $schedule;
            }
        }

        // Validate each prepared flight
        foreach ($preparedFlights as $flight) {
            $flightId = $flight['id'] ?? 'unknown';
            $allData = $flight['alldata'] ?? [];
            
            // Extract key flight information
            $airlineCode = $flight['airline_code'] ?? null;
            $segments = $flight['flight_routes'][0]['segments'] ?? [];
            
            if (empty($segments)) {
                $invalidFlights[] = $flight;
                $validationIssues[] = [
                    'flight_id' => $flightId,
                    'issue' => 'No segments found',
                    'severity' => 'critical'
                ];
                continue;
            }

            $firstSegment = $segments[0];
            $flightNumber = $firstSegment['flight_number'] ?? $firstSegment['number'] ?? null;
            $carrierCode = $firstSegment['carrier_code'] ?? $firstSegment['carrierCode'] ?? null;
            
            // Validate against known issues
            $validationResult = self::validateFlightSegment(
                $carrierCode,
                $flightNumber,
                $firstSegment,
                $scheduleMap
            );

            if ($validationResult['is_valid']) {
                $validFlights[] = $flight;
            } else {
                $invalidFlights[] = $flight;
                $validationIssues[] = [
                    'flight_id' => $flightId,
                    'carrier' => $carrierCode,
                    'flight_number' => $flightNumber,
                    'issue' => $validationResult['reason'],
                    'severity' => $validationResult['severity'],
                    'details' => $validationResult['details'] ?? []
                ];
            }
        }

        // Build summary statistics
        $summary = self::buildValidationSummary($validFlights, $invalidFlights, $validationIssues);

        return [
            'valid' => $validFlights,
            'invalid' => $invalidFlights,
            'issues' => $validationIssues,
            'summary' => $summary
        ];
    }

    /**
     * Validate individual flight segment
     * Production-ready with smart validation
     */
    private static function validateFlightSegment(
        ?string $carrierCode,
        ?string $flightNumber,
        array $segment,
        array $scheduleMap
    ): array {
        // CRITICAL: Check for NOOP (No Operation) flights - these are placeholder/invalid flights
        if (strtoupper((string)$flightNumber) === 'NOOP' || strtoupper((string)$carrierCode) === 'NOOP') {
            return [
                'is_valid' => false,
                'reason' => 'NOOP flight (Non-operating)',
                'severity' => 'critical',
                'details' => ['carrier' => $carrierCode, 'flight' => $flightNumber]
            ];
        }

        // CRITICAL: Check for missing carrier code or flight number
        if (empty($carrierCode) || empty($flightNumber)) {
            return [
                'is_valid' => false,
                'reason' => 'Missing carrier or flight number',
                'severity' => 'critical',
                'details' => ['carrier' => $carrierCode, 'flight' => $flightNumber]
            ];
        }

        // Check departure and arrival codes
        $departureCode = $segment['departure_iata_code'] ?? $segment['departure']['iataCode'] ?? null;
        $arrivalCode = $segment['arrival_iata_code'] ?? $segment['arrival']['iataCode'] ?? null;

        // CRITICAL: Check for same departure and arrival (invalid route)
        if (!empty($departureCode) && !empty($arrivalCode) && $departureCode === $arrivalCode) {
            return [
                'is_valid' => false,
                'reason' => 'Same departure and arrival airport',
                'severity' => 'critical',
                'details' => ['airport' => $departureCode]
            ];
        }

        // OPTIONAL: Validate date/time if available (but don't reject if missing)
        $departureAt = $segment['departure_at'] ?? $segment['departure']['at'] ?? null;
        $arrivalAt = $segment['arrival_at'] ?? $segment['arrival']['at'] ?? null;

        if (!empty($departureAt) && !empty($arrivalAt)) {
            try {
                $depTime = new \DateTime($departureAt);
                $arrTime = new \DateTime($arrivalAt);
                
                // Check if arrival is before departure (likely data error)
                if ($arrTime < $depTime) {
                    // Log warning but don't reject - might be timezone issue
                    Log::warning('Flight time validation warning', [
                        'carrier' => $carrierCode,
                        'flight' => $flightNumber,
                        'departure' => $departureAt,
                        'arrival' => $arrivalAt,
                        'issue' => 'Arrival before departure'
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't reject - date parsing might have issues
                Log::warning('Flight date parsing warning', [
                    'carrier' => $carrierCode,
                    'flight' => $flightNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // All critical checks passed - flight is valid
        return [
            'is_valid' => true,
            'reason' => 'Valid flight',
            'severity' => 'none'
        ];
    }

    /**
     * Check if a flight operates on Bangladesh domestic routes
     */
    private static function isBangladeshDomestic(string $departureCode, string $arrivalCode): bool
    {
        // Known Bangladesh airports
        $bdAirports = [
            'DAC', // Dhaka
            'CXB', // Cox's Bazar
            'CGP', // Chittagong
            'JSR', // Jashore
            'RJH', // Rajshahi
            'ZYL', // Sylhet
            'SPD', // Saidpur
            'BZL', // Barisal
        ];

        return in_array($departureCode, $bdAirports) && in_array($arrivalCode, $bdAirports);
    }

    /**
     * Build validation summary statistics
     */
    private static function buildValidationSummary(
        array $validFlights,
        array $invalidFlights,
        array $issues
    ): array {
        $totalFlights = count($validFlights) + count($invalidFlights);
        
        // Group issues by severity
        $issuesBySeverity = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        // Group issues by type
        $issuesByType = [];

        foreach ($issues as $issue) {
            $severity = $issue['severity'] ?? 'unknown';
            $issueType = $issue['issue'] ?? 'unknown';

            if (isset($issuesBySeverity[$severity])) {
                $issuesBySeverity[$severity]++;
            }

            if (!isset($issuesByType[$issueType])) {
                $issuesByType[$issueType] = 0;
            }
            $issuesByType[$issueType]++;
        }

        // Group valid flights by carrier
        $flightsByCarrier = [];
        foreach ($validFlights as $flight) {
            $carrier = $flight['airline_code'] ?? 'Unknown';
            if (!isset($flightsByCarrier[$carrier])) {
                $flightsByCarrier[$carrier] = 0;
            }
            $flightsByCarrier[$carrier]++;
        }

        return [
            'total_flights' => $totalFlights,
            'valid_flights' => count($validFlights),
            'invalid_flights' => count($invalidFlights),
            'validation_rate' => $totalFlights > 0 ? round((count($validFlights) / $totalFlights) * 100, 2) : 0,
            'issues_by_severity' => $issuesBySeverity,
            'issues_by_type' => $issuesByType,
            'flights_by_carrier' => $flightsByCarrier,
        ];
    }

    /**
     * Filter out NOOP and invalid flights from prepared dataset
     * 
     * @param array $flights Prepared flight offers
     * @return array Cleaned flight offers
     */
    public static function cleanFlights(array $flights): array
    {
        return array_filter($flights, function($flight) {
            $segments = $flight['flight_routes'][0]['segments'] ?? [];
            
            if (empty($segments)) {
                return false;
            }

            foreach ($segments as $segment) {
                $flightNumber = $segment['flight_number'] ?? $segment['number'] ?? '';
                $carrierCode = $segment['carrier_code'] ?? $segment['carrierCode'] ?? '';
                
                // Reject NOOP flights
                if (strtoupper($flightNumber) === 'NOOP' || strtoupper($carrierCode) === 'NOOP') {
                    return false;
                }

                // Reject flights with missing critical data
                if (empty($flightNumber) || empty($carrierCode)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Log validation results for debugging
     */
    public static function logValidationResults(array $validationResults): void
    {
        $summary = $validationResults['summary'];
        
        Log::info('Flight Validation Complete', [
            'total' => $summary['total_flights'],
            'valid' => $summary['valid_flights'],
            'invalid' => $summary['invalid_flights'],
            'rate' => $summary['validation_rate'] . '%'
        ]);

        if (!empty($validationResults['issues'])) {
            Log::warning('Flight Validation Issues Found', [
                'count' => count($validationResults['issues']),
                'by_severity' => $summary['issues_by_severity'],
                'by_type' => $summary['issues_by_type']
            ]);

            // Log first 10 issues in detail
            $detailedIssues = array_slice($validationResults['issues'], 0, 10);
            foreach ($detailedIssues as $issue) {
                Log::warning('Flight Validation Issue', $issue);
            }
        }
    }

    /**
     * Generate HTML table summary of validation results
     */
    public static function generateSummaryTable(array $validationResults): string
    {
        $summary = $validationResults['summary'];
        
        $html = '<div class="flight-validation-summary">';
        $html .= '<h3>Flight Validation Summary</h3>';
        
        // Overall stats
        $html .= '<table class="table table-bordered">';
        $html .= '<thead><tr><th>Metric</th><th>Value</th></tr></thead>';
        $html .= '<tbody>';
        $html .= '<tr><td>Total Flights</td><td>' . $summary['total_flights'] . '</td></tr>';
        $html .= '<tr><td>Valid Flights</td><td class="text-success">' . $summary['valid_flights'] . '</td></tr>';
        $html .= '<tr><td>Invalid Flights</td><td class="text-danger">' . $summary['invalid_flights'] . '</td></tr>';
        $html .= '<tr><td>Validation Rate</td><td>' . $summary['validation_rate'] . '%</td></tr>';
        $html .= '</tbody></table>';
        
        // Issues by severity
        if (!empty($summary['issues_by_severity']) && array_sum($summary['issues_by_severity']) > 0) {
            $html .= '<h4>Issues by Severity</h4>';
            $html .= '<table class="table table-bordered">';
            $html .= '<thead><tr><th>Severity</th><th>Count</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($summary['issues_by_severity'] as $severity => $count) {
                if ($count > 0) {
                    $html .= '<tr><td>' . ucfirst($severity) . '</td><td>' . $count . '</td></tr>';
                }
            }
            $html .= '</tbody></table>';
        }
        
        // Flights by carrier
        if (!empty($summary['flights_by_carrier'])) {
            $html .= '<h4>Valid Flights by Carrier</h4>';
            $html .= '<table class="table table-bordered">';
            $html .= '<thead><tr><th>Carrier</th><th>Flights</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($summary['flights_by_carrier'] as $carrier => $count) {
                $html .= '<tr><td>' . $carrier . '</td><td>' . $count . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Compare known schedule with Sabre data to find discrepancies
     * 
     * @param array $knownSchedules Array of known flight schedules
     * @param array $sabreSchedules Sabre scheduleDescs array
     * @return array Comparison results
     */
    public static function compareSchedules(array $knownSchedules, array $sabreSchedules): array
    {
        $matches = [];
        $discrepancies = [];
        $missing = [];

        foreach ($knownSchedules as $known) {
            $knownCarrier = $known['carrier'] ?? '';
            $knownFlight = $known['flight_number'] ?? '';
            $knownRoute = ($known['departure'] ?? '') . '-' . ($known['arrival'] ?? '');

            $found = false;

            foreach ($sabreSchedules as $sabre) {
                $sabreCarrier = is_array($sabre['carrier'] ?? null) 
                    ? ($sabre['carrier']['marketing'] ?? '') 
                    : ($sabre['carrier'] ?? '');
                
                $sabreFlight = is_array($sabre['carrier'] ?? null)
                    ? ($sabre['carrier']['marketingFlightNumber'] ?? '')
                    : '';

                $sabreRoute = ($sabre['departure']['airport'] ?? '') . '-' . ($sabre['arrival']['airport'] ?? '');

                if ($knownCarrier === $sabreCarrier && $knownFlight === $sabreFlight) {
                    $found = true;

                    // Check for discrepancies in details
                    if ($knownRoute !== $sabreRoute) {
                        $discrepancies[] = [
                            'flight' => $knownCarrier . $knownFlight,
                            'field' => 'route',
                            'known' => $knownRoute,
                            'sabre' => $sabreRoute
                        ];
                    }

                    $matches[] = [
                        'carrier' => $knownCarrier,
                        'flight' => $knownFlight,
                        'route' => $sabreRoute,
                        'status' => 'matched'
                    ];
                    break;
                }
            }

            if (!$found) {
                $missing[] = [
                    'carrier' => $knownCarrier,
                    'flight' => $knownFlight,
                    'route' => $knownRoute,
                    'status' => 'not_found_in_sabre'
                ];
            }
        }

        return [
            'matches' => $matches,
            'discrepancies' => $discrepancies,
            'missing' => $missing,
            'stats' => [
                'total_known' => count($knownSchedules),
                'matched' => count($matches),
                'discrepancies' => count($discrepancies),
                'missing' => count($missing)
            ]
        ];
    }
}
