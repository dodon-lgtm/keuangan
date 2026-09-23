@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pelanggan</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ Pelanggan Hijab</a>
    </div>

    {{-- Filter langsung (tanpa tombol): setiap perubahan langsung mengirim form --}}
    <form action="{{ route('customers.index') }}" method="get" class="filter-panel">
        <div class="filter-fields">
            <div class="filter-field">
                <label for="q">Ketikan untuk mencari</label>
                <input type="text" name="q" id="q" value="{{ $q }}" class="filter-input"
                       placeholder="Nama, brand, whatsapp, email..." autocomplete="off">
            </div>
            <div class="filter-field">
                <label for="sumber">Sumber</label>
                <select name="sumber" id="sumber" class="filter-select form-select custom-dropdown-fix" data-searchable onchange="this.form.submit()">
                    <option value="">Sumber</option>
                    @foreach (['Instagram Organik', 'Meta Ads', 'CRM Whatsapp'] as $option)
                        <option value="{{ $option }}" @selected($sumber === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="status">Status</label>
                <select name="status" id="status" class="filter-select form-select custom-dropdown-fix" data-searchable onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="new" @selected($status === 'new')>New</option>
                    <option value="repeat" @selected($status === 'repeat')>Repeat</option>
                </select>
            </div>
            <div class="filter-field">
                <label for="tanggal_from">Tanggal Masuk Van</label>
                <input type="date" name="tanggal_from" id="tanggal_from" value="{{ $tanggalFrom }}" class="filter-input" onchange="this.form.submit()">
            </div>
            <div class="filter-field">
                <label for="tanggal_to">Tanggal Masuk Tot</label>
                <input type="date" name="tanggal_to" id="tanggal_to" value="{{ $tanggalTo }}" class="filter-input" onchange="this.form.submit()">
            </div>
            <a href="{{ route('customers.index') }}" class="filter-reset">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2.5 8a5.5 5.5 0 1 1 1.7 3.95" />
                    <path d="M2.5 13.5v-3h3" />
                </svg>
                Reset
            </a>
            @if ($q !== '' || $sumber !== '' || $status !== '' || $tanggalFrom !== '' || $tanggalTo !== '')
                <span class="filter-active">Filter aktif</span>
            @endif
        </div>
    </form>

    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Nama Brand</th>
                <th>No. WhatsApp</th>
                <th>Sumber</th>
                <th>Tanggal Masuk Chat</th>
                <th>Jumlah Order</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($customers as $customer)
            <tr>
                <td>{{ $customer->id }}</td>
                <td><strong>{{ $customer->nama_lengkap }}</strong></td>
                <td>{{ $customer->nama_brand }}</td>
                <td>{{ $customer->no_whatsapp }}</td>
                <td>{{ $customer->sumber }}</td>
                <td>{{ $customer->tanggal_masuk_chat?->format('d M Y') }}</td>
                <td>{{ $customer->orders_count }}</td>
                <td>
                    @if ($customer->isRepeat())
                        <span class="badge bg-info">Repeat</span>
                    @else
                        <span class="badge bg-secondary">New</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="{{ route('customers.destroy', $customer) }}" method="post" class="d-inline"
                          data-confirm="Pelanggan {{ $customer->nama_lengkap }} beserta data terkait akan dihapus permanen."
                          data-confirm-title="Hapus pelanggan?"
                          data-confirm-label="Ya, hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="pagination-wrap">
        {{ $customers->links() }}
    </div>

    @php
        $sumberHasData = false;
        foreach ($chartSumberValue as $v) { if ((int) $v > 0) { $sumberHasData = true; break; } }

        $statusTotal = (int) $chartStatusValue[0] + (int) $chartStatusValue[1];
    @endphp

    <div class="chart-section">
        <span class="chart-eyebrow">Analisis</span>
        <h2 class="chart-heading">Analisis Grafik</h2>
        <p class="chart-sub">Distribusi pelanggan per sumber dan status (new vs repeat).</p>

        <div class="chart-grid">
            @include('partials.chart-card', [
                'id' => 'chart-sumber',
                'title' => 'Pelanggan per Sumber',
                'desc' => 'Instagram Organik, Meta Ads & CRM Whatsapp',
                'empty' => ! $sumberHasData,
            ])

            @include('partials.chart-card', [
                'id' => 'chart-status',
                'title' => 'New vs Repeat Customers',
                'desc' => 'Transaction count per customer',
                'empty' => $statusTotal === 0,
            ])
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var KC = window.KeuanganChart;

                var searchInput = document.getElementById('q');

                if (searchInput && searchInput.form) {
                    var searchTimer = null;
                    var cancelSearchTimer = function () { clearTimeout(searchTimer); };

                    searchInput.addEventListener('input', function () {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function () { searchInput.form.submit(); }, 500);
                    });

                    searchInput.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter') { return; }
                        event.preventDefault();
                        clearTimeout(searchTimer);
                        searchInput.form.submit();
                    });

                    document.addEventListener('mousedown', function (event) {
                        if (event.target !== searchInput) { cancelSearchTimer(); }
                    }, true);
                }

                if (document.getElementById('chart-sumber')) {
                    KC.register(new ApexCharts(document.getElementById('chart-sumber'), KC.base({
                        chart: { type: 'bar' },
                        series: [{ name: 'Pelanggan', data: @json($chartSumberValue) }],
                        xaxis: { categories: @json($chartSumber) },
                        yaxis: { labels: { formatter: function (v) { return String(Math.round(v)); } } },
                        colors: ['#E11D48']
                    }))).render();
                }

                if (document.getElementById('chart-status')) {
                    KC.register(new ApexCharts(document.getElementById('chart-status'), KC.base({
                        chart: { type: 'donut' },
                        series: @json($chartStatusValue),
                        labels: @json($chartStatus),
                        plotOptions: { pie: { donut: { size: '68%' } } },
                        dataLabels: { enabled: true, formatter: function (val) { return KC.pct(val); } },
                        legend: { show: true, position: 'bottom', labels: { colors: KC.palette().legendColor } },
                        colors: ['#22C55E', '#E11D48']
                    }))).render();
                }
            });
        </script>
    @endpush
@endsection