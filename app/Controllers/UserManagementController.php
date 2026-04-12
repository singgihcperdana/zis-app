<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\UserManagementService;
use RuntimeException;

final class UserManagementController
{
    private AuthService $auth;
    private UserManagementService $users;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
        $this->users = new UserManagementService();
    }

    public function settings(): void
    {
        View::render('settings/users', [
            'title' => 'Kelola User',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
        ]);
    }

    public function addForm(): void
    {
        View::render('settings/user-form', [
            'title' => 'Tambah User',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'formUser' => [
                'id' => '',
                'username' => '',
                'email' => '',
                'role' => '',
                'active' => true,
            ],
            'formTitle' => 'Tambah User',
            'formAction' => '/user/add',
            'successMessage' => Session::pullFlash('success'),
        ]);
    }

    public function addSubmit(): void
    {
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Session::flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            Response::redirect('/user/add');
        }

        try {
            $this->users->create($_POST);
            Session::flash('success', 'User ditambahkan');
            Response::redirect('/settings/users');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Response::redirect('/user/add');
        }
    }

    public function editForm(string $id): void
    {
        try {
            $formUser = $this->users->getById($id);
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Response::redirect('/settings/users');
        }

        View::render('settings/user-form', [
            'title' => 'Edit User',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'formUser' => $formUser,
            'formTitle' => 'Edit User',
            'formAction' => '/user/edit/' . $id,
            'successMessage' => Session::pullFlash('success'),
        ]);
    }

    public function editSubmit(string $id): void
    {
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Session::flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            Response::redirect('/user/edit/' . $id);
        }

        try {
            $this->users->update($id, $_POST);
            Session::flash('success', 'User diperbarui');
            Response::redirect('/settings/users');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Response::redirect('/user/edit/' . $id);
        }
    }

    public function listApi(): void
    {
        try {
            $users = $this->users->getAll();
            Response::json(array_map(static function (array $user): array {
                return [
                    'id' => (string) $user['id'],
                    'username' => (string) $user['username'],
                    'email' => (string) $user['email'],
                    'role' => (string) $user['role'],
                    'active' => (bool) $user['active'],
                ];
            }, $users));
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
            $this->users->deactivate($id);
            Response::noContent();
        } catch (RuntimeException $exception) {
            Response::json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
