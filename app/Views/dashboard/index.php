<?php

$title = $title ?? 'Dashboard';
$databaseName = (string) ($stats['database'] ?? '-');
$databaseHost = (string) ($stats['host'] ?? '-');
$currentRole = (string) ($stats['role'] ?? '-');

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Periode</h3>
                <div class="card-tools">
                    <button class="btn btn-tool" data-card-widget="collapse" type="button">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
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
                        <div class="dashboard-filter-actions">
                            <button class="btn btn-primary" id="btnApply" type="button">Tampilkan</button>
                            <button class="btn btn-default" id="btnReset" type="button">Reset</button>

                            <div class="btn-group" role="group" aria-label="Periode cepat">
                                <button class="btn btn-outline-secondary" id="btnToday" type="button">Hari ini</button>
                                <button class="btn btn-outline-secondary" id="btnThisWeek" type="button">Minggu ini</button>
                                <button class="btn btn-outline-secondary" id="btnThisMonth" type="button">Bulan ini</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= e($databaseName); ?></h3>
                        <p>Database Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <a class="small-box-footer" href="/reports/rekap">Cetak Rekap <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= e($databaseHost); ?></h3>
                        <p>Host Database</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <a class="small-box-footer" href="/reports/rekap">Lihat rincian <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= e((string) ($user['username'] ?? $user['name'] ?? '-')); ?></h3>
                        <p>User Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <a class="small-box-footer" href="/zakat-payments/list">Riwayat Pembayaran <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= e($currentRole); ?></h3>
                        <p>Role Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a class="small-box-footer" href="/reports/muzakki-detail">Export Data <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <div class="row mt-2">
                            <div class="col-6 text-center border-right">
                                <div class="text-uppercase" style="font-size: 0.8rem;">Email</div>
                                <div style="font-size: 1.5rem; font-weight: 700; line-height: 1.1;"><?= e((string) ($user['email'] ?? '-')); ?></div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-uppercase" style="font-size: 0.8rem;">Source</div>
                                <div style="font-size: 1.5rem; font-weight: 700; line-height: 1.1;"><?= e((string) ($user['source'] ?? '-')); ?></div>
                            </div>
                        </div>
                        <p>Informasi Session Login</p>
                    </div>
                    <a class="small-box-footer" href="/zakat-payments/list">Lihat Riwayat <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ringkasan per Jenis</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th class="text-right" style="width: 260px">Nilai</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Database</td>
                                    <td class="text-right"><?= e($databaseName); ?></td>
                                </tr>
                                <tr>
                                    <td>Host</td>
                                    <td class="text-right"><?= e($databaseHost); ?></td>
                                </tr>
                                <tr>
                                    <td>Role</td>
                                    <td class="text-right"><?= e($currentRole); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pembayaran Terbaru (5)</h3>
                        <div class="card-tools">
                            <a class="btn btn-sm btn-primary" href="/zakat-payments/new">
                                <i class="fas fa-plus"></i> Input Pembayaran
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 140px">Tanggal</th>
                                    <th style="width: 170px">Kwitansi</th>
                                    <th>Alamat</th>
                                    <th style="width: 170px">Jenis</th>
                                    <th class="text-right" style="width: 170px">Nominal</th>
                                    <th class="text-right" style="width: 140px">Beras</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Memuat...</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="/zakat-payments/list">Lihat semua</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aksi Cepat</h3>
                    </div>
                    <div class="card-body">
                        <a class="btn btn-primary btn-block mb-2" href="/zakat-payments/new">
                            <i class="fas fa-plus"></i> Input Pembayaran
                        </a>
                        <a class="btn btn-outline-primary btn-block mb-2" href="/zakat-payments/list">
                            <i class="fas fa-list"></i> Riwayat Pembayaran
                        </a>
                        <a class="btn btn-outline-dark btn-block mb-2" href="/public/dashboard" target="_blank" rel="noopener">
                            <i class="fas fa-tv"></i> Dashboard Publik
                        </a>
                        <a class="btn btn-outline-success btn-block mb-2" href="/reports/rekap">
                            <i class="fas fa-print"></i> Cetak Rekap
                        </a>
                        <a class="btn btn-outline-info btn-block" href="/reports/muzakki-detail">
                            <i class="fas fa-file-csv"></i> Export CSV Muzakki
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="font-weight-bold"><?= e((string) ($stats['appName'] ?? 'ZIS App')); ?></div>
                            <div class="text-muted"><?= e($databaseHost); ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">User</div>
                            <div><?= e((string) ($user['username'] ?? $user['name'] ?? '-')); ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Email</div>
                            <div><?= e((string) ($user['email'] ?? '-')); ?></div>
                        </div>

                        <div>
                            <div class="small text-muted">Role</div>
                            <div><?= e($currentRole); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .dashboard-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
        height: 100%;
        padding-bottom: 1rem;
    }

    .dashboard-filter-actions .btn,
    .dashboard-filter-actions .btn-group {
        margin-right: 0 !important;
        margin-bottom: 0 !important;
    }

    @media (max-width: 767.98px) {
        .dashboard-filter-actions {
            padding-bottom: 0;
        }
    }
</style>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
