<?php
/**
 * SEMENTARA — daftar user + status hash. Dihapus setelah verifikasi.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\User::orderBy('id')->get() as $u) {
    printf(
        "id=%d | name=%s | email=%s | hash=%s (%d chars) | created=%s\n",
        $u->id,
        $u->name,
        $u->email,
        substr((string) $u->password, 0, 7),
        strlen((string) $u->password),
        (string) $u->created_at
    );
}
