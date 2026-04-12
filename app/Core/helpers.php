<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function config_value(string $key, mixed $default = null): mixed
{
    return \App\Core\Config::get($key, $default);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/';
}

function path_starts_with(string $prefix): bool
{
    return str_starts_with(current_path(), $prefix);
}

function path_matches_any(array $prefixes): bool
{
    foreach ($prefixes as $prefix) {
        if (is_string($prefix) && $prefix !== '' && path_starts_with($prefix)) {
            return true;
        }
    }

    return false;
}
