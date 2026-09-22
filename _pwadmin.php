<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::find(1);
echo "admin email: " . $u->email . PHP_EOL;
echo "check password123: " . (Illuminate\Support\Facades\Hash::check("password123", $u->password) ? "YES" : "NO") . PHP_EOL;
