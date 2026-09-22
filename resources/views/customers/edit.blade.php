@extends('layouts.app')

@section('title', 'Pelanggan Edit')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Pelanggan Edit</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="post" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $customer->nama_lengkap) }}"
                               class="form-control @error('nama_lengkap') is-invalid @enderror">
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nama_brand" class="form-label">Nama Brand</label>
                        <input type="text" name="nama_brand" id="nama_brand" value="{{ old('nama_brand', $customer->nama_brand) }}"
                               class="form-control @error('nama_brand') is-invalid @enderror">
                        @error('nama_brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @include('partials.whatsapp-field', ['value' => old('no_whatsapp', $customer->no_whatsapp)])

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sumber" class="form-label">Sumber</label>
                        <select name="sumber" id="sumber" class="form-select @error('sumber') is-invalid @enderror">
                            @foreach (['Instagram Organik', 'Meta Ads', 'CRM Whatsapp'] as $option)
                                <option value="{{ $option }}" @selected(old('sumber', $customer->sumber) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('sumber')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_masuk_chat" class="form-label">Tanggal Masuk Chat</label>
                        <input type="date" name="tanggal_masuk_chat" id="tanggal_masuk_chat"
                               value="{{ old('tanggal_masuk_chat', $customer->tanggal_masuk_chat?->format('Y-m-d')) }}"
                               class="form-control @error('tanggal_masuk_chat') is-invalid @enderror">
                        @error('tanggal_masuk_chat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="domisili" class="form-label">Domisili (optional)</label>
                        <input type="text" name="domisili" id="domisili" value="{{ old('domisili', $customer->domisili) }}"
                               class="form-control @error('domisili') is-invalid @enderror">
                        @error('domisili')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="segment" class="form-label">Segment (optional)</label>
                        <select name="segment" id="segment" class="form-select @error('segment') is-invalid @enderror">
                            <option value="" @selected(! old('segment', $customer->segment))>— pilih —</option>
                            @foreach (['A', 'B', 'C'] as $option)
                                <option value="{{ $option }}" @selected(old('segment', $customer->segment) === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('segment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email (optional)</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="catatan" class="form-label">Catatan (optional)</label>
                    <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror">{{ old('catatan', $customer->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Diperbaiki menggunakan style var(--muted) agar terlihat jelas di dark mode -->
                <div class="form-text mb-3" style="color: var(--muted) !important;">
                    Status Pelanggan dihitung otomatis: <strong style="color: var(--text) !important;">repeat</strong> (Pelanggan Aktif) jika memiliki lebih dari 1 transaksi order.
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection