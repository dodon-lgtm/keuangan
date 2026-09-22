{{--
    Notifikasi flash global.

    Menampilkan pesan terbaru dari session (`success`, `error`, `warning`, `info`)
    lengkap dengan ikon, judul, tombol tutup, dan progress bar auto-dismiss.
    Dipakai di layouts/app.blade.php, jadi tersedia di semua halaman aplikasi.
--}}
@php
    $channels = [
        'success' => ['title' => 'Berhasil', 'icon' => 'check'],
        'error' => ['title' => 'Gagal', 'icon' => 'alert'],
        'warning' => ['title' => 'Perhatian', 'icon' => 'alert'],
        'info' => ['title' => 'Informasi', 'icon' => 'info'],
    ];

    $flash = null;

    foreach ($channels as $key => $meta) {
        if (session()->has($key)) {
            $flash = [
                'type' => $key,
                'title' => $meta['title'],
                'icon' => $meta['icon'],
                'message' => session($key),
            ];

            break;
        }
    }

    $duration = 4500;
@endphp

@if ($flash)
    <div class="flash flash-{{ $flash['type'] }}" data-flash data-auto-dismiss="{{ $duration }}"
         style="--flash-duration: {{ $duration }}ms" role="status" aria-live="polite">
        <span class="flash-icon" aria-hidden="true">
            @if ($flash['icon'] === 'check')
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.6 8.6 6 12l7.4-8" /></svg>
            @elseif ($flash['icon'] === 'alert')
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8 1.8 15 14H1z" /><path d="M8 6.2v3.4" /><path d="M8 11.6h.01" /></svg>
            @else
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6.4" /><path d="M8 7.2V11" /><path d="M8 5h.01" /></svg>
            @endif
        </span>

        <span class="flash-content">
            <strong class="flash-title">{{ $flash['title'] }}</strong>
            <span class="flash-message">{{ $flash['message'] }}</span>
        </span>

        <button type="button" class="flash-close" data-flash-close aria-label="Tutup notifikasi" title="Tutup notifikasi">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8" /></svg>
        </button>

        <span class="flash-progress" aria-hidden="true"></span>
    </div>
@endif
