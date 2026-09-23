<?php

namespace App\Http\Controllers;

use App\Services\FinancialCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    public const MONTH_ALL_TIME = 'alltime';
    public const MONTH_ALL = 'all';

    protected const MIN_YEAR = 1900;
    protected const MAX_YEAR = 9999;

    public const MODE_SPECIFIC = 'specific';
    public const MODE_FULL_YEAR = 'full_year';
    public const MODE_CUSTOM_RANGE = 'custom_range';

    protected function periodModeOptions(): array
    {
        return [
            self::MODE_SPECIFIC => 'Bulanan Spesifik',
            self::MODE_FULL_YEAR => 'Full Year',
            self::MODE_CUSTOM_RANGE => 'Rentang Kustom',
        ];
    }

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
     * Also derives the "range" shortcut used by the timeframe pills:
     * daily => 1bln, monthly => 1th, yearly => maks, custom => custom.
     *
     * In addition, three filter modes are supported via "filter_mode":
     *  - MODE_SPECIFIC:     single month & year (default; backward compatible
     *                       with the legacy "month" & "year" parameters).
     *  - MODE_FULL_YEAR:    the whole selected year (Jan - Des).
     *  - MODE_CUSTOM_RANGE: a custom month range across any years, resolved
     *                       from "start_month", "start_year", "end_month" and
     *                       "end_year" (e.g. Nov 2025 - Jan 2026).
     *
     * @return array{month: int|null, monthKey: string, year: int, mode: string, range: string, filterMode: string, startMonth: int, startYear: int, endMonth: int, endYear: int}
     */
    protected function resolvePeriod(Request $request): array
    {
        $now = Carbon::today();

        $month = $request->get('month');
        $rawYear = $request->get('year');

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

        $year = filled($rawYear) && is_numeric($rawYear) ? (int) $rawYear : $now->year;
        $year = min(max($year, self::MIN_YEAR), self::MAX_YEAR);

        // Resolve the requested filter mode. Anything unknown falls back to
        // the legacy single month / year behaviour. When "filter_mode" is
        // absent (legacy links & first load) the mode is inferred from the
        // legacy "month" parameter so the UI stays consistent.
        $filterMode = $request->get('filter_mode');
        if ($filterMode !== null && ! in_array($filterMode, [self::MODE_SPECIFIC, self::MODE_FULL_YEAR, self::MODE_CUSTOM_RANGE], true)) {
            $filterMode = null;
        }

        if ($filterMode === null) {
            $filterMode = $resolvedMonth === null ? self::MODE_FULL_YEAR : self::MODE_SPECIFIC;
        }

        // Custom range inputs. Only a fully valid month/year combination
        // activates the custom range, otherwise the filter falls back to the
        // resolved month / year above.
        $startMonth = $this->resolveMonthParam($request->get('start_month'), $resolvedMonth ?? $now->month);
        $startYear = $this->resolveYearParam($request->get('start_year'), $year);
        $endMonth = $this->resolveMonthParam($request->get('end_month'), $resolvedMonth ?? $now->month);
        $endYear = $this->resolveYearParam($request->get('end_year'), $year);

        $customRangeValid = $startMonth !== null && $startYear !== null
            && $endMonth !== null && $endYear !== null
            && ($startYear < $endYear || ($startYear === $endYear && $startMonth <= $endMonth));

        if ($filterMode === self::MODE_CUSTOM_RANGE && $customRangeValid) {
            $mode = 'custom';
        } elseif ($filterMode === self::MODE_CUSTOM_RANGE) {
            $filterMode = $resolvedMonth === null ? self::MODE_FULL_YEAR : self::MODE_SPECIFIC;
        } elseif ($filterMode === self::MODE_FULL_YEAR && $month !== self::MONTH_ALL_TIME) {
            // "Full Year" meniadakan bulan spesifik, kecuali opsi warisan
            // "Semua Tahun (All Time)" yang tetap memakai grafik tahunan.
            $resolvedMonth = null;
            $monthKey = self::MONTH_ALL;
            $mode = 'monthly';
        }

        $range = match ($mode) {
            'daily' => '1bln',
            'custom' => 'custom',
            'yearly' => 'maks',
            default => '1th',
        };

        return [
            'month' => $resolvedMonth,
            'monthKey' => $monthKey,
            'year' => $year,
            'mode' => $mode,
            'range' => $range,
            'filterMode' => $filterMode,
            'startMonth' => $startMonth ?? $now->month,
            'startYear' => $startYear ?? $year,
            'endMonth' => $endMonth ?? $now->month,
            'endYear' => $endYear ?? $year,
        ];
    }

    /**
     * Resolve a month parameter (1 - 12) with a fallback value.
     */
    protected function resolveMonthParam(mixed $value, int $fallback): ?int
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        $month = (int) $value;

        return ($month >= 1 && $month <= 12) ? $month : $fallback;
    }

    /**
     * Resolve a year parameter (1900 - 9999) with a fallback value.
     */
    protected function resolveYearParam(mixed $value, int $fallback): ?int
    {
        if (! is_numeric($value)) {
            return $fallback;
        }

        $year = (int) $value;

        return min(max($year, self::MIN_YEAR), self::MAX_YEAR);
    }

    /**
     * Whether the resolved period is a custom month range.
     */
    protected function isCustomRange(array $period): bool
    {
        return ($period['filterMode'] ?? null) === self::MODE_CUSTOM_RANGE;
    }

    /**
     * Indonesian period label for a custom month range,
     * e.g. "Periode Mei 2026 – Juli 2026".
     */
    protected function customRangeLabel(int $startMonth, int $startYear, int $endMonth, int $endYear): string
    {
        $months = FinancialCalculator::MONTHS_FULL_ID;

        return 'Periode '.($months[$startMonth] ?? '').' '.$startYear
            .' – '.($months[$endMonth] ?? '').' '.$endYear;
    }

    protected function monthOptions(): array
    {
        $months = [];
        for ($month = 1; $month <= 12; $month++) { $months[$month] = Carbon::createFromDate(2026, $month, 1)->format('F'); }
        return $months;
    }

    protected function monthFilterOptions(): array
    { return [self::MONTH_ALL => 'Semua Bulan (Full Year)'] + $this->monthOptions(); }

    protected function yearOptions(?int $selectedYear = null): array
    {
        $now = Carbon::today();
        $years = [];
        for ($year = $now->year - 1; $year <= $now->year + 1; $year++) { $years[] = $year; }
        if ($selectedYear !== null && ! in_array($selectedYear, $years, true)) { $years[] = $selectedYear; sort($years); }
        return $years;
    }

    /**
     * Year options for the filter dropdowns, guaranteed to contain every year
     * that the resolved period touches (so a custom range such as
     * November 2025 - January 2026 always offers both years).
     *
     * @param array<int, int|null> $selectedYears
     * @return array<int, int>
     */
    protected function yearOptionsFor(array $selectedYears): array
    {
        $years = $this->yearOptions();

        foreach ($selectedYears as $year) {
            if ($year !== null && ! in_array($year, $years, true)) {
                $years[] = $year;
            }
        }

        sort($years);

        return $years;
    }

    protected function periodStart(int $startMonth, int $startYear): string
    { return Carbon::createFromDate($startYear, $startMonth, 1)->startOfMonth()->format('Y-m-d'); }

    protected function periodEnd(int $endMonth, int $endYear): string
    { return Carbon::createFromDate($endYear, $endMonth, 1)->endOfMonth()->format('Y-m-d'); }

    protected function periodLabel(?int $month, int $year, int $startMonth = null, int $startYear = null, int $endMonth = null, int $endYear = null): string
    {
        if ($startMonth !== null && $endMonth !== null && $startYear !== null && $endYear !== null) {
            return 'Periode '.($this->monthOptions()[$startMonth] ?? '').' '.$startYear.' – '.($this->monthOptions()[$endMonth] ?? '').' '.$endYear;
        }
        if ($month === null) { return 'tahun '.$year.' (Jan-Des)'; }
        return ($this->monthOptions()[$month] ?? '').' '.$year;
    }

    protected function periodRange(int $startMonth, int $startYear, int $endMonth, int $endYear): array
    {
        return [
            $this->periodStart($startMonth, $startYear),
            $this->periodEnd($endMonth, $endYear),
        ];
    }
}
