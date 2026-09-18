@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pelanggan</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ Pelanggan Hijab</a>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Nama Brand</th>
                <th>No. WhatsApp</th>
                <th>Sumber</th>
                <th>Tanggal Masuk Chat</th>
                <th>Jumlah Order</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($customers as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td>{{ $customer->nama_lengkap }}</td>
                <td>{{ $customer->nama_brand }}</td>
                <td>{{ $customer->no_whatsapp }}</td>
                <td>{{ $customer->sumber }}</td>
                <td>{{ $customer->tanggal_masuk_chat?->format('d M Y') }}</td>
                <td>{{ $customer->orders_count }}</td>
                <td>
                    @if ($customer->isRepeat())
                        <span class="badge bg-info">repeat</span>
                    @else
                        <span class="badge bg-secondary">new</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('customers.destroy', $customer) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $customers->links() }}
@endsection