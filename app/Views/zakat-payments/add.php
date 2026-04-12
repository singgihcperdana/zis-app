<?php

$title = $title ?? 'Input Pembayaran';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Input Pembayaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Input Pembayaran</li>
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
            <div class="card-header bg-light">
                <h3 class="card-title">Info Pembayaran</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="paymentDate">Tanggal Pembayaran *</label>
                            <div class="input-group">
                                <input autocomplete="off" class="form-control" id="paymentDate" lang="id" placeholder="DD/MM/YYYY" readonly type="text">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="btnPaymentDatePicker" type="button">
                                        <i class="far fa-calendar-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="receivedByName">Diterima Oleh</label>
                            <div class="suggestion-field">
                                <input autocomplete="off" class="form-control" id="receivedByName" placeholder="Nama petugas/amil" type="text">
                                <button aria-label="Kosongkan field diterima oleh" class="suggestion-clear d-none" id="receivedByNameClear" type="button">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="suggestion-menu d-none" id="receivedByNameSuggestions"></div>
                            </div>
                            <small class="text-muted">Nama petugas yang menerima pembayaran dari muzakki.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payerName">Nama Pembayar *</label>
                            <input class="form-control" id="payerName" placeholder="Nama pembayar" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payerPhone">No. HP Pembayar</label>
                            <input class="form-control" id="payerPhone" placeholder="08xxxxxxxxxx" type="tel">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="paymentMethod">Metode Pembayaran *</label>
                            <select class="form-control" id="paymentMethod">
                                <option disabled selected value="">Pilih metode pembayaran...</option>
                                <option value="CASH">Cash</option>
                                <option value="TRANSFER">Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="alamat">Alamat *</label>
                            <textarea class="form-control" id="alamat" placeholder="Contoh: Jl. Mawar No. 10, RT 02/RW 01" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Zakat Fitrah</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="zakatType">Jenis Zakat</label>
                            <select class="form-control" id="zakatType">
                                <option selected value="">Tanpa Fitrah (opsional)</option>
                                <option value="ZAKAT_FITRAH_BERAS">Zakat Fitrah (Beras)</option>
                                <option value="ZAKAT_FITRAH_UANG">Zakat Fitrah (Uang)</option>
                            </select>
                        </div>
                        <div class="form-group" id="groupJumlahJiwa">
                            <label for="jumlahJiwa">Jumlah Jiwa *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary" id="btnJiwaMinus" type="button">-</button>
                                </div>
                                <input class="form-control text-center" id="jumlahJiwa" max="10" min="1" step="1" type="number" value="1">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="btnJiwaPlus" type="button">+</button>
                                </div>
                            </div>
                            <small class="text-muted">Field nama muzakki akan mengikuti jumlah jiwa.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group d-none" id="groupQuality">
                            <label for="zakatQualityId">Zakat Quality *</label>
                            <select class="form-control" disabled id="zakatQualityId">
                                <option disabled selected value="">Pilih zakat quality...</option>
                            </select>
                            <small class="text-muted" id="qualityHint"></small>
                        </div>

                        <div class="form-group d-none" id="groupNominal">
                            <label for="jumlahUang">Nominal Uang (Rp) *</label>
                            <input class="form-control amount-input" id="jumlahUang" inputmode="numeric" placeholder="0" type="text">
                            <small class="text-muted">Isi total nominal untuk payment ini.</small>
                        </div>

                        <div class="callout callout-info d-none" id="previewBox">
                            <div class="row">
                                <div class="col-md-6">
                                    <div><strong>Per jiwa:</strong> <span id="previewPerJiwa">-</span></div>
                                </div>
                                <div class="col-md-6">
                                    <div><strong>Total:</strong> <span id="previewTotal">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" id="muzakkiCard">
                    <div class="card-header bg-light">
                        <h3 class="card-title">Data Muzakki</h3>
                    </div>
                    <div class="card-body" id="muzakkiContainer"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">Zakat Mal, Infaq/Sedekah dan Fidiah</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="zakatMal">Zakat Mal</label>
                            <input class="form-control amount-input" id="zakatMal" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="infaqSedekah">Infaq/Sedekah</label>
                            <input class="form-control amount-input" id="infaqSedekah" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fidiah">Fidiah</label>
                            <input class="form-control amount-input" id="fidiah" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary" id="btnSave" type="button">
                    <i class="fas fa-save"></i> Simpan Pembayaran
                </button>
                <button class="btn btn-default" id="btnReset" type="button">Reset</button>
                <a class="btn btn-link" href="/zakat-payments/list">Lihat Riwayat</a>
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('load', function () {
        (function () {
            const FITRAH_TYPES = new Set(['ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG']);
            const MAX_JIWA = 10;

            const $alertSuccess = $('#alertSuccess');
            const $alertError = $('#alertError');
            const $paymentDate = $('#paymentDate');
            const $btnPaymentDatePicker = $('#btnPaymentDatePicker');
            const $zakatType = $('#zakatType');
            const $jumlahJiwa = $('#jumlahJiwa');
            const $btnJiwaMinus = $('#btnJiwaMinus');
            const $btnJiwaPlus = $('#btnJiwaPlus');
            const $groupJumlahJiwa = $('#groupJumlahJiwa');
            const $groupQuality = $('#groupQuality');
            const $zakatQualityId = $('#zakatQualityId');
            const $qualityHint = $('#qualityHint');
            const $groupNominal = $('#groupNominal');
            const $jumlahUang = $('#jumlahUang');
            const $previewBox = $('#previewBox');
            const $previewPerJiwa = $('#previewPerJiwa');
            const $previewTotal = $('#previewTotal');
            const $muzakkiCard = $('#muzakkiCard');
            const $muzakkiContainer = $('#muzakkiContainer');
            const $alamat = $('#alamat');
            const $payerName = $('#payerName');
            const $payerPhone = $('#payerPhone');
            const $receivedByName = $('#receivedByName');
            const $paymentMethod = $('#paymentMethod');
            const $btnSave = $('#btnSave');
            const $btnReset = $('#btnReset');
            const $receivedBySuggestions = $('#receivedByNameSuggestions');
            const $receivedByNameClear = $('#receivedByNameClear');
            const $amountInputs = $('.amount-input');

            let currentQualities = [];
            let successTimer = null;
            let receivedBySuggestionNames = [];

            async function loadReceivedBySuggestions() {
                try {
                    const res = await fetch('/api/zakat-payments/received-by-suggestions', {
                        headers: { Accept: 'application/json' }
                    });
                    if (!res.ok) {
                        throw new Error('Gagal load petugas');
                    }
                    const names = await res.json();
                    receivedBySuggestionNames = (Array.isArray(names) ? names : []).filter(Boolean);
                    if (document.activeElement === $receivedByName.get(0)) {
                        renderReceivedBySuggestions($receivedByName.val() || '');
                    } else {
                        hideReceivedBySuggestions();
                    }
                    updateReceivedByClearButton();
                } catch (_e) {
                    receivedBySuggestionNames = [];
                    hideReceivedBySuggestions();
                    updateReceivedByClearButton();
                }
            }

            function hideReceivedBySuggestions() {
                $receivedBySuggestions.addClass('d-none').empty();
            }

            function updateReceivedByClearButton() {
                const hasValue = String($receivedByName.val() || '').trim().length > 0;
                $receivedByNameClear.toggleClass('d-none', !hasValue);
            }

            function renderReceivedBySuggestions(query) {
                const trimmedQuery = String(query || '').trim().toLowerCase();
                const matches = receivedBySuggestionNames.filter(function (name) {
                    return !trimmedQuery || name.toLowerCase().includes(trimmedQuery);
                });

                if (!matches.length) {
                    hideReceivedBySuggestions();
                    return;
                }

                $receivedBySuggestions.empty();
                matches.slice(0, 8).forEach(function (name) {
                    const $item = $('<button>')
                        .attr('type', 'button')
                        .addClass('suggestion-menu-item')
                        .text(name)
                        .on('mousedown', function (event) {
                            event.preventDefault();
                            $receivedByName.val(name);
                            hideReceivedBySuggestions();
                            updateReceivedByClearButton();
                            $receivedByName.trigger('change');
                        });
                    $receivedBySuggestions.append($item);
                });
                $receivedBySuggestions.removeClass('d-none');
            }

            function showError(message) {
                $alertSuccess.addClass('d-none').text('');
                $alertError.removeClass('d-none').text(message || 'Terjadi kesalahan');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function showSuccess(message, linkHtml) {
                $alertError.addClass('d-none').text('');
                const safeMessage = String(message || 'Berhasil')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                $alertSuccess.removeClass('d-none').html(safeMessage + (linkHtml ? ' ' + linkHtml : ''));

                if (successTimer) {
                    clearTimeout(successTimer);
                }

                successTimer = setTimeout(function () {
                    $alertSuccess.addClass('d-none').html('');
                    successTimer = null;
                }, 5000);

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function formatNumber(n) {
                if (n === null || n === undefined || n === '') return '-';
                const num = Number(n);
                if (Number.isNaN(num)) return '-';
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function normalizeAmountInput(rawValue) {
                return String(rawValue ?? '').replace(/\D/g, '');
            }

            function formatAmountInputValue(rawValue) {
                const digits = normalizeAmountInput(rawValue);
                if (!digits) return '';
                return new Intl.NumberFormat('id-ID').format(Number(digits));
            }

            function parseOptionalPositiveAmount(rawValue) {
                const digits = normalizeAmountInput(rawValue);
                if (!digits) return null;
                const amount = Number(digits);
                return Number.isNaN(amount) || amount <= 0 ? null : amount;
            }

            function setFormattedAmount($input, value) {
                $input.val(formatAmountInputValue(value));
            }

            function isFitrahType() {
                return FITRAH_TYPES.has($zakatType.val());
            }

            function todayIsoDate() {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const d = String(now.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + d;
            }

            function initPaymentDatePicker(initialDate) {
                moment.locale('id');
                const start = moment(initialDate || todayIsoDate(), 'YYYY-MM-DD', true);
                $paymentDate.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    autoUpdateInput: true,
                    startDate: start,
                    maxDate: moment(),
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Pilih',
                        cancelLabel: 'Batal',
                        fromLabel: 'Dari',
                        toLabel: 'Sampai',
                        customRangeLabel: 'Kustom',
                        weekLabel: 'Mg',
                        daysOfWeek: ['Mg', 'Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb'],
                        monthNames: [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ],
                        firstDay: 1
                    }
                });
                $paymentDate.val(start.format('DD/MM/YYYY'));
            }

            function parseDisplayDateToIso(displayDate) {
                const m = moment(displayDate, 'DD/MM/YYYY', true);
                if (!m.isValid()) {
                    return '';
                }
                return m.format('YYYY-MM-DD');
            }

            function renderMuzakkiInputs(names) {
                $muzakkiContainer.empty();
                const list = Array.isArray(names) ? names : [];
                for (let i = 0; i < list.length; i += 1) {
                    const idx = i + 1;
                    const group = $(
                        '<div class="form-group">' +
                            '<label>Nama Muzakki #' + idx + '</label>' +
                            '<input class="form-control muzakki-name" placeholder="Nama muzakki" type="text">' +
                        '</div>'
                    );
                    group.find('input').val(list[i] || '');
                    $muzakkiContainer.append(group);
                }
            }

            function syncMuzakkiFirstName() {
                if (!isFitrahType()) {
                    return;
                }
                const payerName = ($payerName.val() || '').trim();
                const $first = $('.muzakki-name').first();
                if ($first.length) {
                    $first.val(payerName);
                }
            }

            function currentMuzakkiNames() {
                return $('.muzakki-name').toArray().map(function (el) {
                    return (el.value || '').trim();
                });
            }

            function clampJiwa() {
                let v = parseInt($jumlahJiwa.val(), 10);
                if (Number.isNaN(v) || v < 1) v = 1;
                if (v > MAX_JIWA) v = MAX_JIWA;
                $jumlahJiwa.val(String(v));
                return v;
            }

            function updatePreview() {
                if (!isFitrahType()) {
                    $previewBox.addClass('d-none');
                    return;
                }

                const selectedId = $zakatQualityId.val();
                const q = currentQualities.find(function (item) {
                    return item.id === selectedId;
                });

                if (!q) {
                    $previewBox.addClass('d-none');
                    return;
                }

                const jiwa = clampJiwa();
                if ($zakatType.val() === 'ZAKAT_FITRAH_BERAS') {
                    const perJiwa = q.beratPerJiwaKg;
                    const total = perJiwa * jiwa;
                    $previewPerJiwa.text(formatNumber(perJiwa) + ' Kg');
                    $previewTotal.text(formatNumber(total) + ' Kg');
                } else {
                    const perJiwa = q.nominalPerJiwa;
                    const total = perJiwa * jiwa;
                    $previewPerJiwa.text('Rp ' + formatNumber(perJiwa));
                    $previewTotal.text('Rp ' + formatNumber(total));
                }
                $previewBox.removeClass('d-none');
            }

            async function loadQualities(zakatType) {
                $qualityHint.text('');
                $zakatQualityId.prop('disabled', true);
                $zakatQualityId.empty().append('<option value="" selected disabled>Memuat...</option>');
                currentQualities = [];
                $previewBox.addClass('d-none');

                try {
                    const res = await fetch('/api/zakat-qualities?zakatType=' + encodeURIComponent(zakatType), {
                        headers: { Accept: 'application/json' }
                    });

                    if (!res.ok) {
                        throw new Error('Gagal load zakat quality');
                    }

                    const list = await res.json();
                    currentQualities = Array.isArray(list) ? list : [];

                    $zakatQualityId.empty().append('<option value="" selected disabled>Pilih zakat quality...</option>');
                    currentQualities.forEach(function (q) {
                        let label;
                        if (zakatType === 'ZAKAT_FITRAH_BERAS') {
                            label = q.name + ' (' + formatNumber(q.beratPerJiwaKg) + ' Kg/jiwa)';
                        } else {
                            label = q.name + ' (Rp ' + formatNumber(q.nominalPerJiwa) + '/jiwa)';
                        }
                        $zakatQualityId.append('<option value="' + q.id + '">' + label + '</option>');
                    });
                    $zakatQualityId.prop('disabled', false);
                    $qualityHint.text(currentQualities.length ? '' : 'Belum ada zakat quality aktif untuk jenis ini.');
                } catch (e) {
                    $zakatQualityId.empty().append('<option value="" selected disabled>Gagal memuat</option>');
                    $qualityHint.text(String(e.message || e));
                }
            }

            function updateTypeUI(options) {
                const preserveAlerts = !!(options && options.preserveAlerts);
                if (!preserveAlerts) {
                    $alertError.addClass('d-none').text('');
                    $alertSuccess.addClass('d-none').text('');
                }

                const val = $zakatType.val();
                if (isFitrahType()) {
                    $groupJumlahJiwa.show();
                    $groupNominal.addClass('d-none');
                    $jumlahUang.val('');

                    $groupQuality.removeClass('d-none');
                    $zakatQualityId.val('');
                    loadQualities(val);
                    $muzakkiCard.show();

                    const next = clampJiwa();
                    const names = currentMuzakkiNames().slice(0, next);
                    while (names.length < next) names.push('');
                    renderMuzakkiInputs(names);
                    syncMuzakkiFirstName();
                } else if (val) {
                    $groupJumlahJiwa.hide();
                    $groupQuality.addClass('d-none');
                    $zakatQualityId.val('');
                    $qualityHint.text('');
                    $previewBox.addClass('d-none');

                    $groupNominal.addClass('d-none');
                    renderMuzakkiInputs([]);
                    $muzakkiCard.hide();
                } else {
                    $groupJumlahJiwa.hide();
                    $groupQuality.addClass('d-none');
                    $groupNominal.addClass('d-none');
                    $previewBox.addClass('d-none');
                    renderMuzakkiInputs([]);
                    $muzakkiCard.hide();
                }
            }

            async function submit() {
                const jiwa = clampJiwa();
                const zakatType = $zakatType.val();
                const fitrahSelected = isFitrahType();
                const alamat = ($alamat.val() || '').trim();
                const payerName = ($payerName.val() || '').trim();
                const paymentMethod = $paymentMethod.val();
                const paymentDateDisplay = ($paymentDate.val() || '').trim();
                const names = $('.muzakki-name').toArray().map(function (el) {
                    return (el.value || '').trim();
                }).filter(function (value) {
                    return value.length > 0;
                });
                const today = todayIsoDate();
                const paymentDate = parseDisplayDateToIso(paymentDateDisplay);
                const nominal = parseOptionalPositiveAmount($jumlahUang.val());
                const malAmt = parseOptionalPositiveAmount($('#zakatMal').val());
                const infaqAmt = parseOptionalPositiveAmount($('#infaqSedekah').val());
                const fidiahAmt = parseOptionalPositiveAmount($('#fidiah').val());

                if (!paymentDateDisplay) return showError('Tanggal pembayaran wajib diisi');
                if (!paymentDate) return showError('Format tanggal harus dd/MM/yyyy');
                if (paymentDate > today) return showError('Tanggal pembayaran tidak boleh melebihi hari ini');
                if (!alamat) return showError('Alamat wajib diisi');
                if (!payerName) return showError('Nama pembayar wajib diisi');
                if (!paymentMethod) return showError('Metode pembayaran wajib dipilih');
                if (jiwa > MAX_JIWA) return showError('Jumlah jiwa maksimal ' + MAX_JIWA);
                if (fitrahSelected && names.length !== jiwa) return showError('Jumlah nama muzakki harus sama dengan jumlah jiwa untuk fitrah');

                const payload = {
                    paymentDate: paymentDate,
                    jumlahJiwa: jiwa,
                    alamat: alamat,
                    payerName: payerName,
                    payerPhone: $payerPhone.val() ? $payerPhone.val().trim() : null,
                    receivedByName: $receivedByName.val() ? $receivedByName.val().trim() : null,
                    paymentMethod: paymentMethod,
                    zakatType: zakatType,
                    muzakkiNames: names
                };

                if (fitrahSelected) {
                    const qualityId = $zakatQualityId.val();
                    if (!qualityId) return showError('Zakat quality wajib dipilih');
                    payload.zakatQualityId = qualityId;
                } else {
                    const hasNominal =
                        nominal != null ||
                        malAmt != null ||
                        infaqAmt != null ||
                        fidiahAmt != null;
                    if (!hasNominal) {
                        return showError('Isi minimal salah satu: Fitrah, Zakat Mal, Infaq/Sedekah, atau Fidiah');
                    }
                    if (nominal != null) payload.jumlahUang = nominal;
                }

                if (malAmt != null) payload.jumlahUangZakatMal = malAmt;
                if (infaqAmt != null) payload.jumlahUangInfaqSedekah = infaqAmt;
                if (fidiahAmt != null) payload.jumlahUangFidiah = fidiahAmt;

                $btnSave.prop('disabled', true);
                try {
                    const res = await fetch('/api/zakat-payments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json().catch(function () {
                        return {};
                    });
                    if (!res.ok) {
                        return showError(data.message || 'Gagal menyimpan');
                    }

                    const receipt = data.receiptNumber ? ' No. Kwitansi: ' + data.receiptNumber : '';
                    showSuccess(
                        'Pembayaran tersimpan.' + receipt,
                        '<a href="/zakat-payments/list" class="alert-link ml-2">Riwayat Pembayaran</a>'
                    );

                    $zakatType.val('');
                    $jumlahJiwa.val('1');
                    $payerName.val('');
                    renderMuzakkiInputs(['']);
                    $alamat.val('');
                    $receivedByName.val('');
                    updateReceivedByClearButton();
                    $jumlahUang.val('');
                    $('#zakatMal').val('');
                    $('#infaqSedekah').val('');
                    $('#fidiah').val('');
                    $zakatQualityId.val('');
                    currentQualities = [];
                    $payerPhone.val('');
                    $paymentMethod.val('');
                    updateTypeUI({ preserveAlerts: true });
                } catch (e) {
                    showError(String(e.message || e));
                } finally {
                    $btnSave.prop('disabled', false);
                }
            }

            function resetForm() {
                $alertError.addClass('d-none').text('');
                $alertSuccess.addClass('d-none').text('');

                $zakatType.val('');
                $jumlahJiwa.val('1');
                $payerName.val('');
                renderMuzakkiInputs(['']);
                $alamat.val('');
                $receivedByName.val('');
                updateReceivedByClearButton();
                $jumlahUang.val('');
                const today = todayIsoDate();
                $paymentDate.val(moment(today, 'YYYY-MM-DD').format('DD/MM/YYYY'));
                if ($paymentDate.data('daterangepicker')) {
                    $paymentDate.data('daterangepicker').setStartDate(moment(today, 'YYYY-MM-DD'));
                    $paymentDate.data('daterangepicker').setEndDate(moment(today, 'YYYY-MM-DD'));
                }
                $zakatQualityId.val('');
                currentQualities = [];
                $payerPhone.val('');
                $paymentMethod.val('');
                $('#zakatMal').val('');
                $('#infaqSedekah').val('');
                $('#fidiah').val('');
                updateTypeUI();
            }

            const today = todayIsoDate();
            initPaymentDatePicker(today);
            loadReceivedBySuggestions();
            renderMuzakkiInputs(['']);
            $payerName.val('');
            $payerPhone.val('');
            $receivedByName.val('');
            $paymentMethod.val('');
            updateTypeUI();

            $btnJiwaMinus.on('click', function () {
                const v = clampJiwa();
                const next = Math.max(1, v - 1);
                $jumlahJiwa.val(String(next));
                const names = currentMuzakkiNames().slice(0, next);
                while (names.length < next) names.push('');
                renderMuzakkiInputs(names);
                updatePreview();
            });

            $btnJiwaPlus.on('click', function () {
                const v = clampJiwa();
                if (v >= MAX_JIWA) return;
                const next = v + 1;
                $jumlahJiwa.val(String(next));
                const names = currentMuzakkiNames().slice(0, next);
                while (names.length < next) names.push('');
                renderMuzakkiInputs(names);
                updatePreview();
            });

            $jumlahJiwa.on('change', function () {
                const next = clampJiwa();
                const names = currentMuzakkiNames().slice(0, next);
                while (names.length < next) names.push('');
                renderMuzakkiInputs(names);
                syncMuzakkiFirstName();
                updatePreview();
            });

            $zakatType.on('change', function () {
                updateTypeUI();
            });
            $payerName.on('input', function () {
                syncMuzakkiFirstName();
            });
            $zakatQualityId.on('change', function () {
                updatePreview();
            });

            $btnSave.on('click', submit);
            $btnReset.on('click', resetForm);
            $receivedByName.on('focus input', function () {
                renderReceivedBySuggestions($receivedByName.val());
                updateReceivedByClearButton();
            });
            $receivedByName.on('blur', function () {
                window.setTimeout(hideReceivedBySuggestions, 150);
            });
            $receivedByNameClear.on('click', function () {
                $receivedByName.val('');
                hideReceivedBySuggestions();
                updateReceivedByClearButton();
            });
            $amountInputs.on('input', function () {
                setFormattedAmount($(this), $(this).val());
            });
            $btnPaymentDatePicker.on('click', function () {
                if ($paymentDate.data('daterangepicker')) {
                    $paymentDate.data('daterangepicker').show();
                }
            });
        })();
    });
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
