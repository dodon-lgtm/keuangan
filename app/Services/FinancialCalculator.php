<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Central place for all periodic financial calculations.
 *
 * Every metric takes a nullable month: 1 - 12 scopes the calculation to that
 * single month, while null means "Semua Bulan" and aggregates January -
 * December of the given (fully dynamic) year.
 *
 * Revenue (orders.nominal) is always the sum of the attached order_items
 * subtotals. Operating costs combine shipping, cost of goods sold and the
 * realtime operational expenses (Fix/Variable Cost) of the same period.
 */
class FinancialCalculator
{
    /**
     * The profit share for A Roni.
     */
    public const SHARE_RONI = 0.60;

    /**
     * The profit share for Rizky.
     */
    public const SHARE_RIZKY = 0.40;

    /**
     * Total revenue for the given month / year: sum of orders.nominal.
     * A null month accumulates the whole year.
     */
    public static function totalOmset(?int $month, int $year): int
    {
        return (int) static::ordersOfMonth($month, $year)->sum('nominal');
    }

    /**
     * Number of transactions for the given month / year.
     * A null month accumulates the whole year.
     */
    public static function totalTransaksi(?int $month, int $year): int
    {
        return static::ordersOfMonth($month, $year)->count();
    }

    /**
     * Total shipping fees of the orders in the given month / year.
     * A null month accumulates the whole year.
     */
    public static function totalOngkir(?int $month, int $year): int
    {
        return (int) static::ordersOfMonth($month, $year)->sum('ongkir');
    }

    /**
     * Total cost of goods sold for the period:
     * sum(order_items.hpp_satuan * order_items.jumlah_pcs).
     * A null month accumulates the whole year.
     */
    public static function totalHPP(?int $month, int $year): int
    {
        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', static::periodRange($month, $year))
            ->sum(DB::raw('order_items.hpp_satuan * order_items.jumlah_pcs'));
    }

    /**
     * Total realtime operational expenses (Fix/Variable Cost) for the period.
     * A null month accumulates every month of the year.
     */
    public static function totalOperationalExpenses(?int $month, int $year): int
    {
        $query = OperationalExpense::query()->where('tahun', $year);

        if ($month !== null) {
            $query->where('bulan', $month);
        }

        return (int) $query->sum('nominal');
    }

    /**
     * Total operating costs for the period. Every cost component is included:
     * shipping (orders.ongkir) + cost of goods sold + operational expenses
     * (Fix/Variable Cost) + marketing spend.
     *
     * Marketing spend is part of the operating cost because the ad budget is
     * an actual cash-out for the period. A null month accumulates the whole
     * year.
     */
    public static function totalOperasional(?int $month, int $year): int
    {
        return static::totalOngkir($month, $year)
            + static::totalHPP($month, $year)
            + static::totalOperationalExpenses($month, $year)
            + static::marketingSpend($month, $year);
    }

    /**
     * Net profit for the period: totalOmset - totalOperasional, where the
     * operating costs already contain the marketing spend.
     * A null month accumulates the whole year.
     */
    public static function netProfit(?int $month, int $year): int
    {
        return static::totalOmset($month, $year) - static::totalOperasional($month, $year);
    }

    /**
     * Number of "repeat" customers: customers that have more than one order.
     *
     * When a year is given the orders are counted inside that period (a null
     * month counts the whole year), so the metric follows the period filter.
     * Without arguments every order ever recorded is counted.
     */
    public static function pelangganAktif(?int $month = null, ?int $year = null): int
    {
        $query = Customer::query();

        if ($year === null) {
            $query->withCount('orders');
        } else {
            $range = static::periodRange($month, $year);

            $query->withCount(['orders' => function ($orders) use ($range) {
                $orders->whereBetween('orders.tanggal', $range);
            }]);
        }

        return $query->get()->where('orders_count', '>', 1)->count();
    }

    /**
     * The marketing budget entered for the given month / year.
     * A null month sums the budget of every month in that year.
     */
    public static function marketingSpend(?int $month, int $year): int
    {
        $query = MarketingSpend::query()->where('tahun', $year);

        if ($month === null) {
            return (int) $query->sum('nominal');
        }

        $spend = $query->where('bulan', $month)->first();

        return (int) ($spend?->nominal ?? 0);
    }

