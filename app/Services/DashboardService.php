<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\InstitutionProfileRepository;
use App\Repositories\ZakatPaymentRepository;
use App\Repositories\ZakatQualityRepository;
use DateTimeImmutable;
use RuntimeException;

final class DashboardService
{
    private ZakatPaymentRepository $payments;
    private ZakatQualityRepository $qualities;
    private InstitutionProfileRepository $profiles;

    public function __construct(
        ?ZakatPaymentRepository $payments = null,
        ?ZakatQualityRepository $qualities = null,
        ?InstitutionProfileRepository $profiles = null
    ) {
        $this->payments = $payments ?? new ZakatPaymentRepository();
        $this->qualities = $qualities ?? new ZakatQualityRepository();
        $this->profiles = $profiles ?? new InstitutionProfileRepository();
    }

    public function summary(?string $fromDate, ?string $toDate): array
    {
        $today = new DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
        $from = $this->parseOptionalIsoDate($fromDate) ?? $today;
        $to = $this->parseOptionalIsoDate($toDate) ?? $from;

        if ($to < $from) {
            throw new RuntimeException('toDate tidak boleh lebih kecil dari fromDate');
        }

        $fromInclusive = $from->format('Y-m-d') . ' 00:00:00';
        $toExclusive = $to->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $totals = $this->payments->dashboardTotals($fromInclusive, $toExclusive);
        $breakdown = $this->payments->rekapSummary($fromInclusive, $toExclusive);
        $methodBreakdown = $this->payments->dashboardPaymentMethodBreakdown($fromInclusive, $toExclusive);
        $profile = $this->profiles->first();
        $recent = $this->payments->findRecent($fromInclusive, $toExclusive, 5);

        return [
            'fromDate' => $from->format('Y-m-d'),
            'toDate' => $to->format('Y-m-d'),
            'totalTransaksi' => (int) ($totals['total_transaksi'] ?? 0),
            'totalUangMasuk' => (float) ($totals['total_uang_masuk'] ?? 0),
            'totalUangCash' => (float) ($methodBreakdown['total_uang_cash'] ?? 0),
            'totalUangTransfer' => (float) ($methodBreakdown['total_uang_transfer'] ?? 0),
            'totalBerasKg' => (float) ($totals['total_beras_kg'] ?? 0),
            'totalJiwaFitrah' => (int) ($totals['total_jiwa_fitrah'] ?? 0),
            'byType' => [
                [
                    'zakatType' => 'ZAKAT_FITRAH_UANG',
                    'zakatTypeLabel' => 'Zakat Fitrah (Uang)',
                    'totalUang' => (float) ($breakdown['fitrah_uang'] ?? 0),
                    'totalBerasKg' => 0.0,
                ],
                [
                    'zakatType' => 'ZAKAT_FITRAH_BERAS',
                    'zakatTypeLabel' => 'Zakat Fitrah (Beras)',
                    'totalUang' => 0.0,
                    'totalBerasKg' => (float) ($breakdown['fitrah_beras'] ?? 0),
                ],
                [
                    'zakatType' => 'FIDIAH',
                    'zakatTypeLabel' => 'Fidiah',
                    'totalUang' => (float) ($breakdown['fidiah'] ?? 0),
                    'totalBerasKg' => 0.0,
                ],
                [
                    'zakatType' => 'ZAKAT_MAL',
                    'zakatTypeLabel' => 'Zakat Mal',
                    'totalUang' => (float) ($breakdown['zakat_mal'] ?? 0),
                    'totalBerasKg' => 0.0,
                ],
                [
                    'zakatType' => 'INFAQ_SEDEKAH',
                    'zakatTypeLabel' => 'Infaq/Sedekah',
                    'totalUang' => (float) ($breakdown['infaq_sedekah'] ?? 0),
                    'totalBerasKg' => 0.0,
                ],
            ],
            'institutionProfile' => is_array($profile) ? [
                'id' => (string) $profile['id'],
                'namaInstansi' => (string) ($profile['nama_instansi'] ?? ''),
                'kotaKabupaten' => (string) ($profile['kota_kabupaten'] ?? ''),
                'alamatLengkap' => (string) ($profile['alamat_lengkap'] ?? ''),
                'nomorTelepon' => $profile['nomor_telepon'] !== null ? (string) $profile['nomor_telepon'] : null,
                'email' => $profile['email'] !== null ? (string) $profile['email'] : null,
                'namaKetua' => $profile['nama_ketua'] !== null ? (string) $profile['nama_ketua'] : null,
                'namaBendahara' => $profile['nama_bendahara'] !== null ? (string) $profile['nama_bendahara'] : null,
            ] : null,
            'receiptInfo' => $this->buildReceiptInfo(),
            'activeQualities' => [
                [
                    'zakatType' => 'ZAKAT_FITRAH_UANG',
                    'zakatTypeLabel' => 'Zakat Fitrah (Uang)',
                    'activeCount' => $this->qualities->countActiveByType('ZAKAT_FITRAH_UANG'),
                ],
                [
                    'zakatType' => 'ZAKAT_FITRAH_BERAS',
                    'zakatTypeLabel' => 'Zakat Fitrah (Beras)',
                    'activeCount' => $this->qualities->countActiveByType('ZAKAT_FITRAH_BERAS'),
                ],
            ],
            'recentPayments' => array_map(fn (array $row): array => $this->mapRecentPayment($row), $recent),
        ];
    }

