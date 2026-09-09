@extends('layouts.app')

@section('title', 'Laporan MER & ROI')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan MER &amp; ROI</h1>
    </div>

    <form action="{{ route('reports.mer-roi') }}" method="get" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="month" class="form-label">Bulan</label>
            <select name="month" id="month" class="form-select">
                @foreach ($months as $key => $label)
                    <option value="{{ $key }}" @selected($month == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="year" class="form-label">Tahun</label>
            <select name="year" id="year" class="form-select">
                @foreach ($years as $yearOption)
                    <option value="{{ $yearOption }}" @selected($year == $yearOption)>{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Omset</h5>
                    <p class="card-text fs-4">Rp {{ $totalOmset }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Operasional</h5>
                    <p class="card-text fs-4">Rp {{ $totalOperasional }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Net Profit</h5>
                    <p class="card-text fs-4">Rp {{ $netProfit }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Marketing Spend</h5>
                    <p class="card-text fs-4">Rp {{ $marketingSpend }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">MER (Spend / Omset)</h5>
                    <p class="card-text fs-3">{{ $mer }}%</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">ROI (Net Profit / Spend)</h5>
                    <p class="card-text fs-3">{{ $roi }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Pembagian Profit</h2>
        </div>
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Share</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A Roni</td>
                    <td>60%</td>
                    <td>Rp {{ $profitSplit['roni'] }}</td>
                </tr>
                <tr>
                    <td>Rizky</td>
                    <td>40%</td>
                    <td>Rp {{ $profitSplit['rizky'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection