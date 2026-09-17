@extends('layouts.app')

@section('title', 'Marketing Spend Edit')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="h4 mb-0">Marketing Spend Edit</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('marketing-spends.update', $marketingSpend) }}" method="post" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select name="bulan" id="bulan" class="form-select @error('bulan') is-invalid @enderror">
                            <option value="">-- Kies bulan --</option>
                            @foreach ($months as $key => $label)
                                <option value="{{ $key }}" @selected(old('bulan', $marketingSpend->bulan) == $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('bulan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select name="tahun" id="tahun" class="form-select @error('tahun') is-invalid @enderror">
                            <option value="">-- Kies tahun --</option>
                            @foreach ($years as $yearOption)
                                <option value="{{ $yearOption }}" @selected(old('tahun', $marketingSpend->tahun) == $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                        @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="nominal" class="form-label">Nominal (Rp)</label>
                        <input type="number" name="nominal" id="nominal" value="{{ old('nominal', $marketingSpend->nominal) }}" min="0"
                               class="form-control @error('nominal') is-invalid @enderror">
                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('marketing-spends.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection