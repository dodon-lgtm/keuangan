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

    <div class="dash-head">
        <span class="dash-eyebrow">Ringkasan</span>
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-sub">Pantau performans bisnis hijab Anda dalam satu platform secara real-time.</p>
        </div>
    </div>

    {{-- Filter Panel (horizontal, auto-submit) --}}
    <form action="{{ route('dashboard') }}" method="get" class="filter-panel" id="filterForm">
        @include('partials.period-filter', ['disableYear' => $chartMode === 'yearly'])

        <input type="hidden" name="range" value="{{ $range ?? '1bln' }}">
    </form>

    {{-- Grafik Rentang Waktu --}}
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

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-label">Total Omset</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $totalOmset])</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Total Transaksi</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
            </div>
            <div class="kpi-value">{{ number_format($totalTransaksi) }}</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Rata-rata Nilai Order</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                    <polyline points="17 6 23 6 23 12" />
                </svg>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $averageOrder])</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Pelanggan Repeat</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <div class="kpi-value">{{ number_format($pelangganAktif) }}</div>
        </div>
    </div>

    {{-- Rincian Total Operasional --}}
    <div class="card mt-4">
        <div class="card-body">
            <span class="chart-eyebrow">Biaya</span>
            <h2 class="chart-heading">Rincian Total Operasional</h2>
            <p class="chart-sub mb-0">Akumulasi seluruh komponen biaya untuk {{ $periodLabel }}, termasuk biaya marketing.</p>
        </div>
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total HPP (order_items)</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $totalHPP])</td>
                </tr>
                <tr>
                    <td>Total Ongkir</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $totalOngkir])</td>
                </tr>
                <tr>
                    <td>Biaya Operasional (Fix/Variable Cost)</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $totalOperasionalExpenses])</td>
                </tr>
                <tr>
                    <td>Biaya Marketing (Marketing Spend)</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $marketingSpend])</td>
                </tr>
                <tr class="total-row fw-bold">
                    <td>Total Operasional</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $totalOperasional])</td>
                </tr>
                <tr>
                    <td>Net Profit (Total Omset - Total Operasional)</td>
                    <td class="text-end">@include('partials.rupiah', ['value' => $netProfit])</td>
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
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Tren performans keuangan dan efisiensi marketing untuk {{ $periodLabel }}.</p>

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
        <span class="chart-eyebrow">Efisiensi Marketing</span>
        <h2 class="chart-heading">MER &amp; ROI</h2>
        <p class="chart-sub">Seberapa efektif budget iklan menghasilkan omset dan profit untuk {{ $periodLabel }}.</p>

        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-label">MER (Spend / Omset)</div>
                <div class="kpi-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="5" x2="5" y2="19" />
                        <circle cx="6.5" cy="6.5" r="2.5" />
                        <circle cx="17.5" cy="17.5" r="2.5" />
                    </svg>
                </div>
                <div class="kpi-value">{{ number_format($mer, 2) }}%</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">ROI (Net Profit / Spend)</div>
                <div class="kpi-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                        <polyline points="17 6 23 6 23 12" />
                    </svg>
                </div>
                <div class="kpi-value">{{ number_format($roi, 2) }}%</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Marketing Spend</div>
                <div class="kpi-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 11l18-8-8 18-2-8-8-2z" />
                    </svg>
                </div>
                <div class="kpi-value">@include('partials.rupiah', ['value' => $marketingSpend])</div>
            </div>

            <div class="kpi">
                <div class="kpi-label">Net Profit</div>
                <div class="kpi-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="14" rx="2" />
                        <path d="M2 10h20" />
                    </svg>
                </div>
                <div class="kpi-value">@include('partials.rupiah', ['value' => $netProfit])</div>
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
        <span class="chart-eyebrow">HPP &amp; Profit</span>
        <h2 class="chart-heading">HPP &amp; Profit per Produk</h2>
        <p class="chart-sub">Rincian omset, HPP, dan margin tiap produk untuk {{ $periodLabel }}.</p>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty Terjual</th>
                    <th>Total Omset</th>
                    <th>Total HPP</th>
                    <th>Margin Profit</th>
                    <th>Margin %</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product['nama_produk'] }}</td>
                    <td>{{ $product['total_pcs'] }}</td>
                    <td>@include('partials.rupiah', ['value' => $product['total_omset']])</td>
                    <td>@include('partials.rupiah', ['value' => $product['total_hpp']])</td>
                    <td>@include('partials.rupiah', ['value' => $product['margin']])</td>
                    <td>{{ number_format((float) $product['margin_pct'], 2) }}%</td>
                </tr>
            @endforeach
            @if (empty($products))
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada transaksi untuk periode ini.</td>
                </tr>
            @endif
            </tbody>
        </table>

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

                function tipTitle(text) {
                    var bg = chartPalette.tooltipTheme === 'light' ? 'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)' : 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)';
                    var borderColor = chartPalette.tooltipTheme === 'light' ? 'rgba(0,0,0,0.12)' : 'rgba(255,255,255,0.14)';
                    return '<div style="' + bg + ';border-bottom:1px solid ' + borderColor + ';font-weight:700;margin-bottom:6px;padding-bottom:4px;font-size:12px">'
                        + text
                        + '</div>';
                }

                function tipRow(label, value, color) {
                    var dot = color ? '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';margin-right:6px"></span>' : '';
                    var textColor = chartPalette.tooltipTheme === 'light' ? '#14161A' : '#F8FAFC';
                    return '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.8;color:' + textColor + ';font-size:12px">'
                        + '<span>' + dot + label + '</span>'
                        + '<strong style="font-variant-numeric:tabular-nums;color:' + textColor + '">' + value + '</strong>'
                        + '</div>';
                }

                function tipContainer() {
                    var bg = chartPalette.tooltipTheme === 'light' ? 'background:#FFFFFF;color:#14161A;border:1px solid #D1C9BF;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.08)' : 'background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)';
                    return '<div style="' + bg + '">';
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
                        colors: ['#38BDF8', '#F59E0B', '#22C55E'],
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
                                    + tipRow('Omset', KC.rupiah(omset), '#38BDF8')
                                    + tipRow('Total Operasional', KC.rupiah(operasional), '#F59E0B')
                                    + tipRow('Net Profit', KC.rupiah(netProfit), '#22C55E')
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
                            curve: 'smooth' 
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
                        colors: ['#06B6D4', '#F43F5E'],
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
                                    + tipRow('MER', KC.pct(mer), '#F43F5E')
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
                        stroke: { width: [0, 2.4, 2.4], curve: 'smooth' },
                        plotOptions: { bar: { columnWidth: '35%', borderRadius: 4 } },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        yaxis: [
                            { seriesName: 'Marketing Spend', labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } },
                            { seriesName: 'Omset', show: false },
                            { opposite: true, seriesName: 'MER %', labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } }
                        ],
                        colors: ['#06B6D4', '#38BDF8', '#F59E0B'],
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
                                    + tipRow('Omset', KC.rupiah(omset), '#38BDF8')
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
                        stroke: { width: 2.5, curve: 'smooth' },
                        markers: { size: isDaily ? 0 : 3, hover: { size: 6 } },
                        yaxis: { labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: chartPalette.axisLabelColor, fontFamily: "'Inter', sans-serif" } } },
                        colors: ['#F43F5E', '#22C55E'],
                        tooltip: {
                            theme: chartPalette.tooltipTheme,
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var mer = opts.series[0][i] || 0;
                                var roi = opts.series[1][i] || 0;

                                return tipContainer()
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('MER %', KC.pct(mer), '#F43F5E')
                                    + tipRow('ROI %', KC.pct(roi), '#22C55E')
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
                        colors: ['#E11D48', '#F5B524', '#22C55E'],
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