<?php

$title = $title ?? 'Input Qurban';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Input Qurban</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Input Qurban</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-success d-none" id="alertSuccess"></div>
        <div class="alert alert-danger d-none" id="alertError"></div>
        <div class="alert alert-info">
            Form input qurban sudah disiapkan. Penyimpanan data, cetak kwitansi, dan report akan menyusul di tahap berikutnya.
        </div>

        <div class="card card-outline card-success">
            <div class="card-header bg-light">
                <h3 class="card-title">Data Pemberi Qurban</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="submissionDate">Tanggal Input</label>
                            <input class="form-control" id="submissionDate" type="date">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="payerName">Nama *</label>
                            <input class="form-control" id="payerName" placeholder="Nama pemberi qurban" required type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payerPhone">Telp / HP *</label>
                            <input class="form-control digits-only" id="payerPhone" inputmode="numeric" placeholder="08xxxxxxxxxx" required type="text">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-0">
                            <label for="alamat">Alamat *</label>
                            <textarea class="form-control" id="alamat" placeholder="Alamat lengkap pemberi qurban" required rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Data Hewan Qurban</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="animalType">Jenis Hewan Qurban *</label>
                            <select class="form-control" id="animalType" required>
                                <option value="KAMBING">Kambing</option>
                                <option value="SAPI">Sapi</option>
                                <option value="SAPI_KOLEKTIF">Sapi Kolektif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="slaughterMode">Sembelih Hewan Qurban *</label>
                            <select class="form-control" id="slaughterMode" required>
                                <option value="JAGAL">Jagal</option>
                                <option value="SENDIRI">Sendiri</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="qurbanNumber">Nomor Qurban *</label>
                            <input class="form-control digits-only" id="qurbanNumber" inputmode="numeric" placeholder="Contoh: 01" required type="text">
                            <small class="text-muted">Diisi manual, hanya angka 0-9, dan harus unik. Nomor yang sudah dipakai akan ditolak saat simpan.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="biayaPemeliharaan">Biaya Pemeliharaan dan Pemotongan</label>
                            <input class="form-control amount-input" id="biayaPemeliharaan" inputmode="numeric" placeholder="0" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="shodaqohInfak">Shodaqoh / Infak</label>
                            <input class="form-control amount-input" id="shodaqohInfak" inputmode="numeric" placeholder="0" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="biayaSupplier">Supplier</label>
                            <input class="form-control amount-input" id="biayaSupplier" inputmode="numeric" placeholder="0" type="text">
                        </div>
                    </div>
                </div>

                <div class="row" id="goatPickupFields">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pickupTimeNotes">Waktu Pengambilan</label>
                            <input class="form-control" id="pickupTimeNotes" placeholder="Contoh: Setelah Dzuhur / 13.30 WIB" type="text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="committeePhone">No. HP Panitia</label>
                            <input class="form-control" id="committeePhone" placeholder="Nomor panitia yang bisa dihubungi" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3 mb-0">
                    <label for="notes">Catatan</label>
                    <textarea class="form-control" id="notes" placeholder="Catatan tambahan untuk data qurban ini" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="card" id="participantCard">
            <div class="card-header bg-light">
                <h3 class="card-title">Peserta Sapi Kolektif</h3>
            </div>
            <div class="card-body">
                <div class="row" id="participantContainer"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary" id="btnSave" type="button">
                    <i class="fas fa-save"></i> Simpan Qurban
                </button>
                <button class="btn btn-default" id="btnReset" type="button">Reset</button>
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('load', function () {
        (function () {
            const csrfToken = <?= json_encode((string) ($csrfToken ?? '')); ?>;
            const $alertSuccess = $('#alertSuccess');
            const $alertError = $('#alertError');
            const $submissionDate = $('#submissionDate');
            const $qurbanNumber = $('#qurbanNumber');
            const $animalType = $('#animalType');
            const $participantCard = $('#participantCard');
            const $participantContainer = $('#participantContainer');
            const $goatPickupFields = $('#goatPickupFields');
            const $payerName = $('#payerName');
            const $payerPhone = $('#payerPhone');
            const $alamat = $('#alamat');
            const $biayaPemeliharaan = $('#biayaPemeliharaan');
            const $shodaqohInfak = $('#shodaqohInfak');
            const $biayaSupplier = $('#biayaSupplier');
            const $slaughterMode = $('#slaughterMode');
            const $pickupTimeNotes = $('#pickupTimeNotes');
            const $committeePhone = $('#committeePhone');
            const $notes = $('#notes');
            const $digitsOnlyInputs = $('.digits-only');
            const $amountInputs = $('.amount-input');
            const $btnReset = $('#btnReset');
            const $btnSave = $('#btnSave');
            const $infoAlert = $('.alert-info');

            function buildParticipantFields() {
                $participantContainer.empty();

                for (let i = 1; i <= 7; i += 1) {
                    const field = [
                        '<div class="col-md-6">',
                        '  <div class="form-group">',
                        '    <label for="participant' + i + '">Peserta ' + i + '</label>',
                        '    <input class="form-control participant-input" id="participant' + i + '" placeholder="Nama peserta ' + i + '" type="text">',
                        '  </div>',
                        '</div>'
                    ].join('');

                    $participantContainer.append(field);
                }
            }

            function syncFirstParticipantWithPayerName() {
                if ($animalType.val() !== 'SAPI_KOLEKTIF') {
                    return;
                }

                $('#participant1').val($payerName.val() || '');
            }

            function syncAnimalSections() {
                const animalType = $animalType.val();
                const isGoat = animalType === 'KAMBING';
                const isCollective = animalType === 'SAPI_KOLEKTIF';
                $participantCard.toggleClass('d-none', !isCollective);
                $goatPickupFields.toggleClass('d-none', !isGoat);

                if (isCollective) {
                    syncFirstParticipantWithPayerName();
                }
            }

            function keepDigitsOnly(value) {
                return String(value || '').replace(/\D+/g, '');
            }

            function formatThousands(value) {
                const digits = keepDigitsOnly(value);
                if (digits === '') {
                    return '';
                }

                return new Intl.NumberFormat('id-ID').format(Number(digits));
            }

            function showError(message) {
                $alertSuccess.addClass('d-none').text('');
                $alertError.removeClass('d-none').text(message || 'Terjadi kesalahan.');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function showSuccess(message) {
                $alertError.addClass('d-none').text('');
                $alertSuccess.removeClass('d-none').text(message || 'Berhasil.');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function collectParticipants() {
                return Array.from(document.querySelectorAll('.participant-input')).map(function (input) {
                    return (input.value || '').trim();
                });
            }

            function buildPayload() {
                return {
                    _csrf: csrfToken,
                    submissionDate: ($submissionDate.val() || '').trim(),
                    qurbanNumber: ($qurbanNumber.val() || '').trim(),
                    payerName: ($payerName.val() || '').trim(),
                    payerPhone: ($payerPhone.val() || '').trim(),
                    alamat: ($alamat.val() || '').trim(),
                    animalType: ($animalType.val() || '').trim(),
                    biayaPemeliharaan: ($biayaPemeliharaan.val() || '').trim(),
                    shodaqohInfak: ($shodaqohInfak.val() || '').trim(),
                    biayaSupplier: ($biayaSupplier.val() || '').trim(),
                    slaughterMode: ($slaughterMode.val() || '').trim(),
                    pickupTimeNotes: ($pickupTimeNotes.val() || '').trim(),
                    committeePhone: ($committeePhone.val() || '').trim(),
                    notes: ($notes.val() || '').trim(),
                    participants: collectParticipants()
                };
            }

            function validateRequiredFields() {
                if (!($payerName.val() || '').trim()) return 'Nama wajib diisi.';
                if (!($payerPhone.val() || '').trim()) return 'No. HP wajib diisi.';
                if (!($alamat.val() || '').trim()) return 'Alamat wajib diisi.';
                if (!($animalType.val() || '').trim()) return 'Jenis hewan qurban wajib dipilih.';
                if (!($slaughterMode.val() || '').trim()) return 'Sembelih hewan qurban wajib dipilih.';
                if (!($qurbanNumber.val() || '').trim()) return 'Nomor qurban wajib diisi.';
                return null;
            }

            async function saveQurban() {
                const validationMessage = validateRequiredFields();
                if (validationMessage) {
                    showError(validationMessage);
                    return;
                }

                $btnSave.prop('disabled', true);

                try {
                    const response = await fetch('/api/qurban', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(buildPayload())
                    });

                    const data = await response.json().catch(function () {
                        return {};
                    });

                    if (!response.ok) {
                        throw new Error(data.message || 'Simpan qurban gagal.');
                    }

                    showSuccess(data.message || 'Data qurban berhasil disimpan.');
                    resetForm();
                    $qurbanNumber.focus();
                } catch (error) {
                    showError(error.message || 'Simpan qurban gagal.');
                } finally {
                    $btnSave.prop('disabled', false);
                }
            }

            function resetForm() {
                document.querySelectorAll('input:not([disabled]), textarea, select').forEach(function (el) {
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                        return;
                    }

                    el.value = '';
                });

                $submissionDate.val(new Date().toISOString().slice(0, 10));
                $animalType.val('KAMBING');
                syncAnimalSections();
            }

            $animalType.on('change', syncAnimalSections);
            $payerName.on('input', syncFirstParticipantWithPayerName);
            $digitsOnlyInputs.on('input', function () {
                const cleaned = keepDigitsOnly(this.value);
                if (this.value !== cleaned) {
                    this.value = cleaned;
                }
            });
            $amountInputs.on('input', function () {
                const formatted = formatThousands(this.value);
                if (this.value !== formatted) {
                    this.value = formatted;
                }
            });

            $btnReset.on('click', function () {
                resetForm();
            });

            $btnSave.on('click', function () {
                saveQurban();
            });

            buildParticipantFields();
            $infoAlert.remove();
            resetForm();
        })();
    });
</script>

<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
