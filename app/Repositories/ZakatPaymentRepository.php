<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

final class ZakatPaymentRepository
{
    private const SORT_COLUMNS = [
        'receiptNumber' => 'p.receipt_number',
        'paymentAt' => 'p.payment_at',
        'jumlahUang' => 'p.jumlah_uang',
        'beratBerasKg' => 'p.berat_beras_kg',
        'jumlahUangZakatMal' => 'p.jumlah_uang_zakat_mal',
        'jumlahUangInfaqSedekah' => 'p.jumlah_uang_infaq_sedekah',
        'jumlahUangFidiah' => 'p.jumlah_uang_fidiah',
    ];

    public function findDistinctReceivedByNames(): array
    {
        try {
            $statement = Database::connection()->query(
                "SELECT DISTINCT received_by_name
                 FROM zakat_payment
                 WHERE received_by_name IS NOT NULL
                   AND TRIM(received_by_name) <> ''
                 ORDER BY received_by_name ASC"
            );

            $rows = $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];

            return array_values(array_filter(array_map('strval', $rows), static function (string $value): bool {
                return trim($value) !== '';
            }));
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil daftar petugas penerima.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function create(array $payment, array $muzakkiNames): array
    {
        $connection = Database::connection();

        try {
            $connection->beginTransaction();

            $year = (int) $payment['receipt_year'];
            $currentIssued = $this->lockReceiptSequence($connection, $year);
            $maxExisting = $this->maxReceiptSequenceForYear($connection, $year);
            $base = max($currentIssued, $maxExisting);
            $next = $base + 1;

            $this->persistReceiptSequence($connection, $year, $next);

            $payment['receipt_sequence'] = $next;
            $payment['receipt_number'] = sprintf('MA/%d/%06d', $year, $next);

            $statement = $connection->prepare(
                'INSERT INTO zakat_payment (
                    id,
                    jumlah_jiwa,
                    alamat,
                    payer_name,
                    payer_phone,
                    received_by_name,
                    payment_method,
                    berat_beras_kg,
                    jumlah_uang,
                    jumlah_uang_zakat_mal,
                    jumlah_uang_infaq_sedekah,
                    jumlah_uang_fidiah,
                    payment_at,
                    canceled,
                    receipt_number,
                    receipt_year,
                    receipt_sequence,
                    zakat_quality_id,
                    created_by,
                    updated_by
                 ) VALUES (
                    :id,
                    :jumlah_jiwa,
                    :alamat,
                    :payer_name,
                    :payer_phone,
                    :received_by_name,
                    :payment_method,
                    :berat_beras_kg,
                    :jumlah_uang,
                    :jumlah_uang_zakat_mal,
                    :jumlah_uang_infaq_sedekah,
                    :jumlah_uang_fidiah,
                    :payment_at,
                    :canceled,
                    :receipt_number,
                    :receipt_year,
                    :receipt_sequence,
                    :zakat_quality_id,
                    :created_by,
                    :updated_by
                 )'
            );

            $statement->execute([
                'id' => $payment['id'],
                'jumlah_jiwa' => $payment['jumlah_jiwa'],
                'alamat' => $payment['alamat'],
                'payer_name' => $payment['payer_name'],
                'payer_phone' => $payment['payer_phone'],
                'received_by_name' => $payment['received_by_name'],
                'payment_method' => $payment['payment_method'],
                'berat_beras_kg' => $payment['berat_beras_kg'],
                'jumlah_uang' => $payment['jumlah_uang'],
                'jumlah_uang_zakat_mal' => $payment['jumlah_uang_zakat_mal'],
                'jumlah_uang_infaq_sedekah' => $payment['jumlah_uang_infaq_sedekah'],
                'jumlah_uang_fidiah' => $payment['jumlah_uang_fidiah'],
                'payment_at' => $payment['payment_at'],
                'canceled' => 0,
                'receipt_number' => $payment['receipt_number'],
                'receipt_year' => $payment['receipt_year'],
                'receipt_sequence' => $payment['receipt_sequence'],
                'zakat_quality_id' => $payment['zakat_quality_id'],
                'created_by' => $payment['created_by'],
                'updated_by' => $payment['updated_by'],
            ]);

            if ($muzakkiNames !== []) {
                $muzakkiStatement = $connection->prepare(
                    'INSERT INTO muzakki_person (id, nama, payment_id, sequence_no)
                     VALUES (:id, :nama, :payment_id, :sequence_no)'
                );

                foreach (array_values($muzakkiNames) as $index => $name) {
                    $muzakkiStatement->execute([
                        'id' => $this->uuid(),
                        'nama' => $name,
                        'payment_id' => $payment['id'],
                        'sequence_no' => $index + 1,
                    ]);
                }
            }

            $connection->commit();

            return [
                'id' => $payment['id'],
                'receiptNumber' => $payment['receipt_number'],
            ];
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw new RuntimeException(
                'Simpan pembayaran gagal. Periksa data dan struktur tabel.',
                (int) $exception->getCode(),
                $exception
            );
        } catch (RuntimeException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function search(array $filters): array
    {
        $connection = Database::connection();
        $page = max(0, (int) ($filters['page'] ?? 0));
        $size = max(1, min(100, (int) ($filters['size'] ?? 20)));
        $offset = $page * $size;

        $sortKey = (string) ($filters['sortKey'] ?? 'paymentAt');
        $sortDir = strtolower((string) ($filters['sortDir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $sortColumn = self::SORT_COLUMNS[$sortKey] ?? self::SORT_COLUMNS['paymentAt'];

        $bindings = [];
        $whereSql = $this->buildSearchWhereClause($filters, $bindings);

        try {
            $countStatement = $connection->prepare(
                "SELECT COUNT(*) AS total
                 FROM zakat_payment p
                 {$whereSql}"
            );
            $countStatement->execute($bindings);
            $total = (int) $countStatement->fetchColumn();

            $statement = $connection->prepare(
                "SELECT
                    p.id,
                    p.receipt_number,
                    p.payment_at,
                    p.berat_beras_kg,
                    p.jumlah_uang,
                    p.jumlah_uang_zakat_mal,
                    p.jumlah_uang_infaq_sedekah,
                    p.jumlah_uang_fidiah,
                    p.alamat,
                    p.payer_name,
                    p.payer_phone,
                    p.received_by_name,
                    p.payment_method,
                    p.canceled
                 FROM zakat_payment p
                 {$whereSql}
                 ORDER BY
                    CASE WHEN {$sortColumn} IS NULL THEN 1 ELSE 0 END ASC,
                    {$sortColumn} {$sortDir},
                    p.payment_at DESC,
                    p.receipt_year DESC,
                    p.receipt_sequence DESC,
                    p.id DESC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($bindings as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }
            $statement->bindValue(':limit', $size, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
            $statement->execute();

            $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $paymentIds = array_values(array_map(static function (array $row): string {
                return (string) $row['id'];
            }, $rows));

            $muzakkiByPayment = $this->findMuzakkiNamesByPaymentIds($paymentIds);
            $content = array_map(static function (array $row) use ($muzakkiByPayment): array {
                $names = $muzakkiByPayment[(string) $row['id']] ?? [];
                $previewNames = array_slice($names, 0, 3);
                $preview = implode(', ', $previewNames);
                if (count($names) > 3) {
                    $preview .= ', +' . (count($names) - 3);
                }

                return [
                    'id' => (string) $row['id'],
                    'receiptNumber' => $row['receipt_number'] !== null ? (string) $row['receipt_number'] : null,
                    'paymentAt' => $row['payment_at'] !== null ? str_replace(' ', 'T', (string) $row['payment_at']) : null,
                    'beratBerasKg' => $row['berat_beras_kg'] !== null ? (float) $row['berat_beras_kg'] : null,
                    'jumlahUang' => $row['jumlah_uang'] !== null ? (float) $row['jumlah_uang'] : null,
                    'jumlahUangZakatMal' => $row['jumlah_uang_zakat_mal'] !== null ? (float) $row['jumlah_uang_zakat_mal'] : null,
                    'jumlahUangInfaqSedekah' => $row['jumlah_uang_infaq_sedekah'] !== null ? (float) $row['jumlah_uang_infaq_sedekah'] : null,
                    'jumlahUangFidiah' => $row['jumlah_uang_fidiah'] !== null ? (float) $row['jumlah_uang_fidiah'] : null,
                    'muzakkiCount' => count($names),
                    'muzakkiPreview' => $preview,
                    'alamat' => $row['alamat'] !== null ? (string) $row['alamat'] : null,
                    'payerName' => $row['payer_name'] !== null ? (string) $row['payer_name'] : null,
                    'payerPhone' => $row['payer_phone'] !== null ? (string) $row['payer_phone'] : null,
                    'receivedByName' => $row['received_by_name'] !== null ? (string) $row['received_by_name'] : null,
                    'paymentMethod' => $row['payment_method'] !== null ? (string) $row['payment_method'] : null,
                    'canceled' => (bool) $row['canceled'],
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
                'Gagal memuat riwayat pembayaran.',
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
                    p.*,
                    q.id AS quality_id,
                    q.name AS quality_name,
                    q.zakat_type AS quality_zakat_type,
                    q.berat_per_jiwa_kg AS quality_berat_per_jiwa_kg,
                    q.nominal_per_jiwa AS quality_nominal_per_jiwa
                 FROM zakat_payment p
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.id = :id
                 LIMIT 1'
            );
            $statement->execute(['id' => $id]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return null;
            }

            $row['muzakki_names'] = $this->findMuzakkiNamesByPaymentIds([$id])[$id] ?? [];

            return $row;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil data pembayaran.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function cancel(string $id, string $reason, ?string $canceledBy): void
    {
        try {
            $statement = Database::connection()->prepare(
                'UPDATE zakat_payment
                 SET canceled = 1,
                     canceled_at = NOW(),
                     cancel_reason = :cancel_reason,
                     canceled_by = :canceled_by,
                     updated_by = :updated_by
                 WHERE id = :id'
            );
            $statement->execute([
                'id' => $id,
                'cancel_reason' => $reason,
                'canceled_by' => $canceledBy,
                'updated_by' => $canceledBy,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal membatalkan pembayaran.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(array $payment, array $muzakkiNames): void
    {
        $connection = Database::connection();

        try {
            $connection->beginTransaction();

            $statement = $connection->prepare(
                'UPDATE zakat_payment
                 SET jumlah_jiwa = :jumlah_jiwa,
                     alamat = :alamat,
                     payer_name = :payer_name,
                     payer_phone = :payer_phone,
                     received_by_name = :received_by_name,
                     payment_method = :payment_method,
                     berat_beras_kg = :berat_beras_kg,
                     jumlah_uang = :jumlah_uang,
                     jumlah_uang_zakat_mal = :jumlah_uang_zakat_mal,
                     jumlah_uang_infaq_sedekah = :jumlah_uang_infaq_sedekah,
                     jumlah_uang_fidiah = :jumlah_uang_fidiah,
                     payment_at = :payment_at,
                     zakat_quality_id = :zakat_quality_id,
                     updated_by = :updated_by
                 WHERE id = :id'
            );
            $statement->execute([
                'id' => $payment['id'],
                'jumlah_jiwa' => $payment['jumlah_jiwa'],
                'alamat' => $payment['alamat'],
                'payer_name' => $payment['payer_name'],
                'payer_phone' => $payment['payer_phone'],
                'received_by_name' => $payment['received_by_name'],
                'payment_method' => $payment['payment_method'],
                'berat_beras_kg' => $payment['berat_beras_kg'],
                'jumlah_uang' => $payment['jumlah_uang'],
                'jumlah_uang_zakat_mal' => $payment['jumlah_uang_zakat_mal'],
                'jumlah_uang_infaq_sedekah' => $payment['jumlah_uang_infaq_sedekah'],
                'jumlah_uang_fidiah' => $payment['jumlah_uang_fidiah'],
                'payment_at' => $payment['payment_at'],
                'zakat_quality_id' => $payment['zakat_quality_id'],
                'updated_by' => $payment['updated_by'],
            ]);

            $deleteStatement = $connection->prepare('DELETE FROM muzakki_person WHERE payment_id = :payment_id');
            $deleteStatement->execute(['payment_id' => $payment['id']]);

            if ($muzakkiNames !== []) {
                $insertStatement = $connection->prepare(
                    'INSERT INTO muzakki_person (id, nama, payment_id, sequence_no)
                     VALUES (:id, :nama, :payment_id, :sequence_no)'
                );
                foreach (array_values($muzakkiNames) as $index => $name) {
                    $insertStatement->execute([
                        'id' => $this->uuid(),
                        'nama' => $name,
                        'payment_id' => $payment['id'],
                        'sequence_no' => $index + 1,
                    ]);
                }
            }

            $connection->commit();
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw new RuntimeException(
                'Gagal memperbarui pembayaran.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function rekapSummary(string $fromInclusive, string $toExclusive): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    COALESCE(SUM(CASE
                        WHEN q.zakat_type = 'ZAKAT_FITRAH_UANG' THEN COALESCE(p.jumlah_uang, 0)
                        ELSE 0
                    END), 0) AS fitrah_uang,
                    COALESCE(SUM(CASE
                        WHEN q.zakat_type = 'ZAKAT_FITRAH_BERAS' THEN COALESCE(p.berat_beras_kg, 0)
                        ELSE 0
                    END), 0) AS fitrah_beras,
                    COALESCE(SUM(COALESCE(p.jumlah_uang_fidiah, 0)), 0) AS fidiah,
                    COALESCE(SUM(COALESCE(p.jumlah_uang_zakat_mal, 0)), 0) AS zakat_mal,
                    COALESCE(SUM(COALESCE(p.jumlah_uang_infaq_sedekah, 0)), 0) AS infaq_sedekah
                 FROM zakat_payment p
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menghitung rekap ZIS.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function sumJiwaFitrah(string $fromInclusive, string $toExclusive): int
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT COALESCE(SUM(COALESCE(p.jumlah_jiwa, 0)), 0)
                 FROM zakat_payment p
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0
                   AND q.zakat_type IN ('ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG')"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            return (int) $statement->fetchColumn();
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menghitung total jiwa fitrah.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function dashboardTotals(string $fromInclusive, string $toExclusive): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    COUNT(*) AS total_transaksi,
                    COALESCE(SUM(
                        COALESCE(p.jumlah_uang, 0)
                        + COALESCE(p.jumlah_uang_zakat_mal, 0)
                        + COALESCE(p.jumlah_uang_infaq_sedekah, 0)
                        + COALESCE(p.jumlah_uang_fidiah, 0)
                    ), 0) AS total_uang_masuk,
                    COALESCE(SUM(COALESCE(p.berat_beras_kg, 0)), 0) AS total_beras_kg,
                    COALESCE(SUM(CASE
                        WHEN q.zakat_type IN ('ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG') THEN COALESCE(p.jumlah_jiwa, 0)
                        ELSE 0
                    END), 0) AS total_jiwa_fitrah
                 FROM zakat_payment p
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? $row : [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menghitung total dashboard.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function dashboardPaymentMethodBreakdown(string $fromInclusive, string $toExclusive): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    COALESCE(SUM(CASE
                        WHEN p.payment_method = 'CASH' THEN
                            COALESCE(p.jumlah_uang, 0)
                            + COALESCE(p.jumlah_uang_zakat_mal, 0)
                            + COALESCE(p.jumlah_uang_infaq_sedekah, 0)
                            + COALESCE(p.jumlah_uang_fidiah, 0)
                        ELSE 0
                    END), 0) AS total_uang_cash,
                    COALESCE(SUM(CASE
                        WHEN p.payment_method = 'TRANSFER' THEN
                            COALESCE(p.jumlah_uang, 0)
                            + COALESCE(p.jumlah_uang_zakat_mal, 0)
                            + COALESCE(p.jumlah_uang_infaq_sedekah, 0)
                            + COALESCE(p.jumlah_uang_fidiah, 0)
                        ELSE 0
                    END), 0) AS total_uang_transfer
                 FROM zakat_payment p
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? $row : [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menghitung breakdown metode pembayaran.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function findRecent(string $fromInclusive, string $toExclusive, int $limit = 5): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    p.id,
                    p.receipt_number,
                    p.payment_at,
                    p.alamat,
                    p.jumlah_jiwa,
                    p.jumlah_uang,
                    p.berat_beras_kg,
                    p.jumlah_uang_zakat_mal,
                    p.jumlah_uang_infaq_sedekah,
                    p.jumlah_uang_fidiah,
                    q.zakat_type AS quality_zakat_type
                 FROM zakat_payment p
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0
                 ORDER BY p.payment_at DESC, p.receipt_year DESC, p.receipt_sequence DESC, p.id DESC
                 LIMIT :limit"
            );
            $statement->bindValue(':from_inclusive', $fromInclusive);
            $statement->bindValue(':to_exclusive', $toExclusive);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal memuat pembayaran terbaru.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function maxReceiptSequenceForYearPublic(int $year): int
    {
        return $this->maxReceiptSequenceForYear(Database::connection(), $year);
    }

    public function minPaymentAt(): ?string
    {
        try {
            $statement = Database::connection()->query(
                'SELECT MIN(payment_at) FROM zakat_payment WHERE canceled = 0'
            );
            $value = $statement->fetchColumn();

            return $value !== false && $value !== null ? (string) $value : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal membaca tanggal pembayaran paling awal.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function fitrahJiwaBreakdown(string $fromInclusive, string $toExclusive): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    q.zakat_type,
                    COALESCE(SUM(COALESCE(p.jumlah_jiwa, 0)), 0) AS total_jiwa
                 FROM zakat_payment p
                 INNER JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0
                   AND q.zakat_type IN ('ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG')
                 GROUP BY q.zakat_type"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal menghitung breakdown jiwa fitrah.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function lastIssuedReceiptSequence(int $year): int
    {
        try {
            $statement = Database::connection()->prepare(
                'SELECT last_issued FROM receipt_sequence WHERE receipt_year = :receipt_year LIMIT 1'
            );
            $statement->execute(['receipt_year' => $year]);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? (int) ($row['last_issued'] ?? 0) : 0;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal membaca sequence kwitansi.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function lockReceiptSequence(PDO $connection, int $year): int
    {
        $statement = $connection->prepare(
            'SELECT last_issued
             FROM receipt_sequence
             WHERE receipt_year = :receipt_year
             LIMIT 1
             FOR UPDATE'
        );
        $statement->execute(['receipt_year' => $year]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return 0;
        }

        return (int) ($row['last_issued'] ?? 0);
    }

    private function maxReceiptSequenceForYear(PDO $connection, int $year): int
    {
        $statement = $connection->prepare(
            'SELECT COALESCE(MAX(receipt_sequence), 0)
             FROM zakat_payment
             WHERE receipt_year = :receipt_year'
        );
        $statement->execute(['receipt_year' => $year]);

        return (int) $statement->fetchColumn();
    }

    private function persistReceiptSequence(PDO $connection, int $year, int $lastIssued): void
    {
        $statement = $connection->prepare(
            'INSERT INTO receipt_sequence (receipt_year, version, last_issued)
             VALUES (:receipt_year, :version, :last_issued)
             ON DUPLICATE KEY UPDATE
                 version = VALUES(version),
                 last_issued = VALUES(last_issued)'
        );
        $statement->execute([
            'receipt_year' => $year,
            'version' => $lastIssued,
            'last_issued' => $lastIssued,
        ]);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function buildSearchWhereClause(array $filters, array &$bindings): string
    {
        $clauses = [
            'p.payment_at >= :from_inclusive',
            'p.payment_at < :to_exclusive',
        ];

        $from = (string) ($filters['from'] ?? '');
        $to = (string) ($filters['to'] ?? '');
        $bindings['from_inclusive'] = ($from !== '' ? $from : '1970-01-01') . ' 00:00:00';

        if ($to !== '') {
            $toDate = \DateTimeImmutable::createFromFormat('Y-m-d', $to);
            if ($toDate instanceof \DateTimeImmutable) {
                $bindings['to_exclusive'] = $toDate->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
            } else {
                $bindings['to_exclusive'] = '9999-12-31 23:59:59';
            }
        } else {
            $bindings['to_exclusive'] = '9999-12-31 23:59:59';
        }

        $includeCanceled = !empty($filters['includeCanceled']);
        if (!$includeCanceled) {
            $clauses[] = 'p.canceled = 0';
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $bindings['q_like'] = '%' . mb_strtolower($q) . '%';
            $clauses[] = "(LOWER(COALESCE(p.alamat, '')) LIKE :q_like
                OR LOWER(COALESCE(p.payer_name, '')) LIKE :q_like
                OR LOWER(COALESCE(p.payer_phone, '')) LIKE :q_like
                OR EXISTS (
                    SELECT 1
                    FROM muzakki_person m
                    WHERE m.payment_id = p.id
                      AND LOWER(COALESCE(m.nama, '')) LIKE :q_like
                ))";
        }

        $payerName = trim((string) ($filters['payerName'] ?? ''));
        if ($payerName !== '') {
            $bindings['payer_like'] = '%' . mb_strtolower($payerName) . '%';
            $clauses[] = "LOWER(COALESCE(p.payer_name, '')) LIKE :payer_like";
        }

        $payerPhone = trim((string) ($filters['payerPhone'] ?? ''));
        if ($payerPhone !== '') {
            $bindings['phone_like'] = '%' . mb_strtolower($payerPhone) . '%';
            $clauses[] = "LOWER(COALESCE(p.payer_phone, '')) LIKE :phone_like";
        }

        return 'WHERE ' . implode(' AND ', $clauses);
    }

    private function findMuzakkiNamesByPaymentIds(array $paymentIds): array
    {
        if ($paymentIds === []) {
            return [];
        }

        try {
            $connection = Database::connection();
            $placeholders = implode(', ', array_fill(0, count($paymentIds), '?'));
            $statement = $connection->prepare(
                "SELECT payment_id, nama
                 FROM muzakki_person
                 WHERE payment_id IN ({$placeholders})
                 ORDER BY payment_id ASC, sequence_no ASC, id ASC"
            );
            foreach (array_values($paymentIds) as $index => $paymentId) {
                $statement->bindValue($index + 1, $paymentId);
            }
            $statement->execute();

            $result = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $paymentId = (string) $row['payment_id'];
                if (!isset($result[$paymentId])) {
                    $result[$paymentId] = [];
                }
                $result[$paymentId][] = (string) ($row['nama'] ?? '');
            }

            return $result;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal mengambil daftar muzakki.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
