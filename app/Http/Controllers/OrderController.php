<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use App\Services\FinancialCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Halaman tunggal "Log Order" + "Laporan HPP & Profit".
     *
     * Section 1 (Log Order): panel filter, KPI omset & pcs, tabel transaksi,
     * serta grafik analisis tipe bayar / jenis order / metode bayar.
     * Section 2 (HPP & Profit): KPI ringkasan financial, rincian HPP per
     * produk, dan grafik omset vs HPP vs margin untuk periode terpilih.
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
            $base->whereDate('tanggal', '>=', $tanggalFrom);
        }

        if ($tanggalTo !== '') {
            $base->whereDate('tanggal', '<=', $tanggalTo);
        }

        if ($nominalMin !== null) {
            $base->where('nominal', '>=', $nominalMin);
        }

        if ($nominalMax !== null) {
            $base->where('nominal', '<=', $nominalMax);
        }

        $orders = $base->orderByDesc('id')->paginate(10)->withQueryString();

        // Statistik log order (ikut filter di atas)
        $totalOmset = (int) $base->sum('nominal');

        // ID order hasil filter dipakai untuk menghitung total pcs terjual.
        $orderIds = $base->clone()->select('id')->pluck('id');

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

        // ---------- Section 2: Laporan HPP & Profit (per periode) ----------
        $period = $this->resolvePeriod($request);
        $month = $period['month'];
        $monthKey = $period['monthKey'];
        $year = $period['year'];
        $filterMode = $period['filterMode'];
        $startMonth = $period['startMonth'];
        $startYear = $period['startYear'];
        $endMonth = $period['endMonth'];
        $endYear = $period['endYear'];

        $isCustom = $this->isCustomRange($period);

        // Rentang kustom: batas periode dari pasangan bulan/tahun awal & akhir
        // (lintas tahun didukung). Selebihnya memakai periode bulan/tahun.
        [$start, $end] = $isCustom
            ? FinancialCalculator::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::periodRange($month, $year);

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->select(['products.id', 'products.nama_produk'])
            ->selectRaw('SUM(order_items.jumlah_pcs) as total_pcs')
            ->selectRaw('SUM(order_items.subtotal) as total_omset')
            ->selectRaw('SUM(order_items.hpp_satuan * order_items.jumlah_pcs) as total_hpp')
            ->groupBy('products.id', 'products.nama_produk')
            ->orderByDesc('total_omset')
            ->get();

        $products = [];

        foreach ($rows as $row) {
            $omset = (int) ($row->total_omset ?? 0);
            $hpp = (int) ($row->total_hpp ?? 0);
            $margin = $omset - $hpp;

            $products[] = [
                'nama_produk' => $row->nama_produk,
                'total_pcs' => (int) ($row->total_pcs ?? 0),
                'total_omset' => $omset,
                'total_hpp' => $hpp,
                'margin' => $margin,
                'margin_pct' => $omset > 0 ? round(($margin / $omset) * 100 * 100) / 100 : 0.0,
            ];
        }

        // Ringkasan financial periode terpilih (omset, biaya, laba bersih).
        $periodeOmset = $isCustom
            ? FinancialCalculator::totalOmsetRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOmset($month, $year);
        $totalOperasional = $isCustom
            ? FinancialCalculator::totalOperasionalRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOperasional($month, $year);
        $netProfit = $isCustom
            ? FinancialCalculator::netProfitRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::netProfit($month, $year);

        // Grafik: omset vs hpp vs margin per produk (top 10)
        $chartNama = [];
        $chartOmset = [];
        $chartHpp = [];
        $chartMargin = [];
        $shareNama = [];
        $shareValue = [];

        foreach ($products as $index => $product) {
            if ($index < 10) {
                $chartNama[] = $product['nama_produk'];
                $chartOmset[] = (int) $product['total_omset'];
                $chartHpp[] = (int) $product['total_hpp'];
                $chartMargin[] = (int) $product['margin'];
            }

            if ($index < 8 && (int) $product['margin'] > 0) {
                $shareNama[] = $product['nama_produk'];
                $shareValue[] = (int) $product['margin'];
            }
        }

        $months = $this->monthFilterOptions();
        $monthsId = FinancialCalculator::MONTHS_FULL_ID;
        $years = $this->yearOptionsFor([$year, $startYear, $endYear]);
        $periodLabel = $isCustom
            ? $this->customRangeLabel($startMonth, $startYear, $endMonth, $endYear)
            : $this->periodLabel($month, $year);

        return view('orders.index', compact(
            // Section 1: Log Order
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
            'chartMetodeValue',
            // Section 2: Laporan HPP & Profit
            'products',
            'periodeOmset',
            'totalOperasional',
            'netProfit',
            'chartNama',
            'chartOmset',
            'chartHpp',
            'chartMargin',
            'shareNama',
            'shareValue',
            // Filter periode (dipakai partials.period-filter & label periode)
            'month',
            'monthKey',
            'year',
            'filterMode',
            'months',
            'monthsId',
            'years',
            'periodLabel',
            'startMonth',
            'startYear',
            'endMonth',
            'endYear',
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
            ->with('success', $this->orderLabel($order).' berhasil ditambahkan.');
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
            ->with('success', $this->orderLabel($order).' berhasil diperbarui.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $label = $this->orderLabel($order);

        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', "{$label} berhasil dihapus.");
    }

    /**
     * Label order yang enak dibaca untuk pesan flash
     * ("Order #12 (Fatimah Zahra)").
     */
    private function orderLabel(Order $order): string
    {
        $customer = $order->customer?->nama_lengkap;

        return $customer
            ? "Order #{$order->id} ({$customer})"
            : "Order #{$order->id}";
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
