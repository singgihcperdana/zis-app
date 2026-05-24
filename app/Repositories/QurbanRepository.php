<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class QurbanRepository
{
    public function existsByQurbanNumber(string $qurbanNumber): bool
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT COUNT(*) FROM qurban_submission WHERE qurban_number = :qurban_number'
            );
            $statement->execute(['qurban_number' => $qurbanNumber]);

            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException $exception) {
            throw new RuntimeException('Validasi nomor qurban gagal.', (int) $exception->getCode(), $exception);
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

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
