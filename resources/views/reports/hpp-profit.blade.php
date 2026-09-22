@extends('layouts.app')

@section('title', 'Laporan HPP & Profit')

@section('content')
    <style>
        /* Mengikuti tema: putih di dark mode, gelap di light mode */
        .card-title.text-muted { color: var(--text) !important; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan HPP &amp; Profit</h1>
    </div>

    <form action="{{ route('reports.hpp-profit') }}" method="get" class="filter-panel">
        @include('partials.period-filter')

        <button type="submit" class="filter-btn">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1.5 2.5h13l-5 5.9v4.1l-3 1.5V8.4z" />
            </svg>
            Filter
        </button>
        <a href="{{ route('reports.hpp-profit') }}" class="filter-reset">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2.5 8a5.5 5.5 0 1 1 1.7 3.95" />
                <path d="M2.5 13.5v-3h3" />
            </svg>
            Reset
        </a>
    </form>

    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
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
    </div>

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

    @php
        $chartEmpty = count($chartNama) === 0;
        $shareEmpty = count($shareNama) === 0;
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Rincian omset, HPP, dan margin per produk untuk {{ $periodLabel }}.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-hpp-bar',
                'title' => 'Omset vs HPP vs Margin',
                'desc' => 'Top 10 produk (per omset)',
                'empty' => $chartEmpty,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-hpp-share',
                'title' => 'Pembagian Margin',
                'desc' => 'Margin per produk (top 8)',
                'empty' => $shareEmpty,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-hpp-bar')) {
                    new ApexCharts(document.getElementById('chart-hpp-bar'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Omset', data: @json($chartOmset) },
                            { name: 'HPP', data: @json($chartHpp) },
                            { name: 'Margin', data: @json($chartMargin) }
                        ],
                        xaxis: { categories: @json($chartNama) },
                        legend: { show: true, position: 'bottom' },
                        colors: ['#E11D48', '#F5B524', '#22C55E']
                    })).render();
                }

                if (document.getElementById('chart-hpp-share')) {
                    new ApexCharts(document.getElementById('chart-hpp-share'), KC.base({
                        chart: { type: 'donut' },
                        series: @json($shareValue),
                        labels: @json($shareNama),
                        plotOptions: { pie: { donut: { size: '68%' } } },
                        dataLabels: { enabled: true, formatter: function (val) { return KC.pct(val); } },
                        legend: { show: true, position: 'bottom' },
                        tooltip: { y: { formatter: function (v) { return KC.rupiah(v); } } },
                        colors: KC.colors.slice()
                    })).render();
                }
            });
        </script>
    @endpush
@endsection