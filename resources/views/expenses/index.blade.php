@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengeluaran</h1>
        <span class="text-muted">Kelola seluruh biaya bulanan (Budget Iklan &amp; Operasional) dalam satu halaman.</span>
    </div>

    <form action="{{ route('expenses.index') }}" method="get" class="filter-panel">
        <div class="filter-field">
            <label for="f-bulan">Bulan</label>
            <select name="bulan" id="f-bulan" class="filter-select" data-searchable>
                <option value="">Bulan</option>
                @foreach ($months as $key => $label)
                    <option value="{{ $key }}" @selected($bulan == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="f-tahun">Tahun</label>
            <select name="tahun" id="f-tahun" class="filter-select" data-searchable>
                <option value="">Tahun</option>
                @foreach ($years as $yearOption)
                    <option value="{{ $yearOption }}" @selected($tahun == $yearOption)>{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label for="f-kategori">Kategori</label>
            <select name="kategori" id="f-kategori" class="filter-select" data-searchable>
                <option value="">Kategori</option>
                @foreach (['Fix Cost', 'Variable Cost'] as $option)
                    <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="filter-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="8" cy="8" r="6.2" />
                <rect x="3" y="4.5" width="10" height="2" />
                <rect x="3" y="11.5" width="10" height="2" />
                <rect x="3" y="4.5" width="2" height="7" />
                <rect x="11" y="4.5" width="2" height="7" />
            </svg>
            Filter
        </button>
        <a href="{{ route('expenses.index') }}" class="filter-reset">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M3 6h10M8 3v10" />
            </svg>
            Reset
        </a>
        @if ($bulan !== null || $tahun !== null || $kategori !== '')
            <span class="filter-active">Filter actief</span>
        @endif
    </form>

    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Budget Iklan (Marketing Spend)
                    </h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalMarketing])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Fix Cost</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalFixCost])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Variable Cost</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalVariableCost])</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" id="marketing-section">
        <div class="card-header">
            <h2 class="h5 mb-0">Pengeluaran Marketing / Budget Iklan</h2>
        </div>
        <div class="card-body">
            @error('bulan')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror
            <form action="{{ route('expenses.marketing.store') }}" method="post" class="row g-2 align-items-end mb-3">
                @csrf
                <div class="col-md-3">
                    <label for="spend-bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="spend-bulan" class="form-select form-select-sm">
                        <option value="">-- Pilih bulan --</option>
                        @foreach ($months as $key => $label)
                            <option value="{{ $key }}" @selected(old('bulan') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="spend-tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="spend-tahun" class="form-select form-select-sm">
                        @foreach ($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected(old('tahun') == $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="spend-nominal" class="form-label">Nominal (Rp)</label>
                    <input type="number" name="nominal" id="spend-nominal" value="{{ old('nominal') }}" min="0"
                        class="form-control form-control-sm @error('nominal') is-invalid @enderror">
                    @error('nominal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm">+ Tambah</button>
                </div>
            </form>

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>Nominal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($marketingSpends as $marketingSpend)
                        @php $spendId = 'spend-' . $marketingSpend->id; @endphp
                        <tr>
                            <td>
                                <select form="{{ $spendId }}" name="bulan" class="form-select form-select-sm"
                                    aria-label="Bulan">
                                    @foreach ($months as $key => $label)
                                        <option value="{{ $key }}" @selected($marketingSpend->bulan === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="{{ $spendId }}" name="tahun" class="form-select form-select-sm"
                                    aria-label="Tahun">
                                    @foreach ($years as $yearOption)
                                        <option value="{{ $yearOption }}" @selected($marketingSpend->tahun === $yearOption)>
                                            {{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input form="{{ $spendId }}" type="number" name="nominal" min="0"
                                    value="{{ $marketingSpend->nominal }}" class="form-control form-control-sm"
                                    aria-label="Nominal">
                            </td>
                            <td class="text-end">
                                <button form="{{ $spendId }}" type="submit"
                                    class="btn btn-sm btn-outline-secondary">Simpan</button>
                                <form action="{{ route('expenses.marketing.destroy', $marketingSpend) }}" method="post"
                                    class="d-inline" onsubmit="return confirm('Hapus budget iklan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada budget iklan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @foreach ($marketingSpends as $marketingSpend)
                <form id="spend-{{ $marketingSpend->id }}" class="d-none"
                    action="{{ route('expenses.marketing.update', $marketingSpend) }}" method="post">
                    @csrf
                    @method('PUT')
                </form>
            @endforeach

            <!-- === MARKETING_TABLE === -->
        </div>
    </div>

    <div class="card mb-4" id="operational-section">
        <div class="card-header">
            <h2 class="h5 mb-0">Pengeluaran Operasional (Fix Cost / Variable Cost)</h2>
        </div>
        <div class="card-body">
            @error('nama_pengeluaran')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror
            <form action="{{ route('expenses.operational.store') }}" method="post"
                class="row g-2 align-items-end mb-3">
                @csrf
                <div class="col-md-3">
                    <label for="op-nama" class="form-label">Nama Pengeluaran</label>
                    <input type="text" name="nama_pengeluaran" id="op-nama" value="{{ old('nama_pengeluaran') }}"
                        placeholder="misal Listrik, Internet, Cutting & Mesin"
                        class="form-control form-control-sm @error('nama_pengeluaran') is-invalid @enderror">
                    @error('nama_pengeluaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label for="op-kategori" class="form-label">Kategori</label>
                    <select name="kategori" id="op-kategori" class="form-select form-select-sm">
                        @foreach (['Fix Cost', 'Variable Cost'] as $option)
                            <option value="{{ $option }}" @selected(old('kategori') === $option)>{{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="op-nominal" class="form-label">Nominal (Rp)</label>
                    <input type="number" name="nominal" id="op-nominal" value="{{ old('nominal') }}" min="0"
                        class="form-control form-control-sm @error('nominal') is-invalid @enderror">
                    @error('nominal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label for="op-bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="op-bulan" class="form-select form-select-sm">
                        <option value="">-- Pilih bulan --</option>
                        @foreach ($months as $key => $label)
                            <option value="{{ $key }}" @selected(old('bulan') == $key)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="op-tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="op-tahun" class="form-select form-select-sm">
                        @foreach ($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected(old('tahun') == $yearOption)>{{ $yearOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100 btn-sm">+ Tambah</button>
                </div>
            </form>

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nama Pengeluaran</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($operationalExpenses as $expense)
                        @php $opId = 'op-' . $expense->id; @endphp
                        <tr>
                            <td>
                                <input form="{{ $opId }}" type="text" name="nama_pengeluaran"
                                    value="{{ $expense->nama_pengeluaran }}" class="form-control form-control-sm"
                                    aria-label="Nama pengeluaran">
                            </td>
                            <td>
                                <select form="{{ $opId }}" name="kategori" class="form-select form-select-sm"
                                    aria-label="Kategori">
                                    @foreach (['Fix Cost', 'Variable Cost'] as $option)
                                        <option value="{{ $option }}" @selected($expense->kategori === $option)>
                                            {{ $option }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input form="{{ $opId }}" type="number" name="nominal" min="0"
                                    value="{{ $expense->nominal }}" class="form-control form-control-sm"
                                    aria-label="Nominal">
                            </td>
                            <td>
                                <select form="{{ $opId }}" name="bulan" class="form-select form-select-sm"
                                    aria-label="Bulan">
                                    @foreach ($months as $key => $label)
                                        <option value="{{ $key }}" @selected($expense->bulan === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="{{ $opId }}" name="tahun" class="form-select form-select-sm"
                                    aria-label="Tahun">
                                    @foreach ($years as $yearOption)
                                        <option value="{{ $yearOption }}" @selected($expense->tahun === $yearOption)>
                                            {{ $yearOption }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-end">
                                <button form="{{ $opId }}" type="submit"
                                    class="btn btn-sm btn-outline-secondary">Simpan</button>
                                <form action="{{ route('expenses.operational.destroy', $expense) }}" method="post"
                                    class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada pengeluaran operasional.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @foreach ($operationalExpenses as $expense)
                <form id="op-{{ $expense->id }}" class="d-none"
                    action="{{ route('expenses.operational.update', $expense) }}" method="post">
                    @csrf
                    @method('PUT')
                </form>
            @endforeach
        </div>
    </div>

    @php
        $marketingHasData = false;
        foreach ($chartMarketingValue as $v) {
            if ((int) $v > 0) {
                $marketingHasData = true;
                break;
            }
        }

        $kategoriTotal = (int) $chartKategoriValue[0] + (int) $chartKategoriValue[1];
        $topEmpty = count($chartTopNama) === 0;
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Budget iklan per bulan, pembagian Fix/Variable Cost, dan top pengeluaran operasional.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-marketing',
                'title' => 'Budget Iklan per Bulan (' . $chartYear . ')',
                'desc' => 'Marketing spend per bulan',
                'empty' => !$marketingHasData,
            ])
            @include('partials.chart-card', [
                'id' => 'chart-kategori',
                'title' => 'Fix Cost vs Variable Cost',
                'desc' => 'Pembagian pengeluaran operasional',
                'empty' => $kategoriTotal === 0,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-top',
                'title' => 'Top Pengeluaran Operasional',
                'desc' => 'Top 5 pengeluaran per nominal',
                'empty' => $topEmpty,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-marketing')) {
                    new ApexCharts(document.getElementById('chart-marketing'), KC.base({
                        chart: {
                            type: 'bar'
                        },
                        series: [{
                            name: 'Budget Iklan',
                            data: @json($chartMarketingValue)
                        }],
                        xaxis: {
                            categories: @json($chartMarketingLabels)
                        },
                        colors: ['#22D3EE']
                    })).render();
                }

                if (document.getElementById('chart-kategori')) {
                    new ApexCharts(document.getElementById('chart-kategori'), KC.base({
                        chart: {
                            type: 'donut'
                        },
                        series: @json($chartKategoriValue),
                        labels: @json($chartKategoriLabels),
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return KC.pct(val);
                            }
                        },
                        legend: {
                            show: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            y: {
                                formatter: function(v) {
                                    return KC.rupiah(v);
                                }
                            }
                        },
                        colors: ['#F5B524', '#8B5CF6']
                    })).render();
                }

                if (document.getElementById('chart-top')) {
                    new ApexCharts(document.getElementById('chart-top'), KC.base({
                        chart: {
                            type: 'bar'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '55%'
                            }
                        },
                        series: [{
                            name: 'Nominal',
                            data: @json($chartTopValue)
                        }],
                        xaxis: {
                            categories: @json($chartTopNama)
                        },
                        colors: ['#F47171']
                    })).render();
                }
            });
        </script>
    @endpush
@endsection
