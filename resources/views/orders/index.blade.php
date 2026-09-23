@extends('layouts.app')

@section('title', 'Log Order')

@section('content')
    <style>
        /* ---------- Label Kartu KPI (Judul di paling atas kartu) ---------- */
        /* Dibuat LEBIH BESAR & BOLD, serta menyesuaikan Light/Dark Mode */
        .card-title {
            font-size: 20px !important;
            font-weight: 800 !important;
            color: var(--text-main, #000000) !important;
            letter-spacing: 0.25px;
        }

        html[data-theme="light"] .card-title {
            color: #000000 !important;
        }
        html[data-theme="dark"] .card-title {
            color: #FFFFFF !important;
        }

        /* ---------- Isi/Nilai Kartu KPI (Angka/Nominal) ---------- */
        /* Dibuat BIASA (TIDAK BOLD) */
        .card-text {
            font-weight: 400 !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Log Order</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">+ Order Hijab</a>
    </div>

    <form action="{{ route('orders.index') }}" method="get" class="filter-panel">
        <div class="filter-field">
            <label for="q">Ketikan untuk mencari</label>
            <input type="text" name="q" id="q" value="{{ $q }}" class="filter-input"
                   placeholder="Pelanggan, produk, atau admin...">
        </div>
        <div class="filter-field">
            <label for="tipe_bayar">Tipe Bayar</label>
            <select name="tipe_bayar" id="tipe_bayar" class="filter-select" data-searchable>
                <option value="">Tipe Bayar</option>
                @foreach (['Full Payment', 'DP', 'Pelunasan'] as $option)
                    <option value="{{ $option }}" @selected($tipeBayar === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="jenis_order">Jenis Order</label>
            <select name="jenis_order" id="jenis_order" class="filter-select" data-searchable>
                <option value="">Jenis Order</option>
                @foreach (['Custom Design', 'Ready Stock'] as $option)
                    <option value="{{ $option }}" @selected($jenisOrder === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="metode_bayar">Metode Bayar</label>
            <select name="metode_bayar" id="metode_bayar" class="filter-select" data-searchable>
                <option value="">Metode Bayar</option>
                @foreach (['Transfer Bank', 'QRIS', 'Cash'] as $option)
                    <option value="{{ $option }}" @selected($metodeBayar === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="tanggal_from">Tanggal Van</label>
            <input type="date" name="tanggal_from" id="tanggal_from" value="{{ $tanggalFrom }}" class="filter-input">
        </div>
        <div class="filter-field">
            <label for="tanggal_to">Tanggal Tot</label>
            <input type="date" name="tanggal_to" id="tanggal_to" value="{{ $tanggalTo }}" class="filter-input">
        </div>
        <div class="filter-field">
            <label for="nominal_min">Nominal Min (Rp)</label>
            <input type="number" name="nominal_min" id="nominal_min" value="{{ $nominalMin ?? '' }}" min="0"
                   class="filter-input" placeholder="0">
        </div>
        <div class="filter-field">
            <label for="nominal_max">Nominal Max (Rp)</label>
            <input type="number" name="nominal_max" id="nominal_max" value="{{ $nominalMax ?? '' }}" min="0"
                   class="filter-input" placeholder="Harga Maksimal">
        </div>
        <button type="submit" class="filter-btn">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1.5 2.5h13l-5 5.9v4.1l-3 1.5V8.4z" />
            </svg>
            Filter
        </button>
        <a href="{{ route('orders.index') }}" class="filter-reset">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2.5 8a5.5 5.5 0 1 1 1.7 3.95" />
                <path d="M2.5 13.5v-3h3" />
            </svg>
            Reset
        </a>
        @if ($q !== '' || $tipeBayar !== '' || $jenisOrder !== '' || $metodeBayar !== ''
                || $tanggalFrom !== '' || $tanggalTo !== '' || $nominalMin !== null || $nominalMax !== null)
            <span class="filter-active">Filter aktif</span>
        @endif
    </form>

    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Keseluruhan Omset</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalOmset])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Keseluruhan Pcs Terjual</h5>
                    <p class="card-text fs-4">{{ $totalPcs }} pcs</p>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Tanggal</th>
                <th>Nominal</th>
                <th>Tipe Bayar</th>
                <th>Jenis</th>
                <th>Metode</th>
                <th>Pcs</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td><strong>{{ $order->customer->nama_lengkap }}</strong></td>
                <td>
                    @if ($order->orderItems->isEmpty())
                        <span style="color: var(--muted);">—</span>
                    @else
                        {{ $order->orderItems->map(fn ($item) => $item->product->nama_produk . ' (x' . $item->jumlah_pcs . ')')->implode(', ') }}
                    @endif
                </td>
                <td>{{ $order->tanggal?->format('d M Y') }}</td>
                <td>@include('partials.rupiah', ['value' => $order->nominal])</td>
                <td>{{ $order->tipe_bayar }}</td>
                <td>{{ $order->jenis_order }}</td>
                <td>{{ $order->metode_bayar }}</td>
                <td>{{ $order->orderItems->sum('jumlah_pcs') }}</td>
                <td class="text-end">
                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-secondary">edit</a>
                    <form action="{{ route('orders.destroy', $order) }}" method="post" class="d-inline"
                          data-confirm="Order #{{ $order->id }} ({{ $order->customer?->nama_lengkap ?? 'Tanpa pelanggan' }}) beserta rincian produknya akan dihapus permanen."
                          data-confirm-title="Hapus order?"
                          data-confirm-label="Ya, hapus">
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
                <td colspan="4">Total Keseluruhan</td>
                <td>@include('partials.rupiah', ['value' => $totalOmset])</td>
                <td colspan="3"></td>
                <td>{{ $totalPcs }} pcs</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="pagination-wrap">
        {{ $orders->links() }}
    </div>

    @php
        $tipeHasData = false;
        foreach ($chartTipeValue as $v) { if ((int) $v > 0) { $tipeHasData = true; break; } }

        $jenisHasData = false;
        foreach ($chartJenisValue as $v) { if ((int) $v > 0) { $jenisHasData = true; break; } }
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Rincian transaksi per tipe bayar, jenis order, dan metode bayar.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-tipe',
                'title' => 'Omset per Tipe Bayar',
                'desc' => 'Full Payment, DP & Pelunasan',
                'empty' => ! $tipeHasData,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-jenis',
                'title' => 'Transaksi per Jenis Order',
                'desc' => 'Custom Design vs Ready Stock',
                'empty' => ! $jenisHasData,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-metode',
                'title' => 'Omset per Metode Bayar',
                'desc' => 'Transfer Bank, QRIS & Cash',
                'empty' => ! $tipeHasData,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-tipe')) {
                    new ApexCharts(document.getElementById('chart-tipe'), KC.base({
                        chart: { type: 'bar' },
                        series: [{ name: 'Omset', data: @json($chartTipeValue) }],
                        xaxis: { categories: @json($chartTipe) },
                        colors: ['#E11D48']
                    })).render();
                }

                if (document.getElementById('chart-jenis')) {
                    new ApexCharts(document.getElementById('chart-jenis'), KC.base({
                        chart: { type: 'bar' },
                        series: [{ name: 'Transaksi', data: @json($chartJenisValue) }],
                        xaxis: { categories: @json($chartJenis) },
                        yaxis: { labels: { style: { colors: '#9AA1AB', fontFamily: "'Inter', sans-serif" }, formatter: function (v) { return String(Math.round(v)); } } },
                        colors: ['#22D3EE']
                    })).render();
                }

                if (document.getElementById('chart-metode')) {
                    new ApexCharts(document.getElementById('chart-metode'), KC.base({
                        chart: { type: 'donut' },
                        series: @json($chartMetodeValue),
                        labels: @json($chartMetode),
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