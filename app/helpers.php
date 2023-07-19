<?php

use AmrShawky\LaravelCurrency\Facade\Currency;

if (!function_exists('convert_currency')) {
    function convert_currency($amount, $from, $to)
    {
        return Currency::convert()
            ->from($from)
            ->to($to)
            ->amount($amount)
            ->get();
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        return number_format(round($amount, 2), 2, ',', ' ');
    }
}
