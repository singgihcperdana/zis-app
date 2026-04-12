<?php

declare(strict_types=1);

$router = require __DIR__ . '/app/Core/bootstrap.php';
$requestUri = $_GET['__route'] ?? ($_SERVER['REQUEST_URI'] ?? '/');

if (!is_string($requestUri) || $requestUri === '') {
    $requestUri = '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestUri);
