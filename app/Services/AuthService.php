<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Password;
use App\Core\Session;
use App\Repositories\UserRepository;
use RuntimeException;

final class AuthService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    public function attempt(string $identity, string $password): bool
    {
        try {
            $user = $this->users->findForLogin($identity);
        } catch (RuntimeException $exception) {
            throw $exception;
        }

        if (!is_array($user) || !Password::verify($password, (string) $user['password'])) {
            return false;
        }

        $this->login([
            'id' => (string) $user['id'],
            'name' => (string) $user['username'],
            'username' => (string) $user['username'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'source' => 'database',
        ]);

        return true;
    }

    public function check(): bool
    {
        return is_array(Session::get('auth_user'));
    }

    public function user(): ?array
    {
        $user = Session::get('auth_user');

        return is_array($user) ? $user : null;
    }

    public function logout(): void
    {
        Session::forget('auth_user');
        Session::invalidate();
    }

    public function changePassword(string $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->users->findById($userId);

        if (!is_array($user)) {
            throw new RuntimeException('User tidak ditemukan.');
        }

        if (!Password::verify($currentPassword, (string) $user['password'])) {
            throw new RuntimeException('Password lama tidak sesuai.');
        }

        $this->users->updatePassword($userId, Password::hash($newPassword));
    }

    private function login(array $user): void
    {
        Session::regenerate();
        Session::put('auth_user', $user);
    }
}
