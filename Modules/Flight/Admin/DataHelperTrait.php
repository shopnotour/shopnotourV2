<?php

namespace Modules\Flight\Admin;

trait DataHelperTrait
{

    /**
     * Find item by ID from array
     */
    protected function getById(array $rows, $id)
    {
        foreach ($rows as $r) {
            if (($r['id'] ?? null) == $id) return $r;
        }
        return null;
    }

    /**
     * Get nested array value using dot notation
     * Example: get($data, 'user.profile.name', 'Unknown')
     */
    protected function get($arr, $path, $default = null)
    {
        if (!is_array($arr)) return $default;
        $keys = is_array($path) ? $path : explode('.', (string)$path);
        $cur = $arr;
        foreach ($keys as $k) {
            if (is_array($cur) && array_key_exists($k, $cur)) {
                $cur = $cur[$k];
            } else {
                return $default;
            }
        }
        return $cur;
    }

    /**
     * Get first scalar value from mixed input
     */
    protected function firstScalar($v, $fallback = null)
    {
        if (is_scalar($v) || $v === null) return $v ?? $fallback;
        if (is_array($v)) {
            foreach ($v as $item) {
                if (is_scalar($item)) return $item;
            }
        }
        return $fallback;
    }

    /**
     * Convert value to string with fallback
     */
    protected function strv($v, $fallback = 'N/A')
    {
        $x = $this->firstScalar($v, $fallback);
        return is_scalar($x) ? (string)$x : $fallback;
    }

    /**
     * Convert value to number with fallback
     */
    protected function numv($v, $fallback = 0.0)
    {
        $x = $this->firstScalar($v, $fallback);
        return is_numeric($x) ? (float)$x : (float)$fallback;
    }

    /**
     * Get string value from mixed input
     */
    protected function getStr($value, $fallback = null)
    {
        if (is_string($value) && trim($value) !== '') return $value;
        if (is_array($value)) {
            $first = reset($value);
            return (is_string($first) && trim($first) !== '') ? $first : $fallback;
        }
        if (is_numeric($value)) return (string)$value;
        return $fallback;
    }

    /**
     * Convert minutes to ISO 8601 duration format
     * Example: 90 -> "PT1H30M"
     */
    protected function minsToIso($m)
    {
        if ($m === null || $m === '' || !is_numeric($m)) return null;
        $m = (int)$m;
        $h = intdiv($m, 60);
        $mm = $m % 60;
        $out = 'PT';
        if ($h) $out .= $h . 'H';
        if ($mm) $out .= $mm . 'M';
        return $out === 'PT' ? 'PT0M' : $out;
    }

    /**
     * Clean array by removing null/empty values recursively
     */
    protected function clean($value)
    {
        if (is_array($value)) {
            $tmp = [];
            foreach ($value as $k => $v) {
                $vv = $this->clean($v);
                if ($vv === [] || $vv === null || $vv === '') continue;
                $tmp[$k] = $vv;
            }
            return $tmp;
        }
        return $value;
    }

    /**
     * Convert boolean to string ("true"/"false")
     */
    protected function boolstr($v): string
    {
        return $v ? 'true' : 'false';
    }

    /**
     * Format number to 2 decimal places
     */
    protected function fmt2($v): string
    {
        return number_format((float)$v, 2, '.', '');
    }
}