    private function buildReceiptInfo(): array
    {
        $year = (int) (new DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y');
        $maxExisting = $this->payments->maxReceiptSequenceForYearPublic($year);
        $lastIssued = $this->payments->lastIssuedReceiptSequence($year);
        $base = max($maxExisting, $lastIssued);

        return [
            'year' => $year,
            'lastSequence' => $base,
            'lastReceiptNumber' => $base <= 0 ? null : sprintf('MA/%d/%06d', $year, $base),
            'nextReceiptNumber' => sprintf('MA/%d/%06d', $year, $base + 1),
        ];
    }

    private function mapRecentPayment(array $row): array
    {
        $zakatType = null;
        if ($row['jumlah_uang'] !== null && (float) $row['jumlah_uang'] > 0) {
            $zakatType = 'ZAKAT_FITRAH_UANG';
        } elseif ($row['berat_beras_kg'] !== null && (float) $row['berat_beras_kg'] > 0) {
            $zakatType = 'ZAKAT_FITRAH_BERAS';
        } elseif (!empty($row['quality_zakat_type'])) {
            $zakatType = (string) $row['quality_zakat_type'];
        } elseif ($row['jumlah_uang_zakat_mal'] !== null && (float) $row['jumlah_uang_zakat_mal'] > 0) {
            $zakatType = 'ZAKAT_MAL';
        } elseif ($row['jumlah_uang_infaq_sedekah'] !== null && (float) $row['jumlah_uang_infaq_sedekah'] > 0) {
            $zakatType = 'INFAQ_SEDEKAH';
        } elseif ($row['jumlah_uang_fidiah'] !== null && (float) $row['jumlah_uang_fidiah'] > 0) {
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
            'id' => (string) ($row['id'] ?? ''),
            'receiptNumber' => $row['receipt_number'] !== null ? (string) $row['receipt_number'] : null,
            'paymentAt' => $row['payment_at'] !== null ? str_replace(' ', 'T', (string) $row['payment_at']) : null,
            'alamat' => $row['alamat'] !== null ? (string) $row['alamat'] : null,
            'zakatType' => $zakatType,
            'zakatTypeLabel' => $zakatType !== null ? ($labels[$zakatType] ?? $zakatType) : null,
            'jumlahJiwa' => $row['jumlah_jiwa'] !== null ? (int) $row['jumlah_jiwa'] : null,
            'jumlahUang' => $row['jumlah_uang'] !== null ? (float) $row['jumlah_uang'] : null,
            'beratBerasKg' => $row['berat_beras_kg'] !== null ? (float) $row['berat_beras_kg'] : null,
        ];
    }

    private function parseOptionalIsoDate(?string $value): ?DateTimeImmutable
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $trimmed);
        if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $trimmed) {
            throw new RuntimeException('Format tanggal tidak valid');
        }

        return $date;
    }
}
