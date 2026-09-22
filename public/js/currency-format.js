/* ============================================================
   Auto-format nominal (pemisah ribuan) untuk input angka.
   Contoh: mengetik "20000" -> tampil "20.000".
   Nilai asli (tanpa titik) dikirim ke server saat form submit,
   jadi logika backend tidak berubah sama sekali.
   ============================================================ */
(function () {
    'use strict';

    // Hanya input nominal uang (bukan tahun, qty, dsb.)
    var CURRENCY_PATTERN = /(harga|nominal|jumlah|total|budget|biaya|spend|nilai|hpp|profit|omset|ongkir|kirim|dp\b)/i;

    function isCurrencyInput(input) {
        if (input.dataset.currencyFormatted === '1') return false;
        if (input.classList.contains('currency-input')) return true;
        var key = (input.name || '') + ' ' + (input.id || '');
        return input.type === 'number' && CURRENCY_PATTERN.test(key);
    }

    function groupDigits(digits) {
        // "20000" -> "20.000"
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatValue(input) {
        var digits = input.value.replace(/[^\d]/g, '');
        // Buang nol di depan agar tidak jadi "020.000"
        digits = digits.replace(/^0+(?=\d)/, '');
        input.value = digits === '' ? '' : groupDigits(digits);
    }

    function enhance(input) {
        input.dataset.currencyFormatted = '1';
        input.type = 'text';
        input.inputMode = 'numeric';
        input.setAttribute('autocomplete', 'off');
        input.dataset.plainValue = input.value.replace(/[^\d]/g, '');
        formatValue(input);

        input.addEventListener('input', function () {
            formatValue(input);
            input.dataset.plainValue = input.value.replace(/[^\d]/g, '');
        });

        // Bersihkan titik sebelum form dikirim ke server
        var form = input.closest('form');
        if (form && !form.dataset.currencyCleanBound) {
            form.dataset.currencyCleanBound = '1';
            form.addEventListener('submit', function () {
                form.querySelectorAll('input[data-currency-formatted="1"]').forEach(function (el) {
                    el.value = el.value.replace(/\./g, '');
                });
            });
        }
    }

    function init(root) {
        (root || document).querySelectorAll('input[type="number"], .currency-input').forEach(function (input) {
            if (isCurrencyInput(input)) enhance(input);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
