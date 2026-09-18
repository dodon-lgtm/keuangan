@extends('layouts.app')

@section('title', 'Log Order')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Log Order</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">+ Order Jaubah</a>
    </div>

    
    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Keseluruhan Omset</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $totalOmset])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Keseluruhan Pcs Terjual</h5>
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
                <td>{{ $order->customer->nama_lengkap }}</td>
                <td>
                    @if ($order->orderItems->isEmpty())
                        <span class="text-muted">—</span>
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
                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('orders.destroy', $order) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus order ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light fw-bold" style="color:#0D0F12;">
                <td colspan="4">Total Keseluruhan</td>
                <td>@include('partials.rupiah', ['value' => $totalOmset])</td>
                <td colspan="3"></td>
                <td>{{ $totalPcs }} pcs</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{ $orders->links() }}
@endsection