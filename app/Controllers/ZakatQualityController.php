<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\ZakatQualityService;
use RuntimeException;

final class ZakatQualityController
{
    private AuthService $auth;
    private ZakatQualityService $qualities;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->qualities = new ZakatQualityService();
    }

    public function index(): void
    {
        View::render('settings/zakat-quality', [
            'title' => 'Kelola Zakat Quality',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function listApi(): void
    {
        $zakatType = (string) ($_GET['zakatType'] ?? '');

        try {
            $items = $this->qualities->getByType($zakatType);
            Response::json(array_map(static function (array $item): array {
                return [
                    'id' => (string) $item['id'],
                    'name' => (string) $item['name'],
                    'zakatType' => (string) $item['zakat_type'],
                    'beratPerJiwaKg' => $item['berat_per_jiwa_kg'] !== null ? (string) $item['berat_per_jiwa_kg'] : null,
                    'nominalPerJiwa' => $item['nominal_per_jiwa'] !== null ? (int) $item['nominal_per_jiwa'] : null,
                ];
            }, $items));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function createApi(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            Response::json([
                'success' => false,
                'message' => 'Payload tidak valid.',
            ], 422);
        }

        try {
            $created = $this->qualities->create($payload);
            Response::json([
                'id' => (string) $created['id'],
                'name' => (string) $created['name'],
                'zakatType' => (string) $created['zakat_type'],
                'active' => (bool) $created['active'],
                'beratPerJiwaKg' => $created['berat_per_jiwa_kg'] !== null ? (string) $created['berat_per_jiwa_kg'] : null,
                'nominalPerJiwa' => $created['nominal_per_jiwa'] !== null ? (int) $created['nominal_per_jiwa'] : null,
            ], 201);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function deactivateApi(string $id): void
    {
        try {
            $this->qualities->deactivate($id);
            Response::noContent();
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
