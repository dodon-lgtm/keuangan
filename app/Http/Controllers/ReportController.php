<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
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

        // Rincian per produk dipakai bersama halaman Dashboard. Rentang kustom
        // memakai batas bulan/tahun awal & akhir (lintas tahun didukung),
        // selebihnya periode bulan/tahun yang dipilih.
        $products = $isCustom
            ? FinancialCalculator::productProfitBreakdownRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::productProfitBreakdown($month, $year);

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