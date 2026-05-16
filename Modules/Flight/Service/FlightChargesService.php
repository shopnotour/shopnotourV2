<?php

namespace Modules\Flight\Service;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlightChargesService
{
    /**
     * Calculate flight charges (AIT, Service Charge, Segment Discount)
     *
     * @param string $departureCountry
     * @param string $arrivalCountry
     * @param float $apiSubtotal - API থেকে পাওয়া total price
     * @param float $baseFare - Base fare (equivalent_amount)
     * @param int $totalSegments
     * @return array
     */
    public function calculate(
        string $departureCountry,
        string $arrivalCountry,
        float $apiSubtotal,
        float $baseFare,
        int $totalSegments
    ): array {
        try {
            // Determine type (domestic/international)
            $type = $this->determineType($departureCountry, $arrivalCountry);

            // Get charges from database
            $charges = $this->getCharges($type);

            if (!$charges) {
                return $this->noChargesResponse($apiSubtotal);
            }

            // Calculate AIT (on API subtotal)
            $aitAmount = ($apiSubtotal * $charges->ait_charge) / 100;

            // Service charge (fixed)
            $serviceCharge = $charges->service_charge;

            // Segment discount
            $segmentDiscount = $totalSegments * $charges->segment_discount;

            return [
                'type' => $type,
                'ait_charge_percentage' => $charges->ait_charge,
                'ait_amount' => round($aitAmount, 2),
                'service_charge' => round($serviceCharge, 2),
                'segment_discount_per_segment' => $charges->segment_discount,
                'total_segments' => $totalSegments,
                'segment_discount_total' => round($segmentDiscount, 2),
                'total_charges' => round($aitAmount + $serviceCharge, 2),
                'total_discounts' => round($segmentDiscount, 2),
            ];

        } catch (\Exception $e) {
            Log::error('Flight charges calculation error', [
                'error' => $e->getMessage(),
                'type' => $type ?? 'unknown',
            ]);

            return $this->noChargesResponse($apiSubtotal);
        }
    }

    /**
     * Determine if flight is domestic or international
     */
    private function determineType(string $departureCountry, string $arrivalCountry): string
    {
        return $departureCountry === $arrivalCountry ? 'domestic' : 'international';
    }

    /**
     * Get charges from database
     */
    private function getCharges(string $type)
    {
        return DB::table('flight_charges')
            ->where('type', $type)
            ->where('status', 'active')
            ->first();
    }

    /**
     * No charges response
     */
    private function noChargesResponse(float $apiSubtotal): array
    {
        return [
            'type' => 'unknown',
            'ait_charge_percentage' => 0,
            'ait_amount' => 0,
            'service_charge' => 0,
            'segment_discount_per_segment' => 0,
            'total_segments' => 0,
            'segment_discount_total' => 0,
            'total_charges' => 0,
            'total_discounts' => 0,
        ];
    }
}
