<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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

        $pelangganAktif = Customer::query()
            ->where('status_pelanggan', Customer::STATUS_REPEAT)
            ->count();

        $segmentA = Customer::query()->where('segment', Customer::SEGMENT_A)->count();
        $segmentB = Customer::query()->where('segment', Customer::SEGMENT_B)->count();
        $segmentC = Customer::query()->where('segment', Customer::SEGMENT_C)->count();

        $months = $this->monthOptions();
        $years = $this->yearOptions();

        return view('dashboard.index', compact(
            'month', 'year', 'months', 'years',
            'totalOmset', 'totalTransaksi', 'averageOrder',
            'pelangganAktif', 'segmentA', 'segmentB', 'segmentC'
        ));
    }
}