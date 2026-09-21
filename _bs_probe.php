<?php
// Temp probe: inspect Bootstrap 5.3.3 utility rules that affect dark-mode contrast.
$css = @file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
if ($css === false) { echo "FETCH FAIL\n"; exit; }
echo 'length=' . strlen($css) . "\n\n";
$keys = ['.text-muted{', '.form-text{', '.table-light{', '.card-title{', '--bs-secondary-color:', '.text-body-secondary{', '.table>:not(caption)'];
foreach ($keys as $k) {
    $p = strpos($css, $k);
    echo $k . ' => ' . ($p === false ? 'NOT FOUND' : substr($css, $p, 200)) . "\n\n";
}
