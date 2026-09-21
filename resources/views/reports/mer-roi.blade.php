@extends('layouts.app')

@section('title', 'Laporan MER & ROI')

@section('content')
    <style>
        h1 { color: #FFFFFF; }
        .form-label { color: #C9CED6; }

        .card-title,
        .card-title.text-muted,
        .card-text { color: #FFFFFF; }
        .card-title.text-muted { color: #FFFFFF !important; }

        .card-header h2,
        .card-header .h5 { color: #FFFFFF; }

        .table thead th,
        .table tbody td { color: #FFFFFF; }
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

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Rincian Biaya Operasional</h2>
        </div>
        <table class="table table-striped mb-0">
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
                <tr class="total-row fw-bold">
                    <td>Total Operasional</td>
                    <td>@include('partials.rupiah', ['value' => $totalOperasional])</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- no needed pembagian provit cuz it's just for demonstration --}}

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

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Trend MER &amp; ROI dan verband tussen budget iklan dan omset untuk {{ $periodLabel }}.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-mer-spend',
                'title' => 'Budget Iklan vs Omset & MER%',
                'desc' => 'Marketing spend per bulan (kolom), Omset (lijn) dan MER% (lijn)',
                'empty' => ! $marketingHasData,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-mer-roi',
                'title' => 'Trend MER% & ROI%',
                'desc' => 'Efisiensi marketing per bulan',
                'empty' => ! $marketingHasData,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-mer-spend')) {
                    new ApexCharts(document.getElementById('chart-mer-spend'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Marketing Spend', type: 'column', data: @json($chartMarketing) },
                            { name: 'Omset', type: 'line', data: @json($chartOmset) },
                            { name: 'MER %', type: 'line', data: @json($chartMer) }
                        ],
                        xaxis: { categories: @json($chartMonths) },
                        stroke: { width: [0, 2.4, 2.4], curve: 'smooth' },
                        colors: ['#22D3EE', '#E11D48', '#F5B524'],
                        legend: { show: true, position: 'bottom' }
                    })).render();
                }

                if (document.getElementById('chart-mer-roi')) {
                    new ApexCharts(document.getElementById('chart-mer-roi'), KC.base({
                        chart: { type: 'line' },
                        series: [
                            { name: 'MER %', data: @json($chartMer) },
                            { name: 'ROI %', data: @json($chartRoi) }
                        ],
                        xaxis: { categories: @json($chartMonths) },
                        stroke: { width: 2.4, curve: 'smooth' },
                        yaxis: { labels: { formatter: function (v) { return KC.pct(v); }, style: { colors: '#9AA1AB', fontFamily: "'Inter', sans-serif" } } },
                        markers: { size: 4, strokeColors: '#0E1013' },
                        colors: ['#E11D48', '#22C55E'],
                        legend: { show: true, position: 'bottom' }
                    })).render();
                }
            });
        </script>
    @endpush
@endsection