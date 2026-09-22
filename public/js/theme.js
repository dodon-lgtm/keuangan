/* Sistem Theme Global — Vendor Hijab Bandung
   Penyimpanan pilihan: localStorage key "theme" ("dark" | "light").
   Default: dark. Skrip pre-paint di setiap halaman menetapkan data-theme
   pada <html> sebelum render agar tidak terjadi flash. */
(function () {
    'use strict';

    var KEY = 'theme';

    function current() {
        var t = document.documentElement.getAttribute('data-theme');
        return t === 'light' ? 'light' : 'dark';
    }

    function sync(theme) {
        var light = theme === 'light';
        document.querySelectorAll('.theme-switch').forEach(function (btn) {
            btn.setAttribute('aria-checked', light ? 'true' : 'false');
            btn.setAttribute('aria-label', light ? 'Aktifkan Dark Mode' : 'Aktifkan White Mode');
        });
        document.querySelectorAll('.theme-toggle').forEach(function (btn) {
            btn.setAttribute('aria-pressed', light ? 'true' : 'false');
            btn.setAttribute('aria-label', light ? 'Aktifkan Dark Mode' : 'Aktifkan White Mode');
        });
        /* Label status "Mode Gelap" di modal pengaturan */
        var themeState = document.getElementById('themeState');
        if (themeState) themeState.textContent = light ? 'Nonaktif' : 'Aktif';
    }

    function apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem(KEY, theme); } catch (e) {}
        sync(theme);
    }

    function toggle() {
        apply(current() === 'light' ? 'dark' : 'light');
    }

    /* Klik pakai delegation agar tombol di dalam modal (yang dirender
       setelah skrip ini) tetap berfungsi. */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.theme-switch, .theme-toggle') : null;
        if (btn) toggle();
    });

    /* Sinkronkan state saat DOM lengkap (termasuk elemen di modal) */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { sync(current()); });
    } else {
        sync(current());
    }

    /* Sinkronkan antar-tab */
    window.addEventListener('storage', function (e) {
        if (e.key === KEY && e.newValue) {
            document.documentElement.setAttribute('data-theme', e.newValue === 'light' ? 'light' : 'dark');
            sync(document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark');
        }
    });
})();
