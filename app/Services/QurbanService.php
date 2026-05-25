<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\QurbanRepository;
use RuntimeException;

final class QurbanService
{
    private const ANIMAL_TYPES = ['KAMBING', 'SAPI', 'SAPI_KOLEKTIF'];
    private const SLAUGHTER_MODES = ['JAGAL', 'SENDIRI'];
    private const MAX_COLLECTIVE_PARTICIPANTS = 7;

    private QurbanRepository $qurban;

    public function __construct(?QurbanRepository $qurban = null)
    {
        $this->qurban = $qurban ?? new QurbanRepository();
    }

    public function create(array $payload, ?string $actor = null): array
    {
        [$submission, $participants] = $this->validateAndBuildSubmission($payload, null, $actor);

        return $this->qurban->create($submission, $participants);
    }

    public function getById(string $id): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new RuntimeException('ID qurban wajib diisi');
        }

        $row = $this->qurban->findById($id);
        if (!is_array($row)) {
            throw new RuntimeException('Data qurban tidak ditemukan');
        }

        return [
            'id' => (string) $row['id'],
            'qurbanNumber' => $row['qurban_number'] !== null ? (string) $row['qurban_number'] : null,
            'submissionDate' => $row['submission_date'] !== null ? substr((string) $row['submission_date'], 0, 10) : null,
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
            'notes' => $row['notes'] !== null ? (string) $row['notes'] : null,
            'participants' => is_array($row['participants'] ?? null) ? array_values($row['participants']) : [],
        ];
    }

    public function update(string $id, array $payload, ?string $actor = null): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new RuntimeException('ID qurban wajib diisi');
        }

        $existing = $this->qurban->findById($id);
        if (!is_array($existing)) {
            throw new RuntimeException('Data qurban tidak ditemukan');
        }

        [$submission, $participants] = $this->validateAndBuildSubmission($payload, $id, $actor);
        $submission['id'] = $id;
        $submission['created_by'] = $existing['created_by'] ?? null;

        $updated = $this->qurban->update($submission, $participants);

        return $this->getById((string) ($updated['id'] ?? $id));
    }

    public function search(array $filters): array
    {
        return $this->qurban->search([
            'from' => $this->normalizeDate($filters['from'] ?? null),
            'to' => $this->normalizeDate($filters['to'] ?? null),
            'q' => $this->normalizeOptionalText($filters['q'] ?? null) ?? '',
            'animalType' => $this->normalizeOptionalText($filters['animalType'] ?? null) ?? '',
            'page' => max(0, (int) ($filters['page'] ?? 0)),
            'size' => max(1, (int) ($filters['size'] ?? 20)),
            'sortKey' => $this->normalizeSortKey($filters['sort'] ?? []),
            'sortDir' => $this->normalizeSortDir($filters['sort'] ?? []),
        ]);
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

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException('Format tanggal filter tidak valid');
        }

        return $value;
    }

    private function normalizeSortKey($value): string
    {
        if (is_array($value)) {
            $value = $value['key'] ?? '';
        }

        $value = trim((string) $value);
        return in_array($value, ['submissionDate', 'qurbanNumber', 'payerName', 'animalType'], true)
            ? $value
            : 'submissionDate';
    }

    private function normalizeSortDir($value): string
    {
        if (is_array($value)) {
            $value = $value['dir'] ?? '';
        }

        return strtolower(trim((string) $value)) === 'asc' ? 'asc' : 'desc';
    }

    private function normalizeDigits($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $digits = is_string($digits) ? trim($digits) : '';
        return $digits === '' ? null : $digits;
    }

    private function normalizeOptionalAmount($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $digits = is_string($digits) ? trim($digits) : '';
        return $digits === '' ? null : $digits;
    }

    private function normalizeParticipants($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $participants = [];
        foreach ($value as $participant) {
            $name = trim((string) $participant);
            if ($name !== '') {
                $participants[] = $name;
            }
        }

        return array_values($participants);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function validateAndBuildSubmission(array $payload, ?string $excludeId, ?string $actor): array
    {
        $submissionDate = trim((string) ($payload['submissionDate'] ?? ''));
        $qurbanNumber = $this->normalizeDigits($payload['qurbanNumber'] ?? null);
        $payerName = trim((string) ($payload['payerName'] ?? ''));
        $payerPhone = $this->normalizeDigits($payload['payerPhone'] ?? null);
        $alamat = trim((string) ($payload['alamat'] ?? ''));
        $animalType = trim((string) ($payload['animalType'] ?? ''));
        $biayaPemeliharaan = $this->normalizeOptionalAmount($payload['biayaPemeliharaan'] ?? null);
        $shodaqohInfak = $this->normalizeOptionalAmount($payload['shodaqohInfak'] ?? null);
        $biayaSupplier = $this->normalizeOptionalAmount($payload['biayaSupplier'] ?? null);
        $slaughterMode = trim((string) ($payload['slaughterMode'] ?? ''));
        $pickupTimeNotes = $this->normalizeOptionalText($payload['pickupTimeNotes'] ?? null);
        $committeePhone = $this->normalizeOptionalText($payload['committeePhone'] ?? null);
        $notes = $this->normalizeOptionalText($payload['notes'] ?? null);
        $participants = $this->normalizeParticipants($payload['participants'] ?? []);

        if ($submissionDate === '') {
            throw new RuntimeException('Tanggal input wajib diisi');
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $submissionDate, new \DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d') !== $submissionDate) {
            throw new RuntimeException('Format tanggal input tidak valid');
        }

        if ($qurbanNumber === null) {
            throw new RuntimeException('Nomor qurban wajib diisi');
        }

        if ($payerName === '') {
            throw new RuntimeException('Nama wajib diisi');
        }

        if ($payerPhone === null) {
            throw new RuntimeException('No. HP wajib diisi');
        }

        if ($alamat === '') {
            throw new RuntimeException('Alamat wajib diisi');
        }

        if (!in_array($animalType, self::ANIMAL_TYPES, true)) {
            throw new RuntimeException('Jenis hewan qurban tidak valid');
        }

        $animalNumberGroup = $this->animalNumberGroup($animalType);

        if ($this->qurban->existsByQurbanNumber($qurbanNumber, $animalNumberGroup, $excludeId)) {
            $groupLabel = $animalNumberGroup === 'KAMBING' ? 'kambing' : 'sapi';
            throw new RuntimeException('Nomor qurban sudah dipakai untuk kelompok ' . $groupLabel);
        }

        if (!in_array($slaughterMode, self::SLAUGHTER_MODES, true)) {
            throw new RuntimeException('Pilihan sembelih hewan qurban tidak valid');
        }

        if ($animalType !== 'KAMBING') {
            $pickupTimeNotes = null;
            $committeePhone = null;
        }

        if ($animalType === 'SAPI_KOLEKTIF') {
            if ($participants === []) {
                throw new RuntimeException('Peserta sapi kolektif wajib diisi');
            }

            if (count($participants) > self::MAX_COLLECTIVE_PARTICIPANTS) {
                throw new RuntimeException('Peserta sapi kolektif maksimal ' . self::MAX_COLLECTIVE_PARTICIPANTS . ' orang');
            }

            if (($participants[0] ?? '') !== $payerName) {
                throw new RuntimeException('Peserta 1 harus sama dengan nama pemberi qurban untuk sapi kolektif');
            }
        } else {
            $participants = [];
        }

        return [[
            'id' => $excludeId ?? $this->uuid(),
            'qurban_number' => $qurbanNumber,
            'submission_date' => $date->format('Y-m-d 00:00:00'),
            'payer_name' => $payerName,
            'payer_phone' => $payerPhone,
            'alamat' => $alamat,
            'animal_type' => $animalType,
            'animal_number_group' => $animalNumberGroup,
            'biaya_pemeliharaan' => $biayaPemeliharaan,
            'shodaqoh_infak' => $shodaqohInfak,
            'biaya_supplier' => $biayaSupplier,
            'slaughter_mode' => $slaughterMode,
            'pickup_time_notes' => $pickupTimeNotes,
            'committee_phone' => $committeePhone,
            'notes' => $notes,
            'created_by' => $this->normalizeOptionalText($actor),
            'updated_by' => $this->normalizeOptionalText($actor),
        ], $participants];
    }

    private function animalNumberGroup(string $animalType): string
    {
        return $animalType === 'KAMBING' ? 'KAMBING' : 'SAPI';
    }
}
