@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <style>
        /* ---------- Judul bold di seluruh section ---------- */
        .dash-title,
        .chart-heading,
        .chart-eyebrow,
        .section-title {
            font-weight: 700;
        }

        /* ---------- Label KPI (Total Omset, Total Transaksi, dll) ---------- */
        /* Menggunakan variabel warna tema agar merespons dark/light mode */
        .kpi-label {
            font-size: 22px !important;
            font-weight: 800 !important;
            color: var(--text-main, #000000) !important;
            letter-spacing: 0.25px;
        }

        /* Pengaturan spesifik bila menggunakan data-theme */
        html[data-theme="light"] .kpi-label {
            color: #000000 !important;
        }
        html[data-theme="dark"] .kpi-label {
            color: #FFFFFF !important;
        }

        /* ---------- Nilai KPI (Angka / Nominal di bawahnya) ---------- */
        /* Dibuat BIASA (TIDAK BOLD) */
        .kpi-value {
            font-weight: 400 !important;
        }

        /* ---------- White mode: filter panel & kontrol harus jelas/tajam ---------- */
        html[data-theme="light"] .filter-panel {
            background: #FBFAF7;
            border: 1px solid #D9D3CD;
            border-top-color: #C9C2B8;
            box-shadow: 0 4px 14px rgba(16, 24, 40, 0.05);
        }
        html[data-theme="light"] .filter-panel .filter-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }
        html[data-theme="light"] .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        html[data-theme="light"] .filter-field label {
            font-size: 12px;
            font-weight: 600;
            color: #5C6675;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }
        html[data-theme="light"] .filter-select,
        html[data-theme="light"] .filter-input {
            background: #FFFFFF;
            border: 1px solid #C9C2B8;
            border-radius: 8px;
            color: #14161A;
            padding: 9px 12px;
            font-size: 14px;
            font-family: inherit;
            line-height: 1.4;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            appearance: none;
            -webkit-appearance: none;
        }
        html[data-theme="light"] .filter-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235C6675' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }
        html[data-theme="light"] .filter-select:hover,
        html[data-theme="light"] .filter-input:hover {
            border-color: #A69F96;
        }
        html[data-theme="light"] .filter-select:focus,
        html[data-theme="light"] .filter-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.12);
        }
        html[data-theme="light"] .filter-select:disabled,
        html[data-theme="light"] .filter-input:disabled {
            background: #EEF0F3;
            color: #8A94A3;
            border-color: #D9D3CD;
            cursor: not-allowed;
        }
        html[data-theme="light"] .filter-select option {
            background: #FFFFFF;
            color: #14161A;
        }
        @media (max-width: 576px) {
            html[data-theme="light"] .filter-panel .filter-fields {
                flex-direction: column;
                gap: 10px;
            }
            html[data-theme="light"] .filter-field { width: 100%; }
        }

        /* ---------- White mode: styling kartu grafik ----------
           Gaya light mode komponen grafik (.chart-card, label sumbu, dst.)
           ada di public/css/theme.css agar dipakai bersama semua halaman
           (termasuk /customers) - nilainya sama, dashboard tidak berubah. */

    </style>
    {{-- ===== Dashboard redesign layer =====
         Dimuat setelah theme.css + style lama (di atas), jadi aturan di sini
         menang untuk tampilan halaman ini. Struktur data & JS tidak berubah. --}}
    <style>
        /* Judul (kiri) + strip metrik (kanan) pada satu baris header.
           Semua nilai alignment ditulis eksplisit supaya blok judul tidak
           pernah tergeser ke kanan dan selalu rata kiri dengan konten. */
        .dash-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px 24px;
            width: 100%;
            margin: 0;
            text-align: left;
        }
        .dash-head-main {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            flex: 1 1 auto;
            min-width: 0;
            margin: 0;
            text-align: left;
        }
        .dash-head-main > div { width: 100%; margin: 0; text-align: left; }
        .dash-title,
        .dash-sub { margin-left: 0; text-align: left; }
        .dash-eyebrow { align-self: flex-start; }
        /* Strip metrik: menempel kanan selama masih satu baris, turun
           full-width di bawah judul bila layar tidak cukup lebar. */
        .metric-strip { flex: 0 0 auto; justify-content: flex-end; }

        /* Kartu metrik ringkas (periode / total operasional / net profit) */
        .metric-strip { display: flex; flex-wrap: wrap; gap: 10px; }
        .metric-strip .metric {
            min-width: 148px;
            padding: 10px 14px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }
        .metric-strip .metric span {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .metric-strip .metric strong {
            display: block;
            margin-top: 5px;
            font-size: 15px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--text);
        }
        .metric-strip .metric.is-cost strong { color: var(--warning); }
        .metric-strip .metric.is-profit strong { color: var(--success); }

        /* ---- KPI: netralkan aturan lama (font-size 22px & bold 800) ---- */
        .kpi .kpi-label,
        .kpi-label {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            letter-spacing: 0 !important;
            color: var(--muted) !important;
        }
        .kpi .kpi-value,
        .kpi-value { font-weight: 700 !important; }

        /* ---- Filter bar light mode: permukaan putih bersih ---- */
        html[data-theme="light"] .filter-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-top-color: var(--border);
            box-shadow: var(--shadow-card);
        }
        html[data-theme="light"] .filter-field label { color: var(--muted); }
        html[data-theme="light"] .filter-select,
        html[data-theme="light"] .filter-input {
            background: var(--input);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: var(--radius-sm);
        }
        /* Hover: tetap netral slate (aturan lama memakai abu kecoklatan #A69F96). */
        html[data-theme="light"] .filter-select:hover,
        html[data-theme="light"] .filter-input:hover {
            border-color: var(--border-strong);
        }
        html[data-theme="light"] .filter-select:focus,
        html[data-theme="light"] .filter-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-ring);
        }
        html[data-theme="light"] .filter-select:disabled,
        html[data-theme="light"] .filter-input:disabled {
            background: #F1F5F9;
            color: #94A3B8;
            border-color: var(--border);
        }
        html[data-theme="light"] .filter-select option { background: #FFFFFF; color: var(--text); }

        /* ---- Tabel rincian biaya ---- */
        .breakdown-card { overflow: hidden; }
        .breakdown-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 18px 16px;
            border-bottom: 1px solid var(--border);
        }
        .breakdown-title {
            margin: 10px 0 4px;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text);
        }
        .breakdown-sub { margin: 0; font-size: 12.5px; color: var(--muted); }
        .breakdown-table td:first-child { color: var(--muted); }
        .breakdown-table tr.total-row td:first-child,
        .breakdown-table tr.net-row td:first-child { color: var(--text); }
        .breakdown-table td:last-child,
        .breakdown-table th:last-child { text-align: right; font-variant-numeric: tabular-nums; }
        .breakdown-table td:last-child { font-weight: 600; }
        .row-tag {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            padding: 2px 7px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 999px;
        }
        .total-row .row-tag { color: var(--error); border-color: var(--error-ring); }
        .net-row .row-tag { color: var(--success); border-color: var(--success-ring); }

        /* ---- Tabel HPP & profit per produk ---- */
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .table-wrap + .chart-grid { margin-top: 16px; }
        .table-empty { padding: 26px 18px; text-align: center; font-size: 13px; color: var(--muted); }
        .product-table td, .product-table th { font-variant-numeric: tabular-nums; }
        .product-table th.numeric, .product-table td.numeric { text-align: right; }
        .product-table th.center, .product-table td.center { text-align: center; }
        .product-table .product-name { font-weight: 600; color: var(--text); }
        .margin-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            font-size: 11.5px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            border: 1px solid transparent;
            border-radius: 999px;
        }
        .margin-badge.is-high { color: var(--success); background: var(--success-soft); border-color: var(--success-ring); }
        .margin-badge.is-mid { color: var(--warning); background: var(--warning-soft); border-color: var(--warning-ring); }
        .margin-badge.is-low { color: var(--error); background: var(--error-soft); border-color: var(--error-ring); }
        .margin-badge.is-none { color: var(--muted); background: var(--surface-hover); border-color: var(--border); }

        /* ---- Section analitik: judul + catatan sejajar ---- */
        .section-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
        }
        .section-head-main { display: flex; flex-direction: column; gap: 10px; min-width: 0; }
        .section-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--muted);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 999px;
            font-variant-numeric: tabular-nums;
        }
        .section-note i {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
        }
        @media (max-width: 899.98px) {
            /* Layar sedang/sempit: strip metrik pindah ke baris kedua,
               full-width, rata kiri (opsi B) agar judul tetap bersih di kiri. */
            .metric-strip { flex: 1 1 100%; width: 100%; justify-content: flex-start; }
            .metric-strip .metric { flex: 1 1 170px; }
        }
        @media (max-width: 575.98px) {
            .metric-strip { width: 100%; }
            .metric-strip .metric { flex: 1 1 100%; }
        }

        /* ---- Netralkan aturan warna/ukuran lama ----
           Blok <style> lama di bagian atas section ini ikut ter-render di dalam
           <body> (setelah theme.css), jadi beberapa nilainya perlu ditegaskan
           ulang di sini agar desain baru benar-benar terpakai. */
        .kpi {
            gap: 10px;
            padding: 16px 18px;
            background: var(--card);
            background-image: none;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
        }
        .kpi::before,
        .kpi::after { content: none; }
        .kpi:hover {
            transform: translateY(-1px);
            border-color: var(--accent-ring);
            box-shadow: var(--shadow-pop);
        }
        .kpi-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            color: var(--accent);
            background: var(--accent-soft);
            border: 1px solid var(--accent-ring);
        }
        .kpi-icon svg { width: 16px; height: 16px; }
        .kpi-value {
            font-size: clamp(20px, 1.7vw, 26px);
            font-weight: 700 !important;
            letter-spacing: -0.01em;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        html[data-theme="dark"] .kpi .kpi-label { color: var(--muted) !important; }
        html[data-theme="light"] .kpi .kpi-label { color: var(--muted) !important; }
        .card,
        .breakdown-card {
            background: var(--card);
            background-image: none;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
        }
        .chart-heading { font-weight: 800; }

    </style>


    @php
        // Indikator turunan untuk tag status pada kartu KPI — dihitung dari
        // metrik yang sudah tersedia (tanpa query tambahan).
        $netMarginPct = $totalOmset > 0 ? ($netProfit / $totalOmset) * 100 : 0;
        $totalPcsSold = 0;

        foreach ($products as $productRow) {
            $totalPcsSold += (int) $productRow['total_pcs'];
        }

        $repeatRatePct = $totalTransaksi > 0 ? ($pelangganAktif / $totalTransaksi) * 100 : 0;
    @endphp

    <div class="dash-head">
        <div class="dash-head-main">
            <span class="dash-eyebrow">Ringkasan</span>
            <div>
                <h1 class="dash-title">Dashboard</h1>
                <p class="dash-sub">Pantau performans bisnis hijab Anda dalam satu platform secara real-time.</p>
            </div>
        </div>

        <div class="metric-strip" aria-label="Ringkasan periode aktif">
            <div class="metric">
                <span>Periode Aktif</span>
                <strong>{{ $periodLabel }}</strong>
            </div>
            <div class="metric is-cost">
                <span>Total Operasional</span>
                <strong>@include('partials.rupiah', ['value' => $totalOperasional])</strong>
            </div>
            <div class="metric {{ $netProfit >= 0 ? 'is-profit' : 'is-cost' }}">
                <span>Net Profit</span>
                <strong>@include('partials.rupiah', ['value' => $netProfit])</strong>
            </div>
        </div>
    </div>

    {{-- Filter Bar: Mode Periode, Bulan/Tahun, dan quick tabs pada satu baris --}}
    <form action="{{ route('dashboard') }}" method="get" class="filter-panel" id="filterForm">
        @include('partials.period-filter', ['disableYear' => $chartMode === 'yearly'])

        {{-- Grafik Rentang Waktu (quick tabs) --}}
        <div class="daterange-pills" role="group" aria-label="Filter rentang waktu grafik">
            @foreach ($chartDaterange as $pill)
                @php
                    $isActive = ((string) ($range ?? '') === $pill['value']);
                    $pillQuery = ['range' => $pill['value'], 'month' => $monthKey, 'year' => $year];
                    if (($filterMode ?? 'specific') === 'custom_range') {
                        $pillQuery += [
                            'filter_mode' => 'custom_range',
                            'start_month' => $startMonth,
                            'start_year' => $startYear,
                            'end_month' => $endMonth,
                            'end_year' => $endYear,
                        ];
                    }
                @endphp
                <a href="{{ route('dashboard', $pillQuery) }}"
                   class="pill{{ $isActive ? ' pill-active' : '' }}"
                   data-range="{{ $pill['value'] }}"
                   data-mode="{{ $pill['mode'] }}"
                   aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                    {{ $pill['label'] }}
                    @if(isset($pill['count']) && $pill['count'] !== null)
                        <span class="pill-count">({{ $pill['count'] }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        <input type="hidden" name="range" value="{{ $range ?? '1bln' }}">
    </form>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Total Omset</span>
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </span>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $totalOmset])</div>
            <div class="kpi-foot">
                <span class="kpi-trend {{ $netProfit >= 0 ? 'is-up' : 'is-down' }}">
                    {{ $netProfit >= 0 ? 'Profit' : 'Rugi' }} {{ number_format($netMarginPct, 1) }}%
                </span>
                <span class="kpi-note">margin bersih</span>
            </div>
        </div>

        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Total Transaksi</span>
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                    </svg>
                </span>
            </div>
            <div class="kpi-value">{{ number_format($totalTransaksi) }}</div>
            <div class="kpi-foot">
                <span class="kpi-trend is-neutral">{{ number_format($totalPcsSold) }} pcs</span>
                <span class="kpi-note">terjual di periode ini</span>
            </div>
        </div>

        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Rata-rata Nilai Order</span>
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                        <polyline points="17 6 23 6 23 12" />
                    </svg>
                </span>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $averageOrder])</div>
            <div class="kpi-foot">
                <span class="kpi-note">Nilai rata-rata setiap order pada {{ $periodLabel }}</span>
            </div>
        </div>

        <div class="kpi">
            <div class="kpi-head">
                <span class="kpi-label">Pelanggan Repeat</span>
                <span class="kpi-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </span>
            </div>
            <div class="kpi-value">{{ number_format($pelangganAktif) }}</div>
            <div class="kpi-foot">
                <span class="kpi-trend {{ $pelangganAktif > 0 ? 'is-up' : 'is-neutral' }}">
                    {{ number_format($repeatRatePct, 0) }}% repeat rate
                </span>
            </div>
        </div>
    </div>

    {{-- Rincian Total Operasional --}}
    <div class="card breakdown-card mt-4">
        <div class="breakdown-head">
            <div>
                <span class="chart-eyebrow">Biaya</span>
                <h2 class="breakdown-title">Rincian Total Operasional</h2>
                <p class="breakdown-sub">Akumulasi seluruh komponen biaya untuk {{ $periodLabel }}, termasuk biaya marketing.</p>
            </div>
            <div class="metric-strip">
                <div class="metric is-cost">
                    <span>Total Operasional</span>
                    <strong>@include('partials.rupiah', ['value' => $totalOperasional])</strong>
                </div>
                <div class="metric {{ $netProfit >= 0 ? 'is-profit' : 'is-cost' }}">
                    <span>Net Profit</span>
                    <strong>@include('partials.rupiah', ['value' => $netProfit])</strong>
                </div>
            </div>
        </div>
        <table class="table table-striped breakdown-table mb-0">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total HPP (order_items)</td>
                    <td>@include('partials.rupiah', ['value' => $totalHPP])</td>
                </tr>
                <tr>
                    <td>Total Ongkir</td>
                    <td>@include('partials.rupiah', ['value' => $totalOngkir])</td>
                </tr>
                <tr>
                    <td>Biaya Operasional (Fix/Variable Cost)</td>
                    <td>@include('partials.rupiah', ['value' => $totalOperasionalExpenses])</td>
                </tr>
                <tr>
                    <td>Biaya Marketing (Marketing Spend)</td>
                    <td>@include('partials.rupiah', ['value' => $marketingSpend])</td>
                </tr>
                <tr class="total-row">
                    <td>Total Operasional <span class="row-tag">Kas Keluar</span></td>
                    <td>@include('partials.rupiah', ['value' => $totalOperasional])</td>
                </tr>
                <tr class="net-row">
                    <td>Net Profit (Total Omset - Total Operasional) <span class="row-tag">Bersih</span></td>
                    <td>@include('partials.rupiah', ['value' => $netProfit])</td>
                </tr>
            </tbody>
        </table>
    </div>

    @php
        $chartMonths = [];
        $chartFullLabels = [];
        $chartOmset = [];
        $chartOperasional = [];
        $chartNetProfit = [];
        $chartMarketing = [];
        $chartMer = [];
        $chartRoi = [];

        foreach ($series as $item) {
            $chartMonths[] = $item['label'];
            $chartFullLabels[] = $item['full'];
            $chartOmset[] = (int) $item['omset'];
            $chartOperasional[] = (int) $item['total_operacional'];
            $chartNetProfit[] = (int) $item['net_profit'];
            $chartMarketing[] = (int) $item['marketing'];
            $chartMer[] = (float) $item['mer'];
            $chartRoi[] = (float) $item['roi'];
        }

        $chartEmptyTrend = true;
        $chartEmptyMarketing = true;

        foreach ($chartOmset as $v) {
            if ((int) $v > 0) { $chartEmptyTrend = false; break; }
        }

        foreach ($chartMarketing as $v) {
            if ((int) $v > 0) { $chartEmptyMarketing = false; break; }
        }

        // Kesiapan data grafik HPP & profit per produk (digabung dari laporan).
        $chartEmptyProduct = count($productChartNama) === 0;
        $chartEmptyShare = count($productShareNama) === 0;

        // Granularitas grafik: mode "daily" - termasuk rentang kustom yang
        // menunjuk satu bulan yang sama (mis. Sep 2026 - Sep 2026) - membuat
        // sumbu X menampilkan label tanggal ("01 Sep", "02 Sep", ...) dan
        // kurva area yang mulus, bukan kolom bulanan.
        $isDailyChart = $chartMode === 'daily';

        $chartTrendTitle = match ($chartMode) {
            'daily' => 'Tren Keuangan Harian',
            'custom' => 'Tren Keuangan Rentang Kustom',
            'yearly' => 'Tren Keuangan Tahunan',
            default => 'Tren Keuangan Bulanan',
        };

        $chartTrendDesc = match ($chartMode) {
            'daily' => 'Omset vs Total Operasional dan Net Profit per tanggal (' . $periodLabel . ')',
            'custom' => 'Omset vs Total Operasional dan Net Profit per bulan (' . $periodLabel . ')',
            'yearly' => 'Omset vs Total Operasional dan Net Profit per tahun',
            default => 'Omset vs Total Operasional dan Net Profit per bulan',
        };

        $chartMerTitle = match ($chartMode) {
            'daily' => 'Budget Iklan & MER Harian',
            'custom' => 'Budget Iklan & MER Rentang Kustom',
            'yearly' => 'Budget Iklan & MER Tahunan',
            default => 'Budget Iklan & MER Bulanan',
        };
    @endphp

    {{-- Section Grafik --}}
    <div class="chart-section">
        <div class="section-head">
            <div class="section-head-main">
                <span class="chart-eyebrow">Analisis</span>
                <div>
                    <h2 class="chart-heading">Analisis Grafik</h2>
                    <p class="chart-sub">Tren performans keuangan dan efisiensi marketing untuk {{ $periodLabel }}.</p>
                </div>
            </div>
            <span class="section-note"><i></i>{{ $periodLabel }}</span>
        </div>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-trend',
                'title' => $chartTrendTitle,
                'desc' => $chartTrendDesc,
                'empty' => $chartEmptyTrend,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-mer',
                'title' => $chartMerTitle,
                'desc' => 'Budget Iklan per periode (kolom) dan MER % (garis)',
                'empty' => array_sum($chartMarketing) === 0,
            ])
        </div>
    </div>

    {{-- Efisiensi Marketing: MER & ROI (digabung dari Laporan MER & ROI) --}}
    <div class="chart-section">
        <div class="section-head">
            <div class="section-head-main">
                <span class="chart-eyebrow">Efisiensi Marketing</span>
                <div>
                    <h2 class="chart-heading">MER &amp; ROI</h2>
                    <p class="chart-sub">Seberapa efektif budget iklan menghasilkan omset dan profit untuk {{ $periodLabel }}.</p>
                </div>
            </div>
            <span class="section-note"><i></i>Spend @include('partials.rupiah', ['value' => $marketingSpend])</span>
        </div>

        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-head">
                    <span class="kpi-label">MER (Spend / Omset)</span>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="5" x2="5" y2="19" />
                            <circle cx="6.5" cy="6.5" r="2.5" />
                            <circle cx="17.5" cy="17.5" r="2.5" />
                        </svg>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($mer, 2) }}%</div>
                <div class="kpi-foot">
                    <span class="kpi-trend {{ $mer <= 100 ? 'is-up' : 'is-warn' }}">
                        {{ $mer <= 100 ? 'Efisien' : 'Spend tinggi' }}
                    </span>
                    <span class="kpi-note">makin kecil makin efisien</span>
                </div>
            </div>

            <div class="kpi">
                <div class="kpi-head">
                    <span class="kpi-label">ROI (Net Profit / Spend)</span>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                            <polyline points="17 6 23 6 23 12" />
                        </svg>
                    </span>
                </div>
                <div class="kpi-value">{{ number_format($roi, 2) }}%</div>
                <div class="kpi-foot">
                    <span class="kpi-trend {{ $roi >= 0 ? 'is-up' : 'is-down' }}">
                        {{ $roi >= 0 ? 'Untung' : 'Belum balik modal' }}
                    </span>
                </div>
            </div>

            <div class="kpi">
                <div class="kpi-head">
                    <span class="kpi-label">Marketing Spend</span>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 11l18-8-8 18-2-8-8-2z" />
                        </svg>
                    </span>
                </div>
                <div class="kpi-value">@include('partials.rupiah', ['value' => $marketingSpend])</div>
                <div class="kpi-foot">
                    <span class="kpi-note">Total budget iklan pada {{ $periodLabel }}</span>
                </div>
            </div>

            <div class="kpi">
                <div class="kpi-head">
                    <span class="kpi-label">Net Profit</span>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="14" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                    </span>
                </div>
                <div class="kpi-value">@include('partials.rupiah', ['value' => $netProfit])</div>
                <div class="kpi-foot">
                    <span class="kpi-trend {{ $netProfit >= 0 ? 'is-up' : 'is-down' }}">
                        {{ $netProfit >= 0 ? 'Profit' : 'Rugi' }}
                    </span>
                    <span class="kpi-note">setelah semua biaya</span>
                </div>
            </div>
        </div>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-mer-spend',
                'title' => 'Budget Iklan vs Omset & MER%',
                'desc' => 'Marketing spend (batang), Omset (garis), dan MER% (garis)',
                'empty' => $chartEmptyMarketing,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-mer-roi',
                'title' => 'Tren MER% & ROI%',
                'desc' => 'Efisiensi marketing per periode',
                'empty' => $chartEmptyMarketing,
            ])
        </div>
    </div>

    {{-- HPP & Profit per Produk (digabung dari Laporan HPP & Profit) --}}
    <div class="chart-section">
        <div class="section-head">
            <div class="section-head-main">
                <span class="chart-eyebrow">HPP &amp; Profit</span>
                <div>
                    <h2 class="chart-heading">HPP &amp; Profit per Produk</h2>
                    <p class="chart-sub">Rincian omset, HPP, dan margin tiap produk untuk {{ $periodLabel }}.</p>
                </div>
            </div>
            <span class="section-note"><i></i>{{ number_format($products ? count($products) : 0) }} produk terjual</span>
        </div>

        <div class="table-wrap mt-3">
            <table class="table table-striped product-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="center">Qty Terjual</th>
                        <th class="numeric">Total Omset</th>
                        <th class="numeric">Total HPP</th>
                        <th class="numeric">Margin Profit</th>
                        <th class="numeric">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    @php
                        // Badge margin: hijau > 40%, kuning 20-40%, merah di bawah 20%.
                        $marginPct = (float) $product['margin_pct'];
                        $marginClass = $marginPct > 40 ? 'is-high' : ($marginPct >= 20 ? 'is-mid' : ($marginPct > 0 ? 'is-low' : 'is-none'));
                    @endphp
                    <tr>
                        <td class="product-name">{{ $product['nama_produk'] }}</td>
                        <td class="center">{{ $product['total_pcs'] }}</td>
                        <td class="numeric">@include('partials.rupiah', ['value' => $product['total_omset']])</td>
                        <td class="numeric">@include('partials.rupiah', ['value' => $product['total_hpp']])</td>
                        <td class="numeric">@include('partials.rupiah', ['value' => $product['margin']])</td>
                        <td class="numeric">
                            <span class="margin-badge {{ $marginClass }}">{{ number_format($marginPct, 2) }}%</span>
                        </td>
                    </tr>
                @endforeach
                @if (empty($products))
                    <tr>
                        <td colspan="6" class="table-empty">Belum ada transaksi untuk periode ini.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-hpp-bar',
                'title' => 'Omset vs HPP vs Margin',
                'desc' => 'Top 10 produk (per omset)',
                'empty' => $chartEmptyProduct,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-hpp-share',
                'title' => 'Pembagian Margin',
                'desc' => 'Margin per produk (top 8)',
                'empty' => $chartEmptyShare,
            ])
        </div>
    </div>

   @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;
                var chartFullLabels = @json($chartFullLabels);
                var isDaily = @json($isDailyChart);

                /* Permukaan tooltip: sudut 10px, border tipis, backdrop blur. */
                var tooltipSurface = 'border-radius:10px;padding:10px 12px;min-width:214px;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)';
                var tooltipBox = function () {
                    return chartPalette.tooltipTheme === 'light'
                        ? 'background:rgba(255,255,255,0.96);color:#0F172A;border:1px solid #E3E6EC;box-shadow:0 12px 28px -8px rgba(15,23,42,0.22)'
                        : 'background:rgba(22,25,34,0.94);color:#F8FAFC;border:1px solid #262B38;box-shadow:0 16px 34px -10px rgba(0,0,0,0.7)';
                };

                function tipTitle(text) {
                    var bg = tooltipBox();
                    var borderColor = chartPalette.tooltipTheme === 'light' ? 'rgba(15,23,42,0.08)' : 'rgba(255,255,255,0.08)';
                    return '<div style="' + bg + ';' + tooltipSurface + ';border-bottom:1px solid ' + borderColor + ';font-weight:700;margin-bottom:6px;padding-bottom:4px;font-size:12px">'
                        + text
                        + '</div>';
                }

                function tipRow(label, value, color) {
                    var dot = color ? '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';margin-right:6px"></span>' : '';
                    var textColor = chartPalette.tooltipTheme === 'light' ? '#0F172A' : '#F8FAFC';
                    var labelColor = chartPalette.tooltipTheme === 'light' ? '#475569' : '#94A3B8';
                    return '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.7;color:' + textColor + ';font-size:12px">'
                        + '<span style="color:' + labelColor + '">' + dot + label + '</span>'
                        + '<strong style="font-variant-numeric:tabular-nums;color:' + textColor + '">' + value + '</strong>'
                        + '</div>';
                }

                function tipContainer() {
                    return '<div style="' + tooltipBox() + ';' + tooltipSurface + '">';
                }

                var chartInstances = [];
                var chartPalette = (function () {
                    var light = document.documentElement.getAttribute('data-theme') === 'light';
                    return light ? {
                        axisLabelColor: '#5C6675',
                        gridBorder: 'rgba(0, 0, 0, 0.08)',
                        gridDash: 4,
                        legendColor: '#5C6675',
                        tooltipTheme: 'light',
                    } : {
                        axisLabelColor: '#94A3B8',
                        gridBorder: 'rgba(255, 255, 255, 0.05)',
                        gridDash: 4,
                        legendColor: '#94A3B8',
                        tooltipTheme: 'dark',
                    };
                })();

                var commonXaxis = {
                    categories: @json($chartMonths),
                    tickAmount: isDaily ? 8 : undefined,
                    labels: {
                        rotate: 0,
                        hideOverlappingLabels: true,
                        style: { colors: chartPalette.axisLabelColor, fontSize: '11px', fontFamily: "'Inter', sans-serif" }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                };

                var commonGrid = {
                    borderColor: chartPalette.gridBorder,
                    strokeDashArray: chartPalette.gridDash,
                    padding: { left: 10, right: 10 }
                };

                function syncChartTheme() {
                    var light = document.documentElement.getAttribute('data-theme') === 'light';
                    var next = light ? {
                        axisLabelColor: '#5C6675',
                        gridBorder: 'rgba(0, 0, 0, 0.08)',
                        gridDash: 4,
                        legendColor: '#5C6675',
                        tooltipTheme: 'light',
                    } : {
                        axisLabelColor: '#94A3B8',
                        gridBorder: 'rgba(255, 255, 255, 0.05)',
                        gridDash: 4,
                        legendColor: '#94A3B8',
                        tooltipTheme: 'dark',
                    };
                    if (next.axisLabelColor === chartPalette.axisLabelColor) { return; }
                    chartPalette = next;
                    chartInstances.forEach(function (chart) {
                        chart.updateOptions({
                            xaxis: { labels: { style: { colors: next.axisLabelColor } } },
                            yaxis: { labels: { style: { colors: next.axisLabelColor } } },
                            grid: { borderColor: next.gridBorder, strokeDashArray: next.gridDash },
                            legend: { labels: { colors: next.legendColor } },
                            tooltip: { theme: next.tooltipTheme }
                        });
                    });
                }
                if (window.MutationObserver) {
                    new MutationObserver(syncChartTheme).observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['data-theme']
                    });
                }

                if (document.getElementById('chart-trend')) {
                    new ApexCharts(document.getElementById('chart-trend'), KC.base({
                        chart: { 
                            type: isDaily ? 'area' : 'bar',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: [
                            { name: 'Omset', type: isDaily ? 'area' : 'column', data: @json($chartOmset) },
                            { name: 'Total Operasional', type: isDaily ? 'area' : 'column', data: @json($chartOperasional) },
                            { name: 'Net Profit', type: 'line', data: @json($chartNetProfit) }
                        ],
                        xaxis: commonXaxis,
                        grid: commonGrid,
                        stroke: { 
                            width: isDaily ? [2, 2, 2.5] : [0, 0, 2.5], 
                            curve: 'smooth' 
                        },
                        fill: {
                            type: isDaily ? ['gradient', 'gradient', 'solid'] : 'solid',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.03,
                                stops: [0, 90, 100]
                            }
                        },
                        plotOptions: { 
                            bar: { columnWidth: isDaily ? '30%' : '45%', borderRadius: 4 } 
                        },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        colors: ['#EC4899', '#F59E0B', '#10B981'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var omset = opts.series[0][i] || 0;
                                var operasional = opts.series[1][i] || 0;
                                var netProfit = opts.series[2][i] || 0;

                                return tipContainer()
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('Omset', KC.rupiah(omset), '#EC4899')
                                    + tipRow('Total Operasional', KC.rupiah(operasional), '#F59E0B')
                                    + tipRow('Net Profit', KC.rupiah(netProfit), '#10B981')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }
                    })).render();
                }

                if (document.getElementById('chart-mer')) {
                    new ApexCharts(document.getElementById('chart-mer'), KC.base({
                        chart: { 
                            type: 'line',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: [
                            { name: 'Marketing Spend', type: isDaily ? 'area' : 'column', data: @json($chartMarketing) },
                            { name: 'MER %', type: 'line', data: @json($chartMer) }
                        ],
                        xaxis: commonXaxis,
                        grid: commonGrid,
                        stroke: { 
                            width: isDaily ? [1.8, 2.5] : [0, 2.5], 
                            curve: 'monotoneCubic' 
                        },
                        fill: {
                            type: isDaily ? ['gradient', 'solid'] : 'solid',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.25,
                                opacityTo: 0.02,
                                stops: [0, 90, 100]
                            }
                        },
                        plotOptions: { 
                            bar: { columnWidth: '35%', borderRadius: 4 } 
                        },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        yaxis: [
                            { labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } },
                            { opposite: true, labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } }
                        ],
                        colors: ['#06B6D4', '#F59E0B'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var spend = opts.series[0][i] || 0;
                                var mer = opts.series[1][i] || 0;

                                return tipContainer()
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('Budget Iklan', KC.rupiah(spend), '#06B6D4')
                                    + tipRow('MER', KC.pct(mer), '#F59E0B')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }
                    })).render();
                }

                if (document.getElementById('chart-mer-spend')) {
                    KC.register(new ApexCharts(document.getElementById('chart-mer-spend'), KC.base({
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: [
                            { name: 'Marketing Spend', type: 'column', data: @json($chartMarketing) },
                            { name: 'Omset', type: 'line', data: @json($chartOmset) },
                            { name: 'MER %', type: 'line', data: @json($chartMer) }
                        ],
                        xaxis: commonXaxis,
                        grid: commonGrid,
                        stroke: { width: [0, 2.4, 2.4], curve: 'monotoneCubic' },
                        plotOptions: { bar: { columnWidth: '35%', borderRadius: 4 } },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        yaxis: [
                            { seriesName: 'Marketing Spend', labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } },
                            { seriesName: 'Omset', show: false },
                            { opposite: true, seriesName: 'MER %', labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } }
                        ],
                        colors: ['#06B6D4', '#EC4899', '#F59E0B'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var spend = opts.series[0][i] || 0;
                                var omset = opts.series[1][i] || 0;
                                var mer = opts.series[2][i] || 0;

                                return tipContainer()
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('Budget Iklan', KC.rupiah(spend), '#06B6D4')
                                    + tipRow('Omset', KC.rupiah(omset), '#EC4899')
                                    + tipRow('MER %', KC.pct(mer), '#F59E0B')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }
                    }))).render();
                }

                if (document.getElementById('chart-mer-roi')) {
                    KC.register(new ApexCharts(document.getElementById('chart-mer-roi'), KC.base({
                        chart: {
                            type: 'line',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: [
                            { name: 'MER %', data: @json($chartMer) },
                            { name: 'ROI %', data: @json($chartRoi) }
                        ],
                        xaxis: commonXaxis,
                        grid: commonGrid,
                        stroke: { width: 2.4, curve: 'monotoneCubic' },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        yaxis: { labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } },
                        colors: ['#F59E0B', '#10B981'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var mer = opts.series[0][i] || 0;
                                var roi = opts.series[1][i] || 0;

                                return tipContainer()
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('MER %', KC.pct(mer), '#F59E0B')
                                    + tipRow('ROI %', KC.pct(roi), '#10B981')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }
                    }))).render();
                }

                if (document.getElementById('chart-hpp-bar')) {
                    KC.register(new ApexCharts(document.getElementById('chart-hpp-bar'), KC.base({
                        chart: {
                            type: 'bar',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: [
                            { name: 'Omset', data: @json($productChartOmset) },
                            { name: 'HPP', data: @json($productChartHpp) },
                            { name: 'Margin', data: @json($productChartMargin) }
                        ],
                        xaxis: {
                            categories: @json($productChartNama),
                            labels: {
                                rotate: -28,
                                hideOverlappingLabels: true,
                                style: { colors: chartPalette.axisLabelColor, fontSize: '11px', fontFamily: "'Inter', sans-serif" }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        grid: commonGrid,
                        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                        colors: ['#EC4899', '#F59E0B', '#10B981'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            y: { formatter: function (v) { return KC.rupiah(v); } }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } }
                    }))).render();
                }

                if (document.getElementById('chart-hpp-share')) {
                    KC.register(new ApexCharts(document.getElementById('chart-hpp-share'), KC.base({
                        chart: {
                            type: 'donut',
                            height: 320,
                            toolbar: { show: false }
                        },
                        series: @json($productShareValue),
                        labels: @json($productShareNama),
                        plotOptions: { pie: { donut: { size: '68%' } } },
                        dataLabels: { enabled: true, formatter: function (val) { return KC.pct(val); } },
                        legend: { show: true, position: 'bottom', labels: { colors: chartPalette.legendColor } },
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            y: { formatter: function (v) { return KC.rupiah(v); } }
                        },
                        colors: KC.colors.slice()
                    }))).render();
                }
            });
        </script>
    @endpush
@endsection