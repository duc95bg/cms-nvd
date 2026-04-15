<?php

namespace App\Support;

class FlattenContent
{
    /**
     * Flatten a nested content array into a [dotKey => value] map.
     *
     * Rules:
     * - Scalar (or non-array) → [$prefix => $value]
     * - Indexed list (keys 0..N-1) → LEAF, [$prefix => $value]
     * - Associative array whose keys intersect $locales → translatable LEAF,
     *   [$prefix => $value]
     * - Otherwise → recurse, joining keys with '.'
     *
     * @param  array<string,mixed>  $data
     * @param  array<int,string>    $locales
     * @return array<string,mixed>
     */
    public static function flatten(array $data, array $locales, string $prefix = ''): array
    {
        // Empty array at a prefix → treat as leaf so the caller still sees the key.
        if ($prefix !== '' && $data === []) {
            return [$prefix => $data];
        }

        // Indexed list → leaf.
        if ($data !== [] && array_keys($data) === range(0, count($data) - 1)) {
            return [$prefix => $data];
        }

        // Translatable leaf: associative array whose keys intersect $locales.
        if ($data !== [] && !empty(array_intersect(array_keys($data), $locales))) {
            return [$prefix => $data];
        }

        $out = [];
        foreach ($data as $key => $value) {
            $newKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (!is_array($value)) {
                $out[$newKey] = $value;
                continue;
            }

            $out += self::flatten($value, $locales, $newKey);
        }

        return $out;
    }
}
