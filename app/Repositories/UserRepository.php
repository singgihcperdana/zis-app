<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class UserRepository
{
    public function findAll(): array
    {
        try {
            $statement = Database::connection()->query(
                'SELECT id, username, email, password, role, active
                 FROM users
                 ORDER BY username ASC'
            );

            return $statement->fetchAll() ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query user gagal. Pastikan tabel `users` tersedia dan koneksi MySQL benar.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function findById(string $id): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT id, username, email, password, role, active
                 FROM users
                 WHERE id = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $user = $statement->fetch();

            return is_array($user) ? $user : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query user gagal. Pastikan tabel `users` tersedia dan koneksi MySQL benar.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function existsByUsernameIgnoreCase(string $username, ?string $excludeId = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM users WHERE LOWER(username) = LOWER(:username)';
            $params = ['username' => $username];

            if (is_string($excludeId) && $excludeId !== '') {
                $sql .= ' AND id <> :exclude_id';
                $params['exclude_id'] = $excludeId;
            }

            $statement = Database::connection()->prepare($sql);
            $statement->execute($params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $exception) {
            throw new RuntimeException('Validasi username user gagal.', (int) $exception->getCode(), $exception);
        }
    }

    public function existsByEmailIgnoreCase(string $email, ?string $excludeId = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(:email)';
            $params = ['email' => $email];

            if (is_string($excludeId) && $excludeId !== '') {
                $sql .= ' AND id <> :exclude_id';
                $params['exclude_id'] = $excludeId;
            }

            $statement = Database::connection()->prepare($sql);
            $statement->execute($params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $exception) {
            throw new RuntimeException('Validasi email user gagal.', (int) $exception->getCode(), $exception);
        }
    }

    public function findForLogin(string $identity): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT id, username, email, password, role, active
                 FROM users
                 WHERE active = 1 AND (username = :identity OR email = :identity)
                 LIMIT 1'
            );
            $statement->execute(['identity' => $identity]);
            $user = $statement->fetch();

            return is_array($user) ? $user : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query user gagal. Pastikan tabel `users` tersedia dan koneksi MySQL benar.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function create(array $data): array
    {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO users (id, username, email, password, role, active)
                 VALUES (:id, :username, :email, :password, :role, :active)'
            );
            $statement->execute([
                'id' => $data['id'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'active' => $data['active'],
            ]);

            return $this->findById((string) $data['id']) ?? $data;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Simpan user gagal. Periksa data dan struktur tabel users.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(string $id, array $data): array
    {
        try {
            $fields = [
                'username = :username',
                'email = :email',
                'role = :role',
                'active = :active',
            ];
            $params = [
                'id' => $id,
                'username' => $data['username'],
                'email' => $data['email'],
                'role' => $data['role'],
                'active' => $data['active'],
            ];

            if (isset($data['password'])) {
                $fields[] = 'password = :password';
                $params['password'] = $data['password'];
            }

            $statement = Database::connection()->prepare(
                'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id'
            );
            $statement->execute($params);

            return $this->findById($id) ?? $data;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Update user gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deactivate(string $id): void
    {
        try {
            $statement = Database::connection()->prepare(
                'UPDATE users SET active = 0 WHERE id = :id'
            );
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Nonaktifkan user gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
