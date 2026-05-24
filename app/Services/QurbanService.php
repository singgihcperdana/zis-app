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
        $committeePhone = $this->normalizeDigits($payload['committeePhone'] ?? null);
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

        if ($this->qurban->existsByQurbanNumber($qurbanNumber)) {
            throw new RuntimeException('Nomor qurban sudah dipakai');
        }

        if ($payerName === '') {
            throw new RuntimeException('Nama wajib diisi');
        }

        if ($alamat === '') {
            throw new RuntimeException('Alamat wajib diisi');
        }

        if (!in_array($animalType, self::ANIMAL_TYPES, true)) {
            throw new RuntimeException('Jenis hewan qurban tidak valid');
        }

        if (!in_array($slaughterMode, self::SLAUGHTER_MODES, true)) {
            throw new RuntimeException('Pilihan sembelih hewan qurban tidak valid');
        }

        if ($animalType === 'KAMBING') {
            if ($pickupTimeNotes === null) {
                throw new RuntimeException('Waktu pengambilan wajib diisi untuk kambing');
            }

            if ($committeePhone === null) {
                throw new RuntimeException('No. HP panitia wajib diisi untuk kambing');
            }
        } else {
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

        return $this->qurban->create([
            'id' => $this->uuid(),
            'qurban_number' => $qurbanNumber,
            'submission_date' => $date->format('Y-m-d 00:00:00'),
            'payer_name' => $payerName,
            'payer_phone' => $payerPhone,
            'alamat' => $alamat,
            'animal_type' => $animalType,
            'biaya_pemeliharaan' => $biayaPemeliharaan,
            'shodaqoh_infak' => $shodaqohInfak,
            'biaya_supplier' => $biayaSupplier,
            'slaughter_mode' => $slaughterMode,
            'pickup_time_notes' => $pickupTimeNotes,
            'committee_phone' => $committeePhone,
            'created_by' => $this->normalizeOptionalText($actor),
            'updated_by' => $this->normalizeOptionalText($actor),
        ], $participants);
    }

    private function normalizeOptionalText($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
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
}
