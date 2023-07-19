<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('convert_currency')) {
    function convert_currency($amount, $from, $to)
    {
        $currencyApiKey = env('CURRENCY_API_KEY');
        $result = Http::get("https://v6.exchangerate-api.com/v6/$currencyApiKey/pair/$from/$to/$amount");

        try {
            $result = $result->json();
            return $result['conversion_result'];
        } catch (\Exception $e) {
            return $amount;
        }
    }
}
