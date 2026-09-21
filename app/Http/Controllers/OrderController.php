<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request): View
    {
        $q = filled($request->get('q')) ? trim((string) $request->get('q')) : '';
        $tipeBayar = filled($request->get('tipe_bayar')) ? (string) $request->get('tipe_bayar') : '';
        $jenisOrder = filled($request->get('jenis_order')) ? (string) $request->get('jenis_order') : '';
        $metodeBayar = filled($request->get('metode_bayar')) ? (string) $request->get('metode_bayar') : '';
        $tanggalFrom = filled($request->get('tanggal_from')) ? (string) $request->get('tanggal_from') : '';
        $tanggalTo = filled($request->get('tanggal_to')) ? (string) $request->get('tanggal_to') : '';
        $nominalMin = filled($request->get('nominal_min')) ? (int) $request->get('nominal_min') : null;
        $nominalMax = filled($request->get('nominal_max')) ? (int) $request->get('nominal_max') : null;

        $base = Order::query()->with(['customer', 'orderItems.product']);

        if ($q !== '') {
            $base->where(function ($sub) use ($q) {
                $sub->whereLike('pic_admin', "%$q%")
                    ->orWhereIn('customer_id', Customer::query()->select('id')->whereLike('nama_lengkap', "%$q%"))
                    ->orWhereIn('id', DB::table('order_items')
                        ->join('products', 'products.id', '=', 'order_items.product_id')
                        ->whereLike('products.nama_produk', "%$q%")
                        ->select('order_items.order_id'));
            });
        }

        if ($tipeBayar !== '') {
            $base->where('tipe_bayar', $tipeBayar);
        }

        if ($jenisOrder !== '') {
            $base->where('jenis_order', $jenisOrder);
        }

        if ($metodeBayar !== '') {
            $base->where('metode_bayar', $metodeBayar);
        }

        if ($tanggalFrom !== '') {
            $base->where('tanggal', '>=', $tanggalFrom);
        }

        if ($tanggalTo !== '') {
            $base->where('tanggal', '<=', $tanggalTo);
        }

        if ($nominalMin !== null) {
            $base->where('nominal', '>=', $nominalMin);
        }

        if ($nominalMax !== null) {
            $base->where('nominal', '<=', $nominalMax);
        }

        $orders = $base->orderByDesc('id')->paginate(10)->withQueryString();

        // Statistiken (filter-bewust)
        $filteredIds = $base->clone()->select('id');

        $totalOmset = (int) $base->sum('nominal');

        // Ambil array ID-nya terlebih dahulu menggunakan ->pluck('id')
        $orderIds = is_object($filteredIds) ? $filteredIds->pluck('id') : $filteredIds;

       $orderIds = is_object($filteredIds) ? $filteredIds->pluck('id') : $filteredIds;

$totalPcs = (int) DB::table('order_items')
    ->join('orders', 'orders.id', '=', 'order_items.order_id')
    ->whereIn('orders.id', $orderIds)
    ->sum('order_items.jumlah_pcs');
        // Grafik: omset per tipe bayar
        $tipeTotals = [];

        foreach ($base->clone()->get(['tipe_bayar', 'nominal']) as $row) {
            $label = $row->tipe_bayar ?? '';

            $tipeTotals[$label] = ($tipeTotals[$label] ?? 0) + (int) $row->nominal;
        }

        $chartTipe = [];
        $chartTipeValue = [];

        foreach (Order::TIPES as $label) {
            $chartTipe[] = $label;
            $chartTipeValue[] = (int) ($tipeTotals[$label] ?? 0);
        }

        // Grafik: transaksi per jenis order
        $jenisTotals = [];

        foreach ($base->clone()->get(['jenis_order']) as $row) {
            $label = $row->jenis_order ?? '';

            $jenisTotals[$label] = ($jenisTotals[$label] ?? 0) + 1;
        }

        $chartJenis = [];
        $chartJenisValue = [];

        foreach (Order::JENISES as $label) {
            $chartJenis[] = $label;
            $chartJenisValue[] = (int) ($jenisTotals[$label] ?? 0);
        }

        // Grafik: omset per metode bayar
        $metodeTotals = [];

        foreach ($base->clone()->get(['metode_bayar', 'nominal']) as $row) {
            $label = $row->metode_bayar ?? '';

            $metodeTotals[$label] = ($metodeTotals[$label] ?? 0) + (int) $row->nominal;
        }

        $chartMetode = [];
        $chartMetodeValue = [];

        foreach (Order::METODES as $label) {
            $chartMetode[] = $label;
            $chartMetodeValue[] = (int) ($metodeTotals[$label] ?? 0);
        }

        return view('orders.index', compact(
            'orders',
            'totalOmset',
            'totalPcs',
            'q',
            'tipeBayar',
            'jenisOrder',
            'metodeBayar',
            'tanggalFrom',
            'tanggalTo',
            'nominalMin',
            'nominalMax',
            'chartTipe',
            'chartTipeValue',
            'chartJenis',
            'chartJenisValue',
            'chartMetode',
            'chartMetodeValue'
        ));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $customers = Customer::query()->orderBy('nama_lengkap')->get();
        $products = Product::query()->orderBy('nama_produk')->get();

        return view('orders.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $order = Order::create([
            'customer_id' => $data['customer_id'],
            'tanggal' => $data['tanggal'] ?? now(),
            'tipe_bayar' => $data['tipe_bayar'],
            'jenis_order' => $data['jenis_order'],
            'metode_bayar' => $data['metode_bayar'],
            'pic_admin' => $data['pic_admin'],
            'link_desain' => $data['link_desain'] ?? null,
            'ongkir' => $data['ongkir'] ?? 0,
            'alamat_kirim' => $data['alamat_kirim'] ?? null,
            'ukuran_hijab' => $data['ukuran_hijab'] ?? null,
        ]);

        $this->syncOrderItems($order, $data['items']);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View
    {
        $order->load('orderItems.product');

        $customers = Customer::query()->orderBy('nama_lengkap')->get();
        $products = Product::query()->orderBy('nama_produk')->get();

        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $this->validated($request);

        $order->update([
            'customer_id' => $data['customer_id'],
            'tanggal' => $data['tanggal'] ?? $order->tanggal,
            'tipe_bayar' => $data['tipe_bayar'],
            'jenis_order' => $data['jenis_order'],
            'metode_bayar' => $data['metode_bayar'],
            'pic_admin' => $data['pic_admin'],
            'link_desain' => $data['link_desain'] ?? null,
            'ongkir' => $data['ongkir'] ?? 0,
            'alamat_kirim' => $data['alamat_kirim'] ?? null,
            'ukuran_hijab' => $data['ukuran_hijab'] ?? null,
        ]);

        $this->syncOrderItems($order, $data['items']);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order berhasil diperbarui.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order berhasil dihapus.');
    }

    /**
     * Validate the master order payload including its dynamic line items.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'tanggal' => ['nullable', 'date'],
            'tipe_bayar' => ['required', Rule::in(Order::TIPES)],
            'jenis_order' => ['required', Rule::in(Order::JENISES)],
            'metode_bayar' => ['required', Rule::in(Order::METODES)],
            'pic_admin' => ['required', 'string', 'max:255'],
            'link_desain' => ['nullable', 'string'],
            'ongkir' => ['nullable', 'integer', 'min:0'],
            'alamat_kirim' => ['nullable', 'string'],
            'ukuran_hijab' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.jumlah_pcs' => ['required', 'integer', 'min:1'],
        ]);
    }

    /**
     * Replace the line items of an order and recompute its nominal from the
     * sum of the item subtotals (snapshotting the product price & HPP).
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncOrderItems(Order $order, array $items): void
    {
        $order->orderItems()->delete();

        foreach ($items as $item) {
            $product = Product::findOrFail((int) $item['product_id']);

            $order->orderItems()->create([
                'product_id' => $product->id,
                'jumlah_pcs' => (int) $item['jumlah_pcs'],
                'harga_satuan' => $product->harga_jual,
                'hpp_satuan' => $product->hpp,
                'subtotal' => $product->harga_jual * (int) $item['jumlah_pcs'],
            ]);
        }

        OrderObserver::recalcNominal($order);
    }
}
