<?php
/**
 * SEMENTARA — probe live notifikasi (flash + modal konfirmasi).
 * Bagian A: render partial flash untuk 4 channel.
 * Bagian B: alur HTTP nyata (buat/ubah/hapus) ke server dev.
 * Dihapus setelah verifikasi.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$base = 'http://127.0.0.1:8123';
$jar = __DIR__.'/_notif_jar.txt';
@unlink($jar);

$pass = 0;
$fail = 0;

function check(string $label, bool $ok, string $info = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "  PASS  $label\n";
    } else {
        $fail++;
        echo "  FAIL  $label  ".substr($info, 0, 220)."\n";
    }
}

function http(string $path, array $opt = []): array
{
    global $jar, $base;
    $ch = curl_init($base.$path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    if (isset($opt['post'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opt['post']));
    }
    if (isset($opt['referer'])) {
        curl_setopt($ch, CURLOPT_REFERER, $opt['referer']);
    }
    $raw = (string) curl_exec($ch);
    $size = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $head = substr($raw, 0, $size);
    $body = substr($raw, $size);
    $location = null;
    if (preg_match('/^Location:\s*(.+)$/mi', $head, $m)) {
        $location = trim($m[1]);
    }

    return ['code' => $code, 'body' => $body, 'location' => $location];
}

function token(string $html): string
{
    return preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
}

/* ================= A. Partial flash (4 channel) ================= */
echo "A. Partial flash\n";
$session = $app['session']->driver();
$view = $app['view'];

$channels = [
    'success' => ['flash-success', 'Berhasil'],
    'error' => ['flash-error', 'Gagal'],
    'warning' => ['flash-warning', 'Perhatian'],
    'info' => ['flash-info', 'Informasi'],
];

foreach ($channels as $key => [$cssClass, $title]) {
    $session->flush();
    $session->put($key, "Pesan uji $key");
    $html = $view->make('partials.flash')->render();

    check("[$key] kelas $cssClass", str_contains($html, $cssClass), $html);
    check("[$key] judul \"$title\"", str_contains($html, $title));
    check("[$key] pesan tampil", str_contains($html, "Pesan uji $key"));
    check("[$key] tombol tutup + progress bar", str_contains($html, 'data-flash-close') && str_contains($html, 'flash-progress'));
    check("[$key] auto-dismiss 4500ms", str_contains($html, 'data-auto-dismiss="4500"') && str_contains($html, '--flash-duration: 4500ms'));
    check("[$key] aksesibilitas", str_contains($html, 'role="status"') && str_contains($html, 'aria-live="polite"') && str_contains($html, 'aria-label="Tutup notifikasi"'));
}

$session->flush();
check('tanpa flash -> tidak ada markup', trim($view->make('partials.flash')->render()) === '');

/* ================= B. Alur HTTP ================= */
echo "\nB. Alur HTTP\n";

$email = 'notifprobe@local.test';
$pw = 'password123';

User::where('email', $email)->delete();
User::create(['name' => 'Notif Probe', 'email' => $email, 'password' => Hash::make($pw)]);

// Bersihkan sisa data probe sebelumnya.
Customer::where('nama_lengkap', 'like', 'Probe Notif%')->delete();
Product::where('nama_produk', 'like', 'Probe Notif%')->delete();
MarketingSpend::whereIn('tahun', [2031])->delete();
OperationalExpense::where('nama_pengeluaran', 'like', 'Probe Notif%')->delete();

$page = http('/login');
$login = http('/login', ['post' => ['_token' => token($page['body']), 'login' => $email, 'password' => $pw]]);
check('login sukses', $login['code'] === 302, 'code='.$login['code']);

/* --- Modal konfirmasi tersedia di layout --- */
$dash = http('/dashboard');
check('dashboard 200', $dash['code'] === 200, 'code='.$dash['code']);
check('modal konfirmasi ada di layout', str_contains($dash['body'], 'id="confirmModal"') && str_contains($dash['body'], 'data-confirm-ok') && str_contains($dash['body'], 'data-confirm-cancel'));
check('label tombol konfirmasi Indonesia', str_contains($dash['body'], 'Batal') && ! str_contains($dash['body'], 'Sluiten'));
check('skrip konfirmasi + flash dimuat', str_contains($dash['body'], "querySelectorAll('[data-flash]')") && str_contains($dash['body'], "getAttribute('data-confirm')"));

$csrf = token($dash['body']);

