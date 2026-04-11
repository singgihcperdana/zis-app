<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute(['email' => $email]);
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
}
