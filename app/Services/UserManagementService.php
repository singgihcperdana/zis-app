<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Password;
use App\Repositories\UserRepository;
use RuntimeException;

final class UserManagementService
{
    private UserRepository $users;

    public function __construct(?UserRepository $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    public function getAll(): array
    {
        return $this->users->findAll();
    }

    public function getById(string $id): array
    {
        $user = $this->users->findById($id);
        if (!is_array($user)) {
            throw new RuntimeException('User tidak ditemukan');
        }

        return $user;
    }

    public function create(array $payload): array
    {
        $username = trim((string) ($payload['username'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $role = trim((string) ($payload['role'] ?? ''));

        if ($username === '' || $email === '' || $password === '') {
            throw new RuntimeException('Username, email, dan password wajib diisi');
        }

        if (!in_array($role, ['OPERATOR', 'VIEWER'], true)) {
            throw new RuntimeException('Tidak boleh membuat user ADMIN melalui UI ini');
        }

        if ($this->users->existsByUsernameIgnoreCase($username)) {
            throw new RuntimeException('Username sudah dipakai');
        }

        if ($this->users->existsByEmailIgnoreCase($email)) {
            throw new RuntimeException('Email sudah dipakai');
        }

        return $this->users->create([
            'id' => $this->uuid(),
            'username' => $username,
            'email' => $email,
            'password' => Password::hash($password),
            'role' => $role,
            'active' => 1,
        ]);
    }

    public function update(string $id, array $payload): array
    {
        $existing = $this->getById($id);
        $username = trim((string) ($payload['username'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $role = trim((string) ($payload['role'] ?? ''));
        $active = filter_var($payload['active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($username === '' || $email === '') {
            throw new RuntimeException('Username dan email wajib diisi');
        }

        if (!in_array($role, ['OPERATOR', 'VIEWER'], true)) {
            throw new RuntimeException('Tidak boleh mengubah role menjadi ADMIN melalui UI ini');
        }

        if ($this->users->existsByUsernameIgnoreCase($username, $id)) {
            throw new RuntimeException('Username sudah dipakai');
        }

        if ($this->users->existsByEmailIgnoreCase($email, $id)) {
            throw new RuntimeException('Email sudah dipakai');
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'active' => $active ?? true ? 1 : 0,
        ];

        if (trim($password) !== '') {
            $data['password'] = Password::hash($password);
        }

        if (($existing['role'] ?? '') === 'ADMIN') {
            throw new RuntimeException('User ADMIN tidak boleh diubah melalui UI ini');
        }

        return $this->users->update($id, $data);
    }

    public function deactivate(string $id): void
    {
        $existing = $this->getById($id);
        if (($existing['role'] ?? '') === 'ADMIN') {
            throw new RuntimeException('User ADMIN tidak boleh dihapus melalui UI ini');
        }

        $this->users->deactivate($id);
    }

    public function setActive(string $id, bool $active): void
    {
        $existing = $this->getById($id);
        if (($existing['role'] ?? '') === 'ADMIN') {
            throw new RuntimeException('User ADMIN tidak boleh diubah statusnya melalui UI ini');
        }

        $this->users->setActive($id, $active);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
