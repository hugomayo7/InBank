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
