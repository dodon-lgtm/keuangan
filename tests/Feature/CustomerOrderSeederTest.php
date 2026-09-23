<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\User;
use App\Services\FinancialCalculator;
use Database\Seeders\CustomerOrderSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MarketingSpendSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Memastikan seeder demo (pelanggan + order + budget iklan) menghasilkan
 * "hasil penjumlahan" yang benar-benar tampil di Dashboard & Laporan:
 * nominal tiap order = SUM(order_items.subtotal), lalu total omset/HPP/ongkir
 * operasional dan net profit mengikuti angka yang sama.
 */
class CustomerOrderSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_customers_orders_and_items(): void
    {
        $this->seed(CustomerOrderSeeder::class);

        $this->assertGreaterThanOrEqual(5, Customer::query()->count());
        $this->assertGreaterThanOrEqual(10, Order::query()->count());
        $this->assertSame(Order::query()->count(), (int) DB::table('order_items')->count());
    }

    public function test_every_order_nominal_equals_sum_of_its_items(): void
    {
        $this->seed(CustomerOrderSeeder::class);

        foreach (Order::query()->with('orderItems')->get() as $order) {
            $expected = (int) $order->orderItems->sum('subtotal');

            $this->assertGreaterThan(0, $expected, "Order #{$order->id} tidak punya item.");
            $this->assertSame($expected, (int) $order->nominal, "Nominal order #{$order->id} salah.");
        }
    }

    public function test_orders_span_multiple_months_across_a_year_boundary(): void
    {
        $this->seed(CustomerOrderSeeder::class);

        $buckets = Order::query()
            ->pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal->format('Y-m'))
            ->unique();

        // Cukup banyak titik data untuk grafik bulanan / rentang kustom.
        $this->assertGreaterThanOrEqual(10, $buckets->count());

        // Data melewati batas tahun sehingga filter rentang kustom juga ada isinya.
        $years = Order::query()->pluck('tanggal')->map(fn ($tanggal) => (int) $tanggal->year)->unique();
        $this->assertGreaterThanOrEqual(2, $years->count());
    }

    public function test_customer_order_seeder_is_idempotent(): void
    {
        $this->seed(CustomerOrderSeeder::class);

        $customers = Customer::query()->count();
        $orders = Order::query()->count();
        $items = (int) DB::table('order_items')->count();

        $this->seed(CustomerOrderSeeder::class);

        $this->assertSame($customers, Customer::query()->count());
        $this->assertSame($orders, Order::query()->count());
        $this->assertSame($items, (int) DB::table('order_items')->count());
    }

    public function test_financial_calculator_totals_match_the_seeded_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Rentang yang pasti memuat seluruh order demo (10 bulan terakhir).
        $start = now()->startOfMonth()->subMonths(10);
        $end = now()->copy();
        $args = [(int) $start->month, (int) $start->year, (int) $end->month, (int) $end->year];

        [$startDate, $endDate] = FinancialCalculator::periodRangeCustom(...$args);

        // Perbandingan memakai kunci "YYYY-MM" agar tidak bergantung pada jam
        // / mikrodetik (Carbon dari now() bisa berbeda beberapa mikrodetik).
        $monthsInRange = function (int $bulan, int $tahun) use ($start, $end): bool {
            $key = sprintf('%04d-%02d', $tahun, $bulan);

            return $key >= $start->format('Y-m') && $key <= $end->format('Y-m');
        };

        $expectedOmset = (int) Order::query()->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');
        $expectedOngkir = (int) Order::query()->whereBetween('tanggal', [$startDate, $endDate])->sum('ongkir');
        $expectedHpp = (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.tanggal', [$startDate, $endDate])
            ->sum(DB::raw('order_items.hpp_satuan * order_items.jumlah_pcs'));
        $expectedMarketing = (int) MarketingSpend::query()->get()
            ->filter(fn (MarketingSpend $spend) => $monthsInRange((int) $spend->bulan, (int) $spend->tahun))
            ->sum('nominal');
        $expectedOpex = (int) OperationalExpense::query()->get()
            ->filter(fn (OperationalExpense $expense) => $monthsInRange((int) $expense->bulan, (int) $expense->tahun))
            ->sum('nominal');

        // Data demo harus benar-benar berisi, bukan nol.
        $this->assertGreaterThan(0, $expectedOmset);
        $this->assertGreaterThan(0, $expectedHpp);
        $this->assertGreaterThan(0, $expectedMarketing);

        // Hasil penjumlahan = angka yang dipakai Dashboard & Laporan.
        $this->assertSame($expectedOmset, FinancialCalculator::totalOmsetRange(...$args));
        $this->assertSame($expectedOngkir, FinancialCalculator::totalOngkirRange(...$args));
        $this->assertSame($expectedHpp, FinancialCalculator::totalHPPRange(...$args));
        $this->assertSame($expectedMarketing, FinancialCalculator::marketingSpendRange(...$args));
        $this->assertSame($expectedOpex, FinancialCalculator::totalOperationalExpensesRange(...$args));

        $expectedOperasional = $expectedOngkir + $expectedHpp + $expectedOpex + $expectedMarketing;

        $this->assertSame($expectedOperasional, FinancialCalculator::totalOperasionalRange(...$args));
        $this->assertSame($expectedOmset - $expectedOperasional, FinancialCalculator::netProfitRange(...$args));

        // Pelanggan repeat (order lebih dari sekali) ikut terhitung.
        $expectedRepeat = Customer::query()
            ->withCount(['orders' => fn ($query) => $query->whereBetween('orders.tanggal', [$startDate, $endDate])])
            ->get()
            ->where('orders_count', '>', 1)
            ->count();

        $this->assertGreaterThan(0, $expectedRepeat);
        $this->assertSame($expectedRepeat, FinancialCalculator::pelangganAktifRange(...$args));
    }


    public function test_marketing_spend_seeder_does_not_overwrite_existing_budget(): void
    {
        $this->seed(MarketingSpendSeeder::class);

        $budget = MarketingSpend::query()
            ->where('bulan', (int) now()->month)
            ->where('tahun', (int) now()->year)
            ->sole();

        $budget->update(['nominal' => 999999]);

        $this->seed(MarketingSpendSeeder::class);

        $this->assertSame(999999, (int) $budget->fresh()->nominal);
    }

    public function test_dashboard_shows_the_seeded_totals(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::query()->where('email', 'admin@vendorhijabbandung.com')->sole());

        $start = now()->startOfMonth()->subMonths(10);
        $end = now()->copy();
        $args = [(int) $start->month, (int) $start->year, (int) $end->month, (int) $end->year];

        $omset = FinancialCalculator::totalOmsetRange(...$args);

        $this->assertGreaterThan(0, $omset);

        $this->get('/dashboard?filter_mode=custom_range'
            .'&start_month='.$start->month.'&start_year='.$start->year
            .'&end_month='.$end->month.'&end_year='.$end->year)
            ->assertStatus(200)
            ->assertSee('Total Omset')
            ->assertSee('Rp '.number_format($omset, 0, ',', '.'));

        // Biaya operasional & budget iklan bulan ini tetap ikut terhitung.
        $this->assertGreaterThan(0, OperationalExpense::query()->count());
        $this->assertGreaterThan(0, FinancialCalculator::totalOperationalExpenses(null, (int) now()->year));
    }
}