/* --- Pelanggan --- */
$create = http('/customers', [
    'post' => ['_token' => $csrf, 'nama_lengkap' => 'Probe Notif Zahra', 'nama_brand' => 'Probe Brand', 'no_whatsapp' => '081200000001', 'domisili' => 'Bandung', 'segment' => 'A', 'sumber' => Customer::SUMBER_META_ADS, 'tanggal_masuk_chat' => '2026-09-01'],
    'referer' => $base.'/customers/create',
]);
check('buat pelanggan (dengan domisili/segment) -> 302', $create['code'] === 302, 'code='.$create['code'].' loc='.$create['location']);

$customer = Customer::where('nama_lengkap', 'Probe Notif Zahra')->first();
check('pelanggan tersimpan di DB', $customer !== null);
check('domisili & segment tersimpan', $customer !== null && $customer->domisili === 'Bandung' && $customer->segment === 'A');

$list = http('/customers');
check('flash sukses pelanggan (nama entitas)', str_contains($list['body'], 'Pelanggan Probe Notif Zahra berhasil ditambahkan.'), substr($list['body'], 0, 0));
check('markup komponen flash dipakai', str_contains($list['body'], 'flash flash-success') && str_contains($list['body'], 'data-flash-close') && str_contains($list['body'], 'flash-progress'));
check('judul notifikasi "Berhasil"', str_contains($list['body'], '>Berhasil<'));
check('tombol hapus pakai data-confirm bertema', str_contains($list['body'], 'data-confirm="Pelanggan Probe Notif Zahra') && ! str_contains($list['body'], "confirm('Hapus"));

$again = http('/customers');
check('flash hanya tampil sekali (sekali baca)', ! str_contains($again['body'], 'Pelanggan Probe Notif Zahra berhasil ditambahkan.'));

$update = http('/customers/'.$customer->id, [
    'post' => ['_token' => token($again['body']), '_method' => 'PUT', 'nama_lengkap' => 'Probe Notif Az-Zahra', 'nama_brand' => 'Probe Brand', 'no_whatsapp' => '081200000001', 'domisili' => 'Solo', 'segment' => 'B', 'sumber' => Customer::SUMBER_META_ADS, 'tanggal_masuk_chat' => '2026-09-01'],
    'referer' => $base.'/customers/'.$customer->id.'/edit',
]);
check('ubah pelanggan -> 302', $update['code'] === 302, 'code='.$update['code'].' loc='.$update['location']);
$listUpd = http('/customers');
check('flash ubah pelanggan sebut nama baru', str_contains($listUpd['body'], 'Pelanggan Probe Notif Az-Zahra berhasil diperbarui.'), substr($listUpd['body'], 0, 0));
check('domisili & segment terperbarui', $customer->fresh()->domisili === 'Solo' && $customer->fresh()->segment === 'B');

/* --- Produk --- */
$csrf = token($listUpd['body']);
$createP = http('/products', [
    'post' => ['_token' => $csrf, 'nama_produk' => 'Probe Notif Kue', 'harga_jual' => '25000', 'hpp' => '15000'],
    'referer' => $base.'/products/create',
]);
check('buat produk -> 302', $createP['code'] === 302, 'code='.$createP['code']);
$listP = http('/products');
check('flash sukses produk + nama', str_contains($listP['body'], 'Probe Notif Kue berhasil ditambahkan.'), substr($listP['body'], 0, 0));
check('tombol hapus produk pakai data-confirm', str_contains($listP['body'], 'data-confirm='));

/* --- Order --- *
 * BUG PRA-EKSISTING: live tabel `orders` masih punya kolom legacy
 * `product_id` + `jumlah_pcs` (NOT NULL, tanpa default) yang tidak ada di
 * migrasi repo, sehingga OrderController::store (desain multi-item) selalu
 * 500. Didokumentasikan; order dibuat via DB, lalu flash ubah & hapus diuji.
 */
