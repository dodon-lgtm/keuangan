<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Read-only monthly MER & ROI recap (marketing spend efficiency).
     */
    public function merRoi(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $month = $period['month'];
        $year = $period['year'];

        $totalOmset = FinancialCalculator::totalOmset($month, $year);
        $totalHPP = FinancialCalculator::totalHPP($month, $year);
        $totalOngkir = FinancialCalculator::totalOngkir($month, $year);
        $totalOperasionalExpenses = FinancialCalculator::totalOperationalExpenses($month, $year);
        $totalOperasional = FinancialCalculator::totalOperasional($month, $year);
        $netProfit = FinancialCalculator::netProfit($month, $year);
        $marketingSpend = FinancialCalculator::marketingSpend($month, $year);
        $averageOrder = FinancialCalculator::averageOrder($month, $year);
        $mer = FinancialCalculator::mer($month, $year);
        $roi = FinancialCalculator::roi($month, $year);
        $profitSplit = FinancialCalculator::profitSplit($month, $year);

        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('reports.mer-roi', compact(
            'month', 'year', 'months', 'years',
            'totalOmset', 'totalHPP', 'totalOngkir', 'totalOperasionalExpenses',
            'totalOperasional', 'netProfit',
            'marketingSpend', 'averageOrder', 'mer', 'roi', 'profitSplit'
        ));
    }

    /**
     * Read-only HPP & profit breakdown of sales per product.
     */
    public function hppProfit(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $month = $period['month'];
        $year = $period['year'];

        $start = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

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

        $totalOmset = FinancialCalculator::totalOmset($month, $year);
        $totalOperasional = FinancialCalculator::totalOperasional($month, $year);
        $netProfit = FinancialCalculator::netProfit($month, $year);

        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('reports.hpp-profit', compact(
            'month', 'year', 'months', 'years', 'products',
            'totalOmset', 'totalOperasional', 'netProfit'
        ));
    }
}