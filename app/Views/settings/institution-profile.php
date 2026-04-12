<?php

$title = $title ?? 'Profile Instansi/Masjid';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Profile Instansi/Masjid</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Pengaturan</li>
                    <li class="breadcrumb-item active">Profile Instansi/Masjid</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-success d-none" id="alertSuccess"></div>
        <div class="alert alert-danger d-none" id="alertError"></div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Profile</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="namaInstansi">Nama Instansi/Masjid *</label>
                    <input class="form-control" id="namaInstansi" type="text" placeholder="Nama instansi">
                </div>
                <div class="form-group">
                    <label for="kotaKabupaten">Kota / Kabupaten *</label>
                    <input class="form-control" id="kotaKabupaten" type="text" placeholder="Kota / Kabupaten">
                </div>
                <div class="form-group">
                    <label for="alamatLengkap">Alamat Lengkap *</label>
                    <textarea class="form-control" id="alamatLengkap" rows="3" placeholder="Alamat lengkap"></textarea>
                </div>
                <div class="form-group">
                    <label for="nomorTelepon">Nomor Telepon</label>
                    <input class="form-control" id="nomorTelepon" type="text" placeholder="Nomor telepon instansi">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" id="email" type="email" placeholder="Email instansi">
                </div>
                <div class="form-group">
                    <label for="namaKetua">Nama Ketua</label>
                    <input class="form-control" id="namaKetua" type="text" placeholder="Nama ketua">
                </div>
                <div class="form-group">
                    <label for="namaBendahara">Nama Bendahara</label>
                    <input class="form-control" id="namaBendahara" type="text" placeholder="Nama bendahara">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" id="btnSave" type="button"><i class="fas fa-save"></i> Simpan</button>
                <button class="btn btn-default" id="btnReset" type="button" disabled>Kembalikan ke tersimpan</button>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const alertSuccess = document.getElementById('alertSuccess');
        const alertError = document.getElementById('alertError');
        const namaInstansi = document.getElementById('namaInstansi');
        const kotaKabupaten = document.getElementById('kotaKabupaten');
        const alamatLengkap = document.getElementById('alamatLengkap');
        const nomorTelepon = document.getElementById('nomorTelepon');
        const email = document.getElementById('email');
        const namaKetua = document.getElementById('namaKetua');
        const namaBendahara = document.getElementById('namaBendahara');
        const btnSave = document.getElementById('btnSave');
        const btnReset = document.getElementById('btnReset');
        const csrfToken = <?= json_encode((string) $csrfToken, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        let originalData = null;
        let isLoading = false;

        function getCurrentData() {
            return {
                namaInstansi: (namaInstansi.value || '').trim(),
                kotaKabupaten: (kotaKabupaten.value || '').trim(),
                alamatLengkap: (alamatLengkap.value || '').trim(),
                nomorTelepon: (nomorTelepon.value || '').trim(),
                email: (email.value || '').trim(),
                namaKetua: (namaKetua.value || '').trim(),
                namaBendahara: (namaBendahara.value || '').trim()
            };
        }

        function isDirty() {
            if (!originalData) return false;
            const cur = getCurrentData();
            return cur.namaInstansi !== (originalData.namaInstansi || '')
                || cur.kotaKabupaten !== (originalData.kotaKabupaten || '')
                || cur.alamatLengkap !== (originalData.alamatLengkap || '')
                || cur.nomorTelepon !== (originalData.nomorTelepon || '')
                || cur.email !== (originalData.email || '')
                || cur.namaKetua !== (originalData.namaKetua || '')
                || cur.namaBendahara !== (originalData.namaBendahara || '');
        }

        function updateDirtyState() {
            try {
                const dirty = isDirty();
                btnReset.disabled = !dirty;
            } catch (e) {
                btnReset.disabled = true;
            }
        }

        function showError(msg) {
            alertSuccess.classList.add('d-none');
            alertSuccess.textContent = '';
            alertError.classList.remove('d-none');
            alertError.textContent = msg || 'Terjadi kesalahan';
            window.scrollTo({top: 0, behavior: "smooth"});
        }

        function showSuccess(msg) {
            alertError.classList.add('d-none');
            alertError.textContent = '';
            alertSuccess.classList.remove('d-none');
            alertSuccess.textContent = msg || 'Berhasil';
            window.scrollTo({top: 0, behavior: "smooth"});
        }

        async function load() {
            isLoading = true;
            try {
                const res = await fetch('/api/institution-profile', {headers: {"Accept": "application/json"}});
                if (res.status === 204) {
                    namaInstansi.value = '';
                    kotaKabupaten.value = '';
                    alamatLengkap.value = '';
                    nomorTelepon.value = '';
                    email.value = '';
                    namaKetua.value = '';
                    namaBendahara.value = '';
                    originalData = {
                        namaInstansi: '',
                        kotaKabupaten: '',
                        alamatLengkap: '',
                        nomorTelepon: '',
                        email: '',
                        namaKetua: '',
                        namaBendahara: ''
                    };
                    updateDirtyState();
                    return;
                }
                if (!res.ok) throw new Error(`Gagal memuat (${res.status})`);
                const data = await res.json();
                namaInstansi.value = data.namaInstansi || '';
                kotaKabupaten.value = data.kotaKabupaten || '';
                alamatLengkap.value = data.alamatLengkap || '';
                nomorTelepon.value = data.nomorTelepon || '';
                email.value = data.email || '';
                namaKetua.value = data.namaKetua || '';
                namaBendahara.value = data.namaBendahara || '';
                originalData = {
                    namaInstansi: (data.namaInstansi || '').trim(),
                    kotaKabupaten: (data.kotaKabupaten || '').trim(),
                    alamatLengkap: (data.alamatLengkap || '').trim(),
                    nomorTelepon: (data.nomorTelepon || '').trim(),
                    email: (data.email || '').trim(),
                    namaKetua: (data.namaKetua || '').trim(),
                    namaBendahara: (data.namaBendahara || '').trim()
                };
                updateDirtyState();
            } catch (e) {
                showError(String(e.message || e));
            } finally {
                isLoading = false;
            }
        }

        function validate() {
            if (!(namaInstansi.value || '').trim()) return 'Nama instansi wajib diisi';
            if (!(kotaKabupaten.value || '').trim()) return 'Kota/Kabupaten wajib diisi';
            if (!(alamatLengkap.value || '').trim()) return 'Alamat lengkap wajib diisi';
            return null;
        }

        async function save() {
            const err = validate();
            if (err) return showError(err);

            btnSave.disabled = true;
            try {
                const res = await fetch('/api/institution-profile', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        _csrf: csrfToken,
                        ...getCurrentData()
                    })
                });
                const payload = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(payload.message || `Gagal menyimpan (${res.status})`);
                }
                showSuccess(payload.message || 'Profile disimpan');
                await load();
            } catch (e) {
                showError(String(e.message || e));
            } finally {
                btnSave.disabled = false;
            }
        }

        function resetForm() {
            alertError.classList.add('d-none');
            alertError.textContent = '';
            alertSuccess.classList.add('d-none');
            alertSuccess.textContent = '';
            load();
        }

        btnReset.disabled = true;

        [namaInstansi, kotaKabupaten, alamatLengkap, nomorTelepon, email, namaKetua, namaBendahara].forEach(function (el) {
            el.addEventListener('input', function () {
                if (isLoading) return;
                updateDirtyState();
            });
        });

        btnSave.addEventListener('click', async function () {
            await save();
        });

        btnReset.addEventListener('click', resetForm);

        load();
    })();
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