$orderId = null;
if ($createP['code'] === 302) {
    $product = Product::where('nama_produk', 'Probe Notif Kue')->first();
    $cust2 = Customer::where('nama_lengkap', 'Probe Notif Az-Zahra')->first();
    $csrf = token($listP['body']);
    $createO = http('/orders', [
        'post' => [
            '_token' => $csrf,
            'customer_id' => $cust2->id,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Probe Admin',
            'items[0][product_id]' => $product->id,
            'items[0][jumlah_pcs]' => '2',
        ],
        'referer' => $base.'/orders/create',
    ]);
    check('BUG PRA-EKSISTING: buat order via form -> 500 (product_id/jumlah_pcs)', $createO['code'] === 500, 'code='.$createO['code']);

    \Illuminate\Support\Facades\DB::table('orders')->insert([
        'customer_id' => $cust2->id,
        'product_id' => $product->id,
        'tanggal' => date('Y-m-d'),
        'nominal' => 50000,
        'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
        'jenis_order' => Order::JENIS_READY_STOCK,
        'metode_bayar' => Order::METODE_TRANSFER_BANK,
        'pic_admin' => 'Probe Admin',
        'jumlah_pcs' => 2,
        'status' => 'Lunas',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    $order = Order::where('customer_id', $cust2->id)->latest('id')->first();
    $orderId = $order?->id;
    check('order tersimpan di DB', $order !== null);

    $update = http('/orders/'.$orderId, [
        'post' => [
            '_token' => token($listP['body']),
            '_method' => 'PUT',
            'customer_id' => $cust2->id,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Probe Admin',
            'items[0][product_id]' => $product->id,
            'items[0][jumlah_pcs]' => '3',
        ],
        'referer' => $base.'/orders/'.$orderId.'/edit',
    ]);
    check('ubah order -> 302', $update['code'] === 302, 'code='.$update['code'].' loc='.$update['location']);
    $listO = http('/orders');
    check('flash ubah order (label + pelanggan)', str_contains($listO['body'], "Order #{$orderId} (Probe Notif Az-Zahra) berhasil diperbarui."), substr($listO['body'], 0, 0));
    check('markup komponen flash dipakai (order)', str_contains($listO['body'], 'flash flash-success') && str_contains($listO['body'], 'flash-progress'));
    $againO = http('/orders');
    check('flash order hanya tampil sekali', ! str_contains($againO['body'], "Order #{$orderId} berhasil diperbarui."));

    $delO = http('/orders/'.$orderId, ['post' => ['_token' => token($againO['body']), '_method' => 'DELETE']]);
    check('hapus order -> 302', $delO['code'] === 302, 'code='.$delO['code'].' loc='.$delO['location']);
    $listDelO = http('/orders');
    check('flash hapus order', str_contains($listDelO['body'], "Order #{$orderId} (Probe Notif Az-Zahra) berhasil dihapus."), substr($listDelO['body'], 0, 0));
    $orderId = null;
}

/* --- Pengeluaran (biaya iklan) --- */
$csrf = token($listO['body'] ?? $listP['body']);
$createE = http('/expenses/marketing', [
    'post' => ['_token' => $csrf, 'bulan' => '1', 'tahun' => '2031', 'nominal' => '150000'],
    'referer' => $base.'/expenses',
]);
check('buat biaya iklan -> 302', $createE['code'] === 302, 'code='.$createE['code'].' body='.substr($createE['body'], 0, 150));
$listE = http('/expenses');
check('flash sukses biaya iklan (sebut periode)', str_contains($listE['body'], 'Budget iklan January 2031 berhasil ditambahkan.'), substr($listE['body'], 0, 0));

$createX = http('/expenses/operational', [
    'post' => [
        '_token' => token($listE['body']),
        'nama_pengeluaran' => 'Probe Notif ATK',
        'kategori' => OperationalExpense::KATEGORI_VARIABLE_COST,
        'nominal' => '50000',
        'bulan' => '9',
        'tahun' => '2026',
    ],
    'referer' => $base.'/expenses',
]);
check('buat pengeluaran -> 302', $createX['code'] === 302, 'code='.$createX['code'].' body='.substr($createX['body'], 0, 150));
$listX = http('/expenses');
check('flash sukses pengeluaran (sebut nama)', str_contains($listX['body'], 'Pengeluaran Probe Notif ATK berhasil ditambahkan.'));

/* --- Hapus via data-confirm (POST _method DELETE) --- */
$csrf = token($listX['body']);
$del = http('/customers/'.$customer->id, ['post' => ['_token' => $csrf, '_method' => 'DELETE']]);
check('hapus pelanggan -> 302', $del['code'] === 302, 'code='.$del['code']);
$listDel = http('/customers');
check('flash hapus pelanggan tampil', str_contains($listDel['body'], 'dihapus'));

/* ================= C. Selesai + cleanup ================= */
echo "\n";
echo $fail === 0 ? "SEMUA PROBE PASS ($pass)\n" : "$fail GAGAL / $pass PASS\n";

Customer::where('nama_lengkap', 'like', 'Probe Notif%')->delete();
Product::where('nama_produk', 'like', 'Probe Notif%')->delete();
MarketingSpend::whereIn('tahun', [2031])->delete();
OperationalExpense::where('nama_pengeluaran', 'like', 'Probe Notif%')->delete();
if ($orderId) {
    \App\Models\OrderItem::where('order_id', $orderId)->delete();
    Order::where('id', $orderId)->delete();
}
User::where('email', $email)->delete();
@unlink($jar);
exit($fail === 0 ? 0 : 1);


