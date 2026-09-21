<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the business dashboard for the selected period.
     */
    public function index(Request $request): View
    {
        $period = $this->resolvePeriod($request);
        $month = $period['month'];
        $monthKey = $period['monthKey'];
        $year = $period['year'];

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

        // Grafik data (analisis)
        $series = FinancialCalculator::monthlySeries($year);
        $mer = FinancialCalculator::mer($month, $year);
        $roi = FinancialCalculator::roi($month, $year);
        $profitSplit = FinancialCalculator::profitSplit($month, $year);

        $months = $this->monthFilterOptions();
        $years = $this->yearOptions($year);
        $periodLabel = $this->periodLabel($month, $year);

        return view('dashboard.index', compact(
            'month', 'monthKey', 'year', 'months', 'years', 'periodLabel',
            'totalOmset', 'totalTransaksi', 'averageOrder', 'pelangganAktif',
            'totalHPP', 'totalOngkir', 'totalOperasionalExpenses', 'marketingSpend',
            'totalOperasional', 'netProfit',
            'series', 'mer', 'roi', 'profitSplit'
        ));
    }
}