<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;
use App\Services\PublicDashboardService;
use RuntimeException;

final class PublicDashboardController
{
    private PublicDashboardService $dashboard;

    public function __construct()
    {
        $this->dashboard = new PublicDashboardService();
    }

    public function page(): void
    {
        View::render('public/dashboard', [
            'title' => 'Dashboard Publik ZIS',
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
