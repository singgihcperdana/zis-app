<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\QurbanDistributionService;
use App\Services\QurbanService;
use RuntimeException;

final class QurbanController
{
    private AuthService $auth;
    private QurbanService $qurban;
    private QurbanDistributionService $distributions;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->qurban = new QurbanService();
        $this->distributions = new QurbanDistributionService();
    }

    public function createForm(): void
    {
        View::render('qurban/add', [
            'title' => 'Input Qurban',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function listPage(): void
    {
        View::render('qurban/list', [
            'title' => 'Riwayat Qurban',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function distributionCreateForm(): void
    {
        View::render('qurban-distributions/add', [
            'title' => 'Input Penyaluran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function distributionEditForm(string $id): void
    {
        View::render('qurban-distributions/edit', [
            'title' => 'Edit Penyaluran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'distributionId' => $id,
        ]);
    }

    public function distributionListPage(): void
    {
        View::render('qurban-distributions/list', [
            'title' => 'Riwayat Penyaluran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function distributionCreateApi(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            Response::json([
                'success' => false,
                'message' => 'Payload tidak valid.',
            ], 422);
        }

        $csrfToken = $payload['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        try {
            $currentUser = $this->auth->user();
            $created = $this->distributions->create($payload, (string) ($currentUser['username'] ?? ''));

            Response::json([
                'success' => true,
                'id' => (string) $created['id'],
                'message' => 'Data penyaluran qurban berhasil disimpan.',
                'data' => $created,
            ], 201);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function distributionListApi(): void
    {
        try {
            Response::json($this->distributions->search($_GET));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function distributionShowApi(string $id): void
    {
        try {
            Response::json($this->distributions->getById($id));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function distributionCsv(): void
    {
        try {
            $export = $this->distributions->exportCsv($_GET);
            http_response_code(200);
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . ($export['filename'] ?? 'riwayat-penyaluran-qurban.csv') . '"');
            echo "\xEF\xBB\xBF" . (string) ($export['csv'] ?? '');
            exit;
        } catch (RuntimeException $exception) {
            Response::abort(422, $exception->getMessage());
        }
    }

    public function distributionUpdateApi(string $id): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            Response::json([
                'success' => false,
                'message' => 'Payload tidak valid.',
            ], 422);
        }

        $csrfToken = $payload['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        try {
            $currentUser = $this->auth->user();
            Response::json([
                'success' => true,
                'message' => 'Data penyaluran qurban berhasil diperbarui.',
                'data' => $this->distributions->update($id, $payload, (string) ($currentUser['username'] ?? '')),
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function distributionDeleteApi(string $id): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            $payload = [];
        }

        $csrfToken = $payload['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        try {
            $this->distributions->delete($id);
            Response::json([
                'success' => true,
                'message' => 'Data penyaluran qurban berhasil dihapus.',
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function editForm(string $id): void
    {
        View::render('qurban/edit', [
            'title' => 'Edit Qurban',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'qurbanId' => $id,
        ]);
    }

    public function showApi(string $id): void
    {
        try {
            Response::json($this->qurban->getById($id));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
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

        $csrfToken = $payload['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        try {
            $currentUser = $this->auth->user();
            $created = $this->qurban->create($payload, (string) ($currentUser['username'] ?? ''));

            Response::json([
                'success' => true,
                'id' => (string) $created['id'],
                'qurbanNumber' => (string) $created['qurbanNumber'],
                'message' => 'Data qurban berhasil disimpan.',
            ], 201);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function listApi(): void
    {
        try {
            Response::json($this->qurban->search($_GET));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function updateApi(string $id): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode(is_string($rawBody) ? $rawBody : '', true);

        if (!is_array($payload)) {
            Response::json([
                'success' => false,
                'message' => 'Payload tidak valid.',
            ], 422);
        }

        $csrfToken = $payload['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::json([
                'success' => false,
                'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.',
            ], 419);
        }

        try {
            $currentUser = $this->auth->user();
            Response::json([
                'success' => true,
                'message' => 'Data qurban berhasil diperbarui.',
                'data' => $this->qurban->update($id, $payload, (string) ($currentUser['username'] ?? '')),
            ]);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
