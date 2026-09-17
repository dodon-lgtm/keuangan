@extends('layouts.app')

@section('title', 'Laporan HPP & Profit')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan HPP &amp; Profit</h1>
    </div>

    <form action="{{ route('reports.hpp-profit') }}" method="get" class="row g-3 align-items-end mb-4">
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
                <td>{{ $product['margin_pct'] }}%</td>
            </tr>
        @endforeach
        @if (empty($products))
            <tr>
                <td colspan="6" class="text-center text-muted">Geen verkoop in deze periode.</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection