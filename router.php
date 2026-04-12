<?php

declare(strict_types=1);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

if (is_string($path) && $path !== '/') {
    $filePath = __DIR__ . $path;
    if (is_file($filePath)) {
        return false;
    }
}

require __DIR__ . '/index.php';
