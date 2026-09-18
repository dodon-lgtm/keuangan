@extends('layouts.app')

@section('title', 'Pengeluaran Operasional')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengeluaran Operasional</h1>
        <a href="{{ route('operational-expenses.create') }}" class="btn btn-primary">+ Pengeluaran Jaubah</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
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

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pengeluaran</th>
                <th>Kategori</th>
                <th>Nominal</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($operationalExpenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ $expense->nama_pengeluaran }}</td>
                <td>
                    @if ($expense->kategori === \App\Models\OperationalExpense::KATEGORI_FIX_COST)
                        <span class="badge bg-primary">Fix Cost</span>
                    @else
                        <span class="badge bg-warning text-dark">Variable Cost</span>
                    @endif
                </td>
                <td>@include('partials.rupiah', ['value' => $expense->nominal])</td>
                <td>{{ $months[$expense->bulan] ?? $expense->bulan }}</td>
                <td>{{ $expense->tahun }}</td>
                <td class="text-end">
                    <a href="{{ route('operational-expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('operational-expenses.destroy', $expense) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $operationalExpenses->links() }}
@endsection