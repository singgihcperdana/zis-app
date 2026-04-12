<?php

declare(strict_types=1);

ob_start();
?>
<style>
    .report-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
        height: 100%;
        padding-bottom: 1rem;
    }

    .report-filter-actions .btn {
        margin-right: 0 !important;
    }

    @media (max-width: 767.98px) {
        .report-filter-actions {
            padding-bottom: 0;
        }
    }

    @media print {
        html, body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
        }

        .no-print { display: none !important; }
        .main-header, .main-sidebar, .main-footer, .control-sidebar { display: none !important; }
        .content-wrapper { background: #fff !important; }
        .content-wrapper { margin-left: 0 !important; }
        .card { box-shadow: none !important; border: none !important; }
        .card-body { padding: 0 !important; }

        .report-rekap-page {
            --rekap-print-font-size: 14pt;
        }

        .report-rekap-page,
        .report-rekap-page * {
            font-family: "Times New Roman", Times, serif !important;
        }

        .report-rekap-page .report-title,
        .report-rekap-page .report-title * {
            font-size: 17pt !important;
            line-height: 1.2 !important;
        }

        .report-rekap-page .report-meta,
        .report-rekap-page .report-meta *,
        .report-rekap-page #rekapSummary,
        .report-rekap-page #rekapSummary *,
        .report-rekap-page #ttdBox,
        .report-rekap-page #ttdBox *,
        .report-rekap-page #ttdBox .signature-name::before {
            font-size: var(--rekap-print-font-size) !important;
            line-height: 1.35 !important;
        }

        .report-rekap-page #rekapTable,
        .report-rekap-page #rekapTable *,
        .report-rekap-page #rekapTable th,
        .report-rekap-page #rekapTable td {
            font-size: var(--rekap-print-font-size) !important;
            line-height: 1.35 !important;
        }

        .report-rekap-page #rekapTable th,
        .report-rekap-page #rekapTable td {
            padding: 6px 8px !important;
        }
    }
</style>

