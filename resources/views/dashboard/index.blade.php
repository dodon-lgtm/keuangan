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
        <div class="filter-field">
            <label for="month">Bulan</label>
            <select name="month" id="month" class="filter-select" data-searchable>
                @foreach ($months as $key => $label)
                    <option value="{{ $key }}" @selected($month == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label for="year">Tahun</label>
            <select name="year" id="year" class="filter-select" data-searchable>
                @foreach ($years as $yearOption)
                    <option value="{{ $yearOption }}" @selected($year == $yearOption)>{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="filter-btn">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="8" r="6.2" />
                <rect x="3" y="4.5" width="10" height="2" />
                <rect x="3" y="11.5" width="10" height="2" />
            </svg>
            Filter
        </button>
        <a href="{{ route('dashboard') }}" class="filter-reset">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M3 6h10M8 3v10" />
            </svg>
            Reset
        </a>
    </form>

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

    @php
        $chartMonths = [];
        $chartOmset = [];
        $chartOperasional = [];
        $chartNetProfit = [];
        $chartMarketing = [];
        $chartMer = [];

        foreach ($series as $item) {
            $chartMonths[] = $item['label'];
            $chartOmset[] = (int) $item['omset'];
            $chartOperasional[] = (int) $item['total_operacional'];
            $chartNetProfit[] = (int) $item['net_profit'];
            $chartMarketing[] = (int) $item['marketing'];
            $chartMer[] = (float) $item['mer'];
        }

        $splitTotal = (int) $profitSplit['roni'] + (int) $profitSplit['rizky'];

        $chartEmptyTrend = true;
        $chartEmptyMarketing = true;

        foreach ($chartOmset as $v) {
            if ((int) $v > 0) { $chartEmptyTrend = false; break; }
        }

        foreach ($chartMarketing as $v) {
            if ((int) $v > 0) { $chartEmptyMarketing = false; break; }
        }
    @endphp

    {{-- Section Grafik --}}
    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Tren performans keuangan, efisiensi marketing, dan pembagian profit untuk tahun {{ $year }}.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-trend',
                'title' => 'Tren Keuangan Bulanan',
                'desc' => 'Omset vs Total Operasional dan Net Profit',
                'empty' => $chartEmptyTrend,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-mer',
                'title' => 'Budget Iklan & MER',
                'desc' => 'Budget Iklan per bulan (kolom) dan MER % (garis)',
                'empty' => array_sum($chartMarketing) === 0,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-split',
                'title' => 'Pembagian Profit',
                'desc' => 'Pembagian Profit ' . ($months[$month] ?? '') . ': A Roni 60% & Rizky 40%',
                'empty' => $splitTotal <= 0,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-trend')) {
                    new ApexCharts(document.getElementById('chart-trend'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Omset', type: 'column', data: @json($chartOmset) },
                            { name: 'Total Operasional', type: 'column', data: @json($chartOperasional) },
                            { name: 'Net Profit', type: 'line', data: @json($chartNetProfit) }
                        ],
                        xaxis: { categories: @json($chartMonths) },
                        stroke: { width: [0, 0, 2.4], curve: 'smooth' },
                        colors: ['#E11D48', '#F5B524', '#22C55E'],
                        legend: { show: true, position: 'bottom' }
                    })).render();
                }

                if (document.getElementById('chart-mer')) {
                    new ApexCharts(document.getElementById('chart-mer'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Marketing Spend', type: 'column', data: @json($chartMarketing) },
                            { name: 'MER %', type: 'line', data: @json($chartMer) }
                        ],
                        xaxis: { categories: @json($chartMonths) },
                        stroke: { width: [0, 2.4], curve: 'smooth' },
                        yaxis: [
                            { labels: { formatter: function (v) { return KC.rupiah(v); }, style: { colors: '#9AA1AB', fontFamily: "'Inter', sans-serif" } } },
                            { opposite: true, labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: '#9AA1AB', fontFamily: "'Inter', sans-serif" } } }
                        ],
                        colors: ['#22D3EE', '#E11D48'],
                        legend: { show: true, position: 'bottom' }
                    })).render();
                }

                if (document.getElementById('chart-split')) {
                    new ApexCharts(document.getElementById('chart-split'), KC.base({
                        chart: { type: 'donut' },
                        series: {!! json_encode([(int) $profitSplit['roni'], (int) $profitSplit['rizky']]) !!},
                        labels: ['A Roni (60%)', 'Rizky (40%)'],
                        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Keuntungan', color: '#9AA1AB', formatter: function (w) { return KC.rupiah(w); } } } } } },
                        dataLabels: { enabled: true, formatter: function (val) { return KC.pct(val); } },
                        legend: { show: true, position: 'bottom' },
                        tooltip: { y: { formatter: function (v) { return KC.rupiah(v); } } },
                        colors: ['#E11D48', '#22C55E']
                    })).render();
                }
            });
        </script>
    @endpush
@endsection