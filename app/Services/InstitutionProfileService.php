<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\InstitutionProfileRepository;

final class InstitutionProfileService
{
    private InstitutionProfileRepository $profiles;

    public function __construct(?InstitutionProfileRepository $profiles = null)
    {
        $this->profiles = $profiles ?? new InstitutionProfileRepository();
    }

    public function getProfile(): ?array
    {
        return $this->profiles->first();
    }

    public function update(array $input): void
    {
        $profile = $this->profiles->first();

        if (!is_array($profile)) {
            throw new \RuntimeException('Data profile instansi belum tersedia di database.');
        }

        $this->profiles->update((string) $profile['id'], [
            'nama_instansi' => trim((string) ($input['nama_instansi'] ?? '')),
            'kota_kabupaten' => trim((string) ($input['kota_kabupaten'] ?? '')),
            'alamat_lengkap' => trim((string) ($input['alamat_lengkap'] ?? '')),
            'nomor_telepon' => $this->nullable($input['nomor_telepon'] ?? null),
            'email' => $this->nullable($input['email'] ?? null),
            'nama_ketua' => $this->nullable($input['nama_ketua'] ?? null),
            'nama_bendahara' => $this->nullable($input['nama_bendahara'] ?? null),
        ]);
    }

    private function nullable(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result === '' ? null : $result;
    }
}
