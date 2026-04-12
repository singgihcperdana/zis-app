<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\InstitutionProfileRepository;
use App\Repositories\ZakatPaymentRepository;
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

    private ZakatPaymentRepository $payments;
    private InstitutionProfileRepository $profiles;

    public function __construct(
        ?ZakatPaymentRepository $payments = null,
        ?InstitutionProfileRepository $profiles = null
    ) {
        $this->payments = $payments ?? new ZakatPaymentRepository();
        $this->profiles = $profiles ?? new InstitutionProfileRepository();
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

        return $this->renderPdfTemplate($templatePath, $fields, $checkboxes);
    }

    private function renderPdfTemplate(string $templatePath, array $fields, array $checkboxes): string
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

        $this->drawPdfFields($pdf, $size['height'], $fields, $checkboxes);

        return $pdf->Output('S');
    }

    private function drawPdfFields(Fpdi $pdf, float $pageHeight, array $fields, array $checkboxes): void
    {
        foreach ($fields as $name => $value) {
            if (!isset(self::PDF_FIELD_RECTS[$name])) {
                continue;
            }

            [$x1, $y1, $x2, $y2] = self::PDF_FIELD_RECTS[$name];
            $fontSize = $name === 'noKwitansi' ? 10 : 9;
            $baseline = $pageHeight - $y2 + ($fontSize + 2);

            $pdf->SetFont('Times', '', $fontSize);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($x1 + 1, $baseline - $fontSize);
            $pdf->Cell(max(0, $x2 - $x1 - 2), $fontSize + 2, $this->encodePdfText((string) $value), 0, 0, 'L');
        }

        foreach ($checkboxes as $name => $checked) {
            if (!$checked || !isset(self::PDF_FIELD_RECTS[$name])) {
                continue;
            }

            [$x1, $y1, $x2, $y2] = self::PDF_FIELD_RECTS[$name];
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

        return number_format((float) $value, 0, '', '');
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
