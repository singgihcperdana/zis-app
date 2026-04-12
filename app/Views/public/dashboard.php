<?php

declare(strict_types=1);
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title><?= e($title ?? 'Dashboard Publik ZIS'); ?></title>
    <link href="/assets/adminlte/plugins/fontawesome-free/css/all.min.css" rel="stylesheet">
    <script src="/assets/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        :root {
            --bg: #f4f0e8;
            --panel: rgba(255, 255, 255, 0.86);
            --line: rgba(35, 40, 45, 0.1);
            --ink: #1f2933;
            --muted: #5f6c7b;
            --green: #0f766e;
            --gold: #c58a1d;
            --blue: #1f5c8a;
            --red: #a63f34;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            overflow: hidden;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(197, 138, 29, 0.18), transparent 28%),
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.18), transparent 30%),
                linear-gradient(135deg, #f7f4ec 0%, #efe7da 48%, #e9dfd2 100%);
        }

        .screen {
            height: 100vh;
            padding: 12px;
            display: grid;
            grid-template-rows: auto auto minmax(0, 1fr) auto;
            gap: 8px;
            overflow: hidden;
        }

        .hero, .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(31, 41, 51, 0.07);
            backdrop-filter: blur(10px);
        }

        .hero {
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 12px;
        }

        .hero-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }

        .zis-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            height: 88px;
            padding: 0 18px;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f766e 0%, #1f5c8a 100%);
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 0.08em;
            font-family: "Trebuchet MS", sans-serif;
            box-shadow: 0 14px 26px rgba(31, 92, 138, 0.24);
        }

        .zis-copy {
            display: grid;
            gap: 4px;
        }

        .zis-kicker {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(197, 138, 29, 0.14);
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            font-family: "Trebuchet MS", sans-serif;
        }

        .zis-title {
            font-size: clamp(16px, 1.8vw, 24px);
            font-weight: 700;
            line-height: 1.1;
            color: var(--ink);
        }

        .zis-subtitle {
            color: var(--muted);
            font-size: clamp(12px, 1.1vw, 15px);
            font-family: "Trebuchet MS", sans-serif;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(24px, 3vw, 40px);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .hero-subtitle {
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
        }

        .hero-side {
            text-align: right;
            min-width: 260px;
            display: grid;
            justify-items: end;
        }

        .clock {
            font-size: clamp(22px, 2.6vw, 34px);
            font-weight: 700;
            color: var(--blue);
            line-height: 1;
        }

        .date-label {
            color: var(--muted);
            margin-top: 4px;
            font-size: 14px;
            font-family: "Trebuchet MS", sans-serif;
        }

        .generated-at-label {
            color: rgba(95, 108, 123, 0.88);
            margin-top: 2px;
            font-size: 12px;
            font-family: "Trebuchet MS", sans-serif;
        }

        .hero-filter {
            margin-top: 12px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(180px, 1fr) minmax(180px, 1fr) auto;
            gap: 12px 16px;
            align-items: end;
        }

        .toolbar > div {
            min-width: 0;
        }

        .toolbar-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: end;
        }

        .toolbar-label {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-family: "Trebuchet MS", sans-serif;
        }

        .quick-toggle {
            border: 1px solid rgba(35, 40, 45, 0.12);
            background: rgba(255,255,255,0.72);
            color: var(--ink);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .quick-toggle.active {
            background: var(--green);
            color: #fff;
            border-color: var(--green);
        }

        .quick-toggle:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .date-input {
            width: 100%;
            min-width: 0;
            border: 1px solid rgba(35, 40, 45, 0.12);
            background: rgba(255,255,255,0.72);
            color: var(--ink);
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: "Trebuchet MS", sans-serif;
        }

        .stat-card {
            padding: 14px 16px 12px;
            position: relative;
            overflow: hidden;
        }

        .stat-label {
            font-size: 15px;
            color: rgba(255,255,255,0.88);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-family: "Trebuchet MS", sans-serif;
        }

        .stat-value {
            margin-top: 6px;
            font-size: clamp(22px, 2.5vw, 38px);
            color: #fff;
            font-weight: 700;
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        .stat-icon {
            position: absolute;
            right: 18px;
            top: 14px;
            font-size: clamp(28px, 2.4vw, 42px);
            color: rgba(255, 255, 255, 0.22);
            line-height: 1;
            z-index: 2;
            pointer-events: none;
        }

        .stat-money { background: linear-gradient(135deg, #0f766e, #14967f); }
        .stat-rice { background: linear-gradient(135deg, #c58a1d, #d9a845); }
        .stat-tx { background: linear-gradient(135deg, #1f5c8a, #2e78a8); }
        .stat-jiwa { background: linear-gradient(135deg, #a63f34, #c15843); }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: stretch;
            min-height: 0;
            overflow: hidden;
        }

        .panel {
            padding: 12px 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .panel-title {
            margin: 0 0 10px;
            font-size: 20px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 10px;
        }

        .panel-subtitle {
            color: var(--muted);
            font-size: 14px;
            font-family: "Trebuchet MS", sans-serif;
            font-weight: 400;
        }

        .summary-box {
            padding: 10px 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.72);
            border: 1px solid rgba(35, 40, 45, 0.08);
        }

        .chart-box {
            display: grid;
            gap: 10px;
        }

        .chart-canvas-wrap {
            position: relative;
            height: 228px;
        }

        .chart-canvas-wrap canvas {
            display: none;
        }

        .money-type-chart-wrap {
            height: 248px;
        }

        .chart-svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .chart-svg-slice {
            stroke: #ffffff;
            stroke-width: 3;
        }

        .chart-svg-label {
            fill: #ffffff;
            font: 700 12px "Trebuchet MS", sans-serif;
            text-anchor: middle;
            dominant-baseline: middle;
            paint-order: stroke;
            stroke: rgba(31, 41, 51, 0.18);
            stroke-width: 3px;
            stroke-linejoin: round;
        }

        .chart-meta {
            display: grid;
            gap: 6px;
            width: fit-content;
            max-width: 100%;
            align-self: center;
            margin-left: auto;
            margin-right: auto;
        }

        .chart-meta-row {
            display: grid;
            grid-template-columns: max-content auto;
            align-items: center;
            column-gap: 14px;
            font-family: "Trebuchet MS", sans-serif;
        }

        .chart-meta-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .chart-dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            display: inline-block;
        }

        .chart-meta-value {
            display: inline-grid;
            grid-template-columns: auto auto;
            align-items: baseline;
            column-gap: 10px;
            min-width: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            justify-self: end;
        }

        .currency-main {
            display: inline-block;
            min-width: 13ch;
            text-align: right;
        }

        .currency-percent {
            display: inline-block;
            min-width: 4ch;
            text-align: right;
        }

        .chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            font-family: "Trebuchet MS", sans-serif;
            border: 1px dashed rgba(35, 40, 45, 0.12);
            border-radius: 14px;
            background: rgba(255,255,255,0.55);
            padding: 16px;
        }

        .filter-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(19, 27, 34, 0.38);
            backdrop-filter: blur(8px);
            z-index: 30;
        }

        .filter-modal.is-open {
            display: flex;
        }

        .filter-modal-card {
            width: min(920px, 100%);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(35, 40, 45, 0.08);
            border-radius: 22px;
            box-shadow: 0 22px 48px rgba(31, 41, 51, 0.18);
            padding: 18px 20px;
        }

        .filter-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .filter-modal-title {
            margin: 0;
            font-size: 22px;
        }

        .filter-modal-subtitle {
            color: var(--muted);
            font-size: 12px;
            font-family: "Trebuchet MS", sans-serif;
        }

        @media (max-width: 1100px) {
            .stats, .content-grid {
                grid-template-columns: 1fr 1fr;
            }

            .toolbar {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                min-height: 100svh;
                height: auto;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .screen {
                min-height: 100svh;
                height: auto;
                padding: 16px;
                grid-template-rows: auto auto auto auto;
                overflow: visible;
            }

            .hero {
                flex-direction: column;
                align-items: start;
            }

            .hero-brand {
                align-items: flex-start;
            }

            .zis-mark {
                min-width: 72px;
                height: 72px;
                font-size: 28px;
                border-radius: 20px;
            }

            .hero-side {
                text-align: left;
                min-width: auto;
            }

            .stats, .content-grid {
                grid-template-columns: 1fr;
            }

            .toolbar {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .content-grid,
            .panel {
                overflow: visible;
            }
        }
    </style>
</head>
<body>
<div class="screen">
    <section class="hero">
        <div>
            <div class="hero-brand">
                <div class="zis-mark">ZIS</div>
                <div class="zis-copy">
                    <div class="zis-kicker">Dashboard Publik</div>
                    <div class="zis-title">Penghimpunan Zakat, Infaq, Sedekah</div>
                    <div class="zis-subtitle">Laporan agregat penghimpunan ZIS untuk jamaah, panitia, dan publik.</div>
                </div>
            </div>
            <h1 id="institutionName">Dashboard Publik Zakat, Infaq, Sedekah</h1>
            <div class="hero-subtitle" id="institutionAddress">Memuat informasi masjid...</div>
        </div>
        <div class="hero-side">
            <div class="clock" id="liveClock">--:--:--</div>
            <div class="date-label" id="periodLabel">Periode hari ini</div>
            <div class="generated-at-label" id="generatedAtLabel">Pembaruan data: -</div>
            <div class="hero-filter">
                <button class="quick-toggle" id="btnToggleFilters" type="button">Ubah Periode</button>
            </div>
        </div>
    </section>

    <section class="stats">
        <article class="panel stat-card stat-money">
            <div class="stat-label">Total Uang</div>
            <div class="stat-value" id="totalMoney">-</div>
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
        </article>
        <article class="panel stat-card stat-rice">
            <div class="stat-label">Total Beras</div>
            <div class="stat-value" id="totalRice">-</div>
            <div class="stat-icon"><i class="fas fa-seedling"></i></div>
        </article>
        <article class="panel stat-card stat-tx">
            <div class="stat-label">Transaksi</div>
            <div class="stat-value" id="totalTransactions">-</div>
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        </article>
        <article class="panel stat-card stat-jiwa">
            <div class="stat-label">Jiwa Fitrah</div>
            <div class="stat-value" id="totalJiwa">-</div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </article>
    </section>

    <section class="content-grid">
        <section class="panel">
            <h2 class="panel-title">
                <span>Perolehan Uang</span>
                <span class="panel-subtitle">Komposisi per jenis</span>
            </h2>
            <div class="summary-box">
                <div class="chart-box">
                    <div class="chart-canvas-wrap money-type-chart-wrap">
                        <canvas id="moneyByTypeChart"></canvas>
                    </div>
                    <div class="chart-meta" id="moneyByTypeMeta">
                        <div class="chart-meta-row">
                            <span class="chart-meta-name"><span class="chart-dot" style="background:#1f5c8a"></span>Memuat</span>
                            <span class="chart-meta-value">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2 class="panel-title">
                <span>Zakat Fitrah</span>
                <span class="panel-subtitle">Perbandingan beras dan uang</span>
            </h2>
            <div class="summary-box">
                <div class="chart-box">
                    <div class="chart-canvas-wrap">
                        <canvas id="fitrahModeChart"></canvas>
                    </div>
                    <div class="chart-meta" id="fitrahModeMeta">
                        <div class="chart-meta-row">
                            <span class="chart-meta-name"><span class="chart-dot" style="background:#c58a1d"></span>Beras</span>
                            <span class="chart-meta-value">0 jiwa</span>
                        </div>
                        <div class="chart-meta-row">
                            <span class="chart-meta-name"><span class="chart-dot" style="background:#1f5c8a"></span>Uang</span>
                            <span class="chart-meta-value">0 jiwa</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</div>

<div class="filter-modal" id="filterModal">
    <div class="filter-modal-card">
        <div class="filter-modal-head">
            <div>
                <h2 class="filter-modal-title">Filter Periode</h2>
                <div class="filter-modal-subtitle">Atur tampilan data publik berdasarkan rentang waktu yang dipilih.</div>
            </div>
            <button class="quick-toggle" id="btnCloseFilterModal" type="button">Tutup</button>
        </div>
        <div class="toolbar">
            <div>
                <span class="toolbar-label">Periode Cepat</span>
                <div class="toolbar-group">
                    <button class="quick-toggle" data-range="today" type="button">Hari Ini</button>
                    <button class="quick-toggle active" data-range="month" type="button">Bulan Berjalan</button>
                    <button class="quick-toggle" data-range="all" type="button">Semua</button>
                    <button class="quick-toggle" data-range="custom" type="button">Custom Tanggal</button>
                </div>
            </div>
            <div>
                <span class="toolbar-label">Tanggal Mulai</span>
                <input class="date-input" id="filterFrom" type="date" disabled>
            </div>
            <div>
                <span class="toolbar-label">Tanggal Selesai</span>
                <input class="date-input" id="filterTo" type="date" disabled>
            </div>
            <div>
                <span class="toolbar-label">Terapkan</span>
                <button class="quick-toggle" id="btnApplyRange" type="button" disabled>Tampilkan</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const rangeButtons = Array.from(document.querySelectorAll("[data-range]"));
        const fromInput = document.getElementById("filterFrom");
        const toInput = document.getElementById("filterTo");
        const applyButton = document.getElementById("btnApplyRange");
        const toggleFiltersButton = document.getElementById("btnToggleFilters");
        const closeFilterModalButton = document.getElementById("btnCloseFilterModal");
        const filterModal = document.getElementById("filterModal");
        let currentMode = "month";
        const chartInstances = {};

        function formatCurrency(value) {
            const number = Number(value || 0);
            return "Rp " + new Intl.NumberFormat("id-ID").format(number);
        }

        function formatCurrencyValue(value, suffix) {
            const amount = new Intl.NumberFormat("id-ID").format(Number(value || 0));
            const percent = suffix || "";
            return `<span class="currency-main">Rp ${amount}</span><span class="currency-percent">${percent}</span>`;
        }

        function formatRice(value) {
            const number = Number(value || 0);
            return new Intl.NumberFormat("id-ID", {maximumFractionDigits: 2}).format(number) + " Kg";
        }

        function formatNumber(value) {
            return new Intl.NumberFormat("id-ID").format(Number(value || 0));
        }

        function formatDate(dateString) {
            const date = new Date(dateString + "T00:00:00");
            return new Intl.DateTimeFormat("id-ID", {day: "2-digit", month: "long", year: "numeric"}).format(date);
        }

        function formatDateTime(dateString) {
            return new Intl.DateTimeFormat("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            }).format(new Date(dateString));
        }

        function formatTime(date) {
            return new Intl.DateTimeFormat("id-ID", {
                hour: "2-digit",
                minute: "2-digit"
            }).format(date);
        }

        function todayIsoDate() {
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart("2", "0");
            const d = String(now.getDate()).padStart("2", "0");
            return `${y}-${m}-${d}`;
        }

        function monthStartIsoDate() {
            const now = new Date();
            const y = now.getFullYear();
            const m = String(now.getMonth() + 1).padStart(2, "0");
            return `${y}-${m}-01`;
        }

        function setActiveMode(mode) {
            currentMode = mode;
            rangeButtons.forEach(button => {
                button.classList.toggle("active", button.dataset.range === mode);
            });
            syncApplyButtonState();
        }

        function syncApplyButtonState() {
            const isCustomMode = currentMode === "custom";
            applyButton.disabled = !isCustomMode;
            fromInput.disabled = !isCustomMode;
            toInput.disabled = !isCustomMode;
        }

        function syncInputsForMode(mode) {
            const today = todayIsoDate();
            if (mode === "today") {
                fromInput.value = today;
                toInput.value = today;
            } else if (mode === "month") {
                fromInput.value = monthStartIsoDate();
                toInput.value = today;
            } else if (mode === "all") {
                fromInput.value = "";
                toInput.value = "";
            } else if (mode === "custom") {
                if (!fromInput.value) fromInput.value = monthStartIsoDate();
                if (!toInput.value) toInput.value = today;
            }
        }

        function currentQuery() {
            if (currentMode === "all" && !fromInput.value && !toInput.value) {
                return "";
            }
            const params = new URLSearchParams();
            if (fromInput.value) params.set("from", fromInput.value);
            if (toInput.value) params.set("to", toInput.value);
            return params.toString() ? `?${params.toString()}` : "";
        }

        function updateClock() {
            document.getElementById("liveClock").textContent = new Intl.DateTimeFormat("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit"
            }).format(new Date());
        }

        function computeRoundedPercentages(values) {
            const numbers = values.map(value => Math.max(Number(value || 0), 0));
            const total = numbers.reduce((sum, value) => sum + value, 0);
            if (total <= 0) return numbers.map(() => 0);

            const raw = numbers.map(value => (value / total) * 100);
            const floors = raw.map(value => Math.floor(value));
            let remaining = 100 - floors.reduce((sum, value) => sum + value, 0);

            const rankedFractions = raw
                .map((value, index) => ({index, fraction: value - Math.floor(value), value: numbers[index]}))
                .filter(item => item.value > 0)
                .sort((a, b) => b.fraction - a.fraction);

            for (let i = 0; i < remaining && i < rankedFractions.length; i += 1) {
                floors[rankedFractions[i].index] += 1;
            }

            return floors;
        }

        function destroyChart(key) {
            const chartState = chartInstances[key];
            if (!chartState) {
                return;
            }
            if (chartState.svg && chartState.svg.parentNode) {
                chartState.svg.parentNode.removeChild(chartState.svg);
            }
            delete chartInstances[key];
        }

        function polarToCartesian(centerX, centerY, radius, angle) {
            return {
                x: centerX + Math.cos(angle) * radius,
                y: centerY + Math.sin(angle) * radius
            };
        }

        function createPieSlicePath(centerX, centerY, radius, startAngle, endAngle) {
            const start = polarToCartesian(centerX, centerY, radius, startAngle);
            const end = polarToCartesian(centerX, centerY, radius, endAngle);
            const largeArcFlag = endAngle - startAngle > Math.PI ? 1 : 0;

            return [
                `M ${centerX} ${centerY}`,
                `L ${start.x} ${start.y}`,
                `A ${radius} ${radius} 0 ${largeArcFlag} 1 ${end.x} ${end.y}`,
                "Z"
            ].join(" ");
        }

        function renderPieChart(key, canvasId, labels, values, colors, tooltipValueFormatter, showLegend) {
            destroyChart(key);
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
            const parent = canvas.parentElement;
            parent.querySelectorAll(".chart-empty").forEach(el => el.remove());
            canvas.style.display = "none";
            if (total <= 0) {
                const empty = document.createElement("div");
                empty.className = "chart-empty";
                empty.textContent = "Belum ada data pada periode ini.";
                parent.appendChild(empty);
                return;
            }

            const svgNamespace = "http://www.w3.org/2000/svg";
            const svg = document.createElementNS(svgNamespace, "svg");
            svg.setAttribute("viewBox", "0 0 240 240");
            svg.setAttribute("class", "chart-svg");
            svg.setAttribute("role", "img");
            svg.setAttribute("aria-label", labels.join(", "));

            const centerX = 120;
            const centerY = 120;
            const radius = 92;
            const roundedPercentages = computeRoundedPercentages(values);
            const positiveSlices = values
                .map((rawValue, index) => ({
                    index,
                    value: Number(rawValue || 0),
                    label: labels[index] || "-"
                }))
                .filter(item => item.value > 0);

            if (positiveSlices.length === 1) {
                const onlySlice = positiveSlices[0];
                const circle = document.createElementNS(svgNamespace, "circle");
                circle.setAttribute("cx", String(centerX));
                circle.setAttribute("cy", String(centerY));
                circle.setAttribute("r", String(radius));
                circle.setAttribute("fill", colors[onlySlice.index % colors.length]);
                circle.setAttribute("class", "chart-svg-slice");
                const title = document.createElementNS(svgNamespace, "title");
                const formattedValue = typeof tooltipValueFormatter === "function"
                    ? tooltipValueFormatter(onlySlice.value)
                    : new Intl.NumberFormat("id-ID").format(onlySlice.value);
                title.textContent = `${onlySlice.label}: ${formattedValue} (100%)`;
                circle.appendChild(title);
                svg.appendChild(circle);

                const text = document.createElementNS(svgNamespace, "text");
                text.setAttribute("x", String(centerX));
                text.setAttribute("y", String(centerY));
                text.setAttribute("class", "chart-svg-label");
                text.textContent = "100%";
                svg.appendChild(text);
            } else {
                let currentAngle = -Math.PI / 2;
                values.forEach((rawValue, index) => {
                    const value = Number(rawValue || 0);
                    if (value <= 0) {
                        return;
                    }

                    const sliceAngle = (value / total) * Math.PI * 2;
                    const endAngle = currentAngle + sliceAngle;
                    const path = document.createElementNS(svgNamespace, "path");
                    path.setAttribute("d", createPieSlicePath(centerX, centerY, radius, currentAngle, endAngle));
                    path.setAttribute("fill", colors[index % colors.length]);
                    path.setAttribute("class", "chart-svg-slice");

                    const title = document.createElementNS(svgNamespace, "title");
                    const formattedValue = typeof tooltipValueFormatter === "function"
                        ? tooltipValueFormatter(value)
                        : new Intl.NumberFormat("id-ID").format(value);
                    title.textContent = `${labels[index] || "-"}: ${formattedValue} (${Number(roundedPercentages[index] || 0)}%)`;
                    path.appendChild(title);
                    svg.appendChild(path);

                    const percentageLabel = `${Number(roundedPercentages[index] || 0)}%`;
                    if (percentageLabel !== "0%") {
                        const labelAngle = currentAngle + (sliceAngle / 2);
                        const labelPoint = polarToCartesian(centerX, centerY, radius * 0.62, labelAngle);
                        const text = document.createElementNS(svgNamespace, "text");
                        text.setAttribute("x", String(labelPoint.x));
                        text.setAttribute("y", String(labelPoint.y));
                        text.setAttribute("class", "chart-svg-label");
                        text.textContent = percentageLabel;
                        svg.appendChild(text);
                    }

                    currentAngle = endAngle;
                });
            }

            parent.appendChild(svg);
            chartInstances[key] = {svg};
        }

        function openFilterModal() {
            filterModal.classList.add("is-open");
        }

        function closeFilterModal() {
            filterModal.classList.remove("is-open");
        }

        async function loadSummary() {
            const response = await fetch(`/api/public/dashboard/summary${currentQuery()}`, {
                headers: {"Accept": "application/json"}
            });
            if (!response.ok) {
                throw new Error("Gagal memuat dashboard publik");
            }
            const data = await response.json();
            const profile = data.institutionProfile || {};

            document.getElementById("institutionName").textContent = profile.namaInstansi || "Dashboard Publik ZIS";
            document.getElementById("institutionAddress").textContent = profile.alamatLengkap || profile.kotaKabupaten || "Informasi masjid";
            document.getElementById("periodLabel").textContent = `Periode ${formatDate(data.fromDate)} - ${formatDate(data.toDate)}`;
            document.getElementById("totalMoney").textContent = formatCurrency(data.totalUangMasuk);
            document.getElementById("totalRice").textContent = formatRice(data.totalBerasKg);
            document.getElementById("totalTransactions").textContent = formatNumber(data.totalTransaksi);
            document.getElementById("totalJiwa").textContent = formatNumber(data.totalJiwaFitrah);

            const moneyByTypeItems = (data.byType || [])
                .filter(item => Number(item.totalUang || 0) > 0)
                .sort((a, b) => Number(b.totalUang || 0) - Number(a.totalUang || 0));
            const moneyByTypeColors = ["#1f5c8a", "#0f766e", "#c58a1d", "#a63f34", "#6b7280", "#7c3aed", "#c2410c"];
            const moneyByTypeRoundedPercentages = computeRoundedPercentages(moneyByTypeItems.map(item => Number(item.totalUang || 0)));

            renderPieChart(
                "moneyByTypeChart",
                "moneyByTypeChart",
                moneyByTypeItems.map(item => item.zakatTypeLabel || "-"),
                moneyByTypeItems.map(item => Number(item.totalUang || 0)),
                moneyByTypeColors,
                formatCurrency,
                false
            );
            document.getElementById("moneyByTypeMeta").innerHTML = moneyByTypeItems.length ? moneyByTypeItems.map((item, index) => `
                <div class="chart-meta-row">
                    <span class="chart-meta-name"><span class="chart-dot" style="background:${moneyByTypeColors[index % moneyByTypeColors.length]}"></span>${item.zakatTypeLabel || "-"}</span>
                    <span class="chart-meta-value">${formatCurrencyValue(item.totalUang, `(${moneyByTypeRoundedPercentages[index]}%)`)}</span>
                </div>
            `).join("") : `
                <div class="chart-meta-row">
                    <span class="chart-meta-name"><span class="chart-dot" style="background:#94a3b8"></span>Belum ada data</span>
                    <span class="chart-meta-value">${formatCurrencyValue(0)}</span>
                </div>
            `;

            renderPieChart(
                "fitrahModeChart",
                "fitrahModeChart",
                ["Fitrah Beras", "Fitrah Uang"],
                [Number(data.totalJiwaFitrahBeras || 0), Number(data.totalJiwaFitrahUang || 0)],
                ["#c58a1d", "#1f5c8a"],
                null,
                false
            );
            document.getElementById("fitrahModeMeta").innerHTML = `
                <div class="chart-meta-row">
                    <span class="chart-meta-name"><span class="chart-dot" style="background:#c58a1d"></span>Beras</span>
                    <span class="chart-meta-value">${formatNumber(data.totalJiwaFitrahBeras)} jiwa</span>
                </div>
                <div class="chart-meta-row">
                    <span class="chart-meta-name"><span class="chart-dot" style="background:#1f5c8a"></span>Uang</span>
                    <span class="chart-meta-value">${formatNumber(data.totalJiwaFitrahUang)} jiwa</span>
                </div>
            `;

            document.getElementById("generatedAtLabel").textContent = `Diperbarui ${formatDateTime(data.generatedAt)}, berikutnya ${formatTime(nextHalfHourDate())}`;
        }

        async function refresh() {
            try {
                await loadSummary();
            } catch (error) {
                document.getElementById("generatedAtLabel").textContent = error.message || "Gagal memuat data";
            }
        }

        function nextHalfHourDate() {
            const now = new Date();
            const next = new Date(now);
            next.setSeconds(0, 0);
            if (now.getMinutes() < 30) {
                next.setMinutes(30);
            } else {
                next.setHours(now.getHours() + 1);
                next.setMinutes(0);
            }
            return next;
        }

        function msUntilNextHalfHour() {
            const now = new Date();
            const next = nextHalfHourDate();
            return Math.max(next.getTime() - now.getTime(), 1000);
        }

        function scheduleAlignedRefresh() {
            window.setTimeout(function () {
                refresh();
                scheduleAlignedRefresh();
            }, msUntilNextHalfHour());
        }

        updateClock();
        setInterval(updateClock, 1000);
        syncInputsForMode(currentMode);
        syncApplyButtonState();
        rangeButtons.forEach(button => {
            button.addEventListener("click", function () {
                setActiveMode(button.dataset.range);
                syncInputsForMode(currentMode);
                if (currentMode === "custom") {
                    return;
                }
                refresh();
                closeFilterModal();
            });
        });
        applyButton.addEventListener("click", function () {
            setActiveMode("custom");
            if (fromInput.value && toInput.value && fromInput.value > toInput.value) {
                document.getElementById("generatedAtLabel").textContent = "Tanggal mulai tidak boleh melebihi tanggal selesai";
                return;
            }
            refresh();
            closeFilterModal();
        });
        fromInput.addEventListener("change", function () {
            setActiveMode("custom");
        });
        toInput.addEventListener("change", function () {
            setActiveMode("custom");
        });
        toggleFiltersButton.addEventListener("click", function () {
            openFilterModal();
        });
        closeFilterModalButton.addEventListener("click", function () {
            closeFilterModal();
        });
        filterModal.addEventListener("click", function (event) {
            if (event.target === filterModal) {
                closeFilterModal();
            }
        });
        refresh();
        scheduleAlignedRefresh();
    })();
</script>
</body>
</html>
