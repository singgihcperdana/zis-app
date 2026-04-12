<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ZakatPaymentRepository;
use App\Repositories\ZakatQualityRepository;
use RuntimeException;

final class ZakatPaymentService
{
    private const MAX_JIWA = 10;

    private ZakatPaymentRepository $payments;
    private ZakatQualityRepository $qualities;

    public function __construct(
        ?ZakatPaymentRepository $payments = null,
        ?ZakatQualityRepository $qualities = null
    ) {
        $this->payments = $payments ?? new ZakatPaymentRepository();
        $this->qualities = $qualities ?? new ZakatQualityRepository();
    }

    public function receivedBySuggestions(): array
    {
        return $this->payments->findDistinctReceivedByNames();
    }

    public function search(array $filters): array
    {
        return $this->payments->search([
            'from' => $this->normalizeDate($filters['from'] ?? null),
            'to' => $this->normalizeDate($filters['to'] ?? null),
            'q' => $this->normalizeOptionalText($filters['q'] ?? null) ?? '',
            'payerName' => $this->normalizeOptionalText($filters['payerName'] ?? null) ?? '',
            'payerPhone' => $this->normalizeOptionalText($filters['payerPhone'] ?? null) ?? '',
            'includeCanceled' => filter_var($filters['includeCanceled'] ?? false, FILTER_VALIDATE_BOOL),
            'page' => max(0, (int) ($filters['page'] ?? 0)),
            'size' => max(1, (int) ($filters['size'] ?? 20)),
            'sortKey' => $this->normalizeSortKey($filters['sort'] ?? []),
            'sortDir' => $this->normalizeSortDir($filters['sort'] ?? []),
        ]);
    }

