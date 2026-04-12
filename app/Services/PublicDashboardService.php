<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ZakatPaymentRepository;
use DateTimeImmutable;

final class PublicDashboardService
{
    private DashboardService $dashboard;
    private ZakatPaymentRepository $payments;

    public function __construct(
        ?DashboardService $dashboard = null,
        ?ZakatPaymentRepository $payments = null
    ) {
        $this->dashboard = $dashboard ?? new DashboardService();
        $this->payments = $payments ?? new ZakatPaymentRepository();
    }

    public function summary(?string $fromDate, ?string $toDate): array
    {
        $today = new DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));

        if (trim((string) $fromDate) === '' && trim((string) $toDate) === '') {
            $earliest = $this->payments->minPaymentAt();
            $from = $earliest !== null ? substr($earliest, 0, 10) : $today->format('Y-m-d');
            $to = $today->format('Y-m-d');
        } else {
            $from = trim((string) $fromDate) !== '' ? trim((string) $fromDate) : $today->format('Y-m-d');
            $to = trim((string) $toDate) !== '' ? trim((string) $toDate) : $from;
        }

        $summary = $this->dashboard->summary($from, $to);
        $fromInclusive = $summary['fromDate'] . ' 00:00:00';
        $toExclusive = (new DateTimeImmutable($summary['toDate'], new \DateTimeZone('Asia/Jakarta')))
            ->modify('+1 day')
            ->format('Y-m-d') . ' 00:00:00';

        $fitrahBreakdown = $this->payments->fitrahJiwaBreakdown($fromInclusive, $toExclusive);
        $fitrahMap = [];
        foreach ($fitrahBreakdown as $row) {
            $type = (string) ($row['zakat_type'] ?? '');
            if ($type === '') {
                continue;
            }
            $fitrahMap[$type] = (int) ($row['total_jiwa'] ?? 0);
        }

        return [
            'fromDate' => $summary['fromDate'],
            'toDate' => $summary['toDate'],
            'totalTransaksi' => $summary['totalTransaksi'],
            'totalUangMasuk' => $summary['totalUangMasuk'],
            'totalUangCash' => $summary['totalUangCash'],
            'totalUangTransfer' => $summary['totalUangTransfer'],
            'totalBerasKg' => $summary['totalBerasKg'],
            'totalJiwaFitrah' => $summary['totalJiwaFitrah'],
            'totalJiwaFitrahBeras' => $fitrahMap['ZAKAT_FITRAH_BERAS'] ?? 0,
            'totalJiwaFitrahUang' => $fitrahMap['ZAKAT_FITRAH_UANG'] ?? 0,
            'byType' => $summary['byType'],
            'institutionProfile' => $summary['institutionProfile'],
            'generatedAt' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
        ];
    }
}
