<?php

$title = $title ?? 'Riwayat Pembayaran';
$canMutate = in_array((string) ($user['role'] ?? ''), ['ADMIN', 'OPERATOR'], true);

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Riwayat Pembayaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Riwayat Pembayaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Payment</h3>
                <div class="card-tools">
                    <?php if ($canMutate): ?>
                        <a class="btn btn-sm btn-primary" href="/zakat-payments/new">
                            <i class="fas fa-plus"></i> Input Baru
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-default" id="btnRefresh" type="button">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterFrom">Dari Tanggal</label>
                            <input class="form-control" id="filterFrom" type="date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterTo">Sampai Tanggal</label>
                            <input class="form-control" id="filterTo" type="date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterQ">Cari (nama/alamat)</label>
                            <input class="form-control" id="filterQ" placeholder="Ketik nama muzakki atau alamat..." type="text">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" id="filterIncludeCanceled" type="checkbox">
                                <label class="custom-control-label" for="filterIncludeCanceled">Termasuk batal</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterPayerName">Nama Pembayar</label>
                            <input class="form-control" id="filterPayerName" placeholder="Nama pembayar..." type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterPayerPhone">No. HP Pembayar</label>
                            <input class="form-control" id="filterPayerPhone" placeholder="08..." type="text">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="btnApply" type="button">Terapkan</button>
                        <button class="btn btn-default" id="btnReset" type="button">Reset</button>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                    <tr>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="receiptNumber" type="button">Kwitansi <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="paymentAt" type="button">Tanggal Pembayaran <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="jumlahUang" type="button">Fitrah Uang (Rp) <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="beratBerasKg" type="button">Fitrah Beras (Kg) <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="jumlahUangZakatMal" type="button">Zakat Mal (Rp) <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="jumlahUangInfaqSedekah" type="button">Infaq/Sedekah (Rp) <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="jumlahUangFidiah" type="button">Fidiah (Rp) <i class="fas fa-sort ml-1"></i></button></th>
                        <th>Metode</th>
                        <th>Pembayar</th>
                        <th>Diterima Oleh</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Nominal</th>
                        <th>Muzakki</th>
                        <th>Preview</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody id="paymentRows">
                    <tr>
                        <td class="text-center text-muted" colspan="16">Memuat...</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <div class="float-left text-muted" id="pageInfo"></div>
                <ul class="pagination pagination-sm m-0 float-right">
                    <li class="page-item"><a class="page-link" href="#" id="btnPrev">&laquo;</a></li>
                    <li class="page-item"><a class="page-link" href="#" id="btnNext">&raquo;</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script>
    const CAN_MUTATE = <?= $canMutate ? 'true' : 'false'; ?>;

    window.addEventListener('load', function () {
        (function () {
            const $rows = $('#paymentRows');
            const $pageInfo = $('#pageInfo');
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $btnRefresh = $('#btnRefresh');
            const $btnApply = $('#btnApply');
            const $btnReset = $('#btnReset');
            const $filterFrom = $('#filterFrom');
            const $filterTo = $('#filterTo');
            const $filterQ = $('#filterQ');
            const $filterIncludeCanceled = $('#filterIncludeCanceled');
            const $filterPayerName = $('#filterPayerName');
            const $filterPayerPhone = $('#filterPayerPhone');
            const $sortButtons = $('.sort-btn');

            let page = 0;
            const size = 20;
            let sortKey = 'paymentAt';
            let sortDir = 'desc';

            function sortIconClass(key) {
                if (key !== sortKey) return 'fas fa-sort ml-1';
                return sortDir === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';
            }

            function refreshSortButtons() {
                $sortButtons.each(function () {
                    const key = $(this).data('sort-key');
                    const active = key === sortKey;
                    $(this).toggleClass('text-primary', active).toggleClass('text-dark', !active);
                    $(this).find('i').attr('class', sortIconClass(key));
                });
            }

            function formatInstant(iso) {
                if (!iso) return '-';
                const d = new Date(iso);
                if (Number.isNaN(d.getTime())) return iso;
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).format(d);
            }

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function toLocalIsoDate(d) {
                const x = new Date(d);
                const y = x.getFullYear();
                const m = String(x.getMonth() + 1).padStart(2, '0');
                const day = String(x.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + day;
            }

            function startOfMonthIso(d) {
                const x = new Date(d);
                x.setDate(1);
                return toLocalIsoDate(x);
            }

            function render(data) {
                const content = data && data.content ? data.content : [];
                $rows.empty();
                if (!content.length) {
                    $rows.append('<tr><td colspan="16" class="text-center text-muted">Tidak ada data</td></tr>');
                } else {
                    for (const item of content) {
                        const canceledBadge = item.canceled ? '<span class="badge badge-secondary">BATAL</span>' : '';
                        const receipt = item.receiptNumber ? '<code>' + escapeHtml(item.receiptNumber) + '</code>' : '-';
                        const alamat = item.alamat ? escapeHtml(item.alamat) : '-';
                        const payer = item.payerName ? escapeHtml(item.payerName) : '-';
                        const receivedBy = item.receivedByName ? escapeHtml(item.receivedByName) : '-';
                        const payerPhone = item.payerPhone ? escapeHtml(item.payerPhone) : '-';
                        const paymentMethod = item.paymentMethod === 'TRANSFER' ? 'Transfer' : (item.paymentMethod === 'CASH' ? 'Cash' : '-');
                        const printHtml = '<button class="btn btn-xs btn-success mr-1" type="button" disabled><i class="fas fa-print"></i> Kwitansi</button>';
                        const editHtml = '<a class="btn btn-xs btn-info mr-1" href="/zakat-payments/' + encodeURIComponent(item.id) + '/edit"><i class="fas fa-edit"></i> Edit</a>';
                        const actionHtml = (!CAN_MUTATE || item.canceled)
                            ? printHtml
                            : editHtml + printHtml + '<button class="btn btn-xs btn-danger btn-cancel" data-id="' + escapeHtml(item.id) + '" type="button"><i class="fas fa-ban"></i> Batalkan</button>';

                        const fmtMoney = function (v) {
                            return v == null ? '-' : 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
                        };
                        const fmtKg = function (v) {
                            return v == null ? '-' : v + ' Kg';
                        };
                        const totalNominal = ((item.jumlahUang ?? 0) + (item.jumlahUangZakatMal ?? 0) + (item.jumlahUangInfaqSedekah ?? 0) + (item.jumlahUangFidiah ?? 0));
                        const nominalDisplay = totalNominal > 0
                            ? 'Rp ' + new Intl.NumberFormat('id-ID').format(totalNominal)
                            : (item.beratBerasKg != null ? item.beratBerasKg + ' Kg' : '-');

                        $rows.append(
                            '<tr class="' + (item.canceled ? 'text-muted' : '') + '">' +
                                '<td>' + receipt + ' ' + canceledBadge + '</td>' +
                                '<td>' + formatInstant(item.paymentAt) + '</td>' +
                                '<td>' + fmtMoney(item.jumlahUang) + '</td>' +
                                '<td>' + fmtKg(item.beratBerasKg) + '</td>' +
                                '<td>' + fmtMoney(item.jumlahUangZakatMal) + '</td>' +
                                '<td>' + fmtMoney(item.jumlahUangInfaqSedekah) + '</td>' +
                                '<td>' + fmtMoney(item.jumlahUangFidiah) + '</td>' +
                                '<td>' + escapeHtml(paymentMethod) + '</td>' +
                                '<td>' + payer + '</td>' +
                                '<td>' + receivedBy + '</td>' +
                                '<td>' + payerPhone + '</td>' +
                                '<td>' + alamat + '</td>' +
                                '<td>' + nominalDisplay + '</td>' +
                                '<td>' + (item.muzakkiCount ?? 0) + '</td>' +
                                '<td>' + escapeHtml(item.muzakkiPreview || '-') + '</td>' +
                                '<td>' + actionHtml + '</td>' +
                            '</tr>'
                        );
                    }
                }

                const total = data.totalElements ?? 0;
                const totalPages = data.totalPages ?? 0;
                $pageInfo.text('Page ' + (page + 1) + ' / ' + Math.max(1, totalPages) + ' • Total: ' + total);

                $btnPrev.parent().toggleClass('disabled', page <= 0);
                $btnNext.parent().toggleClass('disabled', page >= (totalPages - 1));
            }

            function buildQuery() {
                const params = new URLSearchParams();
                params.set('page', String(page));
                params.set('size', String(size));

                const from = $filterFrom.val();
                const to = $filterTo.val();
                const q = ($filterQ.val() || '').trim();
                const payerName = ($filterPayerName.val() || '').trim();
                const payerPhone = ($filterPayerPhone.val() || '').trim();
                const includeCanceled = $filterIncludeCanceled.is(':checked');

                if (from) params.set('from', from);
                if (to) params.set('to', to);
                if (q) params.set('q', q);
                if (payerName) params.set('payerName', payerName);
                if (payerPhone) params.set('payerPhone', payerPhone);
                if (includeCanceled) params.set('includeCanceled', 'true');
                params.append('sort', sortKey + ',' + sortDir);
                params.append('sort', 'id,desc');

                return params.toString();
            }

            async function load() {
                $btnRefresh.prop('disabled', true);
                try {
                    const res = await fetch('/api/zakat-payments?' + buildQuery(), { headers: { Accept: 'application/json' } });
                    if (!res.ok) throw new Error('Gagal load (' + res.status + ')');
                    const data = await res.json();
                    render(data);
                } catch (e) {
                    $rows.empty().append('<tr><td colspan="16" class="text-center text-danger">' + escapeHtml(String(e.message || e)) + '</td></tr>');
                } finally {
                    $btnRefresh.prop('disabled', false);
                }
            }

            async function cancelPayment(paymentId) {
                const reason = window.prompt('Alasan pembatalan (wajib diisi):');
                if (reason == null) return;
                if (!reason.trim()) return alert('Alasan wajib diisi');

                try {
                    const res = await fetch('/api/zakat-payments/' + encodeURIComponent(paymentId) + '/cancel', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ reason: reason.trim() })
                    });
                    if (!res.ok) {
                        const text = await res.text();
                        return alert(text || ('Gagal membatalkan (' + res.status + ')'));
                    }
                    load();
                } catch (e) {
                    alert(String(e.message || e));
                }
            }

            $btnPrev.on('click', function (e) {
                e.preventDefault();
                if (page <= 0) return;
                page -= 1;
                load();
            });
            $btnNext.on('click', function (e) {
                e.preventDefault();
                page += 1;
                load();
            });
            $btnRefresh.on('click', function () {
                load();
            });
            $btnApply.on('click', function () {
                page = 0;
                load();
            });
            $btnReset.on('click', function () {
                const today = new Date();
                const iso = toLocalIsoDate(today);
                $filterFrom.val(startOfMonthIso(today));
                $filterTo.val(iso);
                $filterQ.val('');
                $filterPayerName.val('');
                $filterPayerPhone.val('');
                $filterIncludeCanceled.prop('checked', false);
                page = 0;
                load();
            });
            $rows.on('click', '.btn-cancel', function () {
                const id = $(this).data('id');
                if (!id) return;
                cancelPayment(id);
            });
            $sortButtons.on('click', function () {
                const nextKey = $(this).data('sort-key');
                if (!nextKey) return;
                if (sortKey === nextKey) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortKey = nextKey;
                    sortDir = 'asc';
                }
                page = 0;
                refreshSortButtons();
                load();
            });

            const today = new Date();
            const iso = toLocalIsoDate(today);
            $filterFrom.val(startOfMonthIso(today));
            $filterTo.val(iso);
            $filterPayerName.val('');
            $filterPayerPhone.val('');
            refreshSortButtons();
            load();
        })();
    });
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
