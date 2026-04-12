<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\ZakatPaymentService;
use RuntimeException;

final class ZakatPaymentController
{
    private AuthService $auth;
    private ZakatPaymentService $payments;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->payments = new ZakatPaymentService();
    }

    public function createForm(): void
    {
        View::render('zakat-payments/add', [
            'title' => 'Input Pembayaran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function listPage(): void
    {
        View::render('zakat-payments/list', [
            'title' => 'Riwayat Pembayaran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function editForm(string $id): void
    {
        View::render('zakat-payments/edit', [
            'title' => 'Edit Pembayaran',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'paymentId' => $id,
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

        try {
            $currentUser = $this->auth->user();
            $created = $this->payments->create($payload, (string) ($currentUser['username'] ?? ''));

            Response::json([
                'id' => (string) $created['id'],
                'receiptNumber' => (string) $created['receiptNumber'],
            ], 201);
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function receivedBySuggestionsApi(): void
    {
        try {
            Response::json($this->payments->receivedBySuggestions());
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
            Response::json($this->payments->search($_GET));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function showApi(string $id): void
    {
        try {
            Response::json($this->payments->getById($id));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 404);
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

        try {
            $currentUser = $this->auth->user();
            Response::json($this->payments->update($id, $payload, (string) ($currentUser['username'] ?? '')));
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function cancelApi(string $id): void
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
            $currentUser = $this->auth->user();
            $this->payments->cancel($id, (string) ($payload['reason'] ?? ''), (string) ($currentUser['username'] ?? ''));
            Response::noContent();
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
