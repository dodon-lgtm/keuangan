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
        sync(theme);
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
    }

    function toggle() {
        apply(current() === 'light' ? 'dark' : 'light');
    }

    document.querySelectorAll('.theme-switch, .theme-toggle').forEach(function (btn) {
        btn.addEventListener('click', toggle);
    });
    sync(current());

    /* Sinkronkan antar-tab */
    window.addEventListener('storage', function (e) {
        if (e.key === KEY && e.newValue) {
            document.documentElement.setAttribute('data-theme', e.newValue === 'light' ? 'light' : 'dark');
        }
    });
})();