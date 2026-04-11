<?php

declare(strict_types=1);

$router = require __DIR__ . '/app/Core/bootstrap.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
