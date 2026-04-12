<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ZakatQualityRepository;
use RuntimeException;

final class ZakatQualityService
{
    private ZakatQualityRepository $qualities;

    public function __construct(?ZakatQualityRepository $qualities = null)
    {
        $this->qualities = $qualities ?? new ZakatQualityRepository();
    }

    public function getByType(string $zakatType): array
    {
        if (!in_array($zakatType, ['ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG'], true)) {
            throw new RuntimeException('ZisType tidak valid');
        }

        return $this->qualities->findActiveByType($zakatType);
    }

    public function create(array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $zakatType = trim((string) ($payload['zakatType'] ?? ''));
        $beratPerJiwaKg = $payload['beratPerJiwaKg'] ?? null;
        $nominalPerJiwa = $payload['nominalPerJiwa'] ?? null;

        if ($name === '') {
            throw new RuntimeException('Label wajib diisi');
        }

        if (!in_array($zakatType, ['ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG'], true)) {
            throw new RuntimeException('ZisType tidak valid');
        }

        if ($zakatType === 'ZAKAT_FITRAH_BERAS') {
            if ($beratPerJiwaKg === null || (float) $beratPerJiwaKg <= 0) {
                throw new RuntimeException('beratPerJiwaKg wajib diisi untuk ZAKAT_FITRAH_BERAS');
            }
            if ($nominalPerJiwa !== null && $nominalPerJiwa !== '') {
                throw new RuntimeException('nominalPerJiwa harus null untuk ZAKAT_FITRAH_BERAS');
            }
        }

        if ($zakatType === 'ZAKAT_FITRAH_UANG') {
            if ($nominalPerJiwa === null || (int) $nominalPerJiwa <= 0) {
                throw new RuntimeException('nominalPerJiwa wajib diisi untuk ZAKAT_FITRAH_UANG');
            }
            if ($beratPerJiwaKg !== null && $beratPerJiwaKg !== '') {
                throw new RuntimeException('beratPerJiwaKg harus null untuk ZAKAT_FITRAH_UANG');
            }
        }

        return $this->qualities->create([
            'id' => $this->uuid(),
            'name' => $name,
            'zakat_type' => $zakatType,
            'active' => 1,
            'berat_per_jiwa_kg' => $zakatType === 'ZAKAT_FITRAH_BERAS' ? (float) $beratPerJiwaKg : null,
            'nominal_per_jiwa' => $zakatType === 'ZAKAT_FITRAH_UANG' ? (int) $nominalPerJiwa : null,
        ]);
    }

    public function deactivate(string $id): void
    {
        $existing = $this->qualities->findById($id);
        if (!is_array($existing)) {
            throw new RuntimeException('Zakat quality tidak ditemukan');
        }

        $this->qualities->deactivate($id);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
