<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\InstitutionProfileRepository;
use App\Repositories\MuzakkiPersonRepository;
use App\Repositories\QurbanRepository;
use App\Repositories\ZakatPaymentRepository;
use DateTimeImmutable;
use RuntimeException;
use setasign\Fpdi\Fpdi;

final class ReportService
{
    private const PDF_FIELD_RECTS = [
        'nama' => [142.0, 353.83423, 373.16702, 368.83423],
        'noTelp' => [432.29852, 354.94327, 573.7609, 369.94327],
        'noKwitansi' => [513.1884, 378.5161, 583.30865, 393.5161],
        'alamat' => [142.0, 337.9787, 570.21674, 352.9787],
        'jumlahBeras' => [134.89095, 290.36, 157.00833, 305.46],
        'jumlahUang' => [157.21541, 275.15857, 208.78886, 289.20004],
        'jumlahJiwaBeras' => [212.13202, 290.46, 234.97136, 305.46],
        'jumlahJiwaUang' => [259.74255, 275.52814, 283.0335, 290.52814],
        'muzakki1' => [51.63682, 216.26, 201.63683, 231.26],
        'muzakki2' => [51.877216, 197.3588, 201.87721, 212.3588],
        'muzakki3' => [51.84573, 178.45999, 201.84573, 193.45999],
        'muzakki4' => [235.06755, 216.45999, 386.06757, 231.45999],
        'muzakki5' => [235.31332, 197.45999, 387.08905, 212.45999],
        'muzakki6' => [235.11057, 177.68327, 386.8064, 194.85999],
        'muzakki7' => [420.0, 216.45999, 570.0, 231.45999],
        'muzakki8' => [420.0, 197.45999, 570.0, 212.45999],
        'muzakki9' => [420.0, 178.41574, 570.0, 193.41574],
        'muzakki10' => [420.3992, 159.99188, 570.3992, 174.99188],
        'jumlahZakatMal' => [165.90045, 160.93811, 375.85406, 175.93811],
        'jumlahInfaqSedekah' => [166.4043, 142.1084, 375.57452, 157.1084],
        'jumlahFidiah' => [166.26912, 124.07193, 375.11368, 139.07193],
        'dd' => [475.06943, 112.3024, 494.46793, 127.3024],
        'MM' => [499.2874, 112.661835, 517.583, 127.661835],
        'yyyy' => [521.6669, 112.88043, 556.97003, 127.88043],
        'totalBeras' => [139.05574, 253.15778, 182.32816, 268.15778],
        'totalUang' => [259.5129, 253.9054, 374.64365, 268.9054],
        'checkboxCash' => [430.44632, 253.28722, 442.44632, 268.58722],
        'checkboxTransfer' => [500.28854, 252.74867, 512.2886, 268.34314],
    ];

    private const QURBAN_SAPI_FIELD_RECTS = [
        'nama' => [162.0, 777.0, 397.0, 791.0],
        'peserta1' => [421.0, 774.0, 580.0, 788.0],
        'alamat' => [162.0, 760.0, 397.0, 774.0],
        'peserta2' => [421.0, 752.0, 580.0, 766.0],
        'telp' => [162.0, 743.0, 397.0, 757.0],
        'peserta3' => [421.0, 729.0, 580.0, 743.0],
        'peserta4' => [421.0, 707.0, 580.0, 721.0],
        'peserta5' => [422.0, 684.0, 581.0, 698.0],
        'biayaSapi' => [190.0, 660.0, 291.0, 674.0],
        'peserta6' => [422.0, 661.0, 581.0, 675.0],
        'infak' => [190.0, 643.0, 291.0, 657.0],
        'peserta7' => [422.0, 639.0, 581.0, 653.0],
        'biayaSupplier' => [190.0, 626.0, 291.0, 640.0],
        'isSembelihJagal' => [164.0, 599.0, 177.0, 612.0],
        'isSembelihSendiri' => [307.0, 599.0, 321.0, 613.0],
        'tanggal' => [489.0, 583.0, 514.0, 604.0],
        'bulan' => [525.0, 584.0, 550.0, 605.0],
        'tahun' => [563.0, 583.0, 600.0, 604.0],
        'notes' => [162.0, 690.0, 397.0, 704.0],
        'nomorKurban' => [73.0, 506.0, 162.0, 567.0],
    ];

