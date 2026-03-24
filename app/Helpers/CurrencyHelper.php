<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format a value as currency.
     *
     * @param float $amount
     * @return string
     */
    public static function format($amount): string
    {
        $symbol = env('APP_CURRENCY_SYMBOL', '€');
        return $symbol . ' ' . number_format($amount, 2, ',', '.');
    }
}
