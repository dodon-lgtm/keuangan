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

    function apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem(KEY, theme); } catch (e) {}
        document.querySelectorAll('.theme-toggle').forEach(function (btn) {
            btn.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
            btn.setAttribute('aria-label', theme === 'light' ? 'Aktifkan Dark Mode' : 'Aktifkan White Mode');
        });
    }

    document.querySelectorAll('.theme-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            apply(current() === 'light' ? 'dark' : 'light');
        });
    });

    /* Sinkronkan antar-tab */
    window.addEventListener('storage', function (e) {
        if (e.key === KEY && e.newValue) {
            document.documentElement.setAttribute('data-theme', e.newValue === 'light' ? 'light' : 'dark');
        }
    });
})();