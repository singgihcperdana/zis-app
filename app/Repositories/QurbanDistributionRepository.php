<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class QurbanDistributionRepository
{
    private const SORT_COLUMNS = [
        'distributionDate' => 'd.distribution_date',
        'recipientType' => 'd.recipient_type',
        'recipientName' => 'd.recipient_name',
        'packageCount' => 'd.package_count',
        'distributedBy' => 'd.distributed_by',
    ];

    public function create(array $distribution): array
    {
        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO qurban_distribution (
                    id,
                    distribution_date,
                    distribution_time,
                    recipient_type,
                    recipient_name,
                    pic_name,
                    recipient_phone,
                    recipient_area,
                    package_count,
                    notes,
                    distributed_by,
                    created_by,
                    updated_by
                 ) VALUES (
                    :id,
                    :distribution_date,
                    :distribution_time,
                    :recipient_type,
                    :recipient_name,
                    :pic_name,
                    :recipient_phone,
                    :recipient_area,
                    :package_count,
                    :notes,
                    :distributed_by,
                    :created_by,
                    :updated_by
                 )'
            );

            $statement->execute([
                'id' => $distribution['id'],
                'distribution_date' => $distribution['distribution_date'],
                'distribution_time' => $distribution['distribution_time'],
                'recipient_type' => $distribution['recipient_type'],
                'recipient_name' => $distribution['recipient_name'],
                'pic_name' => $distribution['pic_name'],
                'recipient_phone' => $distribution['recipient_phone'],
                'recipient_area' => $distribution['recipient_area'],
                'package_count' => $distribution['package_count'],
                'notes' => $distribution['notes'],
                'distributed_by' => $distribution['distributed_by'],
                'created_by' => $distribution['created_by'],
                'updated_by' => $distribution['updated_by'],
            ]);

            return [
                'id' => $distribution['id'],
                'distributionDate' => $distribution['distribution_date'],
                'recipientType' => $distribution['recipient_type'],
                'recipientName' => $distribution['recipient_name'],
            ];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Simpan penyaluran qurban gagal. Periksa data dan struktur tabel penyaluran.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function findById(string $id): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT
                    d.id,
                    d.distribution_date,
                    d.distribution_time,
                    d.recipient_type,
                    d.recipient_name,
                    d.pic_name,
                    d.recipient_phone,
                    d.recipient_area,
                    d.package_count,
                    d.notes,
                    d.distributed_by,
                    d.created_by,
                    d.updated_by
                 FROM qurban_distribution d
                 WHERE d.id = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();

            return is_array($row) ? $row : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil data penyaluran qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function search(array $filters): array
    {
        $connection = Database::connection();
        $page = max(0, (int) ($filters['page'] ?? 0));
        $size = max(1, min(100, (int) ($filters['size'] ?? 20)));
        $offset = $page * $size;

        $sortKey = (string) ($filters['sortKey'] ?? 'distributionDate');
        $sortDir = strtolower((string) ($filters['sortDir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $sortColumn = self::SORT_COLUMNS[$sortKey] ?? self::SORT_COLUMNS['distributionDate'];

        $bindings = [];
        $whereSql = $this->buildSearchWhereClause($filters, $bindings);

        try {
            $countStatement = $connection->prepare(
                "SELECT COUNT(*) AS total
                 FROM qurban_distribution d
                 {$whereSql}"
            );
            $countStatement->execute($bindings);
            $total = (int) $countStatement->fetchColumn();

            $statement = $connection->prepare(
                "SELECT
                    d.id,
                    d.distribution_date,
                    d.distribution_time,
                    d.recipient_type,
                    d.recipient_name,
                    d.pic_name,
                    d.recipient_phone,
                    d.recipient_area,
                    d.package_count,
                    d.notes,
                    d.distributed_by,
                    d.created_at
                 FROM qurban_distribution d
                 {$whereSql}
                 ORDER BY
                    CASE WHEN {$sortColumn} IS NULL THEN 1 ELSE 0 END ASC,
                    {$sortColumn} {$sortDir},
                    d.distribution_date DESC,
                    d.id DESC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($bindings as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }
            $statement->bindValue(':limit', $size, \PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $statement->execute();

            $rows = $statement->fetchAll() ?: [];
            $content = array_map(static function (array $row): array {
                return [
                    'id' => (string) $row['id'],
                    'distributionDate' => $row['distribution_date'] !== null ? (string) $row['distribution_date'] : null,
                    'distributionTime' => $row['distribution_time'] !== null ? substr((string) $row['distribution_time'], 0, 5) : null,
                    'recipientType' => $row['recipient_type'] !== null ? (string) $row['recipient_type'] : null,
                    'recipientName' => $row['recipient_name'] !== null ? (string) $row['recipient_name'] : null,
                    'picName' => $row['pic_name'] !== null ? (string) $row['pic_name'] : null,
                    'recipientPhone' => $row['recipient_phone'] !== null ? (string) $row['recipient_phone'] : null,
                    'recipientArea' => $row['recipient_area'] !== null ? (string) $row['recipient_area'] : null,
                    'packageCount' => $row['package_count'] !== null ? (int) $row['package_count'] : null,
                    'notes' => $row['notes'] !== null ? (string) $row['notes'] : null,
                    'distributedBy' => $row['distributed_by'] !== null ? (string) $row['distributed_by'] : null,
                    'createdAt' => $row['created_at'] !== null ? str_replace(' ', 'T', (string) $row['created_at']) : null,
                ];
            }, $rows);

            $totalPages = $total === 0 ? 0 : (int) ceil($total / $size);

            return [
                'content' => $content,
                'totalElements' => $total,
                'totalPages' => $totalPages,
                'number' => $page,
                'size' => $size,
            ];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal memuat riwayat penyaluran qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function exportRows(array $filters): array
    {
        $connection = Database::connection();
        $bindings = [];
        $whereSql = $this->buildSearchWhereClause($filters, $bindings);

        try {
            $statement = $connection->prepare(
                "SELECT
                    d.distribution_date,
                    d.distribution_time,
                    d.recipient_type,
                    d.recipient_name,
                    d.pic_name,
                    d.recipient_phone,
                    d.recipient_area,
                    d.package_count,
                    d.notes,
                    d.distributed_by,
                    d.created_at
                 FROM qurban_distribution d
                 {$whereSql}
                 ORDER BY d.distribution_date DESC, d.created_at DESC, d.id DESC"
            );

            $statement->execute($bindings);

            return $statement->fetchAll() ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menyiapkan data export penyaluran qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(array $distribution): array
    {
        try {
            $statement = Database::connection()->prepare(
                'UPDATE qurban_distribution
                 SET distribution_date = :distribution_date,
                     distribution_time = :distribution_time,
                     recipient_type = :recipient_type,
                     recipient_name = :recipient_name,
                     pic_name = :pic_name,
                     recipient_phone = :recipient_phone,
                     recipient_area = :recipient_area,
                     package_count = :package_count,
                     notes = :notes,
                     distributed_by = :distributed_by,
                     updated_by = :updated_by
                 WHERE id = :id'
            );

            $statement->execute([
                'id' => $distribution['id'],
                'distribution_date' => $distribution['distribution_date'],
                'distribution_time' => $distribution['distribution_time'],
                'recipient_type' => $distribution['recipient_type'],
                'recipient_name' => $distribution['recipient_name'],
                'pic_name' => $distribution['pic_name'],
                'recipient_phone' => $distribution['recipient_phone'],
                'recipient_area' => $distribution['recipient_area'],
                'package_count' => $distribution['package_count'],
                'notes' => $distribution['notes'],
                'distributed_by' => $distribution['distributed_by'],
                'updated_by' => $distribution['updated_by'],
            ]);

            return $this->findById((string) $distribution['id']) ?? [
                'id' => $distribution['id'],
            ];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Update penyaluran qurban gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function delete(string $id): void
    {
        try {
            $statement = Database::connection()->prepare('DELETE FROM qurban_distribution WHERE id = :id');
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Hapus penyaluran qurban gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function buildSearchWhereClause(array $filters, array &$bindings): string
    {
        $clauses = [];

        if (!empty($filters['from'])) {
            $clauses[] = 'd.distribution_date >= :from_date';
            $bindings['from_date'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $clauses[] = 'd.distribution_date <= :to_date';
            $bindings['to_date'] = $filters['to'];
        }

        if (!empty($filters['recipientType'])) {
            $clauses[] = 'd.recipient_type = :recipient_type';
            $bindings['recipient_type'] = $filters['recipientType'];
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(d.recipient_name LIKE :q OR d.pic_name LIKE :q OR d.recipient_area LIKE :q OR d.distributed_by LIKE :q)';
            $bindings['q'] = '%' . $filters['q'] . '%';
        }

        return $clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses);
    }
}
