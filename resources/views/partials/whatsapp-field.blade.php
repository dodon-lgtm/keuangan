@php
    /*
     * Input No. WhatsApp — satu field penuh, hanya angka.
     *
     * $value boleh berisi format apa pun yang tersimpan di database
     * (081234567890 / 0895-1409-1980 / 6281234567890 / 62 812-3456-7890).
     * JavaScript hanya merapikan tampilan (0812-3456-7890). Saat submit, nomor
     * dikirim kembali sebagai digit murni 081234567890 — sama seperti data lama,
     * jadi penyimpanan & pencarian (LIKE) pada kolom no_whatsapp tidak berubah.
     */
    $_waValue = (string) ($value ?? '');
@endphp

<div class="mb-3 wa-wrap">
    <label for="no_whatsapp" class="form-label">No. WhatsApp</label>
    <input type="tel" name="no_whatsapp" id="no_whatsapp" value="{{ $_waValue }}"
           class="form-control wa-number @error('no_whatsapp') is-invalid @enderror"
           inputmode="numeric" autocomplete="tel-national" spellcheck="false"
           maxlength="17" placeholder="0812-3456-7890" data-wa-input
           aria-describedby="no_whatsapp_help">
    @error('no_whatsapp')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text" id="no_whatsapp_help">
        Hanya angka, maksimal 13 digit.
    </div>
</div>

<style>
    /* ---- No. WhatsApp: satu input penuh (mengikuti gaya .form-control aplikasi) ---- */
    .wa-wrap {
        /* Compact & proporsional: cukup untuk 0812-3456-7890, tidak selebar field lain. */
        width: 100%;
        max-width: 340px;
    }

    .wa-number {
        font-variant-numeric: tabular-nums;
        letter-spacing: 0.3px;
    }

    @media (max-width: 575.98px) {
        /* Di mobile field kembali memakai lebar penuh */
        .wa-wrap { max-width: 100%; }

        /* 16px mencegah iOS Safari auto-zoom saat field difokuskan */
        .wa-number { font-size: 16px; }
    }
</style>

@push('scripts')
    <script>
        /* Format No. WhatsApp: hanya angka, maksimal 13 digit, tampil 0812-3456-7890.
           JavaScript hanya merapikan tampilan; nilai yang dikirim ke server tetap
           digit murni (081234567890) agar sama dengan data yang sudah tersimpan. */
        (function () {
            var input = document.querySelector('[data-wa-input]');
            if (!input) return;

            var form = input.form;
            var MAX_DIGITS = 13;
            var GROUP = 4;                      /* 0812-3456-7890 */
            var PHONE_LIKE = /^[0-9+\-\s().]*$/;

            function digitsOnly(value) {
                return String(value == null ? '' : value).replace(/[^0-9]/g, '');
            }

            /* 62812... (kode negara) tetap diterima dan dirapikan menjadi 0812... */
            function normalize(value) {
                var digits = digitsOnly(value);

                if (digits.length > 10 && digits.indexOf('62') === 0) {
                    digits = '0' + digits.slice(2);
                }

                return digits.slice(0, MAX_DIGITS);
            }

            function format(digits) {
                var parts = [];

                for (var i = 0; i < digits.length; i += GROUP) {
                    parts.push(digits.substr(i, GROUP));
                }

                return parts.join('-');
            }

            var initial = input.value;

            if (initial !== '' && (digitsOnly(initial).length < 7 || !PHONE_LIKE.test(initial))) {
                /* Nilai lama yang bukan berbentuk nomor telepon dibiarkan apa adanya. */
                input.dataset.waLegacy = '1';
            } else {
                input.value = format(normalize(initial));
            }

            input.addEventListener('input', function () {
                input.dataset.waTouched = '1';
                input.value = format(normalize(input.value));
            });

            if (form) {
                form.addEventListener('submit', function () {
                    var digits = normalize(input.value);

                    if (digits === '') return; /* kosong: biarkan validasi server */

                    if (input.dataset.waLegacy === '1' && input.dataset.waTouched !== '1') {
                        return; /* data lama tidak diubah tanpa sepengetahuan user */
                    }

                    input.value = digits; /* simpan sebagai digit murni: 081234567890 */
                });
            }
        })();
    </script>
@endpush
