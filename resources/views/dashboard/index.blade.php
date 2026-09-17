@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Dashboard</h1>
    </div>

    <form action="{{ route('dashboard') }}" method="get" class="row g-3 align-items-end mb-4">
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

    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Omset</h5>
                    <p class="card-text fs-3">Rp {{ $totalOmset }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Transaksi</h5>
                    <p class="card-text fs-3">{{ $totalTransaksi }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Average Order Value</h5>
                    <p class="card-text fs-3">Rp {{ $averageOrder }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Pelanggan Aktif</h5>
                    <p class="card-text fs-3">{{ $pelangganAktif }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Segment A</h5>
                    <p class="card-text fs-3">{{ $segmentA }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Segment B</h5>
                    <p class="card-text fs-3">{{ $segmentB }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Segment C</h5>
                    <p class="card-text fs-3">{{ $segmentC }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection