<?php
/**
 * SEMENTARA — fix tooltip wrapper syntax error.
 * Problem: replacement menghasilkan "return tipContainer() + '" yang terputus.
 * Fix: ganti "return tipContainer() + '" menjadi "return tipContainer()"
 */
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// Cari dan ganti pola yang rusak
$search = "return tipContainer() + '";
$replace = "return tipContainer()";
$count = 0;
while (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    $count++;
}
echo "Fixed $count occurrence(s) of broken tooltip return\n";

// Cek keseimbangan kurung
$open = substr_count($content, '{');
$close = substr_count($content, '}');
echo "Braces: $open / $close" . ($open === $close ? " OK" : " MISMATCH!") . "\n";

file_put_contents($path, $content);
echo "Saved.\n";
