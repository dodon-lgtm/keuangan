<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
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
        $monthKey = $period['monthKey'];
        $year = $period['year'];
        $filterMode = $period['filterMode'];
        $startMonth = $period['startMonth'];
        $startYear = $period['startYear'];
        $endMonth = $period['endMonth'];
        $endYear = $period['endYear'];

        $isCustom = $this->isCustomRange($period);

        // Pada mode rentang kustom seluruh metrik dihitung dari rentang
        // bulan lintas tahun yang dipilih (mis. Nov 2025 - Jan 2026).
        $totalOmset = $isCustom
            ? FinancialCalculator::totalOmsetRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOmset($month, $year);
        $totalHPP = $isCustom
            ? FinancialCalculator::totalHPPRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalHPP($month, $year);
        $totalOngkir = $isCustom
            ? FinancialCalculator::totalOngkirRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOngkir($month, $year);
        $totalOperasionalExpenses = $isCustom
            ? FinancialCalculator::totalOperationalExpensesRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOperationalExpenses($month, $year);
        $totalOperasional = $isCustom
            ? FinancialCalculator::totalOperasionalRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOperasional($month, $year);
        $netProfit = $isCustom
            ? FinancialCalculator::netProfitRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::netProfit($month, $year);
        $marketingSpend = $isCustom
            ? FinancialCalculator::marketingSpendRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::marketingSpend($month, $year);
        $averageOrder = $isCustom
            ? FinancialCalculator::averageOrderRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::averageOrder($month, $year);
        $mer = $isCustom
            ? FinancialCalculator::merRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::mer($month, $year);
        $roi = $isCustom
            ? FinancialCalculator::roiRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::roi($month, $year);
        $profitSplit = $isCustom
            ? FinancialCalculator::profitSplitRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::profitSplit($month, $year);

        // Grafik: trend mer & roi. Rentang kustom menampilkan satu titik
        // data per bulan dalam rentang (lintas tahun), selebihnya per bulan
        // sepanjang tahun yang dipilih.
        $series = $isCustom
            ? FinancialCalculator::monthlyRangeSeries($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::monthlySeries($year);

        $months = $this->monthFilterOptions();
        $monthsId = FinancialCalculator::MONTHS_FULL_ID;
        $years = $this->yearOptionsFor([$year, $startYear, $endYear]);
        $periodLabel = $isCustom
            ? $this->customRangeLabel($startMonth, $startYear, $endMonth, $endYear)
            : $this->periodLabel($month, $year);

        return view('reports.mer-roi', compact(
            'month', 'monthKey', 'year', 'filterMode', 'months', 'monthsId', 'years', 'periodLabel',
            'startMonth', 'startYear', 'endMonth', 'endYear',
            'totalOmset', 'totalHPP', 'totalOngkir', 'totalOperasionalExpenses',
            'totalOperasional', 'netProfit',
            'marketingSpend', 'averageOrder', 'mer', 'roi', 'profitSplit',
            'series'
        ));
    }

    /**
     * Read-only HPP & profit breakdown of sales per product.
     */
    public function hppProfit(Request $request): View
    {
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

        $totalOmset = $isCustom
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

        return view('reports.hpp-profit', compact(
            'month', 'monthKey', 'year', 'filterMode', 'months', 'monthsId', 'years', 'periodLabel',
            'startMonth', 'startYear', 'endMonth', 'endYear', 'products',
            'totalOmset', 'totalOperasional', 'netProfit',
            'chartNama', 'chartOmset', 'chartHpp', 'chartMargin',
            'shareNama', 'shareValue'
        ));
    }
}