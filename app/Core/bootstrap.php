<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Env;
use App\Core\Router;
use App\Core\Session;

require_once __DIR__ . '/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = base_path('app/' . str_replace('\\', '/', $relativeClass) . '.php');

    if (is_file($path)) {
        require_once $path;
    }
});

Env::load(base_path('.env'));
Session::start();

Config::set([
    'app' => require base_path('config/app.php'),
    'database' => require base_path('config/database.php'),
]);

$router = new Router();
$routes = require base_path('app/Core/routes.php');
$routes($router);

return $router;
