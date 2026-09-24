<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): View
    {
        $q = filled($request->get('q')) ? trim((string) $request->get('q')) : '';
        $hargaMin = filled($request->get('harga_min')) ? (int) $request->get('harga_min') : null;
        $hargaMax = filled($request->get('harga_max')) ? (int) $request->get('harga_max') : null;

        $filters = compact('q', 'hargaMin', 'hargaMax');

        $products = $this->applyFilters($filters, Product::query())
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $statsQuery = $this->applyFilters($filters, Product::query());

        $stats = [
            'total_produk' => $statsQuery->count(),
            'avg_harga_jual' => (int) round((float) ($statsQuery->avg('harga_jual') ?? 0)),
            'avg_hpp' => (int) round((float) ($statsQuery->avg('hpp') ?? 0)),
            'avg_keuntungan' => (int) round((float) ($statsQuery
                ->selectRaw('AVG(harga_jual - hpp) as avg_keuntungan')
                ->value('avg_keuntungan') ?? 0)),
            // Rata-rata margin % (per produk) sebagai pembanding kolom Margin %.
            'avg_margin_percent' => round((float) ($this->applyFilters($filters, Product::query())
                ->selectRaw('AVG(CASE WHEN harga_jual > 0 THEN ((harga_jual - hpp) * 1.0 / harga_jual) * 100 ELSE 0 END) as avg_margin_percent')
                ->value('avg_margin_percent') ?? 0), 2),
        ];

        // Grafik: Harga Jual vs HPP (top 10 produk oleh keuntungan)
        $top = $this->applyFilters($filters, Product::query())
            ->orderByRaw('(harga_jual - hpp) DESC')
            ->limit(10)
            ->get();

        $chartNama = [];
        $chartJual = [];
        $chartHpp = [];
        $chartMargin = [];

        foreach ($top as $product) {
            $chartNama[] = $product->nama_produk;
            $chartJual[] = (int) $product->harga_jual;
            $chartHpp[] = (int) $product->hpp;
            $chartMargin[] = (int) $product->harga_jual - (int) $product->hpp;
        }

        // Grafik: share keuntungan per produk (top 8, margin positief)
        $shareNama = [];
        $shareValue = [];

        foreach ($this->applyFilters($filters, Product::query())
            ->orderByRaw('(harga_jual - hpp) DESC')
            ->limit(8)
            ->get() as $product) {
            $margin = (int) $product->harga_jual - (int) $product->hpp;

            if ($margin > 0) {
                $shareNama[] = $product->nama_produk;
                $shareValue[] = $margin;
            }
        }

        // State awal modal Tambah/Edit Produk (dirender di dalam index).
        [$formMode, $formProduct] = $this->resolveModalState($request);

        // Modal dibuka otomatis ketika halaman dipanggil lewat ?open=... atau
        // setelah submit form gagal validasi (agar pesan error tetap terlihat).
        $formAutoOpen = $request->filled('open') || old('_modal_mode') !== null;

        return view('products.index', compact(
            'products', 'stats',
            'q', 'hargaMin', 'hargaMax',
            'chartNama', 'chartJual', 'chartHpp', 'chartMargin',
            'shareNama', 'shareValue',
            'formMode', 'formProduct', 'formAutoOpen'
        ));
    }

    /**
     * Apply the shared index filters to the given product query.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    private function applyFilters(array $filters, $query)
    {
        $q = (string) ($filters['q'] ?? '');
        $hargaMin = $filters['hargaMin'] ?? null;
        $hargaMax = $filters['hargaMax'] ?? null;

        if ($q !== '') {
            $query->whereLike('nama_produk', "%$q%");
        }

        if ($hargaMin !== null) {
            $query->where('harga_jual', '>=', (int) $hargaMin);
        }

        if ($hargaMax !== null) {
            $query->where('harga_jual', '<=', (int) $hargaMax);
        }

        return $query;
    }

    /**
     * Resolve the initial state of the Tambah/Edit Produk modal that is now
     * rendered together with the index page.
     *
     * Priorities:
     * 1. old('_modal_mode') — the modal form was submitted and validation failed
     *    (redirect back), so the earlier input/mode must be restored.
     * 2. ?open=create / ?open=edit&product={id} — old products.create /
     *    products.edit links (kept as redirects) and the "+ Produk Hijab" button.
     *
     * @return array{0: string, 1: Product|null}
     */
    private function resolveModalState(Request $request): array
    {
        $mode = old('_modal_mode');
        $productId = old('_modal_product_id');

        // Tanpa old input: mode dibaca dari query string (?open=...&product=...).
        if (! is_string($mode)) {
            $mode = $request->query('open');
            $productId = $request->query('product');
        }

        if ($mode !== 'edit' || ! is_scalar($productId) || blank($productId)) {
            return ['create', null];
        }

        $product = Product::query()->find((int) $productId);

        return $product instanceof Product ? ['edit', $product] : ['create', null];
    }

    /**
     * Show the form for creating a new product.
     *
     * The form itself now lives in a modal on the index page, so the legacy
     * products.create URL only redirects there with the modal pre-opened.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('products.index', ['open' => 'create']);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'hpp' => ['required', 'integer', 'min:0'],
        ]);

        // Margin profit dihitung otomatis (tidak diinput manual oleh user).
        $data['margin_profit'] = (int) $data['harga_jual'] - (int) $data['hpp'];

        $product = Product::create($data);

        $message = "Produk {$product->nama_produk} berhasil ditambahkan.";

        // Modal mengirim lewat fetch() (Accept: application/json) agar error
        // validasi bisa tampil di dalam modal tanpa reload halaman.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('products.index'),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified product.
     *
     * Same as create(): the form is a modal on the index page, so this legacy
     * URL redirects there with the edit modal pre-opened for the product.
     */
    public function edit(Product $product): RedirectResponse
    {
        return redirect()->route('products.index', [
            'open' => 'edit',
            'product' => $product->id,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'hpp' => ['required', 'integer', 'min:0'],
        ]);

        // Margin profit selalu dihitung ulang dari harga jual & HPP.
        $data['margin_profit'] = (int) $data['harga_jual'] - (int) $data['hpp'];

        $product->update($data);

        $message = "Produk {$product->nama_produk} berhasil diperbarui.";

        // Lihat store(): jalur JSON dipakai modal, jalur redirect untuk non-JS.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('products.index'),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $nama = $product->nama_produk;

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Produk {$nama} berhasil dihapus.");
    }
}
