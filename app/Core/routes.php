<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InternalController;
use App\Core\Response;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', static function (): void {
        Response::redirect('/login');
    });

    $router->get('/login', [AuthController::class, 'showLogin'], ['guest' => true]);
    $router->post('/login', [AuthController::class, 'login'], ['guest' => true]);
    $router->get('/dashboard', [DashboardController::class, 'index'], ['auth' => true]);
    $router->post('/logout', [AuthController::class, 'logout'], ['auth' => true]);
    $router->post('/internal/migrate', [InternalController::class, 'runMigrations']);
};
