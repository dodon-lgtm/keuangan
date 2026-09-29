@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <style>
        /* ---------- Label Kartu KPI (Judul di paling atas kartu) ---------- */
        /* Dibuat LEBIH BESAR & BOLD, serta menyesuaikan Light/Dark Mode */
        .card-title {
            font-size: 20px !important;
            font-weight: 800 !important;
            color: var(--text-main, #000000) !important;
            letter-spacing: 0.25px;
        }

        html[data-theme="light"] .card-title {
            color: #000000 !important;
        }
        html[data-theme="dark"] .card-title {
            color: #FFFFFF !important;
        }

        /* ---------- Isi/Nilai Kartu KPI (Angka/Nominal) ---------- */
        /* Dibuat BIASA (TIDAK BOLD) */
        .card-text {
            font-weight: 400 !important;
        }

        /* ---------- Modal Tambah/Edit Produk ----------
           Form tidak lagi punya halaman sendiri: muncul sebagai modal kompak
           (max-width 560px) di tengah layar dengan backdrop buram milik
           .modal-backdrop (lihat layouts/app.blade.php). */
        .product-modal .modal-dialog {
            max-width: 560px;
        }
        .product-modal .modal-content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 26px 60px rgba(0, 0, 0, 0.5);
            color: var(--text);
            overflow: hidden;
        }
        .product-modal .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
        }
        .product-modal .modal-head h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--text);
        }
        .product-modal .modal-close {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .product-modal .modal-close:hover {
            color: var(--text);
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.25);
        }
        .product-modal .modal-close svg { width: 15px; height: 15px; }
        .product-modal .modal-body { padding: 16px 18px 4px; }
        .product-modal .form-label { margin-bottom: 0.35rem; }
        .product-modal .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 12px 18px 16px;
            border-top: 1px solid var(--border);
        }
        .product-modal .btn-cancel {
            padding: 9px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: transparent;
            color: var(--muted);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }
        .product-modal .btn-cancel:hover { color: var(--text); background: rgba(255, 255, 255, 0.06); }
        .product-modal .product-modal-error {
            margin: 0 0 14px;
            padding: 9px 12px;
            border-radius: 10px;
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.4);
            color: #F5B8B8;
            font-size: 12.5px;
            font-weight: 600;
        }
        .product-modal .product-modal-error[hidden] { display: none; }

        /* Versi terang: kartu putih, border abu, teks gelap */
        html[data-theme="light"] .product-modal .modal-content {
            background: #FFFFFF;
            border-color: rgba(0, 0, 0, 0.10);
            box-shadow: 0 24px 60px rgba(16, 24, 40, 0.18);
        }
        html[data-theme="light"] .product-modal .modal-head { border-bottom-color: rgba(0, 0, 0, 0.08); }
        html[data-theme="light"] .product-modal .modal-close { border-color: rgba(0, 0, 0, 0.12); color: #5C6675; }
        html[data-theme="light"] .product-modal .modal-close:hover {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.22);
            color: var(--text);
        }
        html[data-theme="light"] .product-modal .modal-foot { border-top-color: rgba(0, 0, 0, 0.08); }
        html[data-theme="light"] .product-modal .btn-cancel { border-color: rgba(0, 0, 0, 0.14); color: #5C6675; }
        html[data-theme="light"] .product-modal .btn-cancel:hover { background: rgba(0, 0, 0, 0.05); color: var(--text); }
        html[data-theme="light"] .product-modal .product-modal-error {
            background: #FEF2F2;
            border-color: rgba(220, 38, 38, 0.35);
            color: #B91C1C;
        }

        @media (max-width: 575.98px) {
            .product-modal .modal-dialog { margin: 0.5rem; }
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Produk</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal"
                data-product-mode="create">+ Produk Hijab</button>
    </div>

    <form action="{{ route('products.index') }}" method="get" class="filter-panel">
        <div class="filter-field">
            <label for="q">Ketikan untuk mencari</label>
            <input type="text" name="q" id="q" value="{{ $q }}" class="filter-input"
                   placeholder="Nama produk...">
        </div>
        <div class="filter-field">
            <label for="harga_min">Harga Jual Min (Rp)</label>
            <input type="number" name="harga_min" id="harga_min" value="{{ $hargaMin ?? '' }}" min="0"
                   class="filter-input" placeholder="0">
        </div>
        <div class="filter-field">
            <label for="harga_max">Harga Jual Max (Rp)</label>
            <input type="number" name="harga_max" id="harga_max" value="{{ $hargaMax ?? '' }}" min="0"
                   class="filter-input" placeholder="Harga Maksimal">
        </div>
        <button type="submit" class="filter-btn">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1.5 2.5h13l-5 5.9v4.1l-3 1.5V8.4z" />
            </svg>
            Filter
        </button>
        <a href="{{ route('products.index') }}" class="filter-reset">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2.5 8a5.5 5.5 0 1 1 1.7 3.95" />
                <path d="M2.5 13.5v-3h3" />
            </svg>
            Reset
        </a>
        @if ($q !== '' || $hargaMin !== null || $hargaMax !== null)
            <span class="filter-active">Filter aktif</span>
        @endif
    </form>

    <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Jenis Produk</h5>
                    <p class="card-text fs-4">{{ $stats['total_produk'] }}</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata Harga Jual</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata HPP</h5>
                    <p class="card-text fs-4">@include('partials.rupiah', ['value' => $stats['avg_hpp']])</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Rata-rata Keuntungan</h5>
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
                <th>Margin Profit</th>
                <th>Margin %</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td><strong>{{ $product->nama_produk }}</strong></td>
                <td>@include('partials.rupiah', ['value' => $product->harga_jual])</td>
                <td>@include('partials.rupiah', ['value' => $product->hpp])</td>
                <td>
                    @if ($product->margin_profit > 0)
                        <span class="text-success">+@include('partials.rupiah', ['value' => $product->margin_profit])</span>
                    @else
                        @include('partials.rupiah', ['value' => $product->margin_profit])
                    @endif
                </td>
                <td>{{ number_format($product->margin_percent, 2) }}%</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#productModal"
                            data-product-mode="edit"
                            data-action="{{ route('products.update', $product) }}"
                            data-product-id="{{ $product->id }}"
                            data-nama-produk="{{ $product->nama_produk }}"
                            data-harga-jual="{{ $product->harga_jual }}"
                            data-hpp="{{ $product->hpp }}">Edit</button>
                    <form action="{{ route('products.destroy', $product) }}" method="post" class="d-inline"
                          data-confirm="Produk {{ $product->nama_produk }} akan dihapus permanen dari daftar produk."
                          data-confirm-title="Hapus produk?"
                          data-confirm-label="Ya, hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row fw-bold">
                <td colspan="2">Rata-rata Keseluruhan</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_harga_jual']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_hpp']])</td>
                <td>@include('partials.rupiah', ['value' => $stats['avg_keuntungan']])</td>
                <td>{{ number_format($stats['avg_margin_percent'], 2) }}%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="pagination-wrap">
        {{ $products->links() }}
    </div>

    @php
        $chartEmpty = count($chartNama) === 0;
        $shareEmpty = count($shareNama) === 0;
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Rincian harga jual, HPP, dan keuntungan per produk.</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-harga',
                'title' => 'Harga Jual vs HPP per Produk',
                'desc' => 'Top 10 produk (per keuntungan)',
                'empty' => $chartEmpty,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-share',
                'title' => 'Pembagian Keuntungan per Produk',
                'desc' => 'Keuntungan per produk (top 8)',
                'empty' => $shareEmpty,
            ])
        </div>
    </div>

    {{--
        Modal Tambah/Edit Produk — satu form dipakai untuk dua mode.
        Mode "create"  : aksi ke products.store (tombol + Produk Hijab).
        Mode "edit"    : aksi ke products.update + _method PUT (tombol Edit baris).
        Server merender state awal dari ?open=create / ?open=edit&product={id}
        maupun dari old input ketika validasi gagal, lalu JS hanya mengubah mode
        saat tombol pemicu diklik.
    --}}
    <div class="modal fade product-modal" id="productModal" tabindex="-1"
         aria-labelledby="productModalLabel" aria-hidden="true"
         data-create-action="{{ route('products.store') }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="productForm"
                      action="{{ $formMode === 'edit' && $formProduct ? route('products.update', $formProduct) : route('products.store') }}"
                      method="post" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="productFormMethod" value="PUT"
                           @if ($formMode !== 'edit') disabled @endif>
                    <input type="hidden" name="_modal_mode" id="productModalMode" value="{{ $formMode }}">
                    <input type="hidden" name="_modal_product_id" id="productModalProductId"
                           value="{{ $formProduct?->id }}">

                    <div class="modal-head">
                        <h5 id="productModalLabel">{{ $formMode === 'edit' ? 'Edit Produk' : 'Tambah Produk' }}</h5>
                        <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Tutup">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" id="nama_produk"
                                   value="{{ old('nama_produk', $formProduct?->nama_produk) }}"
                                   class="form-control @error('nama_produk') is-invalid @enderror"
                                   placeholder="Contoh: Hijab Bella Square">
                            <div class="invalid-feedback" id="nama_produk_error">@error('nama_produk'){{ $message }}@enderror</div>
                        </div>

                        {{-- Layout 2 kolom sejajar agar modal tetap kompak --}}
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="harga_jual" class="form-label">Harga Jual (Rp)</label>
                                <input type="number" name="harga_jual" id="harga_jual" min="0" step="1"
                                       value="{{ old('harga_jual', $formProduct?->harga_jual) }}"
                                       class="form-control @error('harga_jual') is-invalid @enderror" placeholder="0">
                                <div class="invalid-feedback" id="harga_jual_error">@error('harga_jual'){{ $message }}@enderror</div>
                            </div>

                            <div class="col-sm-6">
                                <label for="hpp" class="form-label">HPP (Rp)</label>
                                <input type="number" name="hpp" id="hpp" min="0" step="1"
                                       value="{{ old('hpp', $formProduct?->hpp) }}"
                                       class="form-control @error('hpp') is-invalid @enderror" placeholder="0">
                                <div class="invalid-feedback" id="hpp_error">@error('hpp'){{ $message }}@enderror</div>
                            </div>
                        </div>

                        {{-- Preview Margin Profit (kalkulasi live, adaptif tema) --}}
                        @include('partials.margin-preview', [
                            'hargaJual' => old('harga_jual', $formProduct?->harga_jual),
                            'hpp' => old('hpp', $formProduct?->hpp),
                        ])

                        <div class="product-modal-error" id="productFormError" role="alert" hidden></div>
                    </div>

                    <div class="modal-foot">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4" id="productFormSubmit">
                            {{ $formMode === 'edit' ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            /* Modal Tambah/Edit Produk:
               - Dibuka tombol "+ Produk Hijab" (mode tambah) dan tombol Edit pada
                 baris tabel (mode edit; data produk dibaca dari atribut data-*).
               - Submit lewat fetch() dengan Accept: application/json supaya error
                 validasi tampil di dalam modal tanpa reload. Setelah sukses halaman
                 diarahkan ke daftar produk agar tabel, statistik, dan grafik segar.
               - Bila JavaScript mati, form tetap terkirim normal (POST / PUT). */
            (function () {
                var modalEl = document.getElementById('productModal');
                var formEl = document.getElementById('productForm');

                if (!modalEl || !formEl) {
                    return;
                }

                var titleEl = document.getElementById('productModalLabel');
                var methodEl = document.getElementById('productFormMethod');
                var modeEl = document.getElementById('productModalMode');
                var productIdEl = document.getElementById('productModalProductId');
                var submitEl = document.getElementById('productFormSubmit');
                var errorEl = document.getElementById('productFormError');
                var listUrl = '{{ route('products.index') }}';
                var createAction = modalEl.dataset.createAction || formEl.action;

                var fields = {
                    nama_produk: document.getElementById('nama_produk'),
                    harga_jual: document.getElementById('harga_jual'),
                    hpp: document.getElementById('hpp')
                };

                function setField(name, value) {
                    var input = fields[name];
                    if (!input) return;
                    input.value = value === null || value === undefined ? '' : String(value);
                    /* Picu kalkulasi margin (partials/margin-preview) sekaligus
                       format ribuan (js/currency-format.js) untuk nilai baru. */
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }

                function clearErrors() {
                    Object.keys(fields).forEach(function (name) {
                        var input = fields[name];
                        var box = document.getElementById(name + '_error');
                        if (input) input.classList.remove('is-invalid');
                        if (box) box.textContent = '';
                    });

                    if (errorEl) {
                        errorEl.hidden = true;
                        errorEl.textContent = '';
                    }
                }

                function showErrors(errors, fallbackMessage) {
                    var firstInvalid = null;

                    Object.keys(errors || {}).forEach(function (name) {
                        var input = fields[name];
                        var box = document.getElementById(name + '_error');
                        var message = errors[name] && errors[name][0] ? errors[name][0] : '';

                        if (input) {
                            input.classList.add('is-invalid');
                            if (!firstInvalid) firstInvalid = input;
                        }
                        if (box) box.textContent = message;
                    });

                    if (!firstInvalid && fallbackMessage && errorEl) {
                        errorEl.hidden = false;
                        errorEl.textContent = fallbackMessage;
                    }

                    if (firstInvalid) firstInvalid.focus();
                }

                function setBusy(busy) {
                    if (!submitEl) return;
                    submitEl.disabled = busy;
                    submitEl.textContent = busy
                        ? 'Menyimpan…'
                        : (modeEl && modeEl.value === 'edit' ? 'Update' : 'Simpan');
                }

                function applyMode(mode, data) {
                    var isEdit = mode === 'edit';
                    var values = data || {};

                    formEl.action = isEdit && values.action ? values.action : createAction;

                    if (methodEl) {
                        methodEl.value = 'PUT';
                        /* Input disabled tidak ikut terkirim => mode tambah = POST biasa. */
                        methodEl.disabled = !isEdit;
                    }
                    if (modeEl) modeEl.value = mode;
                    if (productIdEl) productIdEl.value = isEdit && values.id ? values.id : '';
                    if (titleEl) titleEl.textContent = isEdit ? 'Edit Produk' : 'Tambah Produk';

                    setField('nama_produk', isEdit ? values.nama : '');
                    setField('harga_jual', isEdit ? values.harga : '');
                    setField('hpp', isEdit ? values.hpp : '');
                    setBusy(false);
                }

                modalEl.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;

                    /* Tanpa tombol pemicu berarti modal dibuka otomatis oleh server
                       (?open=... atau setelah validasi gagal): pakai state yang
                       sudah dirender server, jangan ditimpa. */
                    if (!trigger) return;

                    clearErrors();

                    if (trigger.dataset.productMode === 'edit') {
                        applyMode('edit', {
                            action: trigger.dataset.action,
                            id: trigger.dataset.productId,
                            nama: trigger.dataset.namaProduk,
                            harga: trigger.dataset.hargaJual,
                            hpp: trigger.dataset.hpp
                        });

                        return;
                    }

                    applyMode('create', {});
                });

                formEl.addEventListener('submit', function (event) {
                    /* Tanpa fetch(): biarkan submit form normal berjalan. */
                    if (typeof window.fetch !== 'function') return;

                    event.preventDefault();
                    clearErrors();
                    setBusy(true);

                    var body = new FormData(formEl);

                    /* Nominal dikirim tanpa titik ribuan, jaga-jaga bila
                       js/currency-format.js belum sempat membersihkannya. */
                    ['harga_jual', 'hpp'].forEach(function (name) {
                        if (fields[name]) {
                            body.set(name, String(fields[name].value || '').replace(/[^0-9]/g, ''));
                        }
                    });

                    fetch(formEl.action, {
                        method: 'POST',
                        body: body,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function (response) {
                        return response.json()
                            .catch(function () { return {}; })
                            .then(function (payload) {
                                return {
                                    status: response.status,
                                    ok: response.ok,
                                    redirected: response.redirected,
                                    payload: payload
                                };
                            });
                    }).then(function (result) {
                        /* 422: validasi gagal -> pesan tampil di dalam modal. */
                        if (result.status === 422) {
                            setBusy(false);
                            showErrors(result.payload.errors, result.payload.message);
                            return;
                        }

                        if (result.redirected) {
                            window.location.assign(listUrl);
                            return;
                        }

                        if (result.ok && result.payload.success) {
                            window.location.assign(result.payload.redirect || listUrl);
                            return;
                        }

                        /* Kasus lain (sesi kedaluwarsa, server error): tampilkan
                           pesan umum dan JANGAN kirim ulang otomatis. */
                        setBusy(false);
                        showErrors({}, result.payload.message || 'Produk gagal disimpan. Silakan coba lagi.');
                    }).catch(function () {
                        setBusy(false);
                        showErrors({}, 'Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.');
                    });
                });
            })();
        </script>

        @if ($formAutoOpen)
            <script>
                /* Modal dibuka otomatis: dipanggil lewat ?open=create /
                   ?open=edit&product={id} atau setelah validasi gagal. */
                document.addEventListener('DOMContentLoaded', function () {
                    var modalEl = document.getElementById('productModal');

                    if (modalEl && window.bootstrap) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                });
            </script>
        @endif
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                if (document.getElementById('chart-harga')) {
                    new ApexCharts(document.getElementById('chart-harga'), KC.base({
                        chart: { type: 'bar' },
                        series: [
                            { name: 'Harga Jual', data: @json($chartJual) },
                            { name: 'HPP', data: @json($chartHpp) },
                            { name: 'Keuntungan', data: @json($chartMargin) }
                        ],
                        xaxis: { categories: @json($chartNama) },
                        legend: { show: true, position: 'bottom' },
                        colors: ['#22D3EE', '#F5B524', '#22C55E']
                    })).render();
                }

                if (document.getElementById('chart-share')) {
                    new ApexCharts(document.getElementById('chart-share'), KC.base({
                        chart: { type: 'donut' },
                        series: @json($shareValue),
                        labels: @json($shareNama),
                        plotOptions: { pie: { donut: { size: '68%' } } },
                        dataLabels: { enabled: true, formatter: function (val) { return KC.pct(val); } },
                        legend: { show: true, position: 'bottom' },
                        tooltip: { y: { formatter: function (v) { return KC.rupiah(v); } } },
                        colors: KC.colors.slice()
                    })).render();
                }
            });
        </script>
    @endpush
@endsection