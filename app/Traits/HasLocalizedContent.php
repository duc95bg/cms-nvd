<?php

namespace App\Traits;

trait HasLocalizedContent
{
    /**
     * Get a localized value from a JSON column.
     * Unlike Site::t() which navigates dot-paths inside a single JSON blob,
     * this resolves at column level: $this->{$column} is the JSON array.
     */
    public function t(string $column, ?string $locale = null, string $default = ''): string
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale');

        return (string) (
            data_get($this->{$column}, $locale)
            ?? data_get($this->{$column}, $fallback)
            ?? $default
        );
    }
}
