<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * The month key that means "Semua Tahun / All Time" in the filter form.
     */
    public const MONTH_ALL_TIME = 'alltime';

    /**
     * The month key that means "Semua Bulan / Full Year" in the filter form.
     */
    public const MONTH_ALL = 'all';

    /**
     * Lowest year accepted by the period filter.
     */
    protected const MIN_YEAR = 1900;

    /**
     * Highest year accepted by the period filter.
     */
    protected const MAX_YEAR = 9999;

    /**
     * Resolve the filter month / year from the request query parameters.
     *
     * The year is fully dynamic (1900 - 9999) so past and future periods can
     * be inspected without extra configuration. Three chart granularities are
     * supported (Google Analytics style):
     *  - mode "daily":   a specific month (1 - 12), data aggregated per date.
     *  - mode "monthly": "all" / empty / invalid ("Semua Bulan"), Jan - Dec of
     *                    the selected year, data aggregated per month.
     *  - mode "yearly":  MONTH_ALL_TIME ("Semua Tahun / All Time"), data
     *                    aggregated per year.
     *
     * @return array{month: int|null, monthKey: string, year: int, mode: string}
     */
    protected function resolvePeriod(Request $request): array
    {
        $now = Carbon::today();

        $month = $request->get('month');
        $year = $request->get('year');

        if ($month === self::MONTH_ALL_TIME) {
            $mode = 'yearly';
            $resolvedMonth = null;
            $monthKey = self::MONTH_ALL_TIME;
        } else {
            $isSpecific = is_numeric($month) && (int) $month >= 1 && (int) $month <= 12;
            $mode = $isSpecific ? 'daily' : 'monthly';
            $resolvedMonth = $isSpecific ? (int) $month : null;
            $monthKey = $isSpecific ? (string) $resolvedMonth : self::MONTH_ALL;
        }

        $year = filled($year) && is_numeric($year) ? (int) $year : $now->year;
        $year = min(max($year, self::MIN_YEAR), self::MAX_YEAR);

        return [
            'month' => $resolvedMonth,
            'monthKey' => $monthKey,
            'year' => $year,
            'mode' => $mode,
        ];
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
     * Month selector options for the period filter, including the
     * "Semua Bulan / Full Year" option (key: "all").
     *
     * The plain monthOptions() stays untouched because the expense page
     * renders a chart with exactly twelve month labels.
     *
     * @return array<int|string, string>
     */
    protected function monthFilterOptions(): array
    {
        return [self::MONTH_ALL => 'Semua Bulan (Full Year)'] + $this->monthOptions();
    }

    /**
     * Year selector options (previous year ... next year).
     *
     * The currently selected year is always added to the suggestions, so a
     * custom year outside that window stays visible in the picker.
     *
     * @return array<int, int>
     */
    protected function yearOptions(?int $selectedYear = null): array
    {
        $now = Carbon::today();
        $years = [];

        for ($year = $now->year - 1; $year <= $now->year + 1; $year++) {
            $years[] = $year;
        }

        if ($selectedYear !== null && ! in_array($selectedYear, $years, true)) {
            $years[] = $selectedYear;
            sort($years);
        }

        return $years;
    }

    /**
     * Human readable label of the resolved period, e.g. "September 2026" or
     * "tahun 2026 (Jan-Des)" when every month of that year is selected.
     */
    protected function periodLabel(?int $month, int $year): string
    {
        if ($month === null) {
            return 'tahun '.$year.' (Jan-Des)';
        }

        return ($this->monthOptions()[$month] ?? '').' '.$year;
    }
}
