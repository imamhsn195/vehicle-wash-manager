<?php

use App\Support\Currency;

if (! function_exists('currency_code')) {
    function currency_code(): string
    {
        return Currency::code();
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        return Currency::symbol();
    }
}

if (! function_exists('money_format_app')) {
    function money_format_app(float|int|string|null $amount, ?string $code = null): string
    {
        return Currency::format($amount, $code);
    }
}
