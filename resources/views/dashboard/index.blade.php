@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="dash-head">
        <span class="dash-eyebrow">Ringkasan</span>
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-sub">Pantau performans bisnis hijab Anda dalam satu platform secara real-time.</p>
        </div>
    </div>

    {{-- Filter Panel --}}
    <form action="{{ route('dashboard') }}" method="get" class="filter-panel">
        @include('partials.period-filter', ['disableYear' => $chartMode === 'yearly'])

        <input type="hidden" name="range" value="{{ $range ?? '1bln' }}">

        <button type="submit" class="filter-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filter
        </button>
        <a href="{{ route('dashboard') }}" class="filter-reset">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                <path d="M3 3v5h5"></path>
            </svg>
            Reset
        </a>
    </form>

    {{-- Grafik Rentang Waktu --}}
    <div class="daterange-pills" role="group" aria-label="Filter rentang waktu grafik">
        @foreach ($chartDaterange as $pill)
            @php
                $isActive = ((string) ($range ?? '') === $pill['value']);
            @endphp
            <a href="{{ route('dashboard', ['range' => $pill['value'], 'month' => $monthKey, 'year' => $year]) }}"
               class="pill{{ $isActive ? ' pill-active' : '' }}"
               data-range="{{ $pill['value'] }}"
               data-mode="{{ $pill['mode'] }}"
               onclick="event.preventDefault(); var f = document.querySelector('.filter-panel'); if(f) { f.querySelector('input[name=range]').value='{{ $pill['value'] }}'; f.submit(); }"
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
            <div class="kpi-label">Pelanggan Aktif</div>
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

        foreach ($series as $item) {
            $chartMonths[] = $item['label'];
            $chartFullLabels[] = $item['full'];
            $chartOmset[] = (int) $item['omset'];
            $chartOperasional[] = (int) $item['total_operacional'];
            $chartNetProfit[] = (int) $item['net_profit'];
            $chartMarketing[] = (int) $item['marketing'];
            $chartMer[] = (float) $item['mer'];
        }

        $chartEmptyTrend = true;
        $chartEmptyMarketing = true;

        foreach ($chartOmset as $v) {
            if ((int) $v > 0) { $chartEmptyTrend = false; break; }
        }

        foreach ($chartMarketing as $v) {
            if ((int) $v > 0) { $chartEmptyMarketing = false; break; }
        }

        $chartTrendTitle = match ($chartMode) {
            'daily' => 'Tren Keuangan Harian',
            'yearly' => 'Tren Keuangan Tahunan',
            default => 'Tren Keuangan Bulanan',
        };

        $chartTrendDesc = match ($chartMode) {
            'daily' => 'Omset vs Total Operasional dan Net Profit per tanggal (' . $periodLabel . ')',
            'yearly' => 'Omset vs Total Operasional dan Net Profit per tahun',
            default => 'Omset vs Total Operasional dan Net Profit per bulan',
        };

        $chartMerTitle = match ($chartMode) {
            'daily' => 'Budget Iklan & MER Harian',
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

   @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;
                var chartFullLabels = @json($chartFullLabels);
                var isDaily = @json($chartMode === 'daily');

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

                // Konfigurasi Sumbu X Otomatis (Rapi & Tidak Berdesakan)
                var commonXaxis = {
                    categories: @json($chartMonths),
                    tickAmount: isDaily ? 8 : undefined, // Batasi jumlah label tanggal di HP/Desktop
                    labels: {
                        rotate: 0,
                        hideOverlappingLabels: true,
                        style: { colors: '#94A3B8', fontSize: '11px', fontFamily: "'Inter', sans-serif" }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                };

                // Grid Halus Transparan
                var commonGrid = {
                    borderColor: 'rgba(255, 255, 255, 0.05)',
                    strokeDashArray: 4,
                    padding: { left: 10, right: 10 }
                };

                // --- 1. GRAFIK TREN KEUANGAN ---
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
                        colors: ['#38BDF8', '#F59E0B', '#22C55E'], // Sky Blue (Omset), Amber (Operasional), Emerald (Profit)
                        tooltip: {
                            theme: 'dark',
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var omset = opts.series[0][i] || 0;
                                var operasional = opts.series[1][i] || 0;
                                var netProfit = opts.series[2][i] || 0;

                                return '<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">'
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('Omset', KC.rupiah(omset), '#38BDF8')
                                    + tipRow('Total Operasional', KC.rupiah(operasional), '#F59E0B')
                                    + tipRow('Net Profit', KC.rupiah(netProfit), '#22C55E')
                                    + '</div>';
                            }
                        },
                        legend: { show: true, position: 'bottom', labels: { colors: '#94A3B8' } }
                    })).render();
                }

                // --- 2. GRAFIK BUDGET IKLAN & MER ---
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
                            { labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: '#94A3B8', fontFamily: "'Inter', sans-serif" } } },
                            { opposite: true, labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: '#94A3B8', fontFamily: "'Inter', sans-serif" } } }
                        ],
                        colors: ['#06B6D4', '#F43F5E'], // Cyan & Rose
                        tooltip: {
                            theme: 'dark',
                            custom: function (opts) {
                                var i = opts.dataPointIndex;
                                if (i === undefined || chartFullLabels[i] === undefined) { return ''; }

                                var spend = opts.series[0][i] || 0;
                                var mer = opts.series[1][i] || 0;

                                return '<div style="background:#0F172A;color:#F8FAFC;border:1px solid #334155;border-radius:8px;padding:10px 12px;min-width:210px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.5)">'
                                    + tipTitle(chartFullLabels[i])
                                    + tipRow('Budget Iklan', KC.rupiah(spend), '#06B6D4')
                                    + tipRow('MER', KC.pct(mer), '#F43F5E')
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