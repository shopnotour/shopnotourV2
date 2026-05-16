<?php

namespace Modules\Flight\Admin;

trait SabreDataProcessorTrait
{
    use DataHelperTrait, FlightFormatHelperTrait;


    protected function getBaggage(array $fareInfo, $defaultChecked = 20, $defaultCabin = 7)
    {
        $unit = 'KG';
        $checked = null;
        $cabin = null;

        // checked baggage
        if (isset($fareInfo['baggageAllowance']['checkedBags']['weight'])) {
            $checked = (float)$fareInfo['baggageAllowance']['checkedBags']['weight'];
            $unit = $fareInfo['baggageAllowance']['checkedBags']['unit'] ?? $unit;
        } elseif (isset($fareInfo['baggage']['checked']['weight'])) {
            $checked = (float)$fareInfo['baggage']['checked']['weight'];
            $unit = $fareInfo['baggage']['checked']['unit'] ?? $unit;
        }

        // cabin baggage
        if (isset($fareInfo['baggageAllowance']['cabinBags']['weight'])) {
            $cabin = (float)$fareInfo['baggageAllowance']['cabinBags']['weight'];
            $unit = $fareInfo['baggageAllowance']['cabinBags']['unit'] ?? $unit;
        } elseif (isset($fareInfo['baggage']['cabin']['weight'])) {
            $cabin = (float)$fareInfo['baggage']['cabin']['weight'];
            $unit = $fareInfo['baggage']['cabin']['unit'] ?? $unit;
        }

        if ($checked === null) $checked = $defaultChecked;
        if ($cabin === null) $cabin = $defaultCabin;

        return [
            'checked' => ['weight' => (string)(int)$checked, 'weightUnit' => $unit],
            'cabin' => ['weight' => (string)(int)$cabin, 'weightUnit' => $unit],
        ];
    }

    protected function findBookableSeats(
        array $itinerary,
        array $pricingInfo,
        array $fare,
        ?array $legDesc,
        array $scheduleDescs
    ) {
        $intOrSeats = function ($v) {
            if ($v === null) return null;
            if (is_int($v) || (is_numeric($v) && (string)(int)$v === (string)$v)) return (int)$v;
            if (is_string($v)) {
                $vv = trim($v);
                if ($vv === '') return null;
                if ($vv === 'A' || $vv === '9+' || strcasecmp($vv, 'AVAILABLE') === 0) return 9;
                if (preg_match('/^\d+$/', $vv)) return (int)$vv;
            }
            return null;
        };

        $capSeats = function ($n) {
            $n = (int)$n;
            if ($n < 0) $n = 0;
            if ($n > 9) $n = 9;
            return $n;
        };

        // Check direct fields
        $candidates = [
            $this->get($itinerary, 'numberOfBookableSeats'),
            $this->get($pricingInfo, 'numberOfBookableSeats'),
            $this->get($fare, 'numberOfBookableSeats'),
            $this->get($pricingInfo, 'availableSeats'),
            $this->get($fare, 'availableSeats'),
        ];

        foreach ($candidates as $cand) {
            $n = $intOrSeats($cand);
            if ($n !== null) return $capSeats($n);
        }

        // Check schedules
        $minSeats = null;
        $legSchedules = is_array($legDesc['schedules'] ?? null) ? ($legDesc['schedules'] ?? []) : [];

        foreach ($legSchedules as $sch) {
            $sd = isset($sch['ref']) ? $this->getById($scheduleDescs, $sch['ref']) : null;
            if (!$sd || !is_array($sd)) continue;

            $keys = ['availableSeats', 'seatsRemaining', 'seatsAvailable'];
            foreach ($keys as $k) {
                if (array_key_exists($k, $sd)) {
                    $n = $intOrSeats($sd[$k]);
                    if ($n !== null) {
                        $minSeats = ($minSeats === null) ? $n : min($minSeats, $n);
                        break;
                    }
                }
            }
        }

        if ($minSeats === null) $minSeats = 9;
        return $capSeats($minSeats);
    }

    protected function getPassengerTypeLabel($code)
    {
        $labels = [
            'ADT' => 'Adult (12+ years)',
            'CNN' => 'Child (2-11 years)',
            'C03' => 'Child (2-4 years, No UT3 tax)',
            'C07' => 'Child (5-11 years, With UT3 tax)',
            'INF' => 'Infant (0-2 years)',
        ];

        return $labels[$code] ?? $code;
    }
}
