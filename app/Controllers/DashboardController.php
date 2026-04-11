<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;

final class DashboardController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function index(): void
    {
        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $this->auth->user(),
            'csrfToken' => Session::csrfToken(),
            'dbConfig' => [
                'host' => Config::get('database.host'),
                'port' => Config::get('database.port'),
                'database' => Config::get('database.database'),
                'username' => Config::get('database.username'),
            ],
        ]);
    }
}
