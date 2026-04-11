<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Services\MigrationService;
use RuntimeException;

final class InternalController
{
    public function runMigrations(): void
    {
        $providedToken = $_SERVER['HTTP_X_MIGRATION_TOKEN'] ?? ($_POST['token'] ?? '');
        $expectedToken = (string) Config::get('app.migration.token', '');

        if ($expectedToken === '' || !is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            $this->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $result = (new MigrationService())->migrate();
        } catch (RuntimeException $exception) {
            $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        $this->json([
            'success' => true,
            'executed' => $result['executed'],
            'skipped' => $result['skipped'],
        ]);
    }

    private function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
