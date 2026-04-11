<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $options = []): void
    {
        $this->add('GET', $path, $handler, $options);
    }

    public function post(string $path, callable|array $handler, array $options = []): void
    {
        $this->add('POST', $path, $handler, $options);
    }

    private function add(string $method, string $path, callable|array $handler, array $options): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'options' => $options,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            Response::abort(404, 'Page not found.');
        }

        $authService = new AuthService();

        if (($route['options']['auth'] ?? false) === true && !$authService->check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            Response::redirect('/login');
        }

        if (($route['options']['guest'] ?? false) === true && $authService->check()) {
            Response::redirect('/dashboard');
        }

        $handler = $route['handler'];

        if (is_array($handler)) {
            [$class, $action] = $handler;
            $controller = $this->makeController($class, $authService);
            $controller->{$action}();
            return;
        }

        $handler();
    }

    private function makeController(string $class, AuthService $authService): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        return new $class($authService);
    }
}
