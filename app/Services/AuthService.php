<?php

declare(strict_types=1);

namespace App\Services;

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

    public function attempt(string $email, string $password): bool
    {
        try {
            $user = $this->users->findByEmail($email);
        } catch (RuntimeException $exception) {
            throw $exception;
        }

        if (!is_array($user) || !password_verify($password, (string) $user['password'])) {
            return false;
        }

        $this->login([
            'id' => (string) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
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

    private function login(array $user): void
    {
        Session::regenerate();
        Session::put('auth_user', $user);
    }
}
