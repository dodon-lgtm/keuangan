@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengeluaran</h1>
        <span class="text-muted">Kelola seluruh biaya bulanan (Budget Iklan &amp; Operasional) dalam satu halaman.</span>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Budget Iklan (Marketing Spend)</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalMarketing])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Fix Cost</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalFixCost])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Variable Cost</h5>
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
                            <select form="{{ $spendId }}" name="bulan" class="form-select form-select-sm" aria-label="Bulan">
                                @foreach ($months as $key => $label)
                                    <option value="{{ $key }}" @selected($marketingSpend->bulan === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select form="{{ $spendId }}" name="tahun" class="form-select form-select-sm" aria-label="Tahun">
                                @foreach ($years as $yearOption)
                                    <option value="{{ $yearOption }}" @selected($marketingSpend->tahun === $yearOption)>{{ $yearOption }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input form="{{ $spendId }}" type="number" name="nominal" min="0" value="{{ $marketingSpend->nominal }}"
                                   class="form-control form-control-sm" aria-label="Nominal">
                        </td>
                        <td class="text-end">
                            <button form="{{ $spendId }}" type="submit" class="btn btn-sm btn-outline-secondary">Simpan</button>
                            <form action="{{ route('expenses.marketing.destroy', $marketingSpend) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus budget iklan ini?')">
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
            <form action="{{ route('expenses.operational.store') }}" method="post" class="row g-2 align-items-end mb-3">
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
                            <option value="{{ $option }}" @selected(old('kategori') === $option)>{{ $option }}</option>
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
                            <option value="{{ $key }}" @selected(old('bulan') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="op-tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="op-tahun" class="form-select form-select-sm">
                        @foreach ($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected(old('tahun') == $yearOption)>{{ $yearOption }}</option>
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
                            <input form="{{ $opId }}" type="text" name="nama_pengeluaran" value="{{ $expense->nama_pengeluaran }}"
                                   class="form-control form-control-sm" aria-label="Nama pengeluaran">
                        </td>
                        <td>
                            <select form="{{ $opId }}" name="kategori" class="form-select form-select-sm" aria-label="Kategori">
                                @foreach (['Fix Cost', 'Variable Cost'] as $option)
                                    <option value="{{ $option }}" @selected($expense->kategori === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input form="{{ $opId }}" type="number" name="nominal" min="0" value="{{ $expense->nominal }}"
                                   class="form-control form-control-sm" aria-label="Nominal">
                        </td>
                        <td>
                            <select form="{{ $opId }}" name="bulan" class="form-select form-select-sm" aria-label="Bulan">
                                @foreach ($months as $key => $label)
                                    <option value="{{ $key }}" @selected($expense->bulan === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select form="{{ $opId }}" name="tahun" class="form-select form-select-sm" aria-label="Tahun">
                                @foreach ($years as $yearOption)
                                    <option value="{{ $yearOption }}" @selected($expense->tahun === $yearOption)>{{ $yearOption }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-end">
                            <button form="{{ $opId }}" type="submit" class="btn btn-sm btn-outline-secondary">Simpan</button>
                            <form action="{{ route('expenses.operational.destroy', $expense) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini?')">
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
@endsection