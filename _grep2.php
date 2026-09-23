<?php
$root = 'resources/views';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $lines = file($f->getPathname());
    foreach ($lines as $i => $l) {
        if (stripos($l, 'segment') !== false) {
            echo $f->getPathname() . ':' . ($i + 1) . ': ' . trim($l) . PHP_EOL;
        }
    }
}
