<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\DashboardService;
use RuntimeException;

final class DashboardController
{
    private AuthService $auth;
    private DashboardService $dashboard;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->dashboard = new DashboardService();
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

    public function summaryApi(): void
    {
        $from = isset($_GET['from']) ? trim((string) $_GET['from']) : null;
        $to = isset($_GET['to']) ? trim((string) $_GET['to']) : null;

        try {
            Response::json($this->dashboard->summary($from, $to));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
