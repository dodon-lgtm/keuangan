@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="dash-head">
        <span class="dash-eyebrow">Overview</span>
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-sub">Pantau performans bisnis hijab Anda dalam satu platform.</p>
        </div>
    </div>

    <form action="{{ route('dashboard') }}" method="get" class="filter-panel" style="margin-top: 24px">
        <div class="filter-field">
            <label for="month">Bulan</label>
            <select name="month" id="month" class="filter-select">
                @foreach ($months as $key => $label)
                    <option value="{{ $key }}" @selected($month == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label for="year">Tahun</label>
            <select name="year" id="year" class="filter-select">
                @foreach ($years as $yearOption)
                    <option value="{{ $yearOption }}" @selected($year == $yearOption)>{{ $yearOption }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="filter-btn">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="8" cy="8" r="6.2" />
                <rect x="3" y="4.5" width="10" height="2" /><rect x="3" y="11.5" width="10" height="2" /><rect x="3" y="4.5" width="2" height="7" /><rect x="11" y="4.5" width="2" height="7" />
            </svg>
            Filter
        </button>
    </form>

    <div class="kpi-grid" style="margin-top: 8px">
        <div class="kpi">
            <div class="kpi-label">Total Omset</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23" />
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $totalOmset])</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Total Transaksi</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
            </div>
            <div class="kpi-value">{{ $totalTransaksi }}</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Average Order Value</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                    <polyline points="17 6 23 6 23 12" />
                </svg>
            </div>
            <div class="kpi-value">@include('partials.rupiah', ['value' => $averageOrder])</div>
        </div>

        <div class="kpi">
            <div class="kpi-label">Pelanggan Aktif</div>
            <div class="kpi-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <div class="kpi-value">{{ $pelangganAktif }}</div>
        </div>
    </div>
@endsection