    private const QURBAN_KAMBING_FIELD_RECTS = [
        'nama' => [162.0, 776.0, 398.0, 790.0],
        'alamat' => [162.0, 759.0, 398.0, 773.0],
        'telepon' => [162.0, 742.0, 398.0, 756.0],
        'notes' => [163.0, 688.0, 399.0, 702.0],
        'biayaKambing' => [190.0, 659.0, 290.0, 673.0],
        'infak' => [191.0, 641.0, 291.0, 655.0],
        'biayaSupplier' => [191.0, 624.0, 291.0, 638.0],
        'isSembelihJagal' => [164.0, 597.0, 178.0, 611.0],
        'isSembelihSendiri' => [306.0, 597.0, 320.0, 611.0],
        'waktuPengambilan' => [101.0, 550.0, 220.0, 564.0],
        'noPanitia' => [101.0, 520.0, 220.0, 534.0],
        'tanggal' => [488.0, 584.0, 515.0, 598.0],
        'bulan' => [524.0, 583.0, 551.0, 597.0],
        'tahun' => [563.0, 583.0, 597.0, 597.0],
        'nomorKurban' => [439.0, 632.0, 594.0, 773.0],
    ];

    private ZakatPaymentRepository $payments;
    private MuzakkiPersonRepository $muzakki;
    private InstitutionProfileRepository $profiles;
    private QurbanRepository $qurban;

    public function __construct(
        ?ZakatPaymentRepository $payments = null,
        ?MuzakkiPersonRepository $muzakki = null,
        ?InstitutionProfileRepository $profiles = null,
        ?QurbanRepository $qurban = null
    ) {
        $this->payments = $payments ?? new ZakatPaymentRepository();
        $this->muzakki = $muzakki ?? new MuzakkiPersonRepository();
        $this->profiles = $profiles ?? new InstitutionProfileRepository();
        $this->qurban = $qurban ?? new QurbanRepository();
    }

    public function rekapZis(string $fromDate, string $toDate): array
    {
        $from = $this->parseIsoDate($fromDate, 'fromDate');
        $to = $this->parseIsoDate($toDate, 'toDate');

        if ($to < $from) {
            throw new RuntimeException('toDate tidak boleh lebih kecil dari fromDate');
        }

        $fromInclusive = $from->format('Y-m-d') . ' 00:00:00';
        $toExclusive = $to->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $summary = $this->payments->rekapSummary($fromInclusive, $toExclusive);
        $fitrahUang = (float) ($summary['fitrah_uang'] ?? 0);
        $fitrahBeras = (float) ($summary['fitrah_beras'] ?? 0);
        $fidiah = (float) ($summary['fidiah'] ?? 0);
        $zakatMal = (float) ($summary['zakat_mal'] ?? 0);
        $infaqSedekah = (float) ($summary['infaq_sedekah'] ?? 0);
        $totalUangMasuk = $fitrahUang + $fidiah + $zakatMal + $infaqSedekah;
        $totalMuzakkiFitrahJiwa = $this->payments->sumJiwaFitrah($fromInclusive, $toExclusive);

        $profile = $this->profiles->first();

        return [
            'fromDate' => $from->format('Y-m-d'),
            'toDate' => $to->format('Y-m-d'),
            'zakatFitrahUang' => $fitrahUang,
            'zakatFitrahBerasKg' => $fitrahBeras,
            'fidiah' => $fidiah,
            'zakatMal' => $zakatMal,
            'infaqSedekah' => $infaqSedekah,
            'totalUangMasuk' => $totalUangMasuk,
            'totalMuzakkiFitrahJiwa' => $totalMuzakkiFitrahJiwa,
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
        ];
    }

    public function muzakkiDetail(string $fromDate, string $toDate): array
    {
        $from = $this->parseIsoDate($fromDate, 'fromDate');
        $to = $this->parseIsoDate($toDate, 'toDate');

        if ($to < $from) {
            throw new RuntimeException('toDate tidak boleh lebih kecil dari fromDate');
        }

        $fromInclusive = $from->format('Y-m-d') . ' 00:00:00';
        $toExclusive = $to->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $rawRows = $this->muzakki->findReportRows($fromInclusive, $toExclusive);
        $rows = [];
        $paymentSeen = [];
        $totalNominal = 0.0;
        $totalBeras = 0.0;
        $totalJiwa = 0;
        $counter = 0;

        foreach ($rawRows as $row) {
            $counter++;
            $jumlahJiwa = isset($row['jumlah_jiwa']) ? (int) $row['jumlah_jiwa'] : 0;
            $jumlahUang = $row['jumlah_uang'] !== null ? (float) $row['jumlah_uang'] : null;
            $berasKg = $row['berat_beras_kg'] !== null ? (float) $row['berat_beras_kg'] : null;

            $nominalPerOrang = $this->perOrang($jumlahUang, $jumlahJiwa, 0);
            $berasPerOrang = $this->perOrang($berasKg, $jumlahJiwa, 2);
            $zakatType = $row['zakat_type'] !== null ? (string) $row['zakat_type'] : null;

            $rows[] = [
                'no' => $counter,
                'tanggal' => isset($row['payment_at']) && is_string($row['payment_at']) ? substr($row['payment_at'], 0, 10) : null,
                'namaMuzakki' => (string) ($row['nama'] ?? ''),
                'zakatType' => $zakatType,
                'zakatTypeLabel' => $zakatType !== null ? ($this->zisTypeLabel($zakatType) ?? $zakatType) : null,
                'nominalRp' => $nominalPerOrang,
                'berasKg' => $berasPerOrang,
            ];

            $paymentId = (string) ($row['payment_id'] ?? '');
            if ($paymentId !== '' && !isset($paymentSeen[$paymentId])) {
                $paymentSeen[$paymentId] = true;
                $totalNominal += $jumlahUang ?? 0.0;
                $totalBeras += $berasKg ?? 0.0;
                $totalJiwa += max(0, $jumlahJiwa);
            }
        }

        $profile = $this->profiles->first();

        return [
            'fromDate' => $from->format('Y-m-d'),
            'toDate' => $to->format('Y-m-d'),
            'rows' => $rows,
            'totalNominalRp' => $totalNominal,
            'totalBerasKg' => $totalBeras,
            'totalJiwa' => $totalJiwa,
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
        ];
    }

    public function muzakkiDetailCsv(array $report): string
    {
        $lines = [];
        $lines[] = 'Periode,' . $this->csvValue($report['fromDate'] ?? '') . ',' . $this->csvValue($report['toDate'] ?? '');

        $profile = $report['institutionProfile'] ?? null;
        if (is_array($profile)) {
            $lines[] = 'Instansi,' . $this->csvValue($profile['namaInstansi'] ?? '');
            $lines[] = 'Kota/Kabupaten,' . $this->csvValue($profile['kotaKabupaten'] ?? '');
            $lines[] = 'Alamat,' . $this->csvValue($profile['alamatLengkap'] ?? '');
            $lines[] = '';
        }

        $lines[] = 'No,Tanggal,Nama Muzakki,Jenis,Nominal (Rp),Beras (Kg)';
        foreach (($report['rows'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $lines[] = implode(',', [
                (string) ($row['no'] ?? ''),
                $this->csvValue($row['tanggal'] ?? ''),
                $this->csvValue($row['namaMuzakki'] ?? ''),
                $this->csvValue(($row['zakatTypeLabel'] ?? '') !== '' ? $row['zakatTypeLabel'] : ($row['zakatType'] ?? '')),
                $row['nominalRp'] === null ? '' : (string) $row['nominalRp'],
                $row['berasKg'] === null ? '' : (string) $row['berasKg'],
            ]);
        }

        $lines[] = '';
        $lines[] = 'TOTAL,,,';
        $lines[] = 'total_nominal_rp,' . (string) ($report['totalNominalRp'] ?? 0);
        $lines[] = 'total_beras_kg,' . (string) ($report['totalBerasKg'] ?? 0);
        $lines[] = 'total_jiwa,' . (string) ($report['totalJiwa'] ?? 0);

        return implode("\n", $lines) . "\n";
    }

    public function kwitansi(string $paymentId): array
    {
        $payment = $this->payments->findById(trim($paymentId));
        if (!is_array($payment)) {
            throw new RuntimeException('Payment tidak ditemukan');
        }

        $profile = $this->profiles->first();
        $paymentAt = $payment['payment_at'] ?? null;
        $tanggal = null;
        if (is_string($paymentAt) && $paymentAt !== '') {
            $tanggal = substr($paymentAt, 0, 10);
        }

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
            'paymentId' => (string) $payment['id'],
            'receiptNumber' => $payment['receipt_number'] !== null ? (string) $payment['receipt_number'] : null,
            'paymentAt' => $paymentAt !== null ? str_replace(' ', 'T', (string) $paymentAt) : null,
            'tanggal' => $tanggal,
            'zakatType' => $zakatType,
            'zakatTypeLabel' => $zakatType !== null ? ($labels[$zakatType] ?? $zakatType) : null,
            'jumlahJiwa' => $payment['jumlah_jiwa'] !== null ? (int) $payment['jumlah_jiwa'] : null,
            'alamat' => $payment['alamat'] !== null ? (string) $payment['alamat'] : null,
            'receivedByName' => $payment['received_by_name'] !== null ? (string) $payment['received_by_name'] : null,
            'nominalRp' => $payment['jumlah_uang'] !== null ? (float) $payment['jumlah_uang'] : null,
            'berasKg' => $payment['berat_beras_kg'] !== null ? (float) $payment['berat_beras_kg'] : null,
            'zakatQuality' => !empty($payment['quality_id']) ? [
                'id' => (string) $payment['quality_id'],
                'name' => $payment['quality_name'] !== null ? (string) $payment['quality_name'] : null,
                'beratPerJiwaKg' => $payment['quality_berat_per_jiwa_kg'] !== null ? (float) $payment['quality_berat_per_jiwa_kg'] : null,
                'nominalPerJiwa' => $payment['quality_nominal_per_jiwa'] !== null ? (int) $payment['quality_nominal_per_jiwa'] : null,
            ] : null,
            'muzakkiCount' => is_array($payment['muzakki_names'] ?? null) ? count($payment['muzakki_names']) : 0,
            'muzakkiNames' => is_array($payment['muzakki_names'] ?? null) ? array_values($payment['muzakki_names']) : [],
            'institutionProfile' => is_array($profile) ? [
                'id' => (string) $profile['id'],
                'namaInstansi' => (string) $profile['nama_instansi'],
                'kotaKabupaten' => (string) $profile['kota_kabupaten'],
                'alamatLengkap' => (string) $profile['alamat_lengkap'],
                'nomorTelepon' => $profile['nomor_telepon'] !== null ? (string) $profile['nomor_telepon'] : null,
                'email' => $profile['email'] !== null ? (string) $profile['email'] : null,
                'namaKetua' => $profile['nama_ketua'] !== null ? (string) $profile['nama_ketua'] : null,
                'namaBendahara' => $profile['nama_bendahara'] !== null ? (string) $profile['nama_bendahara'] : null,
            ] : null,
        ];
    }

    public function kwitansiTemplatePdf(string $paymentId): string
    {
        $kw = $this->kwitansi($paymentId);
        $payment = $this->payments->findById($paymentId);
        if (!is_array($payment)) {
            throw new RuntimeException('Payment tidak ditemukan');
        }

        $templatePath = base_path('assets/pdf/form_zakat_v3_compat.pdf');
        if (!is_file($templatePath)) {
            throw new RuntimeException('Template PDF form_zakat_v3_compat.pdf tidak ditemukan');
        }

        $jiwa = (int) ($payment['jumlah_jiwa'] ?? 0);
        $jumlahUang = $payment['jumlah_uang'] !== null ? (float) $payment['jumlah_uang'] : null;
        $perJiwa = null;
        if ($jiwa > 0 && $jumlahUang !== null) {
            $perJiwa = (int) round($jumlahUang / $jiwa);
        } elseif (!empty($kw['zakatQuality']['nominalPerJiwa'])) {
            $perJiwa = (int) $kw['zakatQuality']['nominalPerJiwa'];
        }

        $tanggal = (string) ($kw['tanggal'] ?? '');
        $dd = '';
        $mm = '';
        $yyyy = '';
        if ($tanggal !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $matches) === 1) {
            $yyyy = $matches[1];
            $mm = $matches[2];
            $dd = $matches[3];
        }

        $fields = [
            'nama' => $this->safe((string) ($payment['payer_name'] ?? '')),
            'noTelp' => $this->safe((string) ($payment['payer_phone'] ?? '')),
            'noKwitansi' => $this->safe((string) ($kw['receiptNumber'] ?? '')),
            'alamat' => $this->safe((string) ($payment['alamat'] ?? '')),
            'jumlahBeras' => $this->formatNumber($payment['berat_beras_kg'] ?? null),
            'jumlahUang' => $this->formatCurrency($perJiwa),
            'jumlahJiwaBeras' => ((float) ($payment['berat_beras_kg'] ?? 0) > 0) ? (string) $jiwa : '',
            'jumlahJiwaUang' => ((float) ($payment['jumlah_uang'] ?? 0) > 0) ? (string) $jiwa : '',
            'jumlahZakatMal' => $this->formatCurrency($payment['jumlah_uang_zakat_mal'] ?? null),
            'jumlahInfaqSedekah' => $this->formatCurrency($payment['jumlah_uang_infaq_sedekah'] ?? null),
            'jumlahFidiah' => $this->formatCurrency($payment['jumlah_uang_fidiah'] ?? null),
            'dd' => $dd,
            'MM' => $mm,
            'yyyy' => $yyyy,
            'totalBeras' => $this->formatNumber($payment['berat_beras_kg'] ?? null),
            'totalUang' => $this->formatCurrency($payment['jumlah_uang'] ?? null),
        ];

        $names = is_array($kw['muzakkiNames'] ?? null) ? $kw['muzakkiNames'] : [];
        for ($i = 1; $i <= 10; $i++) {
            $fields['muzakki' . $i] = $i <= count($names) ? (string) $names[$i - 1] : '';
        }

        $checkboxes = [
            'checkboxCash' => (string) ($payment['payment_method'] ?? '') !== 'TRANSFER',
            'checkboxTransfer' => (string) ($payment['payment_method'] ?? '') === 'TRANSFER',
        ];

        return $this->renderPdfTemplate($templatePath, $fields, $checkboxes, self::PDF_FIELD_RECTS);
    }

