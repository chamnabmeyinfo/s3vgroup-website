<?php
/**
 * Helper functions for formatting dates, currency, etc.
 */

if (!function_exists('format_date')) {
    function format_date($date, ?string $format = null): string
    {
        $format = $format ?? option('date_format', 'M d, Y');
        return date($format, is_numeric($date) ? $date : strtotime($date));
    }
}

if (!function_exists('format_time')) {
    function format_time($time, ?string $format = null): string
    {
        $format = $format ?? option('time_format', 'g:i A');
        return date($format, is_numeric($time) ? $time : strtotime($time));
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, ?string $symbol = null, ?string $code = null): string
    {
        $symbol = $symbol ?? option('currency_symbol', '$');
        $code = $code ?? option('currency_code', 'USD');
        
        // Simple formatting - can be enhanced with locale-aware formatting
        if ($code === 'KHR') {
            return number_format($amount, 0) . ' ' . $code;
        }
        
        return $symbol . number_format($amount, 2);
    }
}

if (!function_exists('display_price')) {
    /**
     * Display price normally (no blur)
     * 
     * @param float|int|string|null $price The price to display
     * @param string $symbol Currency symbol (default: $)
     * @return string HTML formatted price
     */
    function display_price($price, string $symbol = '$'): string
    {
        if (empty($price) || !is_numeric($price)) {
            return '<span class="text-gray-500 italic">Price on request</span>';
        }

        $price = (float) $price;
        return sprintf('<span class="price-display">%s%s</span>', $symbol, number_format($price, 2));
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string
    {
        return option('site_locale', 'en_US');
    }
}

