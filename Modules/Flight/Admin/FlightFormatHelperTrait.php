<?php

namespace Modules\Flight\Admin;

trait FlightFormatHelperTrait
{
//    protected function minsToIso($m)
//    {
//        if ($m === null || $m === '' || !is_numeric($m)) return null;
//        $m = (int)$m;
//        $h = intdiv($m, 60);
//        $mm = $m % 60;
//        $out = 'PT';
//        if ($h) $out .= $h . 'H';
//        if ($mm) $out .= $mm . 'M';
//        return $out === 'PT' ? 'PT0M' : $out;
//    }

//    protected function boolstr($v): string
//    {
//        return $v ? 'true' : 'false';
//    }

//    protected function fmt2($v): string
//    {
//        return number_format((float)$v, 2, '.', '');
//    }

    protected function parseDurationToMinutes($duration)
    {
        if (empty($duration)) return 0;

        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches)) {
            $hours = isset($matches[1]) ? (int)$matches[1] : 0;
            $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
            return ($hours * 60) + $minutes;
        }

        return 0;
    }
}
