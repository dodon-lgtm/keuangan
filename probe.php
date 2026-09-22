<?php
/* probe: login a temp user, GET /dashboard, dump which marker strings appear */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$jar = __DIR__ . '/_probejar.txt';
@unlink($jar);
$base = 'http://127.0.0.1:8000';

$u = User::firstOrCreate(
    ['email' => 'probe@local.test'],
    ['name' => 'probe', 'email' => 'probe@local.test', 'password' => Hash::make('password123')]
);

function http($path, $post = null) {
    global $jar, $base;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 25,
    ]);
    if ($post) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); }
    $raw = curl_exec($ch);
    $h   = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    return ['code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE), 'body' => substr((string) $raw, $h)];
}

function tok($h) { return preg_match('/name="_token" value="([^"]+)"/', $h, $m) ? $m[1] : null; }

$p = http('/login'); $t = tok($p['body']);
$r = http('/login', ['_token' => $t, 'login' => 'probe@local.test', 'password' => 'password123']);
echo "login_code={$r['code']}\n";

$d = http('/dashboard');
echo "dash_code={$d['code']} bodylen=" . strlen($d['body']) . "\n";
foreach (['/settings/password', 'name="password_confirmation"', 'pw-eye', 'pw-match', 'name="current_password"'] as $k) {
    echo "  has $k: " . (str_contains($d['body'], $k) ? 'YES' : 'no') . "\n";
}

$u->delete();
@unlink($jar);

