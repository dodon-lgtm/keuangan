<?php
/**
 * SEMENTARA — hanya untuk audit UI (dark mode contrast + mobile overflow).
 * Menghasilkan snapshot HTML tiap halaman (authenticated) ke public/_audit/.
 * File ini akan dihapus setelah audit selesai.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$BASE = 'http://127.0.0.1:8123';

$user = new App\Models\User();
$user->id = 1;
$user->name = 'Audit';
$user->email = 'audit@local';
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);

$out = __DIR__ . '/public/_audit';
if (! is_dir($out)) {
    mkdir($out, 0777, true);
}

$productId = null;
$customerId = null;
$orderId = null;
try {
    $productId = App\Models\Product::query()->value('id');
    $customerId = App\Models\Customer::query()->value('id');
    $orderId = App\Models\Order::query()->value('id');
} catch (Throwable $e) {
    fwrite(STDERR, 'DB warn: ' . $e->getMessage() . PHP_EOL);
}

$routes = [
    'login' => '/login',
    'dashboard' => '/dashboard',
    'products' => '/products',
    'products-create' => '/products/create',
    'customers' => '/customers',
    'customers-create' => '/customers/create',
    'orders' => '/orders',
    'orders-create' => '/orders/create',
    'expenses' => '/expenses',
    'reports-mer-roi' => '/reports/mer-roi',
    'reports-hpp-profit' => '/reports/hpp-profit',
];

if ($productId) {
    $routes['products-edit'] = '/products/' . $productId . '/edit';
}
if ($customerId) {
    $routes['customers-edit'] = '/customers/' . $customerId . '/edit';
}
if ($orderId) {
    $routes['orders-edit'] = '/orders/' . $orderId . '/edit';
}

// Query string contoh agar state filter (filter-active, dll) ikut teraudit.
$routes['customers-filtered'] = '/customers?q=a&status=repeat';

$report = [];
foreach ($routes as $name => $path) {
    $request = Illuminate\Http\Request::create($BASE . $path, 'GET');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $html = $response->getContent();
        file_put_contents($out . '/' . $name . '.html', $html);
        $report[$name] = ['path' => $path, 'status' => $status, 'bytes' => strlen($html)];
    } catch (Throwable $e) {
        $report[$name] = ['path' => $path, 'error' => $e->getMessage()];
    }
}

foreach ($report as $name => $info) {
    echo str_pad($name, 22) . json_encode($info) . PHP_EOL;
}