    /**
     * Marketing Efficiency Ratio: (marketing spend / total omset) * 100%.
     * Returns 0.0 when there was no omset to avoid a division by zero.
     */
    public static function mer(?int $month, int $year): float
    {
        $omset = static::totalOmset($month, $year);

        if ($omset === 0) {
            return 0.0;
        }

        return (static::marketingSpend($month, $year) / $omset) * 100.0;
    }

    /**
     * Return On Investment: (net profit / marketing spend) * 100%.
     * Returns 0.0 when there was no marketing spend to avoid a division by zero.
     */
    public static function roi(?int $month, int $year): float
    {
        $spend = static::marketingSpend($month, $year);

        if ($spend === 0) {
            return 0.0;
        }

        return (static::netProfit($month, $year) / $spend) * 100.0;
    }

    /**
     * Average order value: totalOmset / totalTransaksi.
     * Returns 0.0 when there were no transactions to avoid a division by zero.
     */
    public static function averageOrder(?int $month, int $year): float
    {
        $transaksi = static::totalTransaksi($month, $year);

        if ($transaksi === 0) {
            return 0.0;
        }

        return static::totalOmset($month, $year) / $transaksi;
    }

    /**
     * Split the net profit: A Roni (60%) and Rizky (40%).
     *
     * @return array<string, int>
     */
    public static function profitSplit(?int $month, int $year): array
    {
        $netProfit = static::netProfit($month, $year);

        return [
            'roni' => (int) round($netProfit * static::SHARE_RONI),
            'rizky' => (int) round($netProfit * static::SHARE_RIZKY),
        ];
    }

    /**
     * Per-month financial breakdown for the given year, used by the
     * dashboard &amp; report analysis charts. Aggregation happens in PHP so the
     * query stays portable between MySQL (produkcija) and SQLite (tests).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function monthlySeries(int $year): array
    {
        $start = Carbon::createFromDate($year, 1, 1)->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::createFromDate($year, 12, 31)->endOfDay()->format('Y-m-d H:i:s');

        $series = [];

        for ($month = 1; $month <= 12; $month++) {
            $series[$month] = [
                'label' => Carbon::createFromDate($year, $month, 1)->format('M'),
                'full' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'omset' => 0,
                'hpp' => 0,
                'ongkir' => 0,
                'operacional' => 0,
                'marketing' => 0,
                'transaksi' => 0,
            ];
        }

        foreach (DB::table('orders')
            ->whereBetween('tanggal', [$start, $end])
            ->get(['tanggal', 'nominal', 'ongkir']) as $row) {
            $month = static::monthOfDate((string) $row->tanggal);

            if ($month === null) {
                continue;
            }

            $series[$month]['omset'] += (int) $row->nominal;
            $series[$month]['ongkir'] += (int) $row->ongkir;
            $series[$month]['transaksi']++;
        }

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->get(['orders.tanggal', 'order_items.hpp_satuan', 'order_items.jumlah_pcs']) as $row) {
            $month = static::monthOfDate((string) $row->tanggal);

            if ($month === null) {
                continue;
            }

            $series[$month]['hpp'] += (int) $row->hpp_satuan * (int) $row->jumlah_pcs;
        }

        foreach (OperationalExpense::query()->where('tahun', $year)->get() as $expense) {
            $series[(int) $expense->bulan]['operacional'] += (int) $expense->nominal;
        }

        foreach (MarketingSpend::query()->where('tahun', $year)->get() as $spend) {
            $series[(int) $spend->bulan]['marketing'] += (int) $spend->nominal;
        }

        for ($month = 1; $month <= 12; $month++) {
            $data = $series[$month];

            // Total operational cost of the month, marketing spend included,
            // so the charts match the Total Operasional shown on the pages.
            $data['total_operacional'] = $data['hpp'] + $data['ongkir'] + $data['operacional'] + $data['marketing'];
            $data['net_profit'] = $data['omset'] - $data['total_operacional'];
            $data['mer'] = $data['omset'] > 0
                ? round((($data['marketing'] / $data['omset']) * 100) * 100) / 100
                : 0.0;
            $data['roi'] = $data['marketing'] > 0
                ? round((($data['net_profit'] / $data['marketing']) * 100) * 100) / 100
                : 0.0;

            $series[$month] = $data;
        }

        return $series;
    }

    /**
     * Per-year financial breakdown across every year that has data ("Semua
     * Tahun / All Time"). Years without any transaction between the first and
     * the last data year are zero-filled so the chart line stays continuous.
     * Returns an empty array when the database has no data at all.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function allTimeSeries(): array
    {
        $orders = DB::table('orders')->get(['tanggal', 'nominal', 'ongkir']);

        $years = [];

        foreach ($orders as $order) {
            $years[] = static::yearOfDate((string) $order->tanggal);
        }

        foreach (OperationalExpense::query()->pluck('tahun') as $tahun) {
            $years[] = (int) $tahun;
        }

        foreach (MarketingSpend::query()->pluck('tahun') as $tahun) {
            $years[] = (int) $tahun;
        }

        $years = array_values(array_filter($years, fn ($year) => $year !== null));

        if ($years === []) {
            return [];
        }

        $years = array_values(array_unique($years));
        sort($years);

        $firstYear = $years[0];
        $lastYear = $years[count($years) - 1];

        $series = [];

        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $series[$year] = [
                'label' => (string) $year,
                'full' => 'Tahun ' . $year,
                'omset' => 0,
                'hpp' => 0,
                'ongkir' => 0,
                'operacional' => 0,
                'marketing' => 0,
                'transaksi' => 0,
            ];
        }

        foreach ($orders as $order) {
            $year = static::yearOfDate((string) $order->tanggal);

            if ($year === null || ! isset($series[$year])) {
                continue;
            }

            $series[$year]['omset'] += (int) $order->nominal;
            $series[$year]['ongkir'] += (int) $order->ongkir;
            $series[$year]['transaksi']++;
        }

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->get(['orders.tanggal', 'order_items.hpp_satuan', 'order_items.jumlah_pcs']) as $row) {
            $year = static::yearOfDate((string) $row->tanggal);

            if ($year === null || ! isset($series[$year])) {
                continue;
            }

            $series[$year]['hpp'] += (int) $row->hpp_satuan * (int) $row->jumlah_pcs;
        }

        foreach (OperationalExpense::query()->get() as $expense) {
            $year = (int) $expense->tahun;

            if (isset($series[$year])) {
                $series[$year]['operacional'] += (int) $expense->nominal;
            }
        }

        foreach (MarketingSpend::query()->get() as $spend) {
            $year = (int) $spend->tahun;

            if (isset($series[$year])) {
                $series[$year]['marketing'] += (int) $spend->nominal;
            }
        }

        foreach ($series as $year => $data) {
            $data['total_operacional'] = $data['hpp'] + $data['ongkir'] + $data['operacional'] + $data['marketing'];
            $data['net_profit'] = $data['omset'] - $data['total_operacional'];
            $data['mer'] = $data['omset'] > 0
                ? round((($data['marketing'] / $data['omset']) * 100) * 100) / 100
                : 0.0;
            $data['roi'] = $data['marketing'] > 0
                ? round((($data['net_profit'] / $data['marketing']) * 100) * 100) / 100
                : 0.0;

            $series[$year] = $data;
        }

        return $series;
    }

    /**
     * Per-day financial breakdown of the given month, used by the dashboard
     * chart when a specific month is selected ("granularitas harian").
     *
     * Aggregation happens in PHP (same approach as monthlySeries) so the
     * query stays portable between MySQL and SQLite. Every day of the month
     * is present: days without transactions are zero-filled so the chart
     * line stays continuous from day 1 until the end of the month.
     *
     * Costs that have no day-level date (Fix/Variable Cost and the marketing
     * budget) are spread evenly across the days of the month - the rounding
     * remainder lands on the last days - so summing the returned rows still
     * reconciles exactly with the monthly totals.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function dailySeries(int $year, int $month): array
    {
        $daysInMonth = (int) Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $series = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);

            $series[$day] = [
                'label' => $date->format('d M'),
                'full' => $date->format('d F Y'),
                'omset' => 0,
                'hpp' => 0,
                'ongkir' => 0,
                'operacional' => 0,
                'marketing' => 0,
                'transaksi' => 0,
            ];
        }

        [$start, $end] = static::periodRange($month, $year);

        foreach (DB::table('orders')
            ->whereBetween('tanggal', [$start, $end])
            ->get(['tanggal', 'nominal', 'ongkir']) as $row) {
            $day = static::dayOfDate((string) $row->tanggal);

            if ($day === null || ! isset($series[$day])) {
                continue;
            }

            $series[$day]['omset'] += (int) $row->nominal;
            $series[$day]['ongkir'] += (int) $row->ongkir;
            $series[$day]['transaksi']++;
        }

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->get(['orders.tanggal', 'order_items.hpp_satuan', 'order_items.jumlah_pcs']) as $row) {
            $day = static::dayOfDate((string) $row->tanggal);

            if ($day === null || ! isset($series[$day])) {
                continue;
            }

            $series[$day]['hpp'] += (int) $row->hpp_satuan * (int) $row->jumlah_pcs;
        }

        // Fix/Variable Cost & budget iklan tidak memiliki tanggal: sebar rata.
        $expenseParts = static::spreadPerDay((int) OperationalExpense::query()
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->sum('nominal'), $daysInMonth);

        $marketingParts = static::spreadPerDay((int) MarketingSpend::query()
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->sum('nominal'), $daysInMonth);

        foreach ($series as $day => $data) {
            $data['operacional'] = $expenseParts[$day - 1] ?? 0;
            $data['marketing'] = $marketingParts[$day - 1] ?? 0;

            $data['total_operacional'] = $data['hpp'] + $data['ongkir'] + $data['operacional'] + $data['marketing'];
            $data['net_profit'] = $data['omset'] - $data['total_operacional'];
            $data['mer'] = $data['omset'] > 0
                ? round((($data['marketing'] / $data['omset']) * 100) * 100) / 100
                : 0.0;
            $data['roi'] = $data['marketing'] > 0
                ? round((($data['net_profit'] / $data['marketing']) * 100) * 100) / 100
                : 0.0;

            $series[$day] = $data;
        }

        return $series;
    }

    /**
     * Split an amount evenly over $count buckets; the rounding remainder is
     * added to the last buckets so the parts always sum up to the total.
     *
     * @return array<int, int>
     */
    protected static function spreadPerDay(int $total, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $base = intdiv($total, $count);
        $remainder = $total - ($base * $count);

        $parts = array_fill(0, $count, $base);

        for ($i = $count - $remainder; $i < $count; $i++) {
            $parts[$i]++;
        }

        return $parts;
    }

