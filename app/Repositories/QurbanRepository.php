<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class QurbanRepository
{
    private const SORT_COLUMNS = [
        'submissionDate' => 'q.submission_date',
        'qurbanNumber' => 'q.qurban_number',
        'payerName' => 'q.payer_name',
        'animalType' => 'q.animal_type',
    ];

    public function existsByQurbanNumber(string $qurbanNumber, string $animalNumberGroup, ?string $excludeId = null): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM qurban_submission WHERE qurban_number = :qurban_number AND animal_number_group = :animal_number_group';
            $params = [
                'qurban_number' => $qurbanNumber,
                'animal_number_group' => $animalNumberGroup,
            ];

            if (is_string($excludeId) && trim($excludeId) !== '') {
                $sql .= ' AND id <> :exclude_id';
                $params['exclude_id'] = $excludeId;
            }

            $statement = Database::connection()->prepare($sql);
            $statement->execute($params);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $exception) {
            throw new RuntimeException('Validasi nomor qurban gagal.', (int) $exception->getCode(), $exception);
        }
    }

    public function findById(string $id): ?array
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT
                    q.id,
                    q.qurban_number,
                    q.submission_date,
                    q.payer_name,
                    q.payer_phone,
                    q.alamat,
                    q.animal_type,
                    q.animal_number_group,
                    q.biaya_pemeliharaan,
                    q.shodaqoh_infak,
                    q.biaya_supplier,
                    q.slaughter_mode,
                    q.pickup_time_notes,
                    q.committee_phone
                 FROM qurban_submission q
                 WHERE q.id = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();

            if (!is_array($row)) {
                return null;
            }

            $participants = $this->findParticipantsBySubmissionIds([$id]);
            $row['participants'] = $participants[$id] ?? [];

            return $row;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil data qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function create(array $submission, array $participants): array
    {
        $connection = Database::connection();

        try {
            $connection->beginTransaction();

            $statement = $connection->prepare(
                'INSERT INTO qurban_submission (
                    id,
                    qurban_number,
                    submission_date,
                    payer_name,
                    payer_phone,
                    alamat,
                    animal_type,
                    animal_number_group,
                    biaya_pemeliharaan,
                    shodaqoh_infak,
                    biaya_supplier,
                    slaughter_mode,
                    pickup_time_notes,
                    committee_phone,
                    created_by,
                    updated_by
                 ) VALUES (
                    :id,
                    :qurban_number,
                    :submission_date,
                    :payer_name,
                    :payer_phone,
                    :alamat,
                    :animal_type,
                    :animal_number_group,
                    :biaya_pemeliharaan,
                    :shodaqoh_infak,
                    :biaya_supplier,
                    :slaughter_mode,
                    :pickup_time_notes,
                    :committee_phone,
                    :created_by,
                    :updated_by
                 )'
            );

            $statement->execute([
                'id' => $submission['id'],
                'qurban_number' => $submission['qurban_number'],
                'submission_date' => $submission['submission_date'],
                'payer_name' => $submission['payer_name'],
                'payer_phone' => $submission['payer_phone'],
                'alamat' => $submission['alamat'],
                'animal_type' => $submission['animal_type'],
                'animal_number_group' => $submission['animal_number_group'],
                'biaya_pemeliharaan' => $submission['biaya_pemeliharaan'],
                'shodaqoh_infak' => $submission['shodaqoh_infak'],
                'biaya_supplier' => $submission['biaya_supplier'],
                'slaughter_mode' => $submission['slaughter_mode'],
                'pickup_time_notes' => $submission['pickup_time_notes'],
                'committee_phone' => $submission['committee_phone'],
                'created_by' => $submission['created_by'],
                'updated_by' => $submission['updated_by'],
            ]);

            if ($participants !== []) {
                $participantStatement = $connection->prepare(
                    'INSERT INTO qurban_participant (id, submission_id, participant_name, sequence_no)
                     VALUES (:id, :submission_id, :participant_name, :sequence_no)'
                );

                foreach (array_values($participants) as $index => $name) {
                    $participantStatement->execute([
                        'id' => $this->uuid(),
                        'submission_id' => $submission['id'],
                        'participant_name' => $name,
                        'sequence_no' => $index + 1,
                    ]);
                }
            }

            $connection->commit();

            return [
                'id' => $submission['id'],
                'qurbanNumber' => $submission['qurban_number'],
            ];
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw new RuntimeException(
                'Simpan qurban gagal. Periksa data dan struktur tabel qurban.',
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

        $sortKey = (string) ($filters['sortKey'] ?? 'submissionDate');
        $sortDir = strtolower((string) ($filters['sortDir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $sortColumn = self::SORT_COLUMNS[$sortKey] ?? self::SORT_COLUMNS['submissionDate'];

        $bindings = [];
        $whereSql = $this->buildSearchWhereClause($filters, $bindings);

        try {
            $countStatement = $connection->prepare(
                "SELECT COUNT(*) AS total
                 FROM qurban_submission q
                 {$whereSql}"
            );
            $countStatement->execute($bindings);
            $total = (int) $countStatement->fetchColumn();

            $statement = $connection->prepare(
                "SELECT
                    q.id,
                    q.qurban_number,
                    q.submission_date,
                    q.payer_name,
                    q.payer_phone,
                    q.alamat,
                    q.animal_type,
                    q.animal_number_group,
                    q.biaya_pemeliharaan,
                    q.shodaqoh_infak,
                    q.biaya_supplier,
                    q.slaughter_mode,
                    q.pickup_time_notes,
                    q.committee_phone
                 FROM qurban_submission q
                 {$whereSql}
                 ORDER BY
                    CASE WHEN {$sortColumn} IS NULL THEN 1 ELSE 0 END ASC,
                    {$sortColumn} {$sortDir},
                    q.submission_date DESC,
                    q.id DESC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($bindings as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }
            $statement->bindValue(':limit', $size, \PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $statement->execute();

            $rows = $statement->fetchAll() ?: [];
            $ids = array_values(array_map(static function (array $row): string {
                return (string) $row['id'];
            }, $rows));

            $participantsBySubmission = $this->findParticipantsBySubmissionIds($ids);

            $content = array_map(static function (array $row) use ($participantsBySubmission): array {
                $participants = $participantsBySubmission[(string) $row['id']] ?? [];
                return [
                    'id' => (string) $row['id'],
                    'qurbanNumber' => (string) $row['qurban_number'],
                    'submissionDate' => $row['submission_date'] !== null ? str_replace(' ', 'T', (string) $row['submission_date']) : null,
                    'payerName' => $row['payer_name'] !== null ? (string) $row['payer_name'] : null,
                    'payerPhone' => $row['payer_phone'] !== null ? (string) $row['payer_phone'] : null,
                    'alamat' => $row['alamat'] !== null ? (string) $row['alamat'] : null,
                    'animalType' => $row['animal_type'] !== null ? (string) $row['animal_type'] : null,
                    'animalNumberGroup' => $row['animal_number_group'] !== null ? (string) $row['animal_number_group'] : null,
                    'biayaPemeliharaan' => $row['biaya_pemeliharaan'] !== null ? (float) $row['biaya_pemeliharaan'] : null,
                    'shodaqohInfak' => $row['shodaqoh_infak'] !== null ? (float) $row['shodaqoh_infak'] : null,
                    'biayaSupplier' => $row['biaya_supplier'] !== null ? (float) $row['biaya_supplier'] : null,
                    'slaughterMode' => $row['slaughter_mode'] !== null ? (string) $row['slaughter_mode'] : null,
                    'pickupTimeNotes' => $row['pickup_time_notes'] !== null ? (string) $row['pickup_time_notes'] : null,
                    'committeePhone' => $row['committee_phone'] !== null ? (string) $row['committee_phone'] : null,
                    'participantCount' => count($participants),
                    'participantPreview' => implode(', ', array_slice($participants, 0, 3)),
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
                'Gagal memuat riwayat qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(array $submission, array $participants): array
    {
        $connection = Database::connection();

        try {
            $connection->beginTransaction();

            $statement = $connection->prepare(
                'UPDATE qurban_submission
                 SET qurban_number = :qurban_number,
                     submission_date = :submission_date,
                     payer_name = :payer_name,
                    payer_phone = :payer_phone,
                    alamat = :alamat,
                    animal_type = :animal_type,
                    animal_number_group = :animal_number_group,
                    biaya_pemeliharaan = :biaya_pemeliharaan,
                     shodaqoh_infak = :shodaqoh_infak,
                     biaya_supplier = :biaya_supplier,
                     slaughter_mode = :slaughter_mode,
                     pickup_time_notes = :pickup_time_notes,
                     committee_phone = :committee_phone,
                     updated_by = :updated_by
                 WHERE id = :id'
            );

            $statement->execute([
                'id' => $submission['id'],
                'qurban_number' => $submission['qurban_number'],
                'submission_date' => $submission['submission_date'],
                'payer_name' => $submission['payer_name'],
                'payer_phone' => $submission['payer_phone'],
                'alamat' => $submission['alamat'],
                'animal_type' => $submission['animal_type'],
                'animal_number_group' => $submission['animal_number_group'],
                'biaya_pemeliharaan' => $submission['biaya_pemeliharaan'],
                'shodaqoh_infak' => $submission['shodaqoh_infak'],
                'biaya_supplier' => $submission['biaya_supplier'],
                'slaughter_mode' => $submission['slaughter_mode'],
                'pickup_time_notes' => $submission['pickup_time_notes'],
                'committee_phone' => $submission['committee_phone'],
                'updated_by' => $submission['updated_by'],
            ]);

            $deleteStatement = $connection->prepare('DELETE FROM qurban_participant WHERE submission_id = :submission_id');
            $deleteStatement->execute(['submission_id' => $submission['id']]);

            if ($participants !== []) {
                $participantStatement = $connection->prepare(
                    'INSERT INTO qurban_participant (id, submission_id, participant_name, sequence_no)
                     VALUES (:id, :submission_id, :participant_name, :sequence_no)'
                );

                foreach (array_values($participants) as $index => $name) {
                    $participantStatement->execute([
                        'id' => $this->uuid(),
                        'submission_id' => $submission['id'],
                        'participant_name' => $name,
                        'sequence_no' => $index + 1,
                    ]);
                }
            }

            $connection->commit();

            return $this->findById((string) $submission['id']) ?? [
                'id' => $submission['id'],
                'qurban_number' => $submission['qurban_number'],
            ];
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw new RuntimeException(
                'Update qurban gagal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function buildSearchWhereClause(array $filters, array &$bindings): string
    {
        $clauses = [];

        if (!empty($filters['from'])) {
            $clauses[] = 'q.submission_date >= :from_inclusive';
            $bindings['from_inclusive'] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to'])) {
            $clauses[] = 'q.submission_date < :to_exclusive';
            $bindings['to_exclusive'] = $filters['to'] . ' 23:59:59';
        }

        if (!empty($filters['q'])) {
            $clauses[] = '(q.payer_name LIKE :q OR q.alamat LIKE :q OR q.qurban_number LIKE :q)';
            $bindings['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['animalType'])) {
            $clauses[] = 'q.animal_type = :animal_type';
            $bindings['animal_type'] = $filters['animalType'];
        }

        return $clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses);
    }

    private function findParticipantsBySubmissionIds(array $submissionIds): array
    {
        if ($submissionIds === []) {
            return [];
        }

        try {
            $placeholders = implode(', ', array_fill(0, count($submissionIds), '?'));
            $statement = Database::connection()->prepare(
                "SELECT submission_id, participant_name
                 FROM qurban_participant
                 WHERE submission_id IN ({$placeholders})
                 ORDER BY sequence_no ASC"
            );
            $statement->execute($submissionIds);
            $rows = $statement->fetchAll() ?: [];

            $grouped = [];
            foreach ($rows as $row) {
                $submissionId = (string) ($row['submission_id'] ?? '');
                if ($submissionId === '') {
                    continue;
                }

                $grouped[$submissionId][] = (string) ($row['participant_name'] ?? '');
            }

            return $grouped;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil peserta qurban.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
