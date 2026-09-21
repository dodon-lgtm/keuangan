<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Central place for all monthly financial calculations.
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
     */
    public static function totalOmset(int $month, int $year): int
    {
        return (int) static::ordersOfMonth($month, $year)->sum('nominal');
    }

    /**
     * Number of transactions for the given month / year.
     */
    public static function totalTransaksi(int $month, int $year): int
    {
        return static::ordersOfMonth($month, $year)->count();
    }

    /**
     * Total shipping fees of the orders in the given month / year.
     */
    public static function totalOngkir(int $month, int $year): int
    {
        return (int) static::ordersOfMonth($month, $year)->sum('ongkir');
    }

    /**
     * Total cost of goods sold for the period:
     * sum(order_items.hpp_satuan * order_items.jumlah_pcs).
     */
    public static function totalHPP(int $month, int $year): int
    {
        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [static::startOfMonth($month, $year), static::endOfMonth($month, $year)])
            ->sum(DB::raw('order_items.hpp_satuan * order_items.jumlah_pcs'));
    }

    /**
     * Total realtime operational expenses (Fix/Variable Cost) for the period.
     */
    public static function totalOperationalExpenses(int $month, int $year): int
    {
        return (int) OperationalExpense::query()
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->sum('nominal');
    }

    /**
     * Total operating costs for the period:
     * shipping (orders.ongkir) + cost of goods sold + operational expenses.
     */
    public static function totalOperasional(int $month, int $year): int
    {
        return static::totalOngkir($month, $year)
            + static::totalHPP($month, $year)
            + static::totalOperationalExpenses($month, $year);
    }

    /**
     * Net profit for the period: totalOmset - totalOperasional.
     */
    public static function netProfit(int $month, int $year): int
    {
        return static::totalOmset($month, $year) - static::totalOperasional($month, $year);
    }

    /**
     * Number of "repeat" customers: customers that have more than one order.
     */
    public static function pelangganAktif(): int
    {
        return Customer::query()
            ->withCount('orders')
            ->get()
            ->where('orders_count', '>', 1)
            ->count();
    }

    /**
     * The marketing budget entered for the given month / year.
     */
    public static function marketingSpend(int $month, int $year): int
    {
        $spend = MarketingSpend::query()
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->first();

        return (int) ($spend?->nominal ?? 0);
    }

    /**
     * Marketing Efficiency Ratio: (marketing spend / total omset) * 100%.
     * Returns 0.0 when there was no omset to avoid a division by zero.
     */
    public static function mer(int $month, int $year): float
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
    public static function roi(int $month, int $year): float
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
    public static function averageOrder(int $month, int $year): float
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
    public static function profitSplit(int $month, int $year): array
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
                'label' => Carbon::createFromDate(2026, $month, 1)->format('M'),
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
            $month = (int) substr((string) $row->tanggal, 6, 2);

            $series[$month]['omset'] += (int) $row->nominal;
            $series[$month]['ongkir'] += (int) $row->ongkir;
            $series[$month]['transaksi']++;
        }

        foreach (DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$start, $end])
            ->get(['orders.tanggal', 'order_items.hpp_satuan', 'order_items.jumlah_pcs']) as $row) {
            $month = (int) substr((string) $row->tanggal, 6, 2);

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

            $data['total_operacional'] = $data['hpp'] + $data['ongkir'] + $data['operacional'];
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
     * Query builder scoped to the orders of the given month / year.
     */
    protected static function ordersOfMonth(int $month, int $year)
    {
        return \App\Models\Order::query()
            ->whereBetween('tanggal', [static::startOfMonth($month, $year), static::endOfMonth($month, $year)]);
    }

    /**
     * First day of the given month as a Y-m-d string.
     */
    protected static function startOfMonth(int $month, int $year): string
    {
        return Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
    }

    /**
     * Last day of the given month as a Y-m-d string.
     */
    protected static function endOfMonth(int $month, int $year): string
    {
        return Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
    }
}