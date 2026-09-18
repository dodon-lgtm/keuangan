<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(): View
    {
        $orders = Order::query()
            ->with(['customer', 'orderItems.product'])
            ->orderByDesc('id')
            ->paginate(10);

        $totalOmset = (int) Order::query()->sum('nominal');

        $totalPcs = (int) \Illuminate\Support\Facades\DB::table('order_items')->sum('jumlah_pcs');

        return view('orders.index', compact('orders', 'totalOmset', 'totalPcs'));
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