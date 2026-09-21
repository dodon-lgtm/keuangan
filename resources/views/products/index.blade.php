@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Produk</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Produk Hijab</a>
    </div>

    <form action="{{ route('products.index') }}" method="get" class="filter-panel">
        <div class="filter-field">
            <label for="q">Ketikan untuk mencari</label>
            <input type="text" name="q" id="q" value="{{ $q }}" class="filter-input"
                   placeholder="Nama produk...">
        </div>
        <div class="filter-field">
            <label for="harga_min">Harga Jual Min (Rp)</label>
            <input type="number" name="harga_min" id="harga_min" value="{{ $hargaMin ?? '' }}" min="0"
                   class="filter-input" placeholder="0">
        </div>
        <div class="filter-field">
            <label for="harga_max">Harga Jual Max (Rp)</label>
            <input type="number" name="harga_max" id="harga_max" value="{{ $hargaMax ?? '' }}" min="0"
                   class="filter-input" placeholder="Harga Maksimal">
        </div>
        <button type="submit" class="filter-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="8" r="6.2" />
                <rect x="3" y="4.5" width="10" height="2" /><rect x="3" y="11.5" width="10" height="2" /><rect x="3" y="4.5" width="2" height="7" /><rect x="11" y="4.5" width="2" height="7" />
            </svg>
            Filter
        </button>
        <a href="{{ route('products.index') }}" class="filter-reset">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h10M8 3v10" /></svg>
            Reset
        </a>
        @if ($q !== '' || $hargaMin !== null || $hargaMax !== null)
            <span class="filter-active">Filter actief</span>
        @endif
    </form>

    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Jenis Produk</h5>
                    <p class="card-text fs-4">{{ $stats['total_produk'] }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Rata-rata Harga Jual</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Rata-rata HPP</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_hpp']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Rata-rata Keuntungan</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_keuntungan']])</p>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>HPP</th>
                <th>Keuntungan</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->nama_produk }}</td>
                <td>@include('partials.rupiah', ['value' => $product->harga_jual])</td>
                <td>@include('partials.rupiah', ['value' => $product->hpp])</td>
                <td>
                    @if ($product->harga_jual - $product->hpp > 0)
                        <span class="text-success">+@include('partials.rupiah', ['value' => $product->harga_jual - $product->hpp])</span>
                    @else
                        @include('partials.rupiah', ['value' => $product->harga_jual - $product->hpp])
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row fw-bold">
                <td colspan="2">Rata-rata Keseluruhan</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_hpp']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_keuntungan']])</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="pagination-wrap">
        {{ $products->links() }}
    </div>

    @php
        $chartEmpty = count($chartNama) === 0;
        $shareEmpty = count($shareNama) === 0;
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Rincian harga jual, HPP, dan keuntungan per produk.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-harga',
                'title' => 'Harga Jual vs HPP per Produk',
                'desc' => 'Top 10 produk (per keuntungan)',
                'empty' => $chartEmpty,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-share',
                'title' => 'Pembagian Keuntungan per Produk',
                'desc' => 'Keuntungan per produk (top 8)',
                'empty' => $shareEmpty,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-harga')) {
                    new ApexCharts(document.getElementById('chart-harga'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Harga Jual', data: @json($chartJual) },
                            { name: 'HPP', data: @json($chartHpp) },
                            { name: 'Keuntungan', data: @json($chartMargin) }
                        ],
                        xaxis: { categories: @json($chartNama) },
                        legend: { show: true, position: 'bottom' },
                        colors: ['#22D3EE', '#F5B524', '#22C55E']
                    })).render();
                }

                if (document.getElementById('chart-share')) {
                    new ApexCharts(document.getElementById('chart-share'), KC.base({
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