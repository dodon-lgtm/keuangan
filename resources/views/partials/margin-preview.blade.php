@php
    /*
     * Preview kalkulasi live "Margin Profit" untuk form Tambah/Edit Produk.
     *
     * Nilai awal dihitung di server agar preview sudah benar sebelum JavaScript
     * berjalan (mis. halaman Edit dengan harga jual 30.000 & HPP 20.000 ->
     * "Rp 10.000" dan "33.33%"). Setiap perubahan pada input #harga_jual / #hpp
     * langsung dihitung ulang oleh script di bagian bawah partial ini.
     *
     * $hargaJual & $hpp boleh berisi angka maupun string berformat Rupiah.
     */
    $_marginNumber = static function ($value): int {
        $raw = trim((string) ($value ?? ''));

        // Kosong atau bernilai negatif dianggap 0 (tanpa NaN / angka minus).
        if ($raw === '' || str_starts_with($raw, '-')) {
            return 0;
        }

        return (int) preg_replace('/[^0-9]/', '', $raw);
    };

    $_marginJual = $_marginNumber($hargaJual ?? 0);
    $_marginHpp = $_marginNumber($hpp ?? 0);

    /*
     * Harga jual kosong / negatif berarti belum ada dasar perhitungan:
     * preview dibiarkan netral "Rp 0" & "0.00%" (tanpa NaN / angka minus).
     */
    if ($_marginJual <= 0) {
        $_marginProfit = 0;
        $_marginPercent = 0.0;
    } else {
        $_marginProfit = $_marginJual - $_marginHpp;
        $_marginPercent = round(($_marginProfit / $_marginJual) * 100, 2);
    }

    $_marginProfitText = 'Rp '.($_marginProfit < 0 ? '-' : '').number_format(abs($_marginProfit), 0, ',', '.');
    $_marginPercentText = number_format($_marginPercent, 2, '.', '');
@endphp

<div class="margin-preview{{ $_marginProfit < 0 ? ' is-negative' : '' }}" id="margin-preview" aria-live="polite">
    <div class="margin-preview-item">
        <span class="margin-preview-label">Margin Profit (Estimasi)</span>
        <span class="margin-preview-value" id="margin-profit-preview">{{ $_marginProfitText }}</span>
    </div>

    <div class="margin-preview-item">
        <span class="margin-preview-label">Margin %</span>
        <span class="margin-preview-value" id="margin-percent-preview">{{ $_marginPercentText }}%</span>
    </div>
</div>

<style>
    /* ---- Margin Profit (Estimasi): mengikuti tema terang/gelap aplikasi ----
       Light mode : kotak terang (#f8fafc) + teks gelap (#0f172a)
       Dark mode  : kotak gelap (#0f172a) + teks terang (#f8fafc)
       Variabel --card-bg/--border-color/--text-main dipakai bila tersedia;
       bila belum didefinisikan, jatuh ke variabel resmi aplikasi
       (--card/--border/--text/--muted dari theme.css). */
    .margin-preview {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 22px;
        margin-bottom: 18px;
        padding: 10px 14px;
        background: var(--card-bg, var(--card, #0F172A));
        border: 1px solid var(--border-color, var(--border, #1E293B));
        border-radius: 12px;
        color: var(--text-main, var(--text, #F8FAFC));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .margin-preview-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 140px;
    }

    .margin-preview-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        color: var(--muted, #94A3B8);
    }

    .margin-preview-value {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
        font-variant-numeric: tabular-nums;
        color: var(--text-main, var(--text, #F8FAFC));
        transition: color 0.15s ease;
    }

    /* ---- Mode Terang: box terang, teks gelap (tidak lagi hitam pekat) ---- */
    html[data-theme="light"] .margin-preview {
        background: #F8FAFC;
        border-color: #E2E8F0;
        color: #0F172A;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }
    html[data-theme="light"] .margin-preview .margin-preview-label { color: #64748B; }
    html[data-theme="light"] .margin-preview .margin-preview-value { color: #0F172A; }

    /* ---- Mode Gelap: box gelap, teks terang ---- */
    html[data-theme="dark"] .margin-preview {
        background: #0F172A;
        border-color: #1E293B;
        color: #F8FAFC;
    }
    html[data-theme="dark"] .margin-preview .margin-preview-label { color: #94A3B8; }
    html[data-theme="dark"] .margin-preview .margin-preview-value { color: #F8FAFC; }

    /* HPP lebih besar dari harga jual: estimasi rugi ditandai merah.
       Selector spesifik per tema agar tidak kalah dari aturan warna di atas. */
    .margin-preview.is-negative .margin-preview-value { color: var(--error); }
    html[data-theme="light"] .margin-preview.is-negative .margin-preview-value { color: #DC2626; }
    html[data-theme="dark"] .margin-preview.is-negative .margin-preview-value { color: #F87171; }

    @media (max-width: 575.98px) {
        .margin-preview { gap: 10px; }
        .margin-preview-item { min-width: 100%; }
    }
</style>

@push('scripts')
    <script>
        /* Kalkulasi live Margin Profit pada form Tambah/Edit Produk:
           Margin Profit = Harga Jual - HPP
           Margin %      = Harga Jual > 0 ? (Margin Profit / Harga Jual) * 100 : 0
           Input berformat Rupiah ("40.000"), kosong, maupun negatif selalu aman:
           karakter non-digit dibuang sehingga preview tidak pernah berisi NaN. */
        (function () {
            var hargaJualInput = document.getElementById('harga_jual');
            var hppInput = document.getElementById('hpp');
            var profitOutput = document.getElementById('margin-profit-preview');
            var percentOutput = document.getElementById('margin-percent-preview');

            if (!hargaJualInput || !hppInput || !profitOutput || !percentOutput) {
                return;
            }

            var preview = document.getElementById('margin-preview');

            function toNumber(value) {
                var raw = String(value == null ? '' : value).trim();

                /* Kosong atau negatif dianggap 0. */
                if (raw === '' || raw.charAt(0) === '-') {
                    return 0;
                }

                var number = parseInt(raw.replace(/[^0-9]/g, ''), 10);

                return isNaN(number) ? 0 : number;
            }

            function formatRupiah(amount) {
                var sign = amount < 0 ? '-' : '';
                var digits = String(Math.abs(amount)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return 'Rp ' + sign + digits;
            }

            function updateMarginPreview() {
                var hargaJual = toNumber(hargaJualInput.value);
                var hpp = toNumber(hppInput.value);
                var marginProfit = 0;
                var marginPercent = 0;

                /* Harga jual kosong / negatif: belum ada dasar perhitungan,
                   preview dibiarkan netral ("Rp 0" dan "0.00%"). */
                if (hargaJual > 0) {
                    marginProfit = hargaJual - hpp;
                    marginPercent = (marginProfit / hargaJual) * 100;
                }

                profitOutput.textContent = formatRupiah(marginProfit);
                percentOutput.textContent = marginPercent.toFixed(2) + '%';

                if (preview) {
                    preview.classList.toggle('is-negative', marginProfit < 0);
                }
            }

            hargaJualInput.addEventListener('input', updateMarginPreview);
            hppInput.addEventListener('input', updateMarginPreview);

            /* Sinkronkan sekali saat halaman dibuka dengan nilai awal dari server. */
            updateMarginPreview();
        })();
    </script>
@endpush