    public function qurbanSapiTemplatePdf(string $qurbanId): string
    {
        $qurban = $this->qurban->findById(trim($qurbanId));
        if (!is_array($qurban)) {
            throw new RuntimeException('Data qurban tidak ditemukan');
        }

        $animalType = (string) ($qurban['animal_type'] ?? '');

        $submissionDate = (string) ($qurban['submission_date'] ?? '');
        $tanggal = '';
        $bulan = '';
        $tahun = '';
        if ($submissionDate !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $submissionDate, $matches) === 1) {
            $tahun = $matches[1];
            $bulan = $matches[2];
            $tanggal = $matches[3];
        }

        if ($animalType === 'KAMBING') {
            $templatePath = base_path('assets/pdf/form_kambing_template_v2.pdf');
            if (!is_file($templatePath)) {
                throw new RuntimeException('Template PDF form_kambing_template_v2.pdf tidak ditemukan');
            }

            $fields = [
                'nama' => $this->safe((string) ($qurban['payer_name'] ?? '')),
                'alamat' => $this->safe((string) ($qurban['alamat'] ?? '')),
                'telepon' => $this->safe((string) ($qurban['payer_phone'] ?? '')),
                'notes' => $this->safe((string) ($qurban['notes'] ?? '')),
                'biayaKambing' => $this->formatCurrency($qurban['biaya_pemeliharaan'] ?? null),
                'infak' => $this->formatCurrency($qurban['shodaqoh_infak'] ?? null),
                'biayaSupplier' => $this->formatCurrency($qurban['biaya_supplier'] ?? null),
                'waktuPengambilan' => $this->safe((string) ($qurban['pickup_time_notes'] ?? '')),
                'noPanitia' => $this->safe((string) ($qurban['committee_phone'] ?? '')),
                'tanggal' => $tanggal,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nomorKurban' => $this->safe((string) ($qurban['qurban_number'] ?? '')),
            ];

            $checkboxes = [
                'isSembelihJagal' => (string) ($qurban['slaughter_mode'] ?? '') === 'JAGAL',
                'isSembelihSendiri' => (string) ($qurban['slaughter_mode'] ?? '') === 'SENDIRI',
            ];

            return $this->renderPdfTemplate($templatePath, $fields, $checkboxes, self::QURBAN_KAMBING_FIELD_RECTS);
        }

