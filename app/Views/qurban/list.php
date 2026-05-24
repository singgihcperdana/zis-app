<?php

$title = $title ?? 'Riwayat Qurban';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Riwayat Qurban</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Riwayat Qurban</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Input Qurban</h3>
                <div class="card-tools">
                    <a class="btn btn-sm btn-primary" href="/qurban/new">
                        <i class="fas fa-plus"></i> Input Baru
                    </a>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterAnimalType">Jenis Hewan</label>
                            <select class="form-control" id="filterAnimalType">
                                <option value="">Semua Jenis</option>
                                <option value="KAMBING">Kambing</option>
                                <option value="SAPI">Sapi</option>
                                <option value="SAPI_KOLEKTIF">Sapi Kolektif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterQ">Cari</label>
                            <input class="form-control" id="filterQ" placeholder="Nomor qurban, nama, atau alamat..." type="text">
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
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="qurbanNumber" type="button">Nomor Qurban <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="submissionDate" type="button">Tanggal Input <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="payerName" type="button">Nama <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="animalType" type="button">Jenis Hewan <i class="fas fa-sort ml-1"></i></button></th>
                        <th>Telp / HP</th>
                        <th>Alamat</th>
                        <th>Pemeliharaan</th>
                        <th>Shodaqoh / Infak</th>
                        <th>Supplier</th>
                        <th>Sembelih</th>
                        <th>Pengambilan</th>
                        <th>No. HP Panitia</th>
                        <th>Peserta</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody id="qurbanRows">
                    <tr>
                        <td class="text-center text-muted" colspan="14">Memuat...</td>
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
    window.addEventListener('load', function () {
        (function () {
            const $rows = $('#qurbanRows');
            const $pageInfo = $('#pageInfo');
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $btnRefresh = $('#btnRefresh');
            const $btnApply = $('#btnApply');
            const $btnReset = $('#btnReset');
            const $filterFrom = $('#filterFrom');
            const $filterTo = $('#filterTo');
            const $filterAnimalType = $('#filterAnimalType');
            const $filterQ = $('#filterQ');
            const $sortButtons = $('.sort-btn');

            let page = 0;
            const size = 20;
            let sortKey = 'submissionDate';
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

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function formatDate(iso) {
                if (!iso) return '-';
                const d = new Date(iso);
                if (Number.isNaN(d.getTime())) return iso;
                return new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).format(d);
            }

            function formatMoney(value) {
                return value == null ? '-' : 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }

            function animalLabel(value) {
                if (value === 'KAMBING') return 'Kambing';
                if (value === 'SAPI') return 'Sapi';
                if (value === 'SAPI_KOLEKTIF') return 'Sapi Kolektif';
                return value || '-';
            }

            function slaughterLabel(value) {
                if (value === 'JAGAL') return 'Jagal';
                if (value === 'SENDIRI') return 'Sendiri';
                return value || '-';
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
                    $rows.append('<tr><td colspan="14" class="text-center text-muted">Tidak ada data</td></tr>');
                } else {
                    content.forEach(function (item) {
                        const actionHtml = [
                            '<a class="btn btn-xs btn-info mr-1" href="/qurban/' + encodeURIComponent(item.id) + '/edit">',
                            '<i class="fas fa-edit"></i> Edit',
                            '</a>',
                            '<button class="btn btn-xs btn-secondary btn-kwitansi" data-id="' + escapeHtml(item.id) + '" type="button">',
                            '<i class="fas fa-print"></i> Kwitansi',
                            '</button>'
                        ].join('');

                        $rows.append(
                            '<tr>' +
                                '<td><code>' + escapeHtml(item.qurbanNumber || '-') + '</code></td>' +
                                '<td>' + escapeHtml(formatDate(item.submissionDate)) + '</td>' +
                                '<td>' + escapeHtml(item.payerName || '-') + '</td>' +
                                '<td>' + escapeHtml(animalLabel(item.animalType)) + '</td>' +
                                '<td>' + escapeHtml(item.payerPhone || '-') + '</td>' +
                                '<td class="text-wrap" style="white-space:normal;min-width:220px;">' + escapeHtml(item.alamat || '-') + '</td>' +
                                '<td>' + escapeHtml(formatMoney(item.biayaPemeliharaan)) + '</td>' +
                                '<td>' + escapeHtml(formatMoney(item.shodaqohInfak)) + '</td>' +
                                '<td>' + escapeHtml(formatMoney(item.biayaSupplier)) + '</td>' +
                                '<td>' + escapeHtml(slaughterLabel(item.slaughterMode)) + '</td>' +
                                '<td class="text-wrap" style="white-space:normal;min-width:200px;">' + escapeHtml(item.pickupTimeNotes || '-') + '</td>' +
                                '<td>' + escapeHtml(item.committeePhone || '-') + '</td>' +
                                '<td class="text-wrap" style="white-space:normal;min-width:180px;">' + escapeHtml(item.participantPreview || '-') + (item.participantCount > 3 ? ' +' + (item.participantCount - 3) : '') + '</td>' +
                                '<td>' + actionHtml + '</td>' +
                            '</tr>'
                        );
                    });
                }

                const total = data.totalElements ?? 0;
                const totalPages = data.totalPages ?? 0;
                $pageInfo.text('Page ' + (page + 1) + ' / ' + Math.max(1, totalPages) + ' • Total: ' + total);
                $btnPrev.parent().toggleClass('disabled', page <= 0);
                $btnNext.parent().toggleClass('disabled', page + 1 >= totalPages);
                refreshSortButtons();
            }

            async function loadData() {
                const params = new URLSearchParams({
                    page: String(page),
                    size: String(size),
                    from: $filterFrom.val() || '',
                    to: $filterTo.val() || '',
                    q: $filterQ.val() || '',
                    animalType: $filterAnimalType.val() || ''
                });
                params.append('sort[key]', sortKey);
                params.append('sort[dir]', sortDir);

                $rows.html('<tr><td colspan="13" class="text-center text-muted">Memuat...</td></tr>');

                try {
                    const response = await fetch('/api/qurban?' + params.toString(), {
                        headers: { Accept: 'application/json' }
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memuat riwayat qurban.');
                    }

                    render(data);
                } catch (error) {
                    $rows.html('<tr><td colspan="14" class="text-center text-danger">' + escapeHtml(error.message || 'Gagal memuat data') + '</td></tr>');
                    $pageInfo.text('');
                }
            }

            function resetFilters() {
                const now = new Date();
                $filterFrom.val(startOfMonthIso(now));
                $filterTo.val(toLocalIsoDate(now));
                $filterAnimalType.val('');
                $filterQ.val('');
                page = 0;
            }

            $btnApply.on('click', function () {
                page = 0;
                loadData();
            });

            $btnRefresh.on('click', function () {
                loadData();
            });

            $btnReset.on('click', function () {
                resetFilters();
                loadData();
            });

            $btnPrev.on('click', function (event) {
                event.preventDefault();
                if (page <= 0) return;
                page -= 1;
                loadData();
            });

            $btnNext.on('click', function (event) {
                event.preventDefault();
                page += 1;
                loadData();
            });

            $sortButtons.on('click', function () {
                const key = $(this).data('sort-key');
                if (sortKey === key) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortKey = key;
                    sortDir = 'asc';
                }
                page = 0;
                loadData();
            });

            $rows.on('click', '.btn-kwitansi', function () {
                window.alert('Fitur kwitansi qurban belum diaktifkan.');
            });

            resetFilters();
            loadData();
        })();
    });
</script>

<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
