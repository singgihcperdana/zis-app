<?php

declare(strict_types=1);

$title = $title ?? 'Edit Penyaluran';
ob_start();
?>
<section class="content pt-3">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Penyaluran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/qurban/list">Qurban</a></li>
                    <li class="breadcrumb-item"><a href="/qurban-distributions/list">Riwayat Penyaluran</a></li>
                    <li class="breadcrumb-item active">Edit Penyaluran</li>
                </ol>
            </div>
        </div>

        <div class="alert alert-success d-none" id="distributionSuccess"></div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Data Penyaluran</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="distributionDate">Tanggal Penyaluran <span class="text-danger">*</span></label>
                            <input class="form-control" id="distributionDate" type="date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="distributionTime">Jam Penyaluran</label>
                            <input class="form-control" id="distributionTime" type="time">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="recipientType">Jenis Penerima <span class="text-danger">*</span></label>
                            <select class="form-control" id="recipientType">
                                <option value="PERORANGAN">Perorangan</option>
                                <option value="KELOMPOK">Kelompok</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Data Penerima</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recipientName" id="recipientNameLabel">Nama Penerima <span class="text-danger">*</span></label>
                            <input class="form-control" id="recipientName" placeholder="Masukkan nama penerima" type="text">
                        </div>
                    </div>
                    <div class="col-md-6 d-none" id="picNameGroup">
                        <div class="form-group">
                            <label for="picName">Nama PIC</label>
                            <input class="form-control" id="picName" placeholder="Nama penanggung jawab kelompok" type="text">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="recipientPhone" id="recipientPhoneLabel">No. HP</label>
                            <input class="form-control digits-only" id="recipientPhone" inputmode="numeric" placeholder="Nomor yang bisa dihubungi" type="text">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label for="recipientArea">Alamat / Wilayah <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="recipientArea" placeholder="Contoh: RT 03 RW 05 / Blok A / Kampung Babakan" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Detail Penyaluran</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="packageCount">Jumlah Paket <span class="text-danger">*</span></label>
                            <input class="form-control digits-only" id="packageCount" inputmode="numeric" placeholder="Jumlah paket yang disalurkan" type="text">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label for="distributionNotes">Keterangan</label>
                            <textarea class="form-control" id="distributionNotes" placeholder="Catatan tambahan penyaluran qurban" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Petugas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="distributedBy">Disalurkan Oleh <span class="text-danger">*</span></label>
                            <input class="form-control" id="distributedBy" placeholder="Nama petugas / panitia yang menyalurkan" type="text">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary" id="btnSave" type="button">
                    <i class="fas fa-save"></i> Update Penyaluran
                </button>
                <button class="btn btn-default" id="btnReset" type="button">Reset</button>
                <a class="btn btn-link" href="/qurban-distributions/list">Kembali ke Riwayat</a>
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('load', function () {
        (function () {
            const csrfToken = <?= json_encode((string) ($csrfToken ?? '')); ?>;
            const distributionId = <?= json_encode($distributionId ?? ''); ?>;
            const $distributionDate = $('#distributionDate');
            const $distributionTime = $('#distributionTime');
            const $recipientType = $('#recipientType');
            const $recipientName = $('#recipientName');
            const $recipientNameLabel = $('#recipientNameLabel');
            const $picNameGroup = $('#picNameGroup');
            const $picName = $('#picName');
            const $recipientPhone = $('#recipientPhone');
            const $recipientPhoneLabel = $('#recipientPhoneLabel');
            const $recipientArea = $('#recipientArea');
            const $packageCount = $('#packageCount');
            const $distributionNotes = $('#distributionNotes');
            const $distributedBy = $('#distributedBy');
            const $success = $('#distributionSuccess');
            const $digitsOnlyInputs = $('.digits-only');
            const $btnSave = $('#btnSave');
            let originalData = null;

            function updateRecipientFields() {
                const type = $recipientType.val();
                const isGroup = type === 'KELOMPOK';

                $recipientNameLabel.text(isGroup ? 'Nama Kelompok *' : 'Nama Penerima *');
                $recipientPhoneLabel.text(isGroup ? 'No. HP PIC' : 'No. HP');
                $recipientName.attr('placeholder', isGroup ? 'Masukkan nama kelompok penerima' : 'Masukkan nama penerima');
                $picNameGroup.toggleClass('d-none', !isGroup);

                if (!isGroup) {
                    $picName.val('');
                }
            }

            function normalizeDigitsInput(event) {
                const clean = String(event.target.value || '').replace(/\D+/g, '');
                if (clean !== event.target.value) {
                    event.target.value = clean;
                }
            }

            function resetAlerts() {
                $success.addClass('d-none').empty();
            }

            function payload() {
                return {
                    _csrf: csrfToken,
                    distributionDate: ($distributionDate.val() || '').trim(),
                    distributionTime: ($distributionTime.val() || '').trim(),
                    recipientType: ($recipientType.val() || '').trim(),
                    recipientName: ($recipientName.val() || '').trim(),
                    picName: ($picName.val() || '').trim(),
                    recipientPhone: ($recipientPhone.val() || '').trim(),
                    recipientArea: ($recipientArea.val() || '').trim(),
                    packageCount: ($packageCount.val() || '').trim(),
                    notes: ($distributionNotes.val() || '').trim(),
                    distributedBy: ($distributedBy.val() || '').trim()
                };
            }

            function applyData(data) {
                $distributionDate.val(data.distributionDate || '');
                $distributionTime.val(data.distributionTime || '');
                $recipientType.val(data.recipientType || 'PERORANGAN');
                updateRecipientFields();
                $recipientName.val(data.recipientName || '');
                $picName.val(data.picName || '');
                $recipientPhone.val(data.recipientPhone || '');
                $recipientArea.val(data.recipientArea || '');
                $packageCount.val(data.packageCount != null ? String(data.packageCount) : '');
                $distributionNotes.val(data.notes || '');
                $distributedBy.val(data.distributedBy || '');
            }

            async function loadData() {
                const response = await fetch('/api/qurban-distributions/' + encodeURIComponent(distributionId), {
                    headers: { Accept: 'application/json' }
                });
                const text = await response.text();
                let data = {};

                try {
                    data = text ? JSON.parse(text) : {};
                } catch (error) {
                    throw new Error(text || 'Gagal memuat data penyaluran.');
                }

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || ('Gagal memuat data (' + response.status + ')'));
                }

                originalData = data;
                applyData(data);
            }

            $digitsOnlyInputs.on('input', normalizeDigitsInput);
            $recipientType.on('change', updateRecipientFields);

            $('#btnReset').on('click', function () {
                resetAlerts();
                if (originalData) {
                    applyData(originalData);
                }
            });

            $('#btnSave').on('click', async function () {
                resetAlerts();
                $btnSave.prop('disabled', true);

                try {
                    const response = await fetch('/api/qurban-distributions/' + encodeURIComponent(distributionId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json'
                        },
                        body: JSON.stringify(payload())
                    });

                    const text = await response.text();
                    let data = {};
                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (error) {
                        data = { message: text || 'Terjadi kesalahan saat memperbarui penyaluran.' };
                    }

                    if (!response.ok || data.success === false) {
                        throw new Error(data.message || ('Gagal menyimpan (' + response.status + ')'));
                    }

                    originalData = data.data || originalData;
                    if (originalData) {
                        applyData(originalData);
                    }
                    $success.removeClass('d-none').text(data.message || 'Data penyaluran qurban berhasil diperbarui.');
                    setTimeout(function () {
                        $success.fadeOut(250, function () {
                            $(this).addClass('d-none').show().empty();
                        });
                    }, 5000);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (error) {
                    window.alert(String(error.message || error));
                } finally {
                    $btnSave.prop('disabled', false);
                }
            });

            (async function init() {
                try {
                    await loadData();
                } catch (error) {
                    window.alert(String(error.message || error));
                    window.location.href = '/qurban-distributions/list';
                }
            })();
        })();
    });
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
