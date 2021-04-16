<?php

namespace App;

use function array_filter;

if (!function_exists(__NAMESPACE__ . '\array_filter_null')) {
    function array_filter_null(array $array): array
    {
        return array_filter(
            $array,
            fn($value): bool => $value !== null,
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\array_map_recursive')) {
    function array_map_recursive(\Closure $callback, array $array): array
    {
        return array_map(
            fn($item) => match (true) {
                is_array($item) => array_map_recursive($callback, $item),
                default => $callback($item),
            },
            $array,
        );
    }
}