    public function getById(string $paymentId): array
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '') {
            throw new RuntimeException('paymentId wajib diisi');
        }

        $payment = $this->payments->findById($paymentId);
        if (!is_array($payment)) {
            throw new RuntimeException('Payment tidak ditemukan');
        }

        return $this->mapPaymentResponse($payment);
    }

    public function create(array $payload, ?string $actor = null): array
    {
        $paymentDate = trim((string) ($payload['paymentDate'] ?? ''));
        $alamat = trim((string) ($payload['alamat'] ?? ''));
        $payerName = trim((string) ($payload['payerName'] ?? ''));
        $payerPhone = $this->normalizeOptionalText($payload['payerPhone'] ?? null);
        $receivedByName = $this->normalizeOptionalText($payload['receivedByName'] ?? null);
        $paymentMethod = trim((string) ($payload['paymentMethod'] ?? ''));
        $zakatType = trim((string) ($payload['zakatType'] ?? ''));
        $jumlahJiwa = $this->normalizePositiveInteger($payload['jumlahJiwa'] ?? null);
        $zakatQualityId = $this->normalizeOptionalText($payload['zakatQualityId'] ?? null);
        $jumlahUang = $this->normalizeOptionalDecimal($payload['jumlahUang'] ?? null);
        $jumlahUangZakatMal = $this->normalizeOptionalDecimal($payload['jumlahUangZakatMal'] ?? null);
        $jumlahUangInfaqSedekah = $this->normalizeOptionalDecimal($payload['jumlahUangInfaqSedekah'] ?? null);
        $jumlahUangFidiah = $this->normalizeOptionalDecimal($payload['jumlahUangFidiah'] ?? null);
        $beratBerasKg = $this->normalizeOptionalDecimal($payload['beratBerasKg'] ?? null);
        $muzakkiNames = $this->normalizeNames($payload['muzakkiNames'] ?? []);

        if ($paymentDate === '') {
            throw new RuntimeException('Tanggal pembayaran wajib diisi');
        }

        $paymentAt = \DateTimeImmutable::createFromFormat('!Y-m-d', $paymentDate, new \DateTimeZone('Asia/Jakarta'));
        if (!$paymentAt || $paymentAt->format('Y-m-d') !== $paymentDate) {
            throw new RuntimeException('Format tanggal pembayaran tidak valid');
        }

        $today = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        if ($paymentAt > $today->setTime(0, 0)) {
            throw new RuntimeException('Tanggal pembayaran tidak boleh melebihi hari ini');
        }

        if ($alamat === '') {
            throw new RuntimeException('Alamat wajib diisi');
        }

        if ($payerName === '') {
            throw new RuntimeException('Nama pembayar wajib diisi');
        }

        if (!in_array($paymentMethod, ['CASH', 'TRANSFER'], true)) {
            throw new RuntimeException('Metode pembayaran wajib dipilih');
        }

        if ($jumlahJiwa === null || $jumlahJiwa < 1) {
            throw new RuntimeException('Jumlah jiwa wajib diisi');
        }

        if ($jumlahJiwa > self::MAX_JIWA) {
            throw new RuntimeException('Jumlah jiwa maksimal ' . self::MAX_JIWA);
        }

        $quality = null;
        if ($zakatQualityId !== null) {
            $quality = $this->qualities->findById($zakatQualityId);
            if (!is_array($quality)) {
                throw new RuntimeException('Zakat quality tidak ditemukan');
            }
            if ((int) ($quality['active'] ?? 0) !== 1) {
                throw new RuntimeException('Zakat quality sudah tidak aktif');
            }
            $zakatType = (string) ($quality['zakat_type'] ?? '');
        }

        $fitrahTypes = ['ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG'];
        $isFitrah = in_array($zakatType, $fitrahTypes, true);

        if ($isFitrah && count($muzakkiNames) !== $jumlahJiwa) {
            throw new RuntimeException('Jumlah nama muzakki harus sama dengan jumlah jiwa untuk fitrah');
        }

        if ($zakatType === 'ZAKAT_FITRAH_BERAS') {
            if ($quality === null) {
                throw new RuntimeException('Zakat quality wajib dipilih');
            }

            $perJiwa = $quality['berat_per_jiwa_kg'] ?? null;
            if ($beratBerasKg === null && $perJiwa !== null) {
                $beratBerasKg = number_format((float) $perJiwa * $jumlahJiwa, 2, '.', '');
            }

            if ($beratBerasKg === null) {
                throw new RuntimeException('beratBerasKg atau zakatQualityId wajib diisi');
            }

            $jumlahUang = null;
        } elseif ($zakatType === 'ZAKAT_FITRAH_UANG') {
            if ($quality === null) {
                throw new RuntimeException('Zakat quality wajib dipilih');
            }

            $perJiwa = $quality['nominal_per_jiwa'] ?? null;
            if ($jumlahUang === null && $perJiwa !== null) {
                $jumlahUang = (string) ((int) $perJiwa * $jumlahJiwa);
            }

            if ($jumlahUang === null) {
                throw new RuntimeException('jumlahUang atau zakatQualityId wajib diisi');
            }

            $beratBerasKg = null;
        } else {
            $hasAnyAmount = $jumlahUang !== null
                || $jumlahUangZakatMal !== null
                || $jumlahUangInfaqSedekah !== null
                || $jumlahUangFidiah !== null
                || $beratBerasKg !== null;

            if (!$hasAnyAmount) {
                throw new RuntimeException('Isi minimal salah satu: Fitrah, Zakat Mal, Infaq/Sedekah, atau Fidiah');
            }
        }

        $timestamp = $paymentAt->format('Y-m-d 00:00:00');
        $actorName = $this->normalizeOptionalText($actor);
        $nowJakarta = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));

        return $this->payments->create([
            'id' => $this->uuid(),
            'jumlah_jiwa' => $jumlahJiwa,
            'alamat' => $alamat,
            'payer_name' => $payerName,
            'payer_phone' => $payerPhone,
            'received_by_name' => $receivedByName,
            'payment_method' => $paymentMethod,
            'berat_beras_kg' => $beratBerasKg,
            'jumlah_uang' => $jumlahUang,
            'jumlah_uang_zakat_mal' => $jumlahUangZakatMal,
            'jumlah_uang_infaq_sedekah' => $jumlahUangInfaqSedekah,
            'jumlah_uang_fidiah' => $jumlahUangFidiah,
            'payment_at' => $timestamp,
            'receipt_year' => (int) $nowJakarta->format('Y'),
            'zakat_quality_id' => $quality['id'] ?? null,
            'created_by' => $actorName,
            'updated_by' => $actorName,
        ], $muzakkiNames);
    }

    public function cancel(string $paymentId, string $reason, ?string $canceledBy = null): void
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '') {
            throw new RuntimeException('paymentId wajib diisi');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('reason wajib diisi');
        }

        $payment = $this->payments->findById($paymentId);
        if (!is_array($payment)) {
            throw new RuntimeException('Payment tidak ditemukan');
        }

        if ((int) ($payment['canceled'] ?? 0) === 1) {
            return;
        }

        $this->payments->cancel($paymentId, $reason, $this->normalizeOptionalText($canceledBy));
    }

    public function update(string $paymentId, array $payload, ?string $actor = null): array
    {
        $paymentId = trim($paymentId);
        if ($paymentId === '') {
            throw new RuntimeException('paymentId wajib diisi');
        }

        $existing = $this->payments->findById($paymentId);
        if (!is_array($existing)) {
            throw new RuntimeException('Payment tidak ditemukan');
        }
        if ((int) ($existing['canceled'] ?? 0) === 1) {
            throw new RuntimeException('Payment sudah dibatalkan');
        }

        $paymentDate = trim((string) ($payload['paymentDate'] ?? ''));
        $alamat = trim((string) ($payload['alamat'] ?? ''));
        $payerName = $this->normalizeOptionalText($payload['payerName'] ?? null);
        $payerPhone = $this->normalizeOptionalText($payload['payerPhone'] ?? null);
        $receivedByName = $this->normalizeOptionalText($payload['receivedByName'] ?? null);
        $paymentMethod = trim((string) ($payload['paymentMethod'] ?? ''));
        $zakatQualityId = $this->normalizeOptionalText($payload['zakatQualityId'] ?? null);
        $jumlahUang = $this->normalizeOptionalDecimal($payload['jumlahUang'] ?? null);
        $jumlahUangZakatMal = $this->normalizeOptionalDecimal($payload['jumlahUangZakatMal'] ?? null);
        $jumlahUangInfaqSedekah = $this->normalizeOptionalDecimal($payload['jumlahUangInfaqSedekah'] ?? null);
        $jumlahUangFidiah = $this->normalizeOptionalDecimal($payload['jumlahUangFidiah'] ?? null);
        $muzakkiNames = $this->normalizeNames($payload['muzakkiNames'] ?? []);

        if ($paymentDate === '') {
            throw new RuntimeException('Tanggal pembayaran wajib diisi');
        }
        $paymentAt = \DateTimeImmutable::createFromFormat('!Y-m-d', $paymentDate, new \DateTimeZone('Asia/Jakarta'));
        if (!$paymentAt || $paymentAt->format('Y-m-d') !== $paymentDate) {
            throw new RuntimeException('Format tanggal pembayaran tidak valid');
        }
        $today = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        if ($paymentAt > $today->setTime(0, 0)) {
            throw new RuntimeException('Tanggal pembayaran tidak boleh melebihi hari ini');
        }
        if ($alamat === '') {
            throw new RuntimeException('Alamat wajib diisi');
        }
        if (!in_array($paymentMethod, ['CASH', 'TRANSFER'], true)) {
            throw new RuntimeException('Metode pembayaran wajib dipilih');
        }
        if (count($muzakkiNames) > self::MAX_JIWA) {
            throw new RuntimeException('Jumlah jiwa maksimal ' . self::MAX_JIWA);
        }

        $quality = null;
        $zakatType = '';
        if ($zakatQualityId !== null) {
            $quality = $this->qualities->findById($zakatQualityId);
            if (!is_array($quality)) {
                throw new RuntimeException('Zakat quality tidak ditemukan');
            }
            if ((int) ($quality['active'] ?? 0) !== 1) {
                throw new RuntimeException('Zakat quality sudah tidak aktif');
            }
            $zakatType = (string) ($quality['zakat_type'] ?? '');
        }

        $jumlahJiwa = max(1, count($muzakkiNames));
        if ($zakatType !== '' && $jumlahJiwa > self::MAX_JIWA) {
            throw new RuntimeException('Jumlah jiwa maksimal ' . self::MAX_JIWA);
        }

        $beratBerasKg = null;
        if ($quality !== null) {
            if ($zakatType === 'ZAKAT_FITRAH_BERAS') {
                $perJiwa = $quality['berat_per_jiwa_kg'] ?? null;
                if ($perJiwa === null) {
                    throw new RuntimeException('Zakat quality tidak memiliki beratPerJiwaKg');
                }
                $beratBerasKg = number_format((float) $perJiwa * $jumlahJiwa, 2, '.', '');
                $jumlahUang = null;
            } elseif ($zakatType === 'ZAKAT_FITRAH_UANG') {
                $perJiwa = $quality['nominal_per_jiwa'] ?? null;
                if ($perJiwa === null) {
                    throw new RuntimeException('Zakat quality tidak memiliki nominalPerJiwa');
                }
                $jumlahUang = (string) ((int) $perJiwa * $jumlahJiwa);
                $beratBerasKg = null;
            }
        } else {
            $hasAnyAmount = $jumlahUang !== null
                || $jumlahUangZakatMal !== null
                || $jumlahUangInfaqSedekah !== null
                || $jumlahUangFidiah !== null;
            if (!$hasAnyAmount) {
                throw new RuntimeException('jumlahUang atau salah satu nominal opsional wajib diisi untuk jenis zakat ini');
            }
        }

        $this->payments->update([
            'id' => $paymentId,
            'jumlah_jiwa' => $jumlahJiwa,
            'alamat' => $alamat,
            'payer_name' => $payerName,
            'payer_phone' => $payerPhone,
            'received_by_name' => $receivedByName,
            'payment_method' => $paymentMethod,
            'berat_beras_kg' => $beratBerasKg,
            'jumlah_uang' => $jumlahUang,
            'jumlah_uang_zakat_mal' => $jumlahUangZakatMal,
            'jumlah_uang_infaq_sedekah' => $jumlahUangInfaqSedekah,
            'jumlah_uang_fidiah' => $jumlahUangFidiah,
            'payment_at' => $paymentAt->format('Y-m-d 00:00:00'),
            'zakat_quality_id' => $quality['id'] ?? null,
            'updated_by' => $this->normalizeOptionalText($actor),
        ], $muzakkiNames);

        return $this->getById($paymentId);
    }

    private function normalizeOptionalText($value): ?string
    {
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizePositiveInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '' || !preg_match('/^\d+$/', $normalized)) {
            return null;
        }

        return (int) $normalized;
    }

    private function normalizeOptionalDecimal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        if ((float) $normalized <= 0) {
            return null;
        }

        return $normalized;
    }

    private function normalizeNames($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $names = [];
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $trimmed = trim((string) $item);
            if ($trimmed !== '') {
                $names[] = $trimmed;
            }
        }

        return $names;
    }

    private function normalizeDate($value): ?string
    {
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $normalized, new \DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d') !== $normalized) {
            throw new RuntimeException('Format tanggal filter tidak valid');
        }

        return $normalized;
    }

    private function normalizeSortKey($sort): string
    {
        $items = is_array($sort) ? $sort : [$sort];
        $allowed = [
            'receiptNumber',
            'paymentAt',
            'jumlahUang',
            'beratBerasKg',
            'jumlahUangZakatMal',
            'jumlahUangInfaqSedekah',
            'jumlahUangFidiah',
        ];

        foreach ($items as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $parts = explode(',', trim((string) $item));
            $key = $parts[0] ?? '';
            if (in_array($key, $allowed, true)) {
                return $key;
            }
        }

        return 'paymentAt';
    }

    private function normalizeSortDir($sort): string
    {
        $items = is_array($sort) ? $sort : [$sort];

        foreach ($items as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $parts = explode(',', trim((string) $item));
            $direction = strtolower($parts[1] ?? '');
            if ($direction === 'asc' || $direction === 'desc') {
                return $direction;
            }
        }

        return 'desc';
    }

    private function mapPaymentResponse(array $payment): array
    {
        $zakatType = null;
        if (!empty($payment['quality_zakat_type'])) {
            $zakatType = (string) $payment['quality_zakat_type'];
        } elseif ($payment['jumlah_uang_zakat_mal'] !== null && (float) $payment['jumlah_uang_zakat_mal'] > 0) {
            $zakatType = 'ZAKAT_MAL';
        } elseif ($payment['jumlah_uang_infaq_sedekah'] !== null && (float) $payment['jumlah_uang_infaq_sedekah'] > 0) {
            $zakatType = 'INFAQ_SEDEKAH';
        } elseif ($payment['jumlah_uang_fidiah'] !== null && (float) $payment['jumlah_uang_fidiah'] > 0) {
            $zakatType = 'FIDIAH';
        }

        $labels = [
            'ZAKAT_FITRAH_BERAS' => 'Zakat Fitrah (Beras)',
            'ZAKAT_FITRAH_UANG' => 'Zakat Fitrah (Uang)',
            'ZAKAT_MAL' => 'Zakat Mal',
            'INFAQ_SEDEKAH' => 'Infaq/Sedekah',
            'FIDIAH' => 'Fidiah',
        ];

        return [
            'id' => (string) $payment['id'],
            'receiptNumber' => $payment['receipt_number'] !== null ? (string) $payment['receipt_number'] : null,
            'canceled' => (bool) ($payment['canceled'] ?? false),
            'paymentAt' => $payment['payment_at'] !== null ? str_replace(' ', 'T', (string) $payment['payment_at']) : null,
            'zakatType' => $zakatType,
            'zakatTypeLabel' => $zakatType !== null ? ($labels[$zakatType] ?? $zakatType) : null,
            'jumlahJiwa' => $payment['jumlah_jiwa'] !== null ? (int) $payment['jumlah_jiwa'] : null,
            'alamat' => $payment['alamat'] !== null ? (string) $payment['alamat'] : null,
            'payerName' => $payment['payer_name'] !== null ? (string) $payment['payer_name'] : null,
            'payerPhone' => $payment['payer_phone'] !== null ? (string) $payment['payer_phone'] : null,
            'receivedByName' => $payment['received_by_name'] !== null ? (string) $payment['received_by_name'] : null,
            'paymentMethod' => $payment['payment_method'] !== null ? (string) $payment['payment_method'] : null,
            'beratBerasKg' => $payment['berat_beras_kg'] !== null ? (float) $payment['berat_beras_kg'] : null,
            'jumlahUang' => $payment['jumlah_uang'] !== null ? (float) $payment['jumlah_uang'] : null,
            'jumlahUangZakatMal' => $payment['jumlah_uang_zakat_mal'] !== null ? (float) $payment['jumlah_uang_zakat_mal'] : null,
            'jumlahUangInfaqSedekah' => $payment['jumlah_uang_infaq_sedekah'] !== null ? (float) $payment['jumlah_uang_infaq_sedekah'] : null,
            'jumlahUangFidiah' => $payment['jumlah_uang_fidiah'] !== null ? (float) $payment['jumlah_uang_fidiah'] : null,
            'zakatQuality' => !empty($payment['quality_id']) ? [
                'id' => (string) $payment['quality_id'],
                'name' => $payment['quality_name'] !== null ? (string) $payment['quality_name'] : null,
                'beratPerJiwaKg' => $payment['quality_berat_per_jiwa_kg'] !== null ? (float) $payment['quality_berat_per_jiwa_kg'] : null,
            ] : null,
            'muzakkiNames' => is_array($payment['muzakki_names'] ?? null) ? array_values($payment['muzakki_names']) : [],
        ];
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