<div class="report-rekap-page">
    <div class="content-header no-print">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Rekap ZIS</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Rekap ZIS</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card no-print">
                <div class="card-header">
                    <h3 class="card-title">Filter</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterFrom">Dari</label>
                                <input class="form-control" id="filterFrom" type="date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterTo">Sampai</label>
                                <input class="form-control" id="filterTo" type="date">
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="report-filter-actions">
                                <button class="btn btn-primary" id="btnApply" type="button">Tampilkan</button>
                                <button class="btn btn-default" id="btnReset" type="button">Reset</button>
                                <button class="btn btn-success" id="btnPrint" type="button">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="instansiHeader" class="d-none report-letterhead">
                        <div class="letterhead-brand">
                            <div class="letterhead-text">
                                <div class="instansi-name" id="instansiNama">-</div>
                                <div class="instansi-address" id="instansiAlamat">-</div>
                                <div class="instansi-contact" id="instansiKontak">-</div>
                            </div>
                            <div class="line-double"></div>
                        </div>
                    </div>

                    <div class="text-center mb-3 report-section-title">
                        <h4 class="mb-0 report-title">
                            <span class="report-title-underline">REKAPITULASI PENERIMAAN ZIS</span>
                        </h4>
                        <div class="text-muted report-meta" id="periodeLabel">Periode: -</div>
                    </div>

                    <table class="table table-bordered" id="rekapTable">
                        <thead>
                        <tr>
                            <th>Ringkasan Penerimaan per Jenis (Total)</th>
                            <th style="width: 240px">Jumlah</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Total Zakat Fitrah (Beras)</td>
                            <td class="text-right" id="valFitrahBeras">-</td>
                        </tr>
                        <tr>
                            <td>Total Zakat Fitrah (Uang)</td>
                            <td class="text-right" id="valFitrahUang">-</td>
                        </tr>
                        <tr>
                            <td>Total Fidiah</td>
                            <td class="text-right" id="valFidiah">-</td>
                        </tr>
                        <tr>
                            <td>Total Zakat Mal</td>
                            <td class="text-right" id="valZakatMal">-</td>
                        </tr>
                        <tr>
                            <td>Total Infaq/Sedekah</td>
                            <td class="text-right" id="valInfaq">-</td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Subtotal Beras</strong></td>
                            <td class="text-right"><strong id="valSubtotalBeras">-</strong></td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Subtotal Uang</strong></td>
                            <td class="text-right"><strong id="valSubtotalUang">-</strong></td>
                        </tr>
                        <tr class="table-secondary">
                            <td><strong>TOTAL UANG MASUK</strong></td>
                            <td class="text-right"><strong id="valTotalUang">-</strong></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="mt-3 report-summary" id="rekapSummary">
                        <div><strong>Total Muzakki (Fitrah):</strong> <span id="valTotalJiwa">-</span> Jiwa</div>
                    </div>

                    <hr>

                    <div id="ttdBox" class="d-none">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <div class="mb-4" id="tanggalTtd">-</div>
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <div>Mengetahui,</div>
                                        <div>Ketua Panitia</div>
                                        <div class="signature-spacer"></div>
                                        <div class="signature-name"><strong id="namaKetua">-</strong></div>
                                    </div>
                                    <div class="col-6 text-center">
                                        <div>&nbsp;</div>
                                        <div>Bendahara</div>
                                        <div class="signature-spacer"></div>
                                        <div class="signature-name"><strong id="namaBendahara">-</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    window.addEventListener('load', function () {
        (function () {
            const $from = $("#filterFrom");
            const $to = $("#filterTo");
            const $btnApply = $("#btnApply");
            const $btnReset = $("#btnReset");
            const $btnPrint = $("#btnPrint");

            const $periodeLabel = $("#periodeLabel");
            const $valFitrahUang = $("#valFitrahUang");
            const $valFitrahBeras = $("#valFitrahBeras");
            const $valFidiah = $("#valFidiah");
            const $valZakatMal = $("#valZakatMal");
            const $valInfaq = $("#valInfaq");
            const $valSubtotalBeras = $("#valSubtotalBeras");
            const $valSubtotalUang = $("#valSubtotalUang");
            const $valTotalUang = $("#valTotalUang");
            const $valTotalJiwa = $("#valTotalJiwa");

            const $instansiHeader = $("#instansiHeader");
            const $ttdBox = $("#ttdBox");
            const $instansiNama = $("#instansiNama");
            const $instansiAlamat = $("#instansiAlamat");
            const $instansiKontak = $("#instansiKontak");
            const $tanggalTtd = $("#tanggalTtd");
            const $namaKetua = $("#namaKetua");
            const $namaBendahara = $("#namaBendahara");

            function formatRupiah(v) {
                const n = Number(v ?? 0);
                return `Rp ${new Intl.NumberFormat("id-ID").format(n)}`;
            }

            function formatKg(v) {
                const n = Number(v ?? 0);
                return `${new Intl.NumberFormat("id-ID", {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(n)} Kg`;
            }

            function toLocalIsoDate(d = new Date()) {
                const x = new Date(d);
                const y = x.getFullYear();
                const m = String(x.getMonth() + 1).padStart(2, "0");
                const day = String(x.getDate()).padStart(2, "0");
                return `${y}-${m}-${day}`;
            }

            function todayIso() {
                return toLocalIsoDate(new Date());
            }

            function startOfMonthIso(d = new Date()) {
                const x = new Date(d);
                x.setDate(1);
                return toLocalIsoDate(x);
            }

            function formatIsoDateDisplay(iso) {
                if (!iso) return "-";
                const parts = String(iso).split("-");
                if (parts.length !== 3) return iso;
                const [yyyy, mm, dd] = parts;
                return `${dd}/${mm}/${yyyy}`;
            }

            function formatKontak(prof) {
                const telp = (prof?.nomorTelepon || "").trim();
                const email = (prof?.email || "").trim();
                if (!telp && !email) return "-";
                if (telp && email) return `Telp: ${telp}, Email: ${email}`;
                if (telp) return `Telp: ${telp}`;
                return `Email: ${email}`;
            }

            async function load() {
                const from = $from.val();
                const to = $to.val();

                if (!from || !to) {
                    return;
                }

                $periodeLabel.text(`Periode: ${formatIsoDateDisplay(from)} s/d ${formatIsoDateDisplay(to)}`);

                const res = await fetch(`/api/reports/rekap-zis?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`, {
                    headers: {"Accept": "application/json"}
                });

                if (!res.ok) {
                    let message = `Gagal load (${res.status})`;

                    try {
                        const data = await res.json();
                        if (data && data.message) {
                            message = data.message;
                        }
                    } catch (e) {
                        const text = await res.text();
                        if (text) {
                            message = text;
                        }
                    }

                    alert(message);
                    return;
                }

                const data = await res.json();
                const fitrahUang = Number(data.zakatFitrahUang ?? 0);
                const fitrahBeras = Number(data.zakatFitrahBerasKg ?? 0);
                const fidiah = Number(data.fidiah ?? 0);
                const zakatMal = Number(data.zakatMal ?? 0);
                const infaqSedekah = Number(data.infaqSedekah ?? 0);
                const subtotalUang = fitrahUang + fidiah + zakatMal + infaqSedekah;
                const subtotalBeras = fitrahBeras;

                $valFitrahBeras.text(formatKg(fitrahBeras));
                $valFitrahUang.text(formatRupiah(fitrahUang));
                $valFidiah.text(formatRupiah(fidiah));
                $valZakatMal.text(formatRupiah(zakatMal));
                $valInfaq.text(formatRupiah(infaqSedekah));
                $valSubtotalBeras.text(formatKg(subtotalBeras));
                $valSubtotalUang.text(formatRupiah(subtotalUang));
                $valTotalUang.text(formatRupiah(data.totalUangMasuk));
                $valTotalJiwa.text(String(data.totalMuzakkiFitrahJiwa ?? 0));

                const prof = data.institutionProfile;
                if (prof) {
                    $instansiHeader.removeClass("d-none");
                    $ttdBox.removeClass("d-none");

                    const namaInstansi = (prof.namaInstansi || "").trim();
                    $instansiNama.text(namaInstansi
                        ? `PANITIA AMIL ZAKAT, INFAQ, DAN SHODAQOH ${namaInstansi}`
                        : "PANITIA AMIL ZAKAT, INFAQ, DAN SHODAQOH");

                    const alamat = prof.alamatLengkap || "-";
                    const kota = prof.kotaKabupaten ? `, ${prof.kotaKabupaten}` : "";
                    $instansiAlamat.text(`${alamat}${kota}`);
                    $instansiKontak.text(formatKontak(prof));

                    const ttd = new Intl.DateTimeFormat("id-ID", {dateStyle: "long"}).format(new Date());
                    $tanggalTtd.text(`${prof.kotaKabupaten || "-"}, ${ttd}`);
                    $namaKetua.text(prof.namaKetua || "-");
                    $namaBendahara.text(prof.namaBendahara || "-");
                } else {
                    $instansiHeader.addClass("d-none");
                    $ttdBox.addClass("d-none");
                    $instansiKontak.text("-");
                }
            }

            $btnApply.on("click", load);
            $btnReset.on("click", function () {
                const iso = todayIso();
                $from.val(startOfMonthIso(new Date()));
                $to.val(iso);
                load();
            });
            $btnPrint.on("click", function () {
                window.print();
            });

            const iso = todayIso();
            $from.val(startOfMonthIso(new Date()));
            $to.val(iso);
            load();
        })();
    });
</script>
<?php
$content = ob_get_clean();

require base_path('app/Views/layouts/admin.php');
