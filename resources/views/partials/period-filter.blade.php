{{--
 | Filter Periode: Bulan + Tahun.
 |
 | Bulan   : daftar bulan 1-12 dengan opsi tambahan "Semua Bulan (Full Year)"
 |           yang mengakumulasi seluruh bulan pada tahun terpilih.
 | Tahun   : input angka bebas (1900 - 9999) sehingga tahun lampau maupun
 |           tahun jauh ke depan tetap bisa dipilih. Datalist hanya sebagai
 |           saran, angka apa pun di dalam rentang tetap diterima.
 |
 | Variabel yang dibutuhkan: $monthKey, $months, $year, $years.
--}}
<div class="filter-field">
    <label for="filter-month">Bulan</label>
    <select name="month" id="filter-month" class="filter-select" data-searchable>
        @foreach ($months as $key => $label)
            <option value="{{ $key }}" @selected((string) $monthKey === (string) $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="filter-field">
    <label for="filter-year">Tahun</label>
    <input type="number" name="year" id="filter-year" class="filter-input" value="{{ $year }}"
        min="1900" max="9999" step="1" inputmode="numeric" autocomplete="off"
        list="filter-year-options" placeholder="mis. 2026" required
        @if (! empty($disableYear)) disabled aria-disabled="true" @endif>
    <datalist id="filter-year-options">
        @foreach ($years as $yearOption)
            <option value="{{ $yearOption }}"></option>
        @endforeach
    </datalist>
</div>
