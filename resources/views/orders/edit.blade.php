@extends('layouts.app')

@section('title', 'Order Edit')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Order Edit</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.update', $order) }}" method="post" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Pelanggan</label>
                        <select name="customer_id" id="customer_id" class="form-select @error('customer_id', 'is-invalid')">
                            <option value="">-- Kies pelanggan --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id) == $customer->id)>
                                    {{ $customer->nama_lengkap }} ({{ $customer->nama_brand }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">Produk</label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id', 'is-invalid')">
                            <option value="">-- Kies produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $order->product_id) == $product->id)>
                                    {{ $product->nama_produk }} — Rp {{ $product->harga_jual }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal', $order->tanggal?->format('Y-m-d')) }}"
                               class="form-control @error('tanggal', 'is-invalid')">
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="jumlah_pcs" class="form-label">Jumlah Pcs</label>
                        <input type="number" name="jumlah_pcs" id="jumlah_pcs" value="{{ old('jumlah_pcs', $order->jumlah_pcs) }}" min="1"
                               class="form-control @error('jumlah_pcs', 'is-invalid')">
                        @error('jumlah_pcs')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select @error('status', 'is-invalid')">
                            @foreach (['Pending', 'Lunas', 'Dibatalkan'] as $option)
                                <option value="{{ $option }}" @selected(old('status', $order->status) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
<div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="nominal" class="form-label">Nominal (Rp)</label>
                        <input type="number" name="nominal" id="nominal" value="{{ old('nominal', $order->nominal) }}" min="0"
                               class="form-control @error('nominal', 'is-invalid')">
                        <div class="form-text">Leeg = auto (harga_jual × pcs)</div>
                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="tipe_bayar" class="form-label">Tipe Bayar</label>
                        <select name="tipe_bayar" id="tipe_bayar" class="form-select @error('tipe_bayar', 'is-invalid')">
                            @foreach (['Full Payment', 'DP', 'Pelunasan'] as $option)
                                <option value="{{ $option }}" @selected(old('tipe_bayar', $order->tipe_bayar) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('tipe_bayar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="metode_bayar" class="form-label">Metode Bayar</label>
                        <select name="metode_bayar" id="metode_bayar" class="form-select @error('metode_bayar', 'is-invalid')">
                            @foreach (['Transfer Bank', 'QRIS', 'Cash'] as $option)
                                <option value="{{ $option }}" @selected(old('metode_bayar', $order->metode_bayar) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('metode_bayar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="jenis_order" class="form-label">Jenis Order</label>
                        <select name="jenis_order" id="jenis_order" class="form-select @error('jenis_order', 'is-invalid')">
                            @foreach (['Custom Design', 'Ready Stock'] as $option)
                                <option value="{{ $option }}" @selected(old('jenis_order', $order->jenis_order) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('jenis_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="pic_admin" class="form-label">Pic Admin</label>
                        <input type="text" name="pic_admin" id="pic_admin" value="{{ old('pic_admin', $order->pic_admin) }}"
                               class="form-control @error('pic_admin', 'is-invalid')">
                        @error('pic_admin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
<div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="ongkir" class="form-label">Ongkir (Rp)</label>
                        <input type="number" name="ongkir" id="ongkir" value="{{ old('ongkir', $order->ongkir) }}" min="0"
                               class="form-control @error('ongkir', 'is-invalid')">
                        @error('ongkir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ukuran_hijab" class="form-label">Ukuran Hijab</label>
                        <input type="text" name="ukuran_hijab" id="ukuran_hijab" value="{{ old('ukuran_hijab', $order->ukuran_hijab) }}"
                               placeholder="bijv. 110x110, 125x125"
                               class="form-control @error('ukuran_hijab', 'is-invalid')">
                        @error('ukuran_hijab')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="link_desain" class="form-label">Link Desain (optional)</label>
                        <input type="url" name="link_desain" id="link_desain" value="{{ old('link_desain', $order->link_desain) }}"
                               class="form-control @error('link_desain', 'is-invalid')">
                        @error('link_desain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat_kirim" class="form-label">Alamat Kirim (optional)</label>
                    <textarea name="alamat_kirim" id="alamat_kirim" rows="2" class="form-control @error('alamat_kirim', 'is-invalid')">{{ old('alamat_kirim', $order->alamat_kirim) }}</textarea>
                    @error('alamat_kirim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection