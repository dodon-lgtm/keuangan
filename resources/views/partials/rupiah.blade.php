@php
    // Output the given value as Indonesian Rupiah with dot thousands separators.
    // e.g. 200000 -> "Rp 200.000", 1500000 -> "Rp 1.500.000"
    $_moneyRaw  = isset($value) ? $value : 0;
    $_moneyN    = (int) round(($_moneyRaw * 1) + 0);
    $_moneyNeg  = $_moneyN < 0 ? '-' : '';
    $_moneyAbs  = $_moneyN < 0 ? -$_moneyN : $_moneyN;
    $_moneyText = (string) $_moneyAbs;
    $_moneyText = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $_moneyText);
@endphp
Rp {{ $_moneyNeg }}{{ $_moneyText }}