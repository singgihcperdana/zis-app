<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\QurbanService;
use RuntimeException;

final class QurbanController
{
    private AuthService $auth;
    private QurbanService $qurban;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->qurban = new QurbanService();
    }

    public function createForm(): void
    {
        View::render('qurban/add', [
            'title' => 'Input Qurban',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
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
}
