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

    public function put(string $path, callable|array $handler, array $options = []): void
    {
        $this->add('PUT', $path, $handler, $options);
    }

    public function delete(string $path, callable|array $handler, array $options = []): void
    {
        $this->add('DELETE', $path, $handler, $options);
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

        if ($route === null && isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $candidate) {
                if (!str_contains($routePath, '{')) {
                    continue;
                }

                $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (is_string($pattern) && preg_match($pattern, $path, $matches) === 1) {
                    array_shift($matches);
                    $route = $candidate;
                    $route['params'] = $matches;
                    break;
                }
            }
        }

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

        $allowedRoles = $route['options']['roles'] ?? [];

        if ($allowedRoles !== []) {
            $user = $authService->user();
            $role = is_array($user) ? (string) ($user['role'] ?? '') : '';

            if (!in_array($role, $allowedRoles, true)) {
                Session::flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                Response::redirect('/dashboard');
            }
        }

        $handler = $route['handler'];

        if (is_array($handler)) {
            [$class, $action] = $handler;
            $controller = $this->makeController($class, $authService);
            $params = $route['params'] ?? [];
            $controller->{$action}(...$params);
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
