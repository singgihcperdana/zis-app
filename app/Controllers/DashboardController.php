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
        $user = $this->auth->user();

        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'pageTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
            'flashError' => Session::pullFlash('error'),
            'flashSuccess' => Session::pullFlash('success'),
            'user' => $user,
            'csrfToken' => Session::csrfToken(),
            'stats' => [
                'appName' => Config::get('app.name', 'ZIS App'),
                'database' => Config::get('database.database'),
                'host' => Config::get('database.host'),
                'role' => is_array($user) ? (string) ($user['role'] ?? '-') : '-',
            ],
        ]);
    }
}
