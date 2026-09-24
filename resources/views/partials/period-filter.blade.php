{{-- Filter Periode dengan tiga mode: Bulanan Spesifik, Full Year,
     dan Rentang Kustom (bulan mulai - bulan selesai, lintas tahun). --}}
@php
    // Set fallback default jika variabel tidak dikirim dari Controller
    $year = $year ?? request('year', date('Y'));
    $monthKey = $monthKey ?? request('month', 'all');
    $months = $months ?? \App\Services\FinancialCalculator::MONTHS_FULL_ID;
    $years = $years ?? range(date('Y') - 5, date('Y') + 5);

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

<div class="filter-fields">
    <div class="filter-field">
    <label for="filter-mode">Mode Periode</label>
    <select name="filter_mode" id="filter-mode" class="filter-select" onchange="this.form.submit()">
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
        onchange="this.form.submit()"
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

<style>
    /* ---------- Dark mode: kontrol filter terlihat jelas, layout horizontal ---------- */
    html[data-theme="dark"] .filter-panel {
        background: var(--panel);
        border: 1px solid var(--border);
    }
    html[data-theme="dark"] .filter-panel .filter-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    html[data-theme="dark"] .filter-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    html[data-theme="dark"] .filter-field label {
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }
    html[data-theme="dark"] .filter-select,
    html[data-theme="dark"] .filter-input {
        background: var(--input);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 9px 12px;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        line-height: 1.4;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        appearance: none;
        -webkit-appearance: none;
    }
    html[data-theme="dark"] .filter-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23D6DBE2' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 32px;
    }
    html[data-theme="dark"] .filter-select:hover,
    html[data-theme="dark"] .filter-input:hover {
        border-color: var(--accent);
    }
    html[data-theme="dark"] .filter-select:focus,
    html[data-theme="dark"] .filter-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }
    html[data-theme="dark"] .filter-select:disabled,
    html[data-theme="dark"] .filter-input:disabled {
        background: #1a1d23;
        color: var(--muted);
        border-color: var(--border);
        cursor: not-allowed;
    }
    html[data-theme="dark"] .filter-select option {
        background: #161922;
        color: var(--text);
    }
    @media (max-width: 576px) {
        html[data-theme="dark"] .filter-panel .filter-fields {
            flex-direction: column;
            gap: 10px;
        }
        html[data-theme="dark"] .filter-field { width: 100%; }
    }
</style>