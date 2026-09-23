<?php
/**
 * SEMENTARA — validasi render dashboard setelah perubahan UI.
 * Me-render /dashboard lewat kernel Laravel (authenticated), lalu memeriksa:
 *  - status HTTP
 *  - tidak ada tombol filter/reset yang tertinggal
 *  - wrapper .filter-fields ada
 *  - CSS style block balance
 *  - ekstraksi JS hasil render & cek syntax Node
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// request + user palsu
$request = Illuminate\Http\Request::create('http://127.0.0.1:8123/dashboard', 'GET');
$app->instance('request', $request);
$user = new App\Models\User();
$user->id = 1;
$user->name = 'Validate';
$user->email = 'validate@local';
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);

$response = $kernel->handle($request);
$html = $response->getContent();

$ok = true;

// 1. status
$statusOk = $response->getStatusCode() === 200;
if (! $statusOk) { $ok = false; }
echo ($statusOk ? 'OK  ' : 'FAIL ') . 'HTTP status 200 (terdapat: ' . $response->getStatusCode() . ')' . PHP_EOL;

// 2. tombol filter/reset harus HILANG dari DASHBOARD (bukan sekadar selector CSS).
//    Cek keberadaan elemen <button> / <a> yang mengandung class tersebut.
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DOMXPath($dom);
$filterBtnEls  = $xpath->query('//button[contains(concat(" ", normalize-space(@class), " "), " filter-btn ")]');
$filterResetEls = $xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " filter-reset ")]');
$noFilterBtn  = $filterBtnEls->length === 0;
$noResetBtn   = $filterResetEls->length === 0;
if (! $noFilterBtn || ! $noResetBtn) {
    echo 'FAIL [tombol Filter/Reset masih ada di DOM]';
    echo ' | filter-btn elemen: ' . $filterBtnEls->length;
    echo ' | filter-reset elemen: ' . $filterResetEls->length . PHP_EOL;
    $ok = false;
} else {
    echo 'OK  [hapus tombol Filter & Reset dari DOM]' . PHP_EOL;
}

// 3. wrapper filter-fields ada (layout horisontal)
$hasFields = str_contains($html, 'filter-fields');
if (! $hasFields) { $ok = false; }
echo ($hasFields ? 'OK  ' : 'FAIL ') . 'wrapper .filter-fields ada' . PHP_EOL;

// 4. onchange auto-submit ada di filter-mode & year
$onchangeMode = str_contains($html, 'id="filter-mode"') && str_contains($html, 'onchange="this.form.submit()"');
$onchangeYear = str_contains($html, 'id="filter-year"') && str_contains($html, 'onchange="this.form.submit()"');
if (! $onchangeMode || ! $onchangeYear) { $ok = false; }
echo ($onchangeMode ? 'OK  ' : 'FAIL ') . 'onchange auto-submit filter_mode';
echo ($onchangeYear  ? ' | OK  ' : ' | FAIL ') . 'onchange auto-submit year' . PHP_EOL;

// 5. judul-bold class di style
$hasBoldStyle = str_contains($html, 'font-weight: 700');
if (! $hasBoldStyle) { $ok = false; }
echo ($hasBoldStyle ? 'OK  ' : 'FAIL ') . 'style: judul-bold (font-weight:700)' . PHP_EOL;

// 6. aksen KPI ::before ada
$hasKpiAccent = str_contains($html, '.kpi::before') && str_contains($html, 'linear-gradient(90deg,');
if (! $hasKpiAccent) { $ok = false; }
echo ($hasKpiAccent ? 'OK  ' : 'FAIL ') . 'style: aksen KPI top ::before' . PHP_EOL;

// 7. filter panel white-mode style ada
$hasFilterStyle = str_contains($html, 'html[data-theme="light"] .filter-panel');
if (! $hasFilterStyle) { $ok = false; }
echo ($hasFilterStyle ? 'OK  ' : 'FAIL ') . 'style: filter-panel white-mode' . PHP_EOL;

// 8. chart palet dinamis (JS: chartPalette, syncChartTheme, MutationObserver)
$hasChartPal = str_contains($html, 'chartPalette') && str_contains($html, 'syncChartTheme');
$hasObserver = str_contains($html, 'MutationObserver') && str_contains($html, "attributeFilter: ['data-theme']");
if (! $hasChartPal || ! $hasObserver) { $ok = false; }
echo ($hasChartPal ? 'OK  ' : 'FAIL ') . 'JS: chartPalette + syncChartTheme';
echo ($hasObserver  ? ' | OK  ' : ' | FAIL ') . 'JS: MutationObserver data-theme' . PHP_EOL;

// 9. keseimbangan kurung kurawal CSS
preg_match_all('#<style[^>]*>(.*?)</style>#is', $html, $styleMatches);
$cssBalOk = true;
foreach ($styleMatches[1] as $css) {
    $open = substr_count($css, '{');
    $close = substr_count($css, '}');
    if ($open !== $close) { $cssBalOk = false; break; }
}
if (! $cssBalOk) { $ok = false; }
echo ($cssBalOk ? 'OK  ' : 'FAIL ') . 'CSS braces balance' . PHP_EOL;

// 10. ekstraksi JS hasil render & cek syntax Node
preg_match_all('#<script[^>]*>(.*?)</script>#is', $html, $jsMatches);
$tmpDir = sys_get_temp_dir() . '/keuangan_js_validate_' . getmypid();
if (! is_dir($tmpDir)) { mkdir($tmpDir, 0777, true); }
$jsIdx = 0;
$jsFail = 0;
foreach ($jsMatches[1] as $js) {
    $js = trim($js);
    if ($js === '') { continue; }
    $jsIdx++;
    $file = $tmpDir . '/script_' . $jsIdx . '.js';
    file_put_contents($file, $js);
    // hilangkan BOM jika ada
    $buf = file_get_contents($file);
    if (str_starts_with($buf, "\xEF\xBB\xBF")) {
        file_put_contents($file, substr($buf, 3));
    }
    // cek syntax
    $cmd = 'node --check ' . escapeshellarg($file) . ' 2>&1';
    $out = [];
    $ret = 0;
    exec($cmd, $out, $ret);
    if ($ret !== 0) {
        $jsFail++;
        echo 'FAIL JS #' . $jsIdx . ' (node --check)\n';
        echo implode("\n", $out) . "\n";
        echo '--- isi JS (5 baris pertama) ---\n';
        $lines = explode("\n", $js);
        echo implode("\n", array_slice($lines, 0, 5)) . "\n\n";
    } else {
        echo 'OK    JS #' . $jsIdx . ' syntax valid' . PHP_EOL;
    }
}
if ($jsFail > 0) { $ok = false; }

echo PHP_EOL . ($ok ? 'SEMUA PEMERIKSAAN LOLOS' : 'ADA PEMERIKSAAN YANG GAGAL') . PHP_EOL;

// bersihkan (biarkan file JS untuk diagnostik jika gagal)
//@unlink(__FILE__);
