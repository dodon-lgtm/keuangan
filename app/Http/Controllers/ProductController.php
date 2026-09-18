<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(): View
    {
        $products = Product::query()
            ->orderByDesc('id')
            ->paginate(10);

        $stats = [
            'total_produk' => Product::count(),
            'avg_harga_jual' => (int) round((float) (Product::avg('harga_jual') ?? 0)),
            'avg_hpp' => (int) round((float) (Product::avg('hpp') ?? 0)),
            'avg_keuntungan' => (int) round((float) (Product::query()
                ->selectRaw('AVG(harga_jual - hpp) as avg_keuntungan')
                ->value('avg_keuntungan') ?? 0)),
        ];

        return view('products.index', compact('products', 'stats'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'hpp' => ['required', 'integer', 'min:0'],
        ]);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'hpp' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
