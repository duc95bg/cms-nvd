<?php

namespace App\Support;

class PriceFormatter
{
    /**
     * Format a price for display based on locale.
     * VI: 250.000₫   EN: $250,000.00
     */
    public static function format(float $price, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'vi' => number_format($price, 0, ',', '.') . '₫',
            default => '$' . number_format($price, 2, '.', ','),
        };
    }
}
