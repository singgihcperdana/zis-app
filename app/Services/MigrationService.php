<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

final class MigrationService
{
    public function migrate(): array
    {
        $connection = Database::connection();

        $this->ensureMigrationsTable($connection);

        $executed = [];
        $skipped = [];
        $files = glob(base_path('database/migrations/*.sql')) ?: [];
        sort($files);

        $applied = $this->appliedMigrations($connection);

        foreach ($files as $filePath) {
            $filename = basename($filePath);

            if (in_array($filename, $applied, true)) {
                $skipped[] = $filename;
                continue;
            }

            $sql = file_get_contents($filePath);

            if (!is_string($sql) || trim($sql) === '') {
                throw new RuntimeException('File migration kosong atau tidak bisa dibaca: ' . $filename);
            }

            $connection->beginTransaction();

            try {
                $connection->exec($sql);

                $statement = $connection->prepare(
                    'INSERT INTO schema_migrations (filename) VALUES (:filename)'
                );
                $statement->execute(['filename' => $filename]);

                $connection->commit();
                $executed[] = $filename;
            } catch (\Throwable $exception) {
                $connection->rollBack();

                throw new RuntimeException(
                    'Migration gagal pada file ' . $filename . ': ' . $exception->getMessage(),
                    (int) $exception->getCode(),
                    $exception
                );
            }
        }

        return [
            'executed' => $executed,
            'skipped' => $skipped,
        ];
    }

    private function ensureMigrationsTable(PDO $connection): void
    {
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );
    }

    private function appliedMigrations(PDO $connection): array
    {
        $statement = $connection->query('SELECT filename FROM schema_migrations ORDER BY id ASC');

        if ($statement === false) {
            return [];
        }

        $rows = $statement->fetchAll(PDO::FETCH_COLUMN);

        return is_array($rows) ? array_map('strval', $rows) : [];
    }
}
