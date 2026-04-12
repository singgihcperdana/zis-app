<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

final class MuzakkiPersonRepository
{
    public function findReportRows(string $fromInclusive, string $toExclusive): array
    {
        try {
            $statement = Database::connection()->prepare(
                "SELECT
                    p.id AS payment_id,
                    p.payment_at,
                    m.nama,
                    CASE
                        WHEN q.zakat_type IS NOT NULL THEN q.zakat_type
                        WHEN COALESCE(p.jumlah_uang_zakat_mal, 0) > 0 THEN 'ZAKAT_MAL'
                        WHEN COALESCE(p.jumlah_uang_infaq_sedekah, 0) > 0 THEN 'INFAQ_SEDEKAH'
                        WHEN COALESCE(p.jumlah_uang_fidiah, 0) > 0 THEN 'FIDIAH'
                        ELSE NULL
                    END AS zakat_type,
                    (
                        COALESCE(p.jumlah_uang, 0)
                        + COALESCE(p.jumlah_uang_zakat_mal, 0)
                        + COALESCE(p.jumlah_uang_infaq_sedekah, 0)
                        + COALESCE(p.jumlah_uang_fidiah, 0)
                    ) AS jumlah_uang,
                    p.berat_beras_kg,
                    p.jumlah_jiwa
                 FROM muzakki_person m
                 INNER JOIN zakat_payment p ON p.id = m.payment_id
                 LEFT JOIN zakat_quality q ON q.id = p.zakat_quality_id
                 WHERE p.payment_at >= :from_inclusive
                   AND p.payment_at < :to_exclusive
                   AND p.canceled = 0
                 ORDER BY p.payment_at ASC, COALESCE(m.sequence_no, 2147483647) ASC, m.id ASC"
            );
            $statement->execute([
                'from_inclusive' => $fromInclusive,
                'to_exclusive' => $toExclusive,
            ]);

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Gagal memuat data muzakki.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