    /**
     * Resolve the month number (1 - 12) from a stored date value.
     *
     * The month always sits at offset 5 of a "YYYY-MM-DD" (or datetime)
     * string. Returns null when the value cannot be resolved, so callers can
     * skip malformed rows instead of crashing on an unknown series key.
     */
    protected static function monthOfDate(string $date): ?int
    {
        $month = (int) substr($date, 5, 2);

        return ($month >= 1 && $month <= 12) ? $month : null;
    }

    /**
     * Resolve the day number (1 - 31) from a stored date value.
     * Returns null when the value cannot be resolved.
     */
    protected static function dayOfDate(string $date): ?int
    {
        $day = (int) substr($date, 8, 2);

        return ($day >= 1 && $day <= 31) ? $day : null;
    }

    /**
     * Resolve the year from a stored date value.
     * Returns null when the value cannot be resolved.
     */
    protected static function yearOfDate(string $date): ?int
    {
        $year = (int) substr($date, 0, 4);

        return ($year >= 1000 && $year <= 9999) ? $year : null;
    }

    /**
     * First and last day of the resolved period as datetime strings
     * ("Y-m-d H:i:s"). A null month resolves to the whole year
     * (1 January - 31 December), which powers the "Semua Bulan / Full Year"
     * filter option.
     *
     * Kedua batas memakai awal/akhir hari: kolom `tanggal` menyimpan nilai
     * "Y-m-d 00:00:00", sehingga batas akhir "Y-m-d" saja akan MENGEcualikan
     * order yang jatuh di hari terakhir periode (mis. 30 September).
     *
     * @return array{0: string, 1: string}
     */
    public static function periodRange(?int $month, int $year): array
    {
        if ($month === null) {
            return [
                Carbon::createFromDate($year, 1, 1)->startOfDay()->format('Y-m-d H:i:s'),
                Carbon::createFromDate($year, 12, 31)->endOfDay()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            Carbon::createFromDate($year, $month, 1)->startOfMonth()->startOfDay()->format('Y-m-d H:i:s'),
            Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Query builder scoped to the orders of the given month / year.
     * A null month scopes the query to the whole year.
     */
    protected static function ordersOfMonth(?int $month, int $year)
    {
        return Order::query()
            ->whereBetween('tanggal', static::periodRange($month, $year));
    }

    /*
     * ------------------------------------------------------------------
     *  Custom month range support ("Rentang Kustom")
     * ------------------------------------------------------------------
     *  The helpers below accept an explicit start month/year and end
     *  month/year (e.g. November 2025 - January 2026) and aggregate every
     *  metric over that whole period, cross-year ranges included.
     */

    /**
     * Indonesian short month names (index 1 => Jan).
     */
    public const MONTHS_SHORT_ID = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    /**
     * Indonesian full month names (index 1 => Januari).
     */
    public const MONTHS_FULL_ID = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /**
     * First day of the start month and last day of the end month as datetime
     * strings ("Y-m-d H:i:s"). Cross-year ranges (e.g. 2025-11-01 ..
     * 2026-01-31) resolve correctly because both bounds are derived
     * independently from their own year, and the end bound covers the whole
     * last day so month-end orders are included.
     *
     * @return array{0: string, 1: string}
     */
    public static function periodRangeCustom(int $startMonth, int $startYear, int $endMonth, int $endYear): array
    {
        return [
            Carbon::createFromDate($startYear, $startMonth, 1)->startOfMonth()->startOfDay()->format('Y-m-d H:i:s'),
            Carbon::createFromDate($endYear, $endMonth, 1)->endOfMonth()->endOfDay()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Every [month, year] pair inside the custom range, oldest first.
     * Used to match operational expenses / marketing spends that are stored
     * per (bulan, tahun) columns instead of a date.
     *
     * @return array<int, array{month: int, year: int}>
     */
    public static function customRangeMonths(int $startMonth, int $startYear, int $endMonth, int $endYear): array
    {
        $months = [];

        // startOfMonth() menormalkan jam/menit/detik/mikrodetik sehingga
        // perbandingan batas rentang tidak pernah meleset.
        $cursor = Carbon::createFromDate($startYear, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($endYear, $endMonth, 1)->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $months[] = ['month' => (int) $cursor->month, 'year' => (int) $cursor->year];
            $cursor->addMonth();
        }

        return $months;
    }

    /**
     * Scope a bulan/tahun based expense query to the given month pairs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array<int, array{month: int, year: int}> $monthPairs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function expensesWithinMonths($query, array $monthPairs)
    {
        return $query->where(function ($outer) use ($monthPairs) {
            foreach ($monthPairs as $pair) {
                $outer->orWhere(function ($inner) use ($pair) {
                    $inner->where('bulan', $pair['month'])->where('tahun', $pair['year']);
                });
            }
        });
    }

    /**
     * Total revenue (sum of orders.nominal) over a custom month range.
     */
    public static function totalOmsetRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        [$start, $end] = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);

        return (int) Order::query()->whereBetween('tanggal', [$start, $end])->sum('nominal');
    }

    /**
     * Number of transactions over a custom month range.
     */
    public static function totalTransaksiRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        [$start, $end] = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);

        return (int) Order::query()->whereBetween('tanggal', [$start, $end])->count();
    }

    /**
     * Total shipping fees over a custom month range.
     */
    public static function totalOngkirRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        [$start, $end] = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);

