<?php

namespace App\Services;

use App\Models\MarketingSpend;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Central place for all monthly financial calculations.
 *
 * Only paid orders (status = Lunas) count as revenue, so cancelled and
 * pending orders never influence the reported figures.
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
     * Total paid (Lunas) revenue for the given month / year.
     */
    public static function totalOmset(int $month, int $year): int
    {
        return (int) (static::paidOrders($month, $year)->sum('nominal') ?? 0);
    }

    /**
     * Number of paid (Lunas) transactions for the given month / year.
     */
    public static function totalTransaksi(int $month, int $year): int
    {
        return static::paidOrders($month, $year)->count();
    }

    /**
     * Total operating costs for the period:
     * shipping fees (orders.ongkir) + cost of goods sold (product.hpp * qty).
     */
    public static function totalOperasional(int $month, int $year): int
    {
        $row = DB::table('orders')
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->where('orders.status', Order::STATUS_LUNAS)
            ->whereBetween('orders.tanggal', [static::startOfMonth($month, $year), static::endOfMonth($month, $year)])
            ->selectRaw('COALESCE(SUM(orders.ongkir), 0) as total_ongkir')
            ->selectRaw('COALESCE(SUM(products.hpp * orders.jumlah_pcs), 0) as total_hpp')
            ->first();

        return (int) ($row->total_ongkir ?? 0) + (int) ($row->total_hpp ?? 0);
    }

    /**
     * Net profit for the period: totalOmset - totalOperasional.
     */
    public static function netProfit(int $month, int $year): int
    {
        return static::totalOmset($month, $year) - static::totalOperasional($month, $year);
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
     * Query builder scoped to the paid orders of the given month / year.
     */
    protected static function paidOrders(int $month, int $year)
    {
        return Order::query()
            ->where('status', Order::STATUS_LUNAS)
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