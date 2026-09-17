@extends('layouts.app')

@section('title', 'Marketing Spends')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Marketing Spends</h1>
        <a href="{{ route('marketing-spends.create') }}" class="btn btn-primary">+ Marketing Spend Jaubah</a>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Nominal</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($marketingSpends as $marketingSpend)
            <tr>
                <td>{{ $marketingSpend->id }}</td>
                <td>{{ $months[$marketingSpend->bulan] ?? $marketingSpend->bulan }}</td>
                <td>{{ $marketingSpend->tahun }}</td>
                <td>Rp {{ $marketingSpend->nominal }}</td>
                <td class="text-end">
                    <a href="{{ route('marketing-spends.edit', $marketingSpend) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('marketing-spends.destroy', $marketingSpend) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus marketing spend ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $marketingSpends->links() }}
@endsection