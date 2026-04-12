<?php

$title = $title ?? 'Kelola Zakat Quality';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Kelola Zakat Quality</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
                    <li class="breadcrumb-item active">Kelola Zakat Quality</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">Opsi Zakat Fitrah (Beras)</h3>
                    </div>
                    <div class="card-body" id="berasList"></div>
                    <div class="card-footer">
                        <div class="form-group">
                            <input class="form-control" id="berasLabel" placeholder="Label (Misal: Standar)" type="text">
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="berasBerat" placeholder="Berat (Kg)" type="number" step="0.1">
                        </div>
                        <button class="btn btn-warning" id="btnAddBeras" type="button">+ Tambah Opsi Beras</button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Opsi Zakat Fitrah (Uang)</h3>
                    </div>
                    <div class="card-body" id="uangList"></div>
                    <div class="card-footer">
                        <div class="form-group">
                            <input class="form-control" id="uangLabel" placeholder="Label (Misal: Premium)" type="text">
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="uangNominal" placeholder="Nominal (Rp)" type="number" step="1">
                        </div>
                        <button class="btn btn-success" id="btnAddUang" type="button">+ Tambah Opsi Uang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const BRAS = 'ZAKAT_FITRAH_BERAS';
        const UANG = 'ZAKAT_FITRAH_UANG';

        const berasList = document.getElementById('berasList');
        const uangList = document.getElementById('uangList');
        const berasLabel = document.getElementById('berasLabel');
        const berasBerat = document.getElementById('berasBerat');
        const uangLabel = document.getElementById('uangLabel');
        const uangNominal = document.getElementById('uangNominal');
        const btnAddBeras = document.getElementById('btnAddBeras');
        const btnAddUang = document.getElementById('btnAddUang');

        function formatNumber(n) {
            try {
                return new Intl.NumberFormat('id-ID').format(Number(n));
            } catch (e) {
                return n;
            }
        }

        function renderList(container, list, type) {
            container.innerHTML = '';
            if (!Array.isArray(list) || list.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'text-muted';
                empty.textContent = 'Belum ada opsi aktif.';
                container.appendChild(empty);
                return;
            }

            list.forEach(function (it) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between mb-2';

                const left = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = it.name;
                const subtitle = document.createElement('div');
                subtitle.className = 'text-muted small';
                subtitle.textContent = type === BRAS
                    ? (it.beratPerJiwaKg + ' Kg')
                    : ('Rp ' + formatNumber(it.nominalPerJiwa));
                left.appendChild(title);
                left.appendChild(subtitle);

                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-outline-danger';
                btn.type = 'button';
                btn.title = 'Nonaktifkan';
                btn.innerHTML = '<i class="fas fa-trash"></i>';
                btn.addEventListener('click', async function () {
                    if (!window.confirm('Nonaktifkan opsi ini?')) {
                        return;
                    }

                    try {
                        const response = await fetch('/api/zakat-qualities/' + encodeURIComponent(it.id), {
                            method: 'DELETE',
                            headers: { Accept: 'application/json' }
                        });

                        if (!response.ok && response.status !== 204) {
                            const payload = await response.json().catch(function () {
                                return {};
                            });
                            throw new Error(payload.message || 'Gagal menonaktifkan');
                        }

                        await loadAll();
                    } catch (error) {
                        window.alert(String(error.message || error));
                    }
                });

                row.appendChild(left);
                row.appendChild(btn);
                container.appendChild(row);
            });
        }

        async function loadByType(type) {
            const response = await fetch('/api/zakat-qualities?zakatType=' + encodeURIComponent(type), {
                headers: { Accept: 'application/json' }
            });

            if (!response.ok) {
                const payload = await response.json().catch(function () {
                    return {};
                });
                throw new Error(payload.message || 'Gagal memuat opsi');
            }

            return await response.json();
        }

        async function loadAll() {
            try {
                const beras = await loadByType(BRAS);
                const uang = await loadByType(UANG);
                renderList(berasList, beras, BRAS);
                renderList(uangList, uang, UANG);
            } catch (error) {
                console.error(error);
                window.alert(String(error.message || error));
            }
        }

        async function createBeras() {
            const name = (berasLabel.value || '').trim();
            const berat = Number(berasBerat.value);

            if (!name) {
                window.alert('Label wajib diisi');
                return;
            }

            if (!berat || berat <= 0) {
                window.alert('Berat harus lebih besar dari 0');
                return;
            }

            btnAddBeras.disabled = true;
            try {
                const response = await fetch('/api/zakat-qualities', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        name: name,
                        zakatType: BRAS,
                        beratPerJiwaKg: String(berat)
                    })
                });

                if (!response.ok) {
                    const payload = await response.json().catch(function () {
                        return {};
                    });
                    throw new Error(payload.message || 'Gagal menambah opsi');
                }

                berasLabel.value = '';
                berasBerat.value = '';
                await loadAll();
            } catch (error) {
                window.alert(String(error.message || error));
            } finally {
                btnAddBeras.disabled = false;
            }
        }

        async function createUang() {
            const name = (uangLabel.value || '').trim();
            const nominal = Number(uangNominal.value);

            if (!name) {
                window.alert('Label wajib diisi');
                return;
            }

            if (!nominal || nominal <= 0) {
                window.alert('Nominal harus lebih besar dari 0');
                return;
            }

            btnAddUang.disabled = true;
            try {
                const response = await fetch('/api/zakat-qualities', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        name: name,
                        zakatType: UANG,
                        nominalPerJiwa: Number(nominal)
                    })
                });

                if (!response.ok) {
                    const payload = await response.json().catch(function () {
                        return {};
                    });
                    throw new Error(payload.message || 'Gagal menambah opsi');
                }

                uangLabel.value = '';
                uangNominal.value = '';
                await loadAll();
            } catch (error) {
                window.alert(String(error.message || error));
            } finally {
                btnAddUang.disabled = false;
            }
        }

        btnAddBeras.addEventListener('click', createBeras);
        btnAddUang.addEventListener('click', createUang);

        loadAll();
    })();
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
