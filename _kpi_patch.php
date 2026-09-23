<?php

// --- Fix indentasi col-md-12 di customers/create.blade.php ---
$path = 'c:/xampp/htdocs/keuangan/resources/views/customers/create.blade.php';
$src = file_get_contents($path);

$before = '                        <div class="col-md-12">';   // 24 spaces
$after  = '                    <div class="col-md-12">';      // 20 spaces

$cnt = substr_count($src, $before);
echo "col-md-12 24sp count: $cnt\n";

if ($cnt === 1) {
    $src = str_replace($before, $after, $src);
    file_put_contents($path, $src);
    echo "indent fixed\n";
}

// --- Verifikasi: tidak boleh ada lagi field segment di form ---
echo "segment field remains: " . (strpos($src, 'name=\"segment\"') !== false ? "YES" : "NO") . "\n";
echo "Segment (optional) label remains: " . (strpos($src, '>Segment (optional)<') !== false ? "YES" : "NO") . "\n";



