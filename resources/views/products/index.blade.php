@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Produk</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Produk Hijab</a>
    </div>

    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Total Jenis Produk</h5>
                    <p class="card-text fs-4">{{ $stats['total_produk'] }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Rata-rata Harga Jual</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Rata-rata HPP</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_hpp']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted" style="color:#FFFFFF !important;">Rata-rata Keuntungan</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_keuntungan']])</p>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>HPP</th>
                <th>Keuntungan</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->nama_produk }}</td>
                <td>@include('partials.rupiah', ['value' => $product->harga_jual])</td>
                <td>@include('partials.rupiah', ['value' => $product->hpp])</td>
                <td>
                    @if ($product->harga_jual - $product->hpp > 0)
                        <span class="text-success">+@include('partials.rupiah', ['value' => $product->harga_jual - $product->hpp])</span>
                    @else
                        @include('partials.rupiah', ['value' => $product->harga_jual - $product->hpp])
                    @endif
                </td>
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
        <tfoot>
            <tr class="table-light fw-bold" style="color:#0D0F12;">
                <td colspan="2">Rata-rata Keseluruhan</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_hpp']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_keuntungan']])</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{ $products->links() }}
@endsection