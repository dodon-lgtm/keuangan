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
        $year = $period['year'];

        $totalOmset = FinancialCalculator::totalOmset($month, $year);
        $totalTransaksi = FinancialCalculator::totalTransaksi($month, $year);
        $averageOrder = FinancialCalculator::averageOrder($month, $year);
        $pelangganAktif = FinancialCalculator::pelangganAktif();

        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('dashboard.index', compact(
            'month', 'year', 'months', 'years',
            'totalOmset', 'totalTransaksi', 'averageOrder', 'pelangganAktif'
        ));
    }
}