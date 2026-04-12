<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

final class ZakatQualityRepository
{
    public function findActiveByType(string $zakatType): array
    {
        try {
            $orderBy = $zakatType === 'ZAKAT_FITRAH_BERAS'
                ? 'berat_per_jiwa_kg ASC'
                : 'nominal_per_jiwa ASC';

            $statement = Database::connection()->prepare(
                "SELECT id, name, zakat_type, active, berat_per_jiwa_kg, nominal_per_jiwa
                 FROM zakat_quality
                 WHERE zakat_type = :zakat_type AND active = 1
                 ORDER BY {$orderBy}"
            );
            $statement->execute(['zakat_type' => $zakatType]);

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query zakat quality gagal. Pastikan migrasi tabel inti sudah dijalankan.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function findById(string $id): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT id, name, zakat_type, active, berat_per_jiwa_kg, nominal_per_jiwa
                 FROM zakat_quality
                 WHERE id = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            return is_array($result) ? $result : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query zakat quality gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function create(array $data): array
    {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO zakat_quality (id, name, zakat_type, active, berat_per_jiwa_kg, nominal_per_jiwa)
                 VALUES (:id, :name, :zakat_type, :active, :berat_per_jiwa_kg, :nominal_per_jiwa)'
            );
            $statement->execute([
                'id' => $data['id'],
                'name' => $data['name'],
                'zakat_type' => $data['zakat_type'],
                'active' => $data['active'],
                'berat_per_jiwa_kg' => $data['berat_per_jiwa_kg'],
                'nominal_per_jiwa' => $data['nominal_per_jiwa'],
            ]);

            return $this->findById((string) $data['id']) ?? $data;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Simpan zakat quality gagal. Periksa data dan struktur tabel.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function deactivate(string $id): void
    {
        try {
            $statement = Database::connection()->prepare(
                'UPDATE zakat_quality SET active = 0 WHERE id = :id'
            );
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Nonaktifkan zakat quality gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
