@php
    $_id = $id ?? '';
    $_title = $title ?? 'Analisis';
    $_desc = $desc ?? '';
    $_empty = $empty ?? false;
    $_note = $note ?? '';
@endphp
<div class="chart-card">
    <div class="chart-head">
        <h3 class="chart-title">{{ $_title }}</h3>
        @if ($_desc !== '')
            <p class="chart-desc">{{ $_desc }}</p>
        @endif
    </div>
    @if ($_empty)
        <div class="chart-empty">Belum ada data untuk periode ini. Ubah filter atau tambahkan data.</div>
    @else
        <div id="{{ $_id }}" class="chart-canvas"></div>
        @if ($_note !== '')
            <p class="chart-note">{{ $_note }}</p>
        @endif
    @endif
</div>