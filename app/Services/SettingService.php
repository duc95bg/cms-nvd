<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    private static array $cache = [];
    private static bool $loaded = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        self::loadAll();

        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        self::$cache[$key] = $value;
    }

    public static function all(): array
    {
        self::loadAll();

        return self::$cache;
    }

    private static function loadAll(): void
    {
        if (self::$loaded) {
            return;
        }

        self::$cache = Setting::pluck('value', 'key')->toArray();
        self::$loaded = true;
    }

    /**
     * Reset cache (useful for testing).
     */
    public static function flush(): void
    {
        self::$cache = [];
        self::$loaded = false;
    }
}
