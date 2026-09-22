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
        $range = $period['range'] ?? '1bln';

        $totalOmset = FinancialCalculator::totalOmset($month, $year);
        $totalTransaksi = FinancialCalculator::totalTransaksi($month, $year);
        $averageOrder = FinancialCalculator::averageOrder($month, $year);
        $pelangganAktif = FinancialCalculator::pelangganAktif($month, $year);

        // Rincian total operasional: HPP, ongkir, Fix/Variable Cost, marketing.
        $totalHPP = FinancialCalculator::totalHPP($month, $year);
        $totalOngkir = FinancialCalculator::totalOngkir($month, $year);
        $totalOperasionalExpenses = FinancialCalculator::totalOperationalExpenses($month, $year);
        $marketingSpend = FinancialCalculator::marketingSpend($month, $year);
        $totalOperasional = FinancialCalculator::totalOperasional($month, $year);
        $netProfit = FinancialCalculator::netProfit($month, $year);

        // Grafik dinamis (mirip Google Analytics): harian saat bulan tertentu
        // dipilih, bulanan saat "Semua Bulan / Full Year", dan tahunan saat
        // "Semua Tahun / All Time".
        $series = match ($chartMode) {
            'daily' => FinancialCalculator::dailySeries($year, $month),
            'yearly' => FinancialCalculator::allTimeSeries(),
            default => FinancialCalculator::monthlySeries($year),
        };

        $chartDaterange = $this->chartDaterange($chartMode, $series);

        $mer = FinancialCalculator::mer($month, $year);
        $roi = FinancialCalculator::roi($month, $year);
        $profitSplit = FinancialCalculator::profitSplit($month, $year);

        $months = $this->monthFilterOptions() + [self::MONTH_ALL_TIME => 'Semua Tahun (All Time)'];
        $years = $this->yearOptions($year);
        $periodLabel = $chartMode === 'yearly'
            ? 'Semua Tahun (All Time)'
            : $this->periodLabel($month, $year);

        return view('dashboard.index', compact(
            'month', 'monthKey', 'year', 'chartMode', 'months', 'years', 'periodLabel', 'range',
            'totalOmset', 'totalTransaksi', 'averageOrder', 'pelangganAktif',
            'totalHPP', 'totalOngkir', 'totalOperasionalExpenses', 'marketingSpend',
            'totalOperasional', 'netProfit',
            'series', 'mer', 'roi', 'profitSplit', 'chartDaterange'
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