@extends('layouts.app')

@section('title', 'Order Jaubah')

@section('content')
    @php
        $oldItems = old('items', null);
        $initialItems = is_array($oldItems) && filled($oldItems) ? $oldItems : [[]];
    @endphp

    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Order Jaubah</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="post" novalidate>
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted mb-3">Data Pelanggan &amp; Tanggal</h6>

                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Pelanggan</label>
                            <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                <option value="">-- Pilih pelanggan --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->nama_lengkap }} ({{ $customer->nama_brand }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal (kosong = hari ini)</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}"
                                   class="form-control @error('tanggal') is-invalid @enderror">
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ukuran_hijab" class="form-label">Ukuran Hijab</label>
                            <input type="text" name="ukuran_hijab" id="ukuran_hijab" value="{{ old('ukuran_hijab') }}"
                                   placeholder="misal 110x110, 125x125"
                                   class="form-control @error('ukuran_hijab') is-invalid @enderror">
                            @error('ukuran_hijab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted mb-3">Pembayaran &amp; Ongkir</h6>

                        <div class="mb-3">
                            <label for="tipe_bayar" class="form-label">Tipe Bayar</label>
                            <select name="tipe_bayar" id="tipe_bayar" class="form-select @error('tipe_bayar') is-invalid @enderror">
                                @foreach (['Full Payment', 'DP', 'Pelunasan'] as $option)
                                    <option value="{{ $option }}" @selected(old('tipe_bayar') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('tipe_bayar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="metode_bayar" class="form-label">Metode Bayar</label>
                            <select name="metode_bayar" id="metode_bayar" class="form-select @error('metode_bayar') is-invalid @enderror">
                                @foreach (['Transfer Bank', 'QRIS', 'Cash'] as $option)
                                    <option value="{{ $option }}" @selected(old('metode_bayar') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('metode_bayar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jenis_order" class="form-label">Jenis Order</label>
                            <select name="jenis_order" id="jenis_order" class="form-select @error('jenis_order') is-invalid @enderror">
                                @foreach (['Custom Design', 'Ready Stock'] as $option)
                                    <option value="{{ $option }}" @selected(old('jenis_order') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('jenis_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ongkir" class="form-label">Ongkir (Rp)</label>
                            <input type="number" name="ongkir" id="ongkir" value="{{ old('ongkir', 0) }}" min="0"
                                   class="form-control @error('ongkir') is-invalid @enderror">
                            @error('ongkir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted mb-3">Admin &amp; Alamat</h6>

                        <div class="mb-3">
                            <label for="pic_admin" class="form-label">Pic Admin</label>
                            <input type="text" name="pic_admin" id="pic_admin" value="{{ old('pic_admin') }}"
                                   class="form-control @error('pic_admin') is-invalid @enderror">
                            @error('pic_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link_desain" class="form-label">Link Desain (optional)</label>
                            <input type="url" name="link_desain" id="link_desain" value="{{ old('link_desain') }}"
                                   class="form-control @error('link_desain') is-invalid @enderror">
                            @error('link_desain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alamat_kirim" class="form-label">Alamat Kirim (optional)</label>
                            <textarea name="alamat_kirim" id="alamat_kirim" rows="4" class="form-control @error('alamat_kirim') is-invalid @enderror">{{ old('alamat_kirim') }}</textarea>
                            @error('alamat_kirim')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                <div class="card border mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="h6 mb-0">Produk yang Dibeli</h6>
                        <button type="button" id="addItemBtn" class="btn btn-sm btn-primary">+ Tambah Produk</button>
                    </div>
                    <div class="card-body">
                        @error('items')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror
                        @error('items.*.product_id')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror
                        @error('items.*.jumlah_pcs')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror

                        <div class="row g-2 mb-2 fw-bold text-muted small">
                            <div class="col-md-7">Produk</div>
                            <div class="col-md-2">Jumlah Pcs</div>
                            <div class="col-md-2">Subtotal</div>
                            <div class="col-md-1"></div>
                        </div>

                        <div id="itemsContainer">
                            @foreach ($initialItems as $itemIndex => $item)
                                <div class="order-item-row row g-2 mb-2">
                                    <div class="col-md-7">
                                        <select name="items[{{ $itemIndex }}][product_id]" class="form-select item-product @error('items.{{ $itemIndex }}.product_id') is-invalid @enderror">
                                            <option value="">-- Pilih produk --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    @selected((int) old('items.'.$itemIndex.'.product_id', $item['product_id'] ?? '') === (int) $product->id)>
                                                    {{ $product->nama_produk }} — Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[{{ $itemIndex }}][jumlah_pcs]" min="1"
                                               value="{{ old('items.'.$itemIndex.'.jumlah_pcs', $item['jumlah_pcs'] ?? 1) }}"
                                               class="form-control item-qty @error('items.{{ $itemIndex }}.jumlah_pcs') is-invalid @enderror">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control item-subtotal" readonly placeholder="0">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Hapus baris">×</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-3">
                            <h6 class="mb-0 me-3 text-muted">Total Nominal Transaksi</h6>
                            <span id="totalNominal" class="fs-4 fw-bold">Rp 0</span>
                        </div>
                    </div>
                </div>

                <template id="itemRowTemplate">
                    <div class="order-item-row row g-2 mb-2">
                        <div class="col-md-7">
                            <select name="items[__INDEX__][product_id]" class="form-select item-product">
                                <option value="">-- Pilih produk --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->nama_produk }} — Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[__INDEX__][jumlah_pcs]" min="1" value="1" class="form-control item-qty">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control item-subtotal" readonly placeholder="0">
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Hapus baris">×</button>
                        </div>
                    </div>
                </template>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var products = @json($products->map(fn ($product) => ['id' => (int) $product->id, 'harga' => (int) $product->harga_jual])->values());
            var container = document.getElementById('itemsContainer');
            var totalEl = document.getElementById('totalNominal');

            function priceOf(id) {
                var found = products.find(function (p) { return p.id === id; });
                return found ? found.harga : 0;
            }

            function recalc() {
                var total = 0;
                var rows = container.querySelectorAll('.order-item-row');
                rows.forEach(function (row, index) {
                    var select = row.querySelector('.item-product');
                    var qty = row.querySelector('.item-qty');
                    var sub = row.querySelector('.item-subtotal');
                    var number = Math.max(parseInt(qty.value, 10) || 0, 0);
                    var subtotal = priceOf(parseInt(select.value, 10) || 0) * number;
                    sub.value = subtotal ? subtotal.toLocaleString('id-ID') : '';

                    row.querySelectorAll('input, select').forEach(function (el) {
                        var m = el.name ? el.name.match(/^items\[[^\]]*\]\[(.*)\]$/) : null;
                        if (m) el.name = 'items[' + index + '][' + m[1] + ']';
                    });

                    total += subtotal;
                });

                totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }

            function addRow() {
                var template = document.getElementById('itemRowTemplate');
                var node = document.importNode(template.content, true);
                container.appendChild(node);
                recalc();
                container.lastElementChild.querySelector('.item-product').focus();
            }

            container.addEventListener('change', recalc);
            container.addEventListener('input', recalc);

            container.addEventListener('click', function (e) {
                var btn = e.target.closest('.remove-item');
                if (!btn) return;
                var rows = container.querySelectorAll('.order-item-row');
                if (rows.length <= 1) return;
                btn.closest('.order-item-row').remove();
                recalc();
            });

            document.getElementById('addItemBtn').addEventListener('click', addRow);

            recalc();
        })();
    </script>
@endsection