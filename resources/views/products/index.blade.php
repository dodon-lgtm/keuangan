@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Produk</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Produk Jaubah</a>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>HPP</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->nama_produk }}</td>
                <td>Rp {{ $product->harga_jual }}</td>
                <td>Rp {{ $product->hpp }}</td>
                <td class="text-end">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $products->links() }}
@endsection