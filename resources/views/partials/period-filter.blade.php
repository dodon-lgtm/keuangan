{{-- Filter Periode dengan tiga mode: Bulanan Spesifik, Full Year,
     dan Rentang Kustom (bulan mulai - bulan selesai, lintas tahun). --}}
@php
    $filterMode = $filterMode ?? \App\Http\Controllers\Controller::MODE_SPECIFIC;
    $monthsId = $monthsId ?? \App\Services\FinancialCalculator::MONTHS_FULL_ID;
    $startMonth = $startMonth ?? 1;
    $startYear = $startYear ?? $year;
    $endMonth = $endMonth ?? 12;
    $endYear = $endYear ?? $year;
    $modes = [
        \App\Http\Controllers\Controller::MODE_SPECIFIC => 'Bulanan Spesifik',
        \App\Http\Controllers\Controller::MODE_FULL_YEAR => 'Full Year',
        \App\Http\Controllers\Controller::MODE_CUSTOM_RANGE => 'Rentang Kustom',
    ];
    $isCustom = $filterMode === \App\Http\Controllers\Controller::MODE_CUSTOM_RANGE;
@endphp

<div class="filter-field">
    <label for="filter-mode">Mode Periode</label>
    <select name="filter_mode" id="filter-mode" class="filter-select">
        @foreach ($modes as $modeValue => $modeLabel)
            <option value="{{ $modeValue }}" @selected($filterMode === $modeValue)>{{ $modeLabel }}</option>
        @endforeach
    </select>
</div>

<div class="filter-field" data-period-mode="specific" @if ($filterMode !== \App\Http\Controllers\Controller::MODE_SPECIFIC) hidden @endif>
    <label for="filter-month">Bulan</label>
    <select name="month" id="filter-month" class="filter-select" onchange="this.form.submit()">
        <option value="all">Semua Bulan (Full Year)</option>
        @foreach ($months as $key => $label)
            <option value="{{ $key }}" @selected((string) $monthKey === (string) $key)>{{ $label }}</option>
        @endforeach
        <option value="alltime">Semua Tahun (All Time)</option>
    </select>
</div>

<div class="filter-field" data-period-mode="specific" @if ($isCustom) hidden @endif>
    <label for="filter-year">Tahun</label>
    <input type="number" name="year" id="filter-year" class="filter-input" value="{{ $year }}"
        min="1900" max="9999" step="1" inputmode="numeric" autocomplete="off"
        list="filter-year-options" placeholder="mis. 2026"
        @if (! empty($disableYear)) disabled aria-disabled="true" @endif>
    <datalist id="filter-year-options">
        @foreach ($years as $yearOption)
            <option value="{{ $yearOption }}"></option>
        @endforeach
    </datalist>
</div>

<div class="filter-field" data-period-mode="custom_range" @if (! $isCustom) hidden @endif>
    <label for="filter-start-month">Bulan Mulai</label>
    <select name="start_month" id="filter-start-month" class="filter-select" onchange="this.form.submit()">
        @foreach ($monthsId as $monthNo => $monthLabel)
            <option value="{{ $monthNo }}" @selected((int) $startMonth === $monthNo)>{{ $monthLabel }}</option>
        @endforeach
    </select>
</div>

<div class="filter-field" data-period-mode="custom_range" @if (! $isCustom) hidden @endif>
    <label for="filter-start-year">Tahun Mulai</label>
    <select name="start_year" id="filter-start-year" class="filter-select" onchange="this.form.submit()">
        @foreach ($years as $yearOption)
            <option value="{{ $yearOption }}" @selected((int) $startYear === (int) $yearOption)>{{ $yearOption }}</option>
        @endforeach
    </select>
</div>

<div class="filter-field" data-period-mode="custom_range" @if (! $isCustom) hidden @endif>
    <label for="filter-end-month">Bulan Selesai</label>
    <select name="end_month" id="filter-end-month" class="filter-select" onchange="this.form.submit()">
        @foreach ($monthsId as $monthNo => $monthLabel)
            <option value="{{ $monthNo }}" @selected((int) $endMonth === $monthNo)>{{ $monthLabel }}</option>
        @endforeach
    </select>
</div>

<div class="filter-field" data-period-mode="custom_range" @if (! $isCustom) hidden @endif>
    <label for="filter-end-year">Tahun Selesai</label>
    <select name="end_year" id="filter-end-year" class="filter-select" onchange="this.form.submit()">
        @foreach ($years as $yearOption)
            <option value="{{ $yearOption }}" @selected((int) $endYear === (int) $yearOption)>{{ $yearOption }}</option>
        @endforeach
    </select>
</div>

<script>
    // Ganti mode: tampilkan hanya field mode aktif (tanpa langsung submit,
    // agar pengguna bisa mengatur rentangnya terlebih dahulu).
    document.addEventListener('change', function (event) {
        var mode = event.target;
        if (!mode || mode.id !== 'filter-mode') { return; }

        var form = mode.form;
        if (!form) { return; }

        var expected = mode.value === 'custom_range' ? 'custom_range' : 'specific';

        form.querySelectorAll('[data-period-mode]').forEach(function (field) {
            field.hidden = field.getAttribute('data-period-mode') !== expected;
        });
    });

    // Saat form dikirim, nonaktifkan field di luar mode periode aktif agar
    // query tetap bersih dan deterministik.
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.matches || !form.matches('.filter-panel')) { return; }

        var mode = form.querySelector('#filter-mode');
        if (!mode) { return; }

        var expected = mode.value === 'custom_range' ? 'custom_range' : 'specific';

        form.querySelectorAll('[data-period-mode]').forEach(function (field) {
            var input = field.querySelector('select, input');
            if (!input) { return; }

            if (field.getAttribute('data-period-mode') !== expected) {
                input.disabled = true;
            }
        });

        // Mode "Full Year": pilihan bulan spesifik tidak dipakai.
        if (mode.value === 'full_year') {
            var monthInput = form.querySelector('#filter-month');
            if (monthInput) { monthInput.disabled = true; }
        }
    }, true);
</script>