        return (int) Order::query()->whereBetween('tanggal', [$start, $end])->sum('ongkir');
    }

    /**
     * Total cost of goods sold over a custom month range.
     */
    public static function totalHPPRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        [$start, $end] = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);

        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->sum(DB::raw('order_items.hpp_satuan * order_items.jumlah_pcs'));
    }

    /**
     * Total realtime operational expenses over a custom month range. Records
     * are matched by the (bulan, tahun) column pairs of the range, so ranges
     * crossing into another year stay accurate.
     */
    public static function totalOperationalExpensesRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        return (int) static::expensesWithinMonths(
            OperationalExpense::query(),
            static::customRangeMonths($startMonth, $startYear, $endMonth, $endYear)
        )->sum('nominal');
    }

    /**
     * Total marketing spend over a custom month range.
     */
    public static function marketingSpendRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        return (int) static::expensesWithinMonths(
            MarketingSpend::query(),
            static::customRangeMonths($startMonth, $startYear, $endMonth, $endYear)
        )->sum('nominal');
    }

    /**
     * Total operating costs over a custom month range.
     */
    public static function totalOperasionalRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        return static::totalOngkirRange($startMonth, $startYear, $endMonth, $endYear)
            + static::totalHPPRange($startMonth, $startYear, $endMonth, $endYear)
            + static::totalOperationalExpensesRange($startMonth, $startYear, $endMonth, $endYear)
            + static::marketingSpendRange($startMonth, $startYear, $endMonth, $endYear);
    }

    /**
     * Net profit over a custom month range.
     */
    public static function netProfitRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        return static::totalOmsetRange($startMonth, $startYear, $endMonth, $endYear)
            - static::totalOperasionalRange($startMonth, $startYear, $endMonth, $endYear);
    }

    /**
     * Average order value over a custom month range.
     */
    public static function averageOrderRange(int $startMonth, int $startYear, int $endMonth, int $endYear): float
    {
        $transaksi = static::totalTransaksiRange($startMonth, $startYear, $endMonth, $endYear);

        if ($transaksi === 0) {
            return 0.0;
        }

        return static::totalOmsetRange($startMonth, $startYear, $endMonth, $endYear) / $transaksi;
    }

    /**
     * Marketing Efficiency Ratio over a custom month range.
     */
    public static function merRange(int $startMonth, int $startYear, int $endMonth, int $endYear): float
    {
        $omset = static::totalOmsetRange($startMonth, $startYear, $endMonth, $endYear);

        if ($omset === 0) {
            return 0.0;
        }

        return (static::marketingSpendRange($startMonth, $startYear, $endMonth, $endYear) / $omset) * 100.0;
    }

    /**
     * Return On Investment over a custom month range.
     */
    public static function roiRange(int $startMonth, int $startYear, int $endMonth, int $endYear): float
    {
        $spend = static::marketingSpendRange($startMonth, $startYear, $endMonth, $endYear);

        if ($spend === 0) {
            return 0.0;
        }

        return (static::netProfitRange($startMonth, $startYear, $endMonth, $endYear) / $spend) * 100.0;
    }

    /**
     * Profit split over a custom month range.
     *
     * @return array<string, int>
     */
    public static function profitSplitRange(int $startMonth, int $startYear, int $endMonth, int $endYear): array
    {
        $netProfit = static::netProfitRange($startMonth, $startYear, $endMonth, $endYear);

        return [
            'roni' => (int) round($netProfit * static::SHARE_RONI),
            'rizky' => (int) round($netProfit * static::SHARE_RIZKY),
        ];
    }

    /**
     * "Repeat customer" count over a custom month range.
     */
    public static function pelangganAktifRange(int $startMonth, int $startYear, int $endMonth, int $endYear): int
    {
        $range = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);

        return Customer::query()
            ->withCount(['orders' => fn ($orders) => $orders->whereBetween('orders.tanggal', $range)])
            ->get()
            ->where('orders_count', '>', 1)
            ->count();
    }

    /**
     * Indonesian label of a month inside a custom range series,
     * e.g. "Nov 2025" / "Des 2025" / "Jan 2026".
     */
    public static function customMonthLabel(int $month, int $year, bool $full = false): string
    {
        $names = $full ? static::MONTHS_FULL_ID : static::MONTHS_SHORT_ID;

        return ($names[$month] ?? (string) $month).' '.$year;
    }

    /**
     * Series key ("Y-m") for a stored date value; null when malformed.
     */
    protected static function seriesKeyOfDate(string $date): ?string
    {
        $year = static::yearOfDate($date);
        $month = $year === null ? null : static::monthOfDate($date);

        if ($year === null || $month === null) {
            return null;
        }

        return static::seriesKey($year, $month);
    }

    /**
     * Normalised "Y-m" series key.
     */
    protected static function seriesKey(int $year, int $month): string
    {
        return $year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Per-month financial breakdown for a custom month range, used by the
     * dashboard & report charts when "Rentang Kustom" is active. Every month
     * between start and end (cross-year included) is present and zero-filled:
     * Nov 2025 - Jan 2026 produces three data points ("Nov 2025", "Des 2025",
     * "Jan 2026"). Aggregation happens in PHP (same approach as
     * monthlySeries) so the query stays portable between MySQL and SQLite.
     *
     * @return array<string, array<string, mixed>> keyed by "Y-m"
     */
    public static function monthlyRangeSeries(int $startMonth, int $startYear, int $endMonth, int $endYear): array
    {
        [$start, $end] = static::periodRangeCustom($startMonth, $startYear, $endMonth, $endYear);
        $monthPairs = static::customRangeMonths($startMonth, $startYear, $endMonth, $endYear);

        $series = [];

        foreach ($monthPairs as $pair) {
            $series[static::seriesKey($pair['year'], $pair['month'])] = [
                'label' => static::customMonthLabel($pair['month'], $pair['year']),
                'full' => static::customMonthLabel($pair['month'], $pair['year'], true),
                'omset' => 0,
                'hpp' => 0,
                'ongkir' => 0,
                'operacional' => 0,
                'marketing' => 0,
                'transaksi' => 0,
            ];
        }

        foreach (DB::table('orders')
            ->whereBetween('tanggal', [$start, $end])
            ->get(['tanggal', 'nominal', 'ongkir']) as $row) {
            $key = static::seriesKeyOfDate((string) $row->tanggal);

            if ($key === null || ! isset($series[$key])) {
                continue;
            }

            $series[$key]['omset'] += (int) $row->nominal;
            $series[$key]['ongkir'] += (int) $row->ongkir;
            $series[$key]['transaksi']++;
        }

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->get(['orders.tanggal', 'order_items.hpp_satuan', 'order_items.jumlah_pcs']) as $row) {
            $key = static::seriesKeyOfDate((string) $row->tanggal);

            if ($key === null || ! isset($series[$key])) {
                continue;
            }

            $series[$key]['hpp'] += (int) $row->hpp_satuan * (int) $row->jumlah_pcs;
        }

        // Fix/Variable Cost & budget iklan tidak memiliki tanggal: cocokkan
        // lewat pasangan (bulan, tahun) di dalam rentang yang dipilih.
        foreach (static::expensesWithinMonths(OperationalExpense::query(), $monthPairs)->get() as $expense) {
            $key = static::seriesKey((int) $expense->tahun, (int) $expense->bulan);

            if (isset($series[$key])) {
                $series[$key]['operacional'] += (int) $expense->nominal;
            }
        }

        foreach (static::expensesWithinMonths(MarketingSpend::query(), $monthPairs)->get() as $spend) {
            $key = static::seriesKey((int) $spend->tahun, (int) $spend->bulan);

            if (isset($series[$key])) {
                $series[$key]['marketing'] += (int) $spend->nominal;
            }
        }

        foreach ($series as $key => $data) {
            $data['total_operacional'] = $data['hpp'] + $data['ongkir'] + $data['operacional'] + $data['marketing'];
            $data['net_profit'] = $data['omset'] - $data['total_operacional'];
            $data['mer'] = $data['omset'] > 0
                ? round((($data['marketing'] / $data['omset']) * 100) * 100) / 100
                : 0.0;
            $data['roi'] = $data['marketing'] > 0
                ? round((($data['net_profit'] / $data['marketing']) * 100) * 100) / 100
                : 0.0;

            $series[$key] = $data;
        }

        return $series;
    }
}