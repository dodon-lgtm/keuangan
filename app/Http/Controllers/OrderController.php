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
            ->with(['customer', 'product'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
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
        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'tanggal' => ['required', 'date'],
            'nominal' => ['nullable', 'integer', 'min:0'],
            'tipe_bayar' => ['required', Rule::in(Order::TIPES)],
            'jenis_order' => ['required', Rule::in(Order::JENISES)],
            'metode_bayar' => ['required', Rule::in(Order::METODES)],
            'pic_admin' => ['required', 'string', 'max:255'],
            'jumlah_pcs' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(Order::STATUSES)],
            'link_desain' => ['nullable', 'string'],
            'ongkir' => ['nullable', 'integer', 'min:0'],
            'alamat_kirim' => ['nullable', 'string'],
            'ukuran_hijab' => ['nullable', 'string', 'max:255'],
        ]);

        $data['ongkir'] ??= 0;

        Order::create($data);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): View
    {
        $customers = Customer::query()->orderBy('nama_lengkap')->get();
        $products = Product::query()->orderBy('nama_produk')->get();

        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $previousCustomerId = $order->customer_id;

        $data = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'tanggal' => ['required', 'date'],
            'nominal' => ['nullable', 'integer', 'min:0'],
            'tipe_bayar' => ['required', Rule::in(Order::TIPES)],
            'jenis_order' => ['required', Rule::in(Order::JENISES)],
            'metode_bayar' => ['required', Rule::in(Order::METODES)],
            'pic_admin' => ['required', 'string', 'max:255'],
            'jumlah_pcs' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(Order::STATUSES)],
            'link_desain' => ['nullable', 'string'],
            'ongkir' => ['nullable', 'integer', 'min:0'],
            'alamat_kirim' => ['nullable', 'string'],
            'ukuran_hijab' => ['nullable', 'string', 'max:255'],
        ]);

        $data['ongkir'] ??= 0;

        // If nominal was left blank, let the observer re-calculate it from the product.
        if (blank($data['nominal'])) {
            $data['nominal'] = null;
        }

        $order->update($data);

        // When the order moves to another customer, re-sync the previous customer too.
        if ($previousCustomerId !== $order->customer_id) {
            OrderObserver::syncCustomer(Customer::findOrFail($previousCustomerId));
        }

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
}
