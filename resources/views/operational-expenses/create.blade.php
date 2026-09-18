@extends('layouts.app')

@section('title', 'Pengeluaran Operasional Jaubah')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Pengeluaran Operasional Jaubah</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('operational-expenses.store') }}" method="post" novalidate>
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="nama_pengeluaran" class="form-label">Nama Pengeluaran</label>
                        <input type="text" name="nama_pengeluaran" id="nama_pengeluaran" value="{{ old('nama_pengeluaran') }}"
                               placeholder="misal Listrik, Internet/WiFi, Cutting & Mesin"
                               class="form-control @error('nama_pengeluaran') is-invalid @enderror">
                        @error('nama_pengeluaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror">
                            @foreach (['Fix Cost', 'Variable Cost'] as $option)
                                <option value="{{ $option }}" @selected(old('kategori') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="nominal" class="form-label">Nominal (Rp)</label>
                        <input type="number" name="nominal" id="nominal" value="{{ old('nominal') }}" min="0"
                               class="form-control @error('nominal') is-invalid @enderror">
                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select name="bulan" id="bulan" class="form-select @error('bulan') is-invalid @enderror">
                            <option value="">-- Pilih bulan --</option>
                            @foreach ($months as $key => $label)
                                <option value="{{ $key }}" @selected(old('bulan') == $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('bulan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select name="tahun" id="tahun" class="form-select @error('tahun') is-invalid @enderror">
                            <option value="">-- Pilih tahun --</option>
                            @foreach ($years as $yearOption)
                                <option value="{{ $yearOption }}" @selected(old('tahun') == $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                        @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('operational-expenses.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection