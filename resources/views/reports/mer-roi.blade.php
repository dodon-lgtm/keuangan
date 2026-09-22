@extends('layouts.app')

@section('title', 'Laporan MER & ROI')

@section('content')
    <style>
        /* Semua teks mengikuti tema via CSS variable */
        h1 { color: var(--text); }
        .form-label { color: var(--muted); }

        .card-title,
        .card-title.text-muted,
        .card-text { color: var(--text); }
        .card-title.text-muted { color: var(--text) !important; }

        .card-header h2,
        .card-header .h5 { color: var(--text); }

        .table thead th,
        .table tbody td { color: var(--text); }
        .table thead th { font-weight: 600; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan MER &amp; ROI</h1>
    </div>

    <form action="{{ route('reports.mer-roi') }}" method="get" class="filter-panel">
        @include('partials.period-filter')

        <button type="submit" class="filter-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="8" r="6.2" />
                <rect x="3" y="4.5" width="10" height="2" /><rect x="3" y="11.5" width="10" height="2" /><rect x="3" y="4.5" width="2" height="7" /><rect x="11" y="4.5" width="2" height="7" />
            </svg>
            Filter
        </button>
        <a href="{{ route('reports.mer-roi') }}" class="filter-reset">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h10M8 3v10" /></svg>
            Reset
        </a>
    </form>

    {{-- Top Cards Summary --}}
    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Omset</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalOmset])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Operasional</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalOperasional])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Net Profit</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $netProfit])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Marketing Spend</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $marketingSpend])</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MER & ROI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">MER (Spend / Omset)</h5>
                    <p class="card-text fs-3">{{ number_format($mer, 2) }}%</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">ROI (Net Profit / Spend)</h5>
                    <p class="card-text fs-3">{{ number_format($roi, 2) }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Biaya Operasional --}}
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Rincian Biaya Operasional</h2>
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
            </tbody>
        </table>
    </div>

    @php
        $chartMonths = [];
        $chartOmset = [];
        $chartMarketing = [];
        $chartMer = [];
        $chartRoi = [];

        foreach ($series as $item) {
            $chartMonths[] = $item['label'];
            $chartOmset[] = (int) $item['omset'];
            $chartMarketing[] = (int) $item['marketing'];
            $chartMer[] = (float) $item['mer'];
            $chartRoi[] = (float) $item['roi'];
        }

        $marketingHasData = false;
        foreach ($chartMarketing as $v) { if ((int) $v > 0) { $marketingHasData = true; break; } }
    @endphp

    {{-- Section Grafik --}}
    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Tren MER &amp; ROI serta hubungan antara budget iklan dan omset untuk {{ $periodLabel }}.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-mer-spend',
                'title' => 'Budget Iklan vs Omset & MER%',
                'desc' => 'Marketing spend (batang), Omset (garis), dan MER% (garis)',
                'empty' => ! $marketingHasData,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-mer-roi',
                'title' => 'Tren MER% & ROI%',
                'desc' => 'Efisiensi marketing per bulan',
                'empty' => ! $marketingHasData,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                function tipTitle(text) {
                    return '<div style="font-weight:700;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid rgba(255,255,255,.14);color:#f8fafc;font-size:12px">'
                        + text
                        + '</div>';
                }

                function tipRow(label, value, color) {
                    var dot = color ? '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';margin-right:6px"></span>' : '';
                    return '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;line-height:1.8;color:#f8fafc;font-size:12px">'
                        + '<span>' + dot + label + '</span>'
                        + '<strong style="font-variant-numeric:tabular-nums">' + value + '</strong>'
                        + '</div>';
                }

                var commonGrid = {
                    borderColor: 'rgba(255, 255, 255, 0.05)',
                    strokeDashArray: 4,
                    padding: { left: 10, right: 10 }
                };

                // --- 1. GRAFIK BUDGET IKLAN VS OMSET & MER% ---
                if (document.getElementById('chart-mer-spend')) {
                    new ApexCharts(document.getElementById('chart-mer-spend'), KC.base({
                        chart: { type: 'bar', height: 320, toolbar: { show: false } },
                        series: [
                            { name: 'Marketing Spend', type: 'column', data: @json($chartMarketing) },
                            { name: 'Omset', type: 'line', data: @json($chartOmset) },
                            { name: 'MER %', type: 'line', data: @json($chartMer) }
                        ],
                        xaxis: {
                            categories: @json($chartMonths),
                            labels: { style: { colors: '#94A3B8', fontSize: '11px', fontFamily: "'Inter', sans-serif" } }
                        },
                        grid: commonGrid,
                        stroke: { width: [0, 2.4, 2.4], curve: 'smooth' },
                        plotOptions: { bar: { columnWidth: '35%', borderRadius: 4 } },
                        markers: { size: 3, hover: { size: 6 } },
                        yaxis: [
                            {
                                seriesName: 'Marketing Spend',
                                labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: '#94A3B8', fontFamily: "'Inter', sans-serif" } }
                            },
                            {
                                seriesName: 'Omset',
                                show: false
                            },
                            {
                                opposite: true,
                                seriesName: 'MER %',
                                labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: '#94A3B8', fontFamily: "'Inter', sans-serif" } }
                            }
                        ],
                        colors: ['#06B6D4', '#38BDF8', '#F59E0B'], // Cyan (Spend), Sky Blue (Omset), Amber (MER %)
                        tooltip: {
                            theme: 'dark',
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined) return '';

                                var spend = opts.series[0][i] || 0;
                                var omset = opts.series[1][i] || 0;
                                var mer = opts.series[2][i] || 0;
                                var monthName = opts.w.globals.labels[i] || '';

                                return '<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">'
                                    + tipTitle(monthName)
                                    + tipRow('Marketing Spend', KC.rupiah(spend), '#06B6D4')
                                    + tipRow('Omset', KC.rupiah(omset), '#38BDF8')
                                    + tipRow('MER %', KC.pct(mer), '#F59E0B')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: '#94A3B8' } }
                    })).render();
                }

                // --- 2. GRAFIK TREN MER% & ROI% ---
                if (document.getElementById('chart-mer-roi')) {
                    new ApexCharts(document.getElementById('chart-mer-roi'), KC.base({
                        chart: { type: 'line', height: 320, toolbar: { show: false } },
                        series: [
                            { name: 'MER %', data: @json($chartMer) },
                            { name: 'ROI %', data: @json($chartRoi) }
                        ],
                        xaxis: {
                            categories: @json($chartMonths),
                            labels: { style: { colors: '#94A3B8', fontSize: '11px', fontFamily: "'Inter', sans-serif" } }
                        },
                        grid: commonGrid,
                        stroke: { width: 2.5, curve: 'smooth' },
                        yaxis: {
                            labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: '#94A3B8', fontFamily: "'Inter', sans-serif" } }
                        },
                        markers: { size: 3, hover: { size: 6 } },
                        colors: ['#F43F5E', '#22C55E'], // Rose (MER %), Emerald (ROI %)
                        tooltip: {
                            theme: 'dark',
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined) return '';

                                var mer = opts.series[0][i] || 0;
                                var roi = opts.series[1][i] || 0;
                                var monthName = opts.w.globals.labels[i] || '';

                                return '<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">'
                                    + tipTitle(monthName)
                                    + tipRow('MER %', KC.pct(mer), '#F43F5E')
                                    + tipRow('ROI %', KC.pct(roi), '#22C55E')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: '#94A3B8' } }
                    })).render();
                }
            });
        </script>
    @endpush
@endsection