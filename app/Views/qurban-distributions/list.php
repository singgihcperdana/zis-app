<?php

declare(strict_types=1);

$title = $title ?? 'Riwayat Penyaluran';
ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Riwayat Penyaluran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Riwayat Penyaluran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Penyaluran Qurban</h3>
                <div class="card-tools">
                    <a class="btn btn-sm btn-primary" href="/qurban-distributions/new">
                        <i class="fas fa-plus"></i> Input Penyaluran
                    </a>
                    <button class="btn btn-sm btn-success" id="btnDownloadCsv" type="button">
                        <i class="fas fa-file-csv"></i> Download CSV
                    </button>
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
                            <label for="filterRecipientType">Jenis Penerima</label>
                            <select class="form-control" id="filterRecipientType">
                                <option value="">Semua Jenis</option>
                                <option value="PERORANGAN">Perorangan</option>
                                <option value="KELOMPOK">Kelompok</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterQ">Cari</label>
                            <input class="form-control" id="filterQ" placeholder="Nama penerima, PIC, wilayah, petugas..." type="text">
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
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="distributionDate" type="button">Tanggal <i class="fas fa-sort ml-1"></i></button></th>
                        <th>Jam</th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="recipientType" type="button">Jenis Penerima <i class="fas fa-sort ml-1"></i></button></th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="recipientName" type="button">Nama Penerima / Kelompok <i class="fas fa-sort ml-1"></i></button></th>
                        <th>PIC</th>
                        <th>No. HP</th>
                        <th>Alamat / Wilayah</th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="packageCount" type="button">Jumlah Paket <i class="fas fa-sort ml-1"></i></button></th>
                        <th>Catatan</th>
                        <th><button class="btn btn-xl btn-link text-dark p-0 sort-btn font-weight-bold" data-sort-key="distributedBy" type="button">Disalurkan Oleh <i class="fas fa-sort ml-1"></i></button></th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody id="distributionRows">
                    <tr>
                        <td class="text-center text-muted" colspan="11">Memuat...</td>
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
            const csrfToken = <?= json_encode((string) ($csrfToken ?? '')); ?>;
            const $rows = $('#distributionRows');
            const $pageInfo = $('#pageInfo');
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $btnRefresh = $('#btnRefresh');
            const $btnDownloadCsv = $('#btnDownloadCsv');
            const $btnApply = $('#btnApply');
            const $btnReset = $('#btnReset');
            const $filterFrom = $('#filterFrom');
            const $filterTo = $('#filterTo');
            const $filterRecipientType = $('#filterRecipientType');
            const $filterQ = $('#filterQ');
            const $sortButtons = $('.sort-btn');

            let page = 0;
            const size = 20;
            let sortKey = 'distributionDate';
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

            function recipientTypeLabel(value) {
                if (value === 'PERORANGAN') return 'Perorangan';
                if (value === 'KELOMPOK') return 'Kelompok';
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
                    $rows.append('<tr><td colspan="11" class="text-center text-muted">Tidak ada data</td></tr>');
                } else {
                    content.forEach(function (item) {
                        const actionHtml = [
                            '<a class="btn btn-xs btn-info mr-1" href="/qurban-distributions/' + encodeURIComponent(item.id) + '/edit">',
                            '<i class="fas fa-edit"></i> Edit',
                            '</a>',
                            '<button class="btn btn-xs btn-danger btn-delete" data-id="' + escapeHtml(item.id) + '" data-name="' + escapeHtml(item.recipientName || '-') + '" type="button">',
                            '<i class="fas fa-trash"></i> Hapus',
                            '</button>'
                        ].join('');

                        $rows.append(
                            '<tr>' +
                                '<td>' + escapeHtml(formatDate(item.distributionDate)) + '</td>' +
                                '<td>' + escapeHtml(item.distributionTime || '-') + '</td>' +
                                '<td>' + escapeHtml(recipientTypeLabel(item.recipientType)) + '</td>' +
                                '<td>' + escapeHtml(item.recipientName || '-') + '</td>' +
                                '<td>' + escapeHtml(item.picName || '-') + '</td>' +
                                '<td>' + escapeHtml(item.recipientPhone || '-') + '</td>' +
                                '<td class="text-wrap" style="white-space:normal;min-width:220px;">' + escapeHtml(item.recipientArea || '-') + '</td>' +
                                '<td>' + escapeHtml(item.packageCount != null ? item.packageCount : '-') + '</td>' +
                                '<td class="text-wrap" style="white-space:normal;min-width:200px;">' + escapeHtml(item.notes || '-') + '</td>' +
                                '<td>' + escapeHtml(item.distributedBy || '-') + '</td>' +
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
                    recipientType: $filterRecipientType.val() || ''
                });
                params.append('sort[key]', sortKey);
                params.append('sort[dir]', sortDir);

                $rows.html('<tr><td colspan="11" class="text-center text-muted">Memuat...</td></tr>');
                $btnRefresh.prop('disabled', true);

                try {
                    const response = await fetch('/api/qurban-distributions?' + params.toString(), {
                        headers: { Accept: 'application/json' }
                    });
                    const text = await response.text();
                    let data = {};

                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (error) {
                        throw new Error(text || 'Gagal memuat riwayat penyaluran.');
                    }

                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || ('Gagal memuat data (' + response.status + ')'));
                    }

                    render(data);
                } catch (error) {
                    $rows.html('<tr><td colspan="11" class="text-center text-danger">' + escapeHtml(String(error.message || error)) + '</td></tr>');
                    $pageInfo.text('');
                } finally {
                    $btnRefresh.prop('disabled', false);
                }
            }

            async function deleteDistribution(id) {
                const response = await fetch('/api/qurban-distributions/' + encodeURIComponent(id), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify({ _csrf: csrfToken })
                });

                const text = await response.text();
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    data = { message: text || 'Terjadi kesalahan saat menghapus penyaluran.' };
                }

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || ('Gagal menghapus (' + response.status + ')'));
                }

                return data;
            }

            function csvQuery() {
                const params = new URLSearchParams({
                    from: $filterFrom.val() || '',
                    to: $filterTo.val() || '',
                    q: $filterQ.val() || '',
                    recipientType: $filterRecipientType.val() || ''
                });

                return params.toString();
            }

            function resetFilters() {
                const today = new Date();
                $filterFrom.val(startOfMonthIso(today));
                $filterTo.val(toLocalIsoDate(today));
                $filterRecipientType.val('');
                $filterQ.val('');
                page = 0;
                sortKey = 'distributionDate';
                sortDir = 'desc';
            }

            $btnApply.on('click', function () {
                page = 0;
                loadData();
            });

            $btnReset.on('click', function () {
                resetFilters();
                loadData();
            });

            $btnRefresh.on('click', function () {
                loadData();
            });

            $btnDownloadCsv.on('click', function () {
                const route = '/api/qurban-distributions.csv';
                const query = csvQuery();
                const fullRoute = query ? route + '?' + query : route;
                window.location.href = '/index.php?__route=' + encodeURIComponent(fullRoute);
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

            $rows.on('click', '.btn-delete', async function () {
                const id = $(this).data('id');
                const name = $(this).data('name') || 'data ini';

                if (!window.confirm('Hapus data penyaluran untuk "' + name + '"?')) {
                    return;
                }

                try {
                    await deleteDistribution(String(id || ''));
                    loadData();
                } catch (error) {
                    window.alert(String(error.message || error));
                }
            });

            resetFilters();
            loadData();
        })();
    });
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
