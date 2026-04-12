<?php

$title = $title ?? 'Edit Pembayaran';

ob_start();
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Edit Pembayaran</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/zakat-payments/list">Riwayat</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                            <label>No. Kwitansi</label>
                            <input class="form-control" id="receiptNumber" readonly type="text">
                        </div>
                    </div>
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
                            <label for="payerName">Nama Pembayar</label>
                            <input class="form-control" id="payerName" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payerPhone">No. HP Pembayar</label>
                            <input class="form-control" id="payerPhone" type="tel">
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
                            <label for="alamat">Alamat</label>
                            <textarea class="form-control" id="alamat" rows="2"></textarea>
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
                            <label for="jumlahJiwa">Jumlah Jiwa</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary" id="btnJiwaMinus" type="button">-</button>
                                </div>
                                <input class="form-control text-center" id="jumlahJiwa" max="10" min="1" step="1" type="number" value="1">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="btnJiwaPlus" type="button">+</button>
                                </div>
                            </div>
                            <small class="text-muted">Edit nama muzakki akan mengikuti jumlah jiwa.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group d-none" id="groupQuality">
                            <label for="zakatQualityId">Zakat Quality</label>
                            <select class="form-control" disabled id="zakatQualityId">
                                <option disabled selected value="">Pilih zakat quality...</option>
                            </select>
                            <small class="text-muted" id="qualityHint"></small>
                        </div>

                        <div class="form-group d-none" id="groupNominal">
                            <label for="jumlahUang">Nominal Uang (Rp)</label>
                            <input class="form-control amount-input" id="jumlahUang" inputmode="numeric" placeholder="0" type="text">
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
                            <label>Zakat Mal</label>
                            <input class="form-control amount-input" id="zakatMal" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Infaq/Sedekah</label>
                            <input class="form-control amount-input" id="infaqSedekah" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fidiah</label>
                            <input class="form-control amount-input" id="fidiah" inputmode="numeric" placeholder="0 (opsional)" type="text">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary" id="btnSave" type="button">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a class="btn btn-default" href="/zakat-payments/list">Kembali</a>
            </div>
        </div>
    </div>
</section>

