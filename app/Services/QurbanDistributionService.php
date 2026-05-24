<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\QurbanDistributionRepository;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class QurbanDistributionService
{
    private const RECIPIENT_TYPES = ['PERORANGAN', 'KELOMPOK'];

    private QurbanDistributionRepository $distributions;

    public function __construct(?QurbanDistributionRepository $distributions = null)
    {
        $this->distributions = $distributions ?? new QurbanDistributionRepository();
    }

    public function create(array $payload, ?string $actor = null): array
    {
        $distribution = $this->validateAndBuildDistribution($payload, $actor);

        return $this->distributions->create($distribution);
    }

    public function getById(string $id): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new RuntimeException('ID penyaluran wajib diisi');
        }

        $row = $this->distributions->findById($id);
        if (!is_array($row)) {
            throw new RuntimeException('Data penyaluran tidak ditemukan');
        }

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
        ];
    }

    public function search(array $filters): array
    {
        return $this->distributions->search([
            'from' => $this->normalizeDate($filters['from'] ?? null),
            'to' => $this->normalizeDate($filters['to'] ?? null),
            'q' => $this->normalizeOptionalText($filters['q'] ?? null) ?? '',
            'recipientType' => $this->normalizeRecipientType($filters['recipientType'] ?? null),
            'page' => max(0, (int) ($filters['page'] ?? 0)),
            'size' => max(1, (int) ($filters['size'] ?? 20)),
            'sortKey' => $this->normalizeSortKey($filters['sort'] ?? []),
            'sortDir' => $this->normalizeSortDir($filters['sort'] ?? []),
        ]);
    }

    public function exportCsv(array $filters): array
    {
        $normalizedFilters = [
            'from' => $this->normalizeDate($filters['from'] ?? null),
            'to' => $this->normalizeDate($filters['to'] ?? null),
            'q' => $this->normalizeOptionalText($filters['q'] ?? null) ?? '',
            'recipientType' => $this->normalizeRecipientType($filters['recipientType'] ?? null),
        ];

        $rows = $this->distributions->exportRows($normalizedFilters);
        $lines = [];
        $lines[] = 'Tanggal Penyaluran,Jam Penyaluran,Jenis Penerima,Nama Penerima/Nama Kelompok,Nama PIC,No. HP,Alamat/Wilayah,Jumlah Paket,Catatan,Disalurkan Oleh,Dibuat Pada';

        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $this->csvValue($row['distribution_date'] ?? ''),
                $this->csvValue($row['distribution_time'] !== null ? substr((string) $row['distribution_time'], 0, 5) : ''),
                $this->csvValue($this->recipientTypeLabel($row['recipient_type'] ?? '')),
                $this->csvValue($row['recipient_name'] ?? ''),
                $this->csvValue($row['pic_name'] ?? ''),
                $this->csvValue($row['recipient_phone'] ?? ''),
                $this->csvValue($row['recipient_area'] ?? ''),
                $this->csvValue((string) ($row['package_count'] ?? '')),
                $this->csvValue($row['notes'] ?? ''),
                $this->csvValue($row['distributed_by'] ?? ''),
                $this->csvValue($row['created_at'] ?? ''),
            ]);
        }

        $from = $normalizedFilters['from'] ?? 'all';
        $to = $normalizedFilters['to'] ?? 'all';

        return [
            'filename' => sprintf('riwayat-penyaluran-qurban_%s_%s.csv', $from, $to),
            'csv' => implode("\n", $lines) . "\n",
        ];
    }

    public function update(string $id, array $payload, ?string $actor = null): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new RuntimeException('ID penyaluran wajib diisi');
        }

        $existing = $this->distributions->findById($id);
        if (!is_array($existing)) {
            throw new RuntimeException('Data penyaluran tidak ditemukan');
        }

        $distribution = $this->validateAndBuildDistribution($payload, $actor);
        $distribution['id'] = $id;
        $distribution['created_by'] = $existing['created_by'] ?? null;

        $this->distributions->update($distribution);

        return $this->getById($id);
    }

    public function delete(string $id): void
    {
        $id = trim($id);
        if ($id === '') {
            throw new RuntimeException('ID penyaluran wajib diisi');
        }

        $existing = $this->distributions->findById($id);
        if (!is_array($existing)) {
            throw new RuntimeException('Data penyaluran tidak ditemukan');
        }

        $this->distributions->delete($id);
    }

    private function validateAndBuildDistribution(array $payload, ?string $actor): array
    {
        $distributionDate = trim((string) ($payload['distributionDate'] ?? ''));
        $distributionTime = trim((string) ($payload['distributionTime'] ?? ''));
        $recipientType = trim((string) ($payload['recipientType'] ?? ''));
        $recipientName = trim((string) ($payload['recipientName'] ?? ''));
        $picName = $this->normalizeOptionalText($payload['picName'] ?? null);
        $recipientPhone = $this->normalizeDigits($payload['recipientPhone'] ?? null);
        $recipientArea = trim((string) ($payload['recipientArea'] ?? ''));
        $packageCount = $this->normalizeDigits($payload['packageCount'] ?? null);
        $notes = $this->normalizeOptionalText($payload['notes'] ?? null);
        $distributedBy = trim((string) ($payload['distributedBy'] ?? ''));

        if ($distributionDate === '') {
            throw new RuntimeException('Tanggal penyaluran wajib diisi');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $distributionDate, new DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d') !== $distributionDate) {
            throw new RuntimeException('Format tanggal penyaluran tidak valid');
        }

        if ($distributionTime !== '') {
            $time = DateTimeImmutable::createFromFormat('!H:i', $distributionTime, new DateTimeZone('Asia/Jakarta'));
            if (!$time || $time->format('H:i') !== $distributionTime) {
                throw new RuntimeException('Format jam penyaluran tidak valid');
            }

            $distributionTime .= ':00';
        } else {
            $distributionTime = null;
        }

        if (!in_array($recipientType, self::RECIPIENT_TYPES, true)) {
            throw new RuntimeException('Jenis penerima tidak valid');
        }

        if ($recipientName === '') {
            throw new RuntimeException($recipientType === 'KELOMPOK' ? 'Nama kelompok wajib diisi' : 'Nama penerima wajib diisi');
        }

        if ($recipientArea === '') {
            throw new RuntimeException('Alamat / wilayah wajib diisi');
        }

        if ($packageCount === null) {
            throw new RuntimeException('Jumlah paket wajib diisi');
        }

        if ((int) $packageCount <= 0) {
            throw new RuntimeException('Jumlah paket harus lebih dari 0');
        }

        if ($distributedBy === '') {
            throw new RuntimeException('Disalurkan oleh wajib diisi');
        }

        if ($recipientType !== 'KELOMPOK') {
            $picName = null;
        }

        return [
            'id' => $this->uuid(),
            'distribution_date' => $distributionDate,
            'distribution_time' => $distributionTime,
            'recipient_type' => $recipientType,
            'recipient_name' => $recipientName,
            'pic_name' => $picName,
            'recipient_phone' => $recipientPhone,
            'recipient_area' => $recipientArea,
            'package_count' => (int) $packageCount,
            'notes' => $notes,
            'distributed_by' => $distributedBy,
            'created_by' => $this->normalizeOptionalText($actor),
            'updated_by' => $this->normalizeOptionalText($actor),
        ];
    }

    private function normalizeOptionalText($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException('Format tanggal filter tidak valid');
        }

        return $value;
    }

    private function normalizeDigits($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $digits = is_string($digits) ? trim($digits) : '';
        return $digits === '' ? null : $digits;
    }

    private function normalizeRecipientType($value): string
    {
        $value = trim((string) $value);
        return in_array($value, self::RECIPIENT_TYPES, true) ? $value : '';
    }

    private function normalizeSortKey($value): string
    {
        if (is_array($value)) {
            $value = $value['key'] ?? '';
        }

        $value = trim((string) $value);
        return in_array($value, ['distributionDate', 'recipientType', 'recipientName', 'packageCount', 'distributedBy'], true)
            ? $value
            : 'distributionDate';
    }

    private function normalizeSortDir($value): string
    {
        if (is_array($value)) {
            $value = $value['dir'] ?? '';
        }

        return strtolower(trim((string) $value)) === 'asc' ? 'asc' : 'desc';
    }

    private function recipientTypeLabel($value): string
    {
        $value = trim((string) $value);
        return match ($value) {
            'PERORANGAN' => 'Perorangan',
            'KELOMPOK' => 'Kelompok',
            default => $value,
        };
    }

    private function csvValue($value): string
    {
        $string = (string) $value;
        $string = str_replace('"', '""', $string);
        return '"' . $string . '"';
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
