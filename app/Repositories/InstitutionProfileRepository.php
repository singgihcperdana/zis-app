<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDOException;
use RuntimeException;

final class InstitutionProfileRepository
{
    public function first(): ?array
    {
        try {
            $statement = Database::connection()->query(
                'SELECT id, nama_instansi, kota_kabupaten, alamat_lengkap, nomor_telepon, email, nama_ketua, nama_bendahara
                 FROM institution_profile
                 ORDER BY nama_instansi ASC
                 LIMIT 1'
            );

            $profile = $statement->fetch();

            return is_array($profile) ? $profile : null;
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Query institution profile gagal. Pastikan migrasi tabel inti sudah dijalankan.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(string $id, array $data): void
    {
        try {
            $statement = Database::connection()->prepare(
                'UPDATE institution_profile
                 SET nama_instansi = :nama_instansi,
                     kota_kabupaten = :kota_kabupaten,
                     alamat_lengkap = :alamat_lengkap,
                     nomor_telepon = :nomor_telepon,
                     email = :email,
                     nama_ketua = :nama_ketua,
                     nama_bendahara = :nama_bendahara
                 WHERE id = :id'
            );

            $statement->execute([
                'id' => $id,
                'nama_instansi' => $data['nama_instansi'],
                'kota_kabupaten' => $data['kota_kabupaten'],
                'alamat_lengkap' => $data['alamat_lengkap'],
                'nomor_telepon' => $data['nomor_telepon'],
                'email' => $data['email'],
                'nama_ketua' => $data['nama_ketua'],
                'nama_bendahara' => $data['nama_bendahara'],
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Simpan institution profile gagal. Periksa struktur tabel dan koneksi database.',
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
