<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use RuntimeException;

final class AuthController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function showLogin(): void
    {
        View::render('auth/login', [
            'title' => 'Login',
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
            'oldLogin' => Session::pullFlash('old_login', ''),
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function login(): void
    {
        $identity = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $csrfToken = $_POST['_csrf'] ?? null;

        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Session::flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            Response::redirect('/login');
        }

        if ($identity === '' || $password === '') {
            Session::flash('error', 'Username atau email dan password wajib diisi.');
            Session::flash('old_login', $identity);
            Response::redirect('/login');
        }

        try {
            $authenticated = $this->auth->attempt($identity, $password);
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Session::flash('old_login', $identity);
            Response::redirect('/login');
        }

        if (!$authenticated) {
            Session::flash('error', 'Login gagal. Periksa kredensial Anda.');
            Session::flash('old_login', $identity);
            Response::redirect('/login');
        }

        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        $csrfToken = $_POST['_csrf'] ?? null;

        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Response::redirect('/dashboard');
        }

        $this->auth->logout();
        Session::flash('success', 'Anda berhasil keluar dari aplikasi.');

        Response::redirect('/login');
    }

    public function showUpdatePassword(): void
    {
        View::render('auth/update-password', [
            'title' => 'Update Password',
            'csrfToken' => Session::csrfToken(),
            'user' => $this->auth->user(),
            'successMessage' => Session::pullFlash('success'),
            'errorMessage' => Session::pullFlash('error'),
        ]);
    }

    public function updatePassword(): void
    {
        $csrfToken = $_POST['_csrf'] ?? null;

        if (!Session::verifyCsrf(is_string($csrfToken) ? $csrfToken : null)) {
            Session::flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            Response::redirect('/update-password');
        }

        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $user = $this->auth->user();
        $userId = is_array($user) ? (string) ($user['id'] ?? '') : '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            Session::flash('error', 'Password lama, password baru, dan konfirmasi password wajib diisi.');
            Response::redirect('/update-password');
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'Konfirmasi password baru tidak cocok.');
            Response::redirect('/update-password');
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'Password baru minimal 6 karakter.');
            Response::redirect('/update-password');
        }

        try {
            $this->auth->changePassword($userId, $currentPassword, $newPassword);
            Session::flash('success', 'Password berhasil diperbarui.');
            Response::redirect('/update-password');
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
            Response::redirect('/update-password');
        }
    }
}
