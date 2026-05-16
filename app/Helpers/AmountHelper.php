<?php

namespace App\Helpers;

class AmountHelper
{
    public static function inWords(float $amount): string
    {
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
            'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
            'Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $n = (int) round($amount);
        if ($n == 0) return 'Zero Taka Only';

        $convert = function(int $n) use (&$convert, $ones, $tens): string {
            if ($n == 0)        return '';
            if ($n < 20)        return $ones[$n].' ';
            if ($n < 100)       return $tens[(int)($n/10)].' '.($n%10 ? $ones[$n%10].' ' : '');
            if ($n < 1000)      return $ones[(int)($n/100)].' Hundred '.($n%100 ? $convert($n%100) : '');
            if ($n < 100000)    return $convert((int)($n/1000)).'Thousand '.($n%1000 ? $convert($n%1000) : '');
            if ($n < 10000000)  return $convert((int)($n/100000)).'Lakh '.($n%100000 ? $convert($n%100000) : '');
            return $convert((int)($n/10000000)).'Crore '.($n%10000000 ? $convert($n%10000000) : '');
        };

        return trim($convert($n)).' Taka Only';
    }
}
