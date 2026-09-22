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
        $start = Carbon::createFromDate($year, 1, 1)->format('Y-m-d');
        $end = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');

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
     * First and last day of the resolved period as Y-m-d strings.
     * A null month resolves to the whole year (1 January - 31 December),
     * which powers the "Semua Bulan / Full Year" filter option.
     *
     * @return array{0: string, 1: string}
     */
    public static function periodRange(?int $month, int $year): array
    {
        if ($month === null) {
            return [
                Carbon::createFromDate($year, 1, 1)->format('Y-m-d'),
                Carbon::createFromDate($year, 12, 31)->format('Y-m-d'),
            ];
        }

        return [
            Carbon::createFromDate($year, $month, 1)->format('Y-m-d'),
            Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d'),
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
}