        if (!in_array($animalType, ['SAPI', 'SAPI_KOLEKTIF'], true)) {
            throw new RuntimeException('Template kwitansi qurban belum tersedia untuk jenis hewan ini.');
        }

        $templatePath = base_path('assets/pdf/form_qurban_sapi_v2_template.pdf');
        if (!is_file($templatePath)) {
            throw new RuntimeException('Template PDF form_qurban_sapi_v2_template.pdf tidak ditemukan');
        }

        $participants = is_array($qurban['participants'] ?? null) ? array_values($qurban['participants']) : [];
        if ($animalType === 'SAPI' && $participants === []) {
            $participants[] = (string) ($qurban['payer_name'] ?? '');
        }

        $fields = [
            'nama' => $this->safe((string) ($qurban['payer_name'] ?? '')),
            'alamat' => $this->safe((string) ($qurban['alamat'] ?? '')),
            'telp' => $this->safe((string) ($qurban['payer_phone'] ?? '')),
            'biayaSapi' => $this->formatCurrency($qurban['biaya_pemeliharaan'] ?? null),
            'infak' => $this->formatCurrency($qurban['shodaqoh_infak'] ?? null),
            'biayaSupplier' => $this->formatCurrency($qurban['biaya_supplier'] ?? null),
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'notes' => $this->safe((string) ($qurban['notes'] ?? '')),
            'nomorKurban' => $this->safe((string) ($qurban['qurban_number'] ?? '')),
        ];

        for ($i = 1; $i <= 7; $i++) {
            $fields['peserta' . $i] = isset($participants[$i - 1]) ? $this->safe((string) $participants[$i - 1]) : '';
        }

        $checkboxes = [
            'isSembelihJagal' => (string) ($qurban['slaughter_mode'] ?? '') === 'JAGAL',
            'isSembelihSendiri' => (string) ($qurban['slaughter_mode'] ?? '') === 'SENDIRI',
        ];

