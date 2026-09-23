<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $month = $period['month'];
        $monthKey = $period['monthKey'];
        $year = $period['year'];
        $chartMode = $period['mode'];
        $filterMode = $period['filterMode'];
        $range = $period['range'];
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
        $totalTransaksi = $isCustom
            ? FinancialCalculator::totalTransaksiRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalTransaksi($month, $year);
        $averageOrder = $isCustom
            ? FinancialCalculator::averageOrderRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::averageOrder($month, $year);
        $pelangganAktif = $isCustom
            ? FinancialCalculator::pelangganAktifRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::pelangganAktif($month, $year);

        // Rincian total operasional: HPP, ongkir, Fix/Variable Cost, marketing.
        $totalHPP = $isCustom
            ? FinancialCalculator::totalHPPRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalHPP($month, $year);
        $totalOngkir = $isCustom
            ? FinancialCalculator::totalOngkirRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOngkir($month, $year);
        $totalOperasionalExpenses = $isCustom
            ? FinancialCalculator::totalOperationalExpensesRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOperationalExpenses($month, $year);
        $marketingSpend = $isCustom
            ? FinancialCalculator::marketingSpendRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::marketingSpend($month, $year);
        $totalOperasional = $isCustom
            ? FinancialCalculator::totalOperasionalRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::totalOperasional($month, $year);
        $netProfit = $isCustom
            ? FinancialCalculator::netProfitRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::netProfit($month, $year);

        // Grafik dinamis (mirip Google Analytics): harian saat bulan tertentu
        // dipilih, bulanan saat "Semua Bulan / Full Year", tahunan saat
        // "Semua Tahun / All Time", dan per bulan untuk rentang kustom.
        $series = match ($chartMode) {
            'daily' => FinancialCalculator::dailySeries($year, $month),
            'yearly' => FinancialCalculator::allTimeSeries(),
            'custom' => FinancialCalculator::monthlyRangeSeries($startMonth, $startYear, $endMonth, $endYear),
            default => FinancialCalculator::monthlySeries($year),
        };

        $chartDaterange = $this->chartDaterange($chartMode, $series);

        $mer = $isCustom
            ? FinancialCalculator::merRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::mer($month, $year);
        $roi = $isCustom
            ? FinancialCalculator::roiRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::roi($month, $year);
        $profitSplit = $isCustom
            ? FinancialCalculator::profitSplitRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::profitSplit($month, $year);

        // Rincian HPP & profit per produk beserta titik grafiknya. Datanya
        // digabung dari Laporan HPP & Profit supaya dashboard menjadi satu
        // tempat pemantauan (tidak perlu pindah ke halaman laporan).
        $products = $isCustom
            ? FinancialCalculator::productProfitBreakdownRange($startMonth, $startYear, $endMonth, $endYear)
            : FinancialCalculator::productProfitBreakdown($month, $year);

        $productChartNama = [];
        $productChartOmset = [];
        $productChartHpp = [];
        $productChartMargin = [];
        $productShareNama = [];
        $productShareValue = [];

        foreach ($products as $index => $product) {
            if ($index < 10) {
                $productChartNama[] = $product['nama_produk'];
                $productChartOmset[] = (int) $product['total_omset'];
                $productChartHpp[] = (int) $product['total_hpp'];
                $productChartMargin[] = (int) $product['margin'];
            }

            if ($index < 8 && (int) $product['margin'] > 0) {
                $productShareNama[] = $product['nama_produk'];
                $productShareValue[] = (int) $product['margin'];
            }
        }

        $months = $this->monthFilterOptions() + [self::MONTH_ALL_TIME => 'Semua Tahun (All Time)'];
        $monthsId = FinancialCalculator::MONTHS_FULL_ID;
        $years = $this->yearOptionsFor([$year, $startYear, $endYear]);
        $periodLabel = $isCustom
            ? $this->customRangeLabel($startMonth, $startYear, $endMonth, $endYear)
            : ($chartMode === 'yearly'
                ? 'Semua Tahun (All Time)'
                : $this->periodLabel($month, $year));

        return view('dashboard.index', compact(
            'month', 'monthKey', 'year', 'chartMode', 'filterMode', 'months', 'monthsId', 'years',
            'periodLabel', 'range', 'startMonth', 'startYear', 'endMonth', 'endYear',
            'totalOmset', 'totalTransaksi', 'averageOrder', 'pelangganAktif',
            'totalHPP', 'totalOngkir', 'totalOperasionalExpenses', 'marketingSpend',
            'totalOperasional', 'netProfit',
            'series', 'mer', 'roi', 'profitSplit', 'chartDaterange',
            'products', 'productChartNama', 'productChartOmset', 'productChartHpp',
            'productChartMargin', 'productShareNama', 'productShareValue'
        ));
    }

    /**
     * Chart date-range pill configuration.
     *
     * @param string $chartMode
     * @param array<int, array<string, mixed>> $series
     * @return array<int, array{label:string, value:string, mode:string, count:int|null}>
     */
    protected function chartDaterange(string $chartMode, array $series): array
    {
        return [
            [
                'label' => '1 BLN',
                'value' => '1bln',
                'mode' => 'daily',
                'count' => $chartMode === 'daily' ? count($series) : null,
            ],
            [
                'label' => '1 TH',
                'value' => '1th',
                'mode' => 'monthly',
                'count' => $chartMode === 'monthly' ? count($series) : null,
            ],
            [
                'label' => 'Maks',
                'value' => 'maks',
                'mode' => 'yearly',
                'count' => $chartMode === 'yearly' ? count($series) : null,
            ],
        ];
    }
}