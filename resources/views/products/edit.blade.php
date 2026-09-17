@extends('layouts.app')

@section('title', 'Produk Edit')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Produk Edit</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="post" novalidate>
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" id="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}"
                           class="form-control @error('nama_produk') is-invalid @enderror">
                    @error('nama_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="harga_jual" class="form-label">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" id="harga_jual" value="{{ old('harga_jual', $product->harga_jual) }}" min="0"
                           class="form-control @error('harga_jual') is-invalid @enderror">
                    @error('harga_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hpp" class="form-label">HPP (Rp)</label>
                    <input type="number" name="hpp" id="hpp" value="{{ old('hpp', $product->hpp) }}" min="0"
                           class="form-control @error('hpp') is-invalid @enderror">
                    @error('hpp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
<script>
        (function () {
            var SEL = 'input[name="harga_jual"], input[name="hpp"]';
            var fields = Array.prototype.slice.call(document.querySelectorAll(SEL));
            if (!fields.length) return;

            function digits(v) { return String(v).replace(/[^\d]/g, ''); }
            function fmt(v) { return digits(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

            fields.forEach(function (field) {
                // Switch to a text field so the dot-formatted value can be displayed while typing.
                field.type = 'text';
                field.setAttribute('inputmode', 'numeric');
                field.setAttribute('autocomplete', 'off');
                field.style.fontVariantNumeric = 'tabular-nums';
                if (field.value) field.value = fmt(field.value);
                field.addEventListener('input', function () {
                    field.value = fmt(field.value);
                });
            });

            var form = fields[0].closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    fields.forEach(function (field) {
                        field.value = digits(field.value);
                    });
                });
            }
        })();
    </script>
@endsection