        return $this->renderPdfTemplate($templatePath, $fields, $checkboxes, self::QURBAN_SAPI_FIELD_RECTS);
    }

    private function renderPdfTemplate(string $templatePath, array $fields, array $checkboxes, array $fieldRects): string
    {
        $pdf = new Fpdi('P', 'pt');
        $pageCount = $pdf->setSourceFile($templatePath);
        if ($pageCount < 1) {
            throw new RuntimeException('Template PDF tidak dapat dibaca');
        }

        $templateId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($templateId);
        $orientation = (($size['width'] ?? 0) > ($size['height'] ?? 0)) ? 'L' : 'P';

        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);

        $this->drawPdfFields($pdf, $size['height'], $fields, $checkboxes, $fieldRects);

        return $pdf->Output('S');
    }

    private function drawPdfFields(Fpdi $pdf, float $pageHeight, array $fields, array $checkboxes, array $fieldRects): void
    {
        $isQurbanSapiTemplate = $fieldRects === self::QURBAN_SAPI_FIELD_RECTS;
        $isQurbanKambingTemplate = $fieldRects === self::QURBAN_KAMBING_FIELD_RECTS;

        foreach ($fields as $name => $value) {
            if (!isset($fieldRects[$name])) {
                continue;
            }

            [$x1, $y1, $x2, $y2] = $fieldRects[$name];
            $fontSize = match (true) {
                $isQurbanSapiTemplate && $name === 'nomorKurban' => 76,
                $isQurbanKambingTemplate && $name === 'nomorKurban' => 150,
                $isQurbanSapiTemplate => 11,
                $isQurbanKambingTemplate => 11,
                $name === 'noKwitansi' => 10,
                default => 9,
            };

            $rectTop = $pageHeight - $y2;
            $rectHeight = max(0, $y2 - $y1);
            $textY = $rectTop + max(0, ($rectHeight - $fontSize) / 2);
            if ($isQurbanKambingTemplate && in_array($name, ['bulan', 'tahun'], true)) {
                $textY = $rectTop;
            }

            $fontFamily = $name === 'nomorKurban' ? 'Helvetica' : 'Times';
            $fontStyle = match (true) {
                $name === 'nomorKurban' => 'B',
                $isQurbanKambingTemplate && in_array($name, ['waktuPengambilan', 'noPanitia'], true) => 'B',
                default => '',
            };
            $align = match (true) {
                $name === 'nomorKurban' => 'C',
                $isQurbanKambingTemplate && in_array($name, ['tanggal', 'bulan', 'tahun'], true) => 'C',
                in_array($name, ['biayaSapi', 'biayaKambing', 'infak', 'biayaSupplier'], true) => 'R',
                default => 'L',
            };

            $pdf->SetFont($fontFamily, $fontStyle, $fontSize);
            if ($isQurbanSapiTemplate && $name === 'nomorKurban') {
                $pdf->SetTextColor(255, 255, 255);
            } else {
                $pdf->SetTextColor(0, 0, 0);
            }

            $paddingX = $name === 'nomorKurban' ? 0 : 1;
            $pdf->SetXY($x1 + $paddingX, $textY);
            $pdf->Cell(max(0, $x2 - $x1 - 2), $fontSize + 2, $this->encodePdfText((string) $value), 0, 0, $align);
        }

        foreach ($checkboxes as $name => $checked) {
            if (!$checked || !isset($fieldRects[$name])) {
                continue;
            }

            [$x1, $y1, $x2, $y2] = $fieldRects[$name];
            $fontSize = 12;
            $baseline = $pageHeight - $y2 + ($fontSize + 1);

            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($x1 + 1, $baseline - $fontSize);
            $pdf->Cell(max(0, $x2 - $x1), $fontSize + 1, 'X', 0, 0, 'C');
        }
    }

    private function formatCurrency($value): string
    {
        if ($value === null || (float) $value <= 0) {
            return '';
        }

        return number_format((float) $value, 0, ',', '.');
    }

    private function formatNumber($value): string
    {
        if ($value === null || (float) $value <= 0) {
            return '';
        }

        $formatted = number_format((float) $value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function safe(string $value): string
    {
        return trim($value);
    }

    private function perOrang(?float $total, int $jumlahJiwa, int $scale): ?float
    {
        if ($total === null) {
            return null;
        }

        if ($jumlahJiwa <= 0) {
            return $total;
        }

        $divided = $total / $jumlahJiwa;
        return $scale === 0 ? round($divided, 0) : round($divided, $scale);
    }

    private function zisTypeLabel(string $type): ?string
    {
        $labels = [
            'ZAKAT_FITRAH_BERAS' => 'Zakat Fitrah (Beras)',
            'ZAKAT_FITRAH_UANG' => 'Zakat Fitrah (Uang)',
            'ZAKAT_MAL' => 'Zakat Mal',
            'INFAQ_SEDEKAH' => 'Infaq/Sedekah',
            'FIDIAH' => 'Fidiah',
        ];

        return $labels[$type] ?? null;
    }

    private function csvValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $string = (string) $value;
        $mustQuote = str_contains($string, ',')
            || str_contains($string, '"')
            || str_contains($string, "\n")
            || str_contains($string, "\r");

        if (!$mustQuote) {
            return $string;
        }

        return '"' . str_replace('"', '""', $string) . '"';
    }

    private function parseIsoDate(string $value, string $field): DateTimeImmutable
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new RuntimeException($field . ' wajib diisi');
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $trimmed);
        if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $trimmed) {
            throw new RuntimeException('Format tanggal tidak valid');
        }

        return $date;
    }

    private function encodePdfText(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $trimmed);

        return $converted === false ? $trimmed : $converted;
    }

}
