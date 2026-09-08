@extends('layouts.app')

@section('title', 'Log Order')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Log Order</h1>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">+ Order Jaubah</a>
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
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->customer->nama_lengkap }}</td>
                <td>{{ $order->product->nama_produk }}</td>
                <td>{{ $order->tanggal?->format('d M Y') }}</td>
                <td>Rp {{ $order->nominal }}</td>
                <td>{{ $order->tipe_bayar }}</td>
                <td>{{ $order->jenis_order }}</td>
                <td>{{ $order->metode_bayar }}</td>
                <td>{{ $order->jumlah_pcs }}</td>
                <td>
                    @if ($order->status === 'Lunas')
                        <span class="badge bg-success">Lunas</span>
                    @elseif ($order->status === 'Pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @else
                        <span class="badge bg-danger">Dibatalkan</span>
                    @endif
                </td>
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
    </table>

    {{ $orders->links() }}
@endsection