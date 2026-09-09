<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * Resolve the filter month / year from the request query parameters,
     * falling back to the current month / year when absent or invalid.
     *
     * @return array<string, int>
     */
    protected function resolvePeriod(Request $request): array
    {
        $now = Carbon::today();

        $month = $request->get('month');
        $year = $request->get('year');

        $month = filled($month) && is_numeric($month) ? min(max((int) $month, 1), 12) : $now->month;
        $year = filled($year) && is_numeric($year) ? min(max((int) $year, 2020), 2100) : $now->year;

        return compact('month', 'year');
    }

    /**
     * Month selector options (1 => January ... 12 => December).
     *
     * @return array<int, string>
     */
    protected function monthOptions(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = Carbon::createFromDate(2026, $month, 1)->format('F');
        }

        return $months;
    }

    /**
     * Year selector options (previous year ... next year).
     *
     * @return array<int, int>
     */
    protected function yearOptions(): array
    {
        $now = Carbon::today();
        $years = [];

        for ($year = $now->year - 1; $year <= $now->year + 1; $year++) {
            $years[] = $year;
        }

        return $years;
    }
}