<script>
    window.addEventListener('load', function () {
        (function () {
            const FITRAH_TYPES = new Set(['ZAKAT_FITRAH_BERAS', 'ZAKAT_FITRAH_UANG']);
            const MAX_JIWA = 10;
            const paymentId = <?= json_encode((string) ($paymentId ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

            const $alertSuccess = $('#alertSuccess');
            const $alertError = $('#alertError');
            const $receiptNumber = $('#receiptNumber');
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
            const $receivedBySuggestions = $('#receivedByNameSuggestions');
            const $receivedByNameClear = $('#receivedByNameClear');
            const $amountInputs = $('.amount-input');

            let currentPayment = null;
            let currentQualities = [];
            let receivedBySuggestionNames = [];

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
                for (const name of matches.slice(0, 8)) {
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
                }
                $receivedBySuggestions.removeClass('d-none');
            }

            async function loadReceivedBySuggestions() {
                try {
                    const res = await fetch('/api/zakat-payments/received-by-suggestions', {
                        headers: { Accept: 'application/json' }
                    });
                    if (!res.ok) throw new Error('Gagal load petugas');
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

            function showError(message) {
                $alertSuccess.addClass('d-none').text('');
                $alertError.removeClass('d-none').text(message || 'Terjadi kesalahan');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function showSuccess(message) {
                $alertError.addClass('d-none').text('');
                $alertSuccess.removeClass('d-none').text(message || 'Berhasil');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function formatNumber(n) {
                if (n === null || n === undefined || n === '') return '-';
                const num = Number(n);
                if (Number.isNaN(num)) return '-';
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function clampJiwa() {
                let v = parseInt($jumlahJiwa.val(), 10);
                if (Number.isNaN(v) || v < 1) v = 1;
                if (v > MAX_JIWA) v = MAX_JIWA;
                $jumlahJiwa.val(String(v));
                return v;
            }

            function renderMuzakkiInputs(names) {
                const count = names.length;
                $muzakkiContainer.empty();
                for (let i = 0; i < count; i += 1) {
                    const idx = i + 1;
                    const name = names[i] || '';
                    const group = $(
                        '<div class="form-group">' +
                            '<label>Nama Muzakki #' + idx + '</label>' +
                            '<input class="form-control muzakki-name" placeholder="Nama muzakki" type="text" value="">' +
                        '</div>'
                    );
                    group.find('input').val(name);
                    $muzakkiContainer.append(group);
                }
            }

            function syncMuzakkiFirstName() {
                const selectedType = $zakatType.val();
                if (!FITRAH_TYPES.has(selectedType)) {
                    return;
                }
                const payerName = ($payerName.val() || '').trim();
                const $first = $('.muzakki-name').first();
                if ($first.length) {
                    $first.val(payerName);
                }
            }

            function toIsoDate(iso) {
                if (!iso) return '';
                const raw = String(iso).slice(0, 10);
                if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                    return raw;
                }
                const d = new Date(iso);
                if (Number.isNaN(d.getTime())) return '';
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + day;
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
                        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                        firstDay: 1
                    }
                });
                $paymentDate.val(start.format('DD/MM/YYYY'));
            }

            function parseDisplayDateToIso(displayDate) {
                const m = moment(displayDate, 'DD/MM/YYYY', true);
                if (!m.isValid()) return '';
                return m.format('YYYY-MM-DD');
            }

            function parseOptionalPositiveAmount(rawValue, fieldLabel) {
                const digits = String(rawValue ?? '').replace(/\D/g, '');
                if (!digits) return null;
                const amount = Number(digits);
                if (Number.isNaN(amount) || amount <= 0) {
                    throw new Error(fieldLabel + ' harus lebih dari 0');
                }
                return amount;
            }

            function formatAmountInputValue(rawValue) {
                const digits = String(rawValue ?? '').replace(/\D/g, '');
                if (!digits) return '';
                return new Intl.NumberFormat('id-ID').format(Number(digits));
            }

            function setFormattedAmount($input, value) {
                $input.val(formatAmountInputValue(value));
            }

            function updatePreview() {
                const selectedType = $zakatType.val();
                if (!currentPayment || !FITRAH_TYPES.has(selectedType)) {
                    $previewBox.addClass('d-none');
                    return;
                }
                const selectedId = $zakatQualityId.val();
                const q = currentQualities.find(function (x) { return x.id === selectedId; });
                if (!q) {
                    $previewBox.addClass('d-none');
                    return;
                }
                const jiwa = clampJiwa();
                if (selectedType === 'ZAKAT_FITRAH_BERAS') {
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

            async function loadQualities(zakatType, selectedId) {
                $qualityHint.text('');
                $zakatQualityId.prop('disabled', true);
                $zakatQualityId.empty().append('<option value="" selected disabled>Memuat...</option>');
                currentQualities = [];
                $previewBox.addClass('d-none');

                try {
                    const res = await fetch('/api/zakat-qualities?zakatType=' + encodeURIComponent(zakatType), {
                        headers: { Accept: 'application/json' }
                    });
                    if (!res.ok) throw new Error('Gagal load zakat quality');
                    const list = await res.json();
                    currentQualities = Array.isArray(list) ? list : [];

                    $zakatQualityId.empty().append('<option value="" selected disabled>Pilih zakat quality...</option>');
                    for (const q of currentQualities) {
                        const label = zakatType === 'ZAKAT_FITRAH_BERAS'
                            ? q.name + ' (' + formatNumber(q.beratPerJiwaKg) + ' Kg/jiwa)'
                            : q.name + ' (Rp ' + formatNumber(q.nominalPerJiwa) + '/jiwa)';
                        $zakatQualityId.append('<option value="' + q.id + '">' + label + '</option>');
                    }
                    $zakatQualityId.prop('disabled', false);
                    if (selectedId) $zakatQualityId.val(selectedId);
                    updatePreview();
                } catch (e) {
                    $zakatQualityId.empty().append('<option value="" selected disabled>Gagal memuat</option>');
                    $qualityHint.text(String(e.message || e));
                }
            }

            function updateTypeUI(selectedQualityId, preferredNames, options) {
                const preserveAlerts = !!(options && options.preserveAlerts);
                if (!preserveAlerts) {
                    $alertError.addClass('d-none').text('');
                    $alertSuccess.addClass('d-none').text('');
                }

                const val = $zakatType.val();
                const existingNames = $('.muzakki-name').toArray().map(function (el) { return (el.value || '').trim(); });
                const namesToRender = Array.isArray(preferredNames) && preferredNames.length ? preferredNames : existingNames;

                if (FITRAH_TYPES.has(val)) {
                    $groupJumlahJiwa.show();
                    $groupNominal.addClass('d-none');
                    $jumlahUang.val('');
                    $groupQuality.removeClass('d-none');
                    $zakatQualityId.val('');
                    loadQualities(val, selectedQualityId);
                    $muzakkiCard.show();
                    const cnt = clampJiwa();
                    const adjustedNames = namesToRender.slice(0, cnt);
                    while (adjustedNames.length < cnt) adjustedNames.push('');
                    renderMuzakkiInputs(adjustedNames);
                    syncMuzakkiFirstName();
                } else {
                    $groupJumlahJiwa.hide();
                    $groupQuality.addClass('d-none');
                    $zakatQualityId.val('');
                    $qualityHint.text('');
                    $previewBox.addClass('d-none');
                    $groupNominal.addClass('d-none');
                    renderMuzakkiInputs([]);
                    $muzakkiCard.hide();
                }
            }

            async function loadPayment() {
                try {
                    const res = await fetch('/api/zakat-payments/' + encodeURIComponent(paymentId), { headers: { Accept: 'application/json' } });
                    if (!res.ok) throw new Error('Gagal load payment');
                    currentPayment = await res.json();

                    if (currentPayment.canceled) {
                        $btnSave.prop('disabled', true);
                        showError('Payment ini sudah dibatalkan dan tidak bisa diedit.');
                    }

                    $receiptNumber.val(currentPayment.receiptNumber || '');
                    $alamat.val(currentPayment.alamat || '');
                    $payerName.val(currentPayment.payerName || '');
                    $payerPhone.val(currentPayment.payerPhone || '');
                    $receivedByName.val(currentPayment.receivedByName || '');
                    updateReceivedByClearButton();
                    $paymentMethod.val(currentPayment.paymentMethod || 'CASH');

                    const existingDate = toIsoDate(currentPayment.paymentAt);
                    const existingDateDisplay = existingDate ? moment(existingDate, 'YYYY-MM-DD', true).format('DD/MM/YYYY') : '';
                    $paymentDate.val(existingDateDisplay);
                    if ($paymentDate.data('daterangepicker') && existingDate) {
                        $paymentDate.data('daterangepicker').setStartDate(moment(existingDate, 'YYYY-MM-DD'));
                        $paymentDate.data('daterangepicker').setEndDate(moment(existingDate, 'YYYY-MM-DD'));
                    }

                    const names = Array.isArray(currentPayment.muzakkiNames) ? currentPayment.muzakkiNames : [];
                    $jumlahJiwa.val(String(currentPayment.jumlahJiwa || names.length || 1));
                    $zakatType.val(currentPayment.zakatType || '');
                    setFormattedAmount($jumlahUang, currentPayment.jumlahUang);
                    setFormattedAmount($('#zakatMal'), currentPayment.jumlahUangZakatMal);
                    setFormattedAmount($('#infaqSedekah'), currentPayment.jumlahUangInfaqSedekah);
                    setFormattedAmount($('#fidiah'), currentPayment.jumlahUangFidiah);

                    const selectedId = currentPayment.zakatQuality ? currentPayment.zakatQuality.id : null;
                    updateTypeUI(selectedId, names);
                } catch (e) {
                    showError(String(e.message || e));
                }
            }

            async function submit() {
                const jiwa = clampJiwa();
                const alamat = ($alamat.val() || '').trim();
                const paymentDateDisplay = ($paymentDate.val() || '').trim();
                const today = todayIsoDate();
                const names = $('.muzakki-name').toArray().map(function (el) {
                    return (el.value || '').trim();
                }).filter(function (x) {
                    return x.length;
                });
                const paymentDate = parseDisplayDateToIso(paymentDateDisplay);
                const selectedType = $zakatType.val();
                const fitrahSelected = FITRAH_TYPES.has(selectedType);
                let nominal;
                let malAmt;
                let infaqAmt;
                let fidiahAmt;

                try {
                    nominal = parseOptionalPositiveAmount($jumlahUang.val(), 'Nominal uang');
                    malAmt = parseOptionalPositiveAmount($('#zakatMal').val(), 'Zakat Mal');
                    infaqAmt = parseOptionalPositiveAmount($('#infaqSedekah').val(), 'Infaq/Sedekah');
                    fidiahAmt = parseOptionalPositiveAmount($('#fidiah').val(), 'Fidiah');
                } catch (e) {
                    return showError(String(e.message || e));
                }

                if (!currentPayment) return;
                if (!alamat) return showError('Alamat wajib diisi');
                if (!paymentDateDisplay) return showError('Tanggal pembayaran wajib diisi');
                if (!paymentDate) return showError('Format tanggal harus dd/MM/yyyy');
                if (paymentDate > today) return showError('Tanggal pembayaran tidak boleh melebihi hari ini');
                const paymentMethod = $paymentMethod.val();
                if (!paymentMethod) return showError('Metode pembayaran wajib dipilih');
                if (jiwa > MAX_JIWA) return showError('Jumlah jiwa maksimal ' + MAX_JIWA);
                if (fitrahSelected && names.length !== jiwa) return showError('Jumlah nama muzakki harus sama dengan jumlah jiwa untuk fitrah');

                const payload = {
                    paymentDate: paymentDate,
                    alamat: alamat,
                    muzakkiNames: names,
                    payerName: $payerName.val() ? $payerName.val().trim() : null,
                    payerPhone: $payerPhone.val() ? $payerPhone.val().trim() : null,
                    receivedByName: $receivedByName.val() ? $receivedByName.val().trim() : null,
                    paymentMethod: paymentMethod
                };

                if (fitrahSelected) {
                    const qualityId = $zakatQualityId.val();
                    if (!qualityId) return showError('Zakat quality wajib dipilih');
                    payload.zakatQualityId = qualityId;
                } else {
                    const hasAny = nominal != null || malAmt != null || infaqAmt != null || fidiahAmt != null;
                    if (!hasAny) return showError('Isi minimal salah satu: Fitrah, Zakat Mal, Infaq/Sedekah, atau Fidiah');
                }
                payload.jumlahUang = nominal;
                payload.jumlahUangZakatMal = malAmt;
                payload.jumlahUangInfaqSedekah = infaqAmt;
                payload.jumlahUangFidiah = fidiahAmt;

                $btnSave.prop('disabled', true);
                try {
                    const res = await fetch('/api/zakat-payments/' + encodeURIComponent(paymentId), {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json().catch(function () { return {}; });
                    if (!res.ok) {
                        return showError(data.message || 'Gagal menyimpan');
                    }
                    currentPayment = data;
                    showSuccess('Perubahan tersimpan.');
                    setFormattedAmount($('#zakatMal'), currentPayment.jumlahUangZakatMal);
                    setFormattedAmount($('#infaqSedekah'), currentPayment.jumlahUangInfaqSedekah);
                    setFormattedAmount($('#fidiah'), currentPayment.jumlahUangFidiah);
                    const selectedId = currentPayment.zakatQuality ? currentPayment.zakatQuality.id : null;
                    updateTypeUI(selectedId, currentPayment.muzakkiNames || [], { preserveAlerts: true });
                } catch (e) {
                    showError(String(e.message || e));
                } finally {
                    $btnSave.prop('disabled', false);
                }
            }

            $btnJiwaMinus.on('click', function () {
                const v = clampJiwa();
                const next = Math.max(1, v - 1);
                $jumlahJiwa.val(String(next));
                const currentNames = $('.muzakki-name').toArray().map(function (el) { return el.value || ''; });
                renderMuzakkiInputs(currentNames.slice(0, next));
                updatePreview();
            });
            $btnJiwaPlus.on('click', function () {
                const v = clampJiwa();
                if (v >= MAX_JIWA) return;
                const next = v + 1;
                $jumlahJiwa.val(String(next));
                const currentNames = $('.muzakki-name').toArray().map(function (el) { return el.value || ''; });
                currentNames.push('');
                renderMuzakkiInputs(currentNames);
                updatePreview();
            });
            $jumlahJiwa.on('change', function () {
                const next = clampJiwa();
                const currentNames = $('.muzakki-name').toArray().map(function (el) { return el.value || ''; });
                const adjusted = currentNames.slice(0, next);
                while (adjusted.length < next) adjusted.push('');
                renderMuzakkiInputs(adjusted);
                syncMuzakkiFirstName();
                updatePreview();
            });
            $zakatQualityId.on('change', updatePreview);
            $zakatType.on('change', function () {
                updateTypeUI();
                updatePreview();
            });
            $payerName.on('input', function () {
                syncMuzakkiFirstName();
            });
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
            $btnSave.on('click', submit);
            $btnPaymentDatePicker.on('click', function () {
                if ($paymentDate.data('daterangepicker')) {
                    $paymentDate.data('daterangepicker').show();
                }
            });

            initPaymentDatePicker(todayIsoDate());
            loadReceivedBySuggestions();
            loadPayment();
        })();
    });
</script>
<?php
$content = ob_get_clean();
require base_path('app/Views/layouts/admin.php');
