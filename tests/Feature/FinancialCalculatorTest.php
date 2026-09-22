<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use App\Services\FinancialCalculator;

class FinancialCalculatorTest extends AuthenticatedTestCase
{
    public function test_totals_use_order_items_operational_and_marketing_expenses(): void
    {
        $this->prepareMonth();

        // Total Omset = sum(orders.nominal)
        $this->assertSame(40000, FinancialCalculator::totalOmset(9, 2026));
        $this->assertSame(1, FinancialCalculator::totalTransaksi(9, 2026));

        // Komponen biaya satu per satu.
        // Total HPP = sum(hpp_satuan * jumlah_pcs) = 10000 * 2
        $this->assertSame(20000, FinancialCalculator::totalHPP(9, 2026));
        $this->assertSame(5000, FinancialCalculator::totalOngkir(9, 2026));
        $this->assertSame(10000, FinancialCalculator::totalOperationalExpenses(9, 2026));
        $this->assertSame(100000, FinancialCalculator::marketingSpend(9, 2026));

        // Total Operasional = ongkir + HPP + Fix/Variable Cost + marketing
        //                  = 5000 + 20000 + 10000 + 100000
        $this->assertSame(135000, FinancialCalculator::totalOperasional(9, 2026));

        // Net Profit = total omset - total operasional (marketing ikut dihitung)
        $this->assertSame(-95000, FinancialCalculator::netProfit(9, 2026));
    }

    public function test_total_operasional_includes_marketing_spend(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        // Order 2 pcs x 20.000 dengan ongkir 5.000 dan HPP 10.000 per pcs.
        $this->createOrder($customer, $product, '2026-09-05');

        // Tanpa pengeluaran lain: operasional = ongkir + HPP = 5000 + 20000.
        $this->assertSame(25000, FinancialCalculator::totalOperasional(9, 2026));

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 10000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        // Bertambah Fix/Variable Cost: 25000 + 10000.
        $this->assertSame(35000, FinancialCalculator::totalOperasional(9, 2026));

        MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 100000]);

        // Bertambah biaya marketing: 35000 + 100000.
        $this->assertSame(135000, FinancialCalculator::totalOperasional(9, 2026));
        $this->assertSame(40000 - 135000, FinancialCalculator::netProfit(9, 2026));
    }

    public function test_mer_roi_average_and_profit_split(): void
    {
        $this->prepareMonth();

        $this->assertSame(100000, FinancialCalculator::marketingSpend(9, 2026));
        $this->assertSame(250.0, FinancialCalculator::mer(9, 2026));

        // ROI = net profit / marketing spend, dan net profit sudah dikurangi
        // biaya marketing: -95000 / 100000 * 100.
        $this->assertSame(-95.0, FinancialCalculator::roi(9, 2026));
        $this->assertSame(40000.0, FinancialCalculator::averageOrder(9, 2026));

        $split = FinancialCalculator::profitSplit(9, 2026);

        // Pembagian profit mengikuti net profit baru: -95000 * 60% / 40%.
        $this->assertSame(-57000, $split['roni']);
        $this->assertSame(-38000, $split['rizky']);
    }

    public function test_guards_avoid_division_by_zero_when_period_is_empty(): void
    {
        $this->assertSame(0, FinancialCalculator::totalOmset(1, 2025));
        $this->assertSame(0, FinancialCalculator::totalHPP(1, 2025));
        $this->assertSame(0, FinancialCalculator::totalOperasional(1, 2025));
        $this->assertSame(0, FinancialCalculator::netProfit(1, 2025));
        $this->assertSame(0.0, FinancialCalculator::mer(1, 2025));
        $this->assertSame(0.0, FinancialCalculator::roi(1, 2025));
        $this->assertSame(0.0, FinancialCalculator::averageOrder(1, 2025));

        $split = FinancialCalculator::profitSplit(1, 2025);

        $this->assertSame(0, $split['roni']);
        $this->assertSame(0, $split['rizky']);

        // All time tanpa data sama sekali: deret kosong, bukan error.
        $this->assertSame([], FinancialCalculator::allTimeSeries());
    }

    public function test_mer_returns_zero_when_omset_is_zero_even_with_spend(): void
    {
        MarketingSpend::create([
            'bulan' => 6,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        // MER tidak bisa dihitung tanpa omset.
        $this->assertSame(0.0, FinancialCalculator::mer(6, 2026));

        // Marketing kini menjadi komponen biaya operasional, sehingga belanja
        // iklan tanpa omset berarti seluruh modal iklan menjadi kerugian:
        // -100000 / 100000 * 100.
        $this->assertSame(-100.0, FinancialCalculator::roi(6, 2026));
    }

    public function test_roi_returns_zero_when_there_is_no_marketing_spend(): void
    {
        $this->prepareMonthWithoutSpend();

        // netProfit = 40000 omset - 25000 operasional (ongkir 5000 + HPP 20000).
        $this->assertSame(15000, FinancialCalculator::netProfit(9, 2026));
        $this->assertSame(0.0, FinancialCalculator::roi(9, 2026));
    }

    public function test_pelanggan_aktif_counts_customers_with_more_than_one_order(): void
    {
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $repeatCustomer = $this->customer('Repeat Buyer');
        $this->createOrder($repeatCustomer, $product, '2026-09-01');
        $this->createOrder($repeatCustomer, $product, '2026-09-05');

        $singleCustomer = $this->customer('One Time');
        $this->createOrder($singleCustomer, $product, '2026-09-03');

        $this->assertSame(1, FinancialCalculator::pelangganAktif());
    }

    public function test_period_range_covers_a_single_month_and_the_full_year(): void
    {
        $this->assertSame(['2026-09-01', '2026-09-30'], FinancialCalculator::periodRange(9, 2026));
        $this->assertSame(['2026-01-01', '2026-12-31'], FinancialCalculator::periodRange(null, 2026));
        $this->assertSame(['2024-02-01', '2024-02-29'], FinancialCalculator::periodRange(2, 2024));
    }

    public function test_null_month_accumulates_every_month_of_the_year(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        // Dua order senilai 40.000 (2 pcs x 20.000) di bulan yang berbeda.
        $this->createOrder($customer, $product, '2026-09-05');
        $this->createOrder($customer, $product, '2026-10-11');

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 10000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        OperationalExpense::create([
            'nama_pengeluaran' => 'Internet',
            'kategori' => OperationalExpense::KATEGORI_VARIABLE_COST,
            'nominal' => 5000,
            'bulan' => 10,
            'tahun' => 2026,
        ]);

        MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 100000]);
        MarketingSpend::create(['bulan' => 10, 'tahun' => 2026, 'nominal' => 60000]);

        // Bulan spesifik: hanya data bulan tersebut.
        $this->assertSame(40000, FinancialCalculator::totalOmset(9, 2026));
        $this->assertSame(1, FinancialCalculator::totalTransaksi(9, 2026));

        // null = "Semua Bulan": akumulasi Januari - Desember 2026.
        $this->assertSame(80000, FinancialCalculator::totalOmset(null, 2026));
        $this->assertSame(2, FinancialCalculator::totalTransaksi(null, 2026));
        $this->assertSame(40000, FinancialCalculator::totalHPP(null, 2026));
        $this->assertSame(10000, FinancialCalculator::totalOngkir(null, 2026));
        $this->assertSame(15000, FinancialCalculator::totalOperationalExpenses(null, 2026));
        $this->assertSame(10000 + 40000 + 15000 + 160000, FinancialCalculator::totalOperasional(null, 2026));
        $this->assertSame(80000 - 225000, FinancialCalculator::netProfit(null, 2026));
        $this->assertSame(40000.0, FinancialCalculator::averageOrder(null, 2026));

        // Marketing, MER, ROI dan pembagian profit mengikuti periode yang sama.
        $this->assertSame(160000, FinancialCalculator::marketingSpend(null, 2026));
        $this->assertSame(200.0, FinancialCalculator::mer(null, 2026));
        $this->assertEqualsWithDelta(-90.625, FinancialCalculator::roi(null, 2026), 0.0001);

        $split = FinancialCalculator::profitSplit(null, 2026);

        $this->assertSame(-87000, $split['roni']);
        $this->assertSame(-58000, $split['rizky']);

        // Pelanggan aktif ikut periode: 2 order di 2026, 1 order di September.
        $this->assertSame(1, FinancialCalculator::pelangganAktif(null, 2026));
        $this->assertSame(0, FinancialCalculator::pelangganAktif(9, 2026));
        $this->assertSame(1, FinancialCalculator::pelangganAktif());
    }

    public function test_daily_series_zero_fills_and_reconciles_with_monthly_totals(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, '2026-09-05');
        $this->createOrder($customer, $product, '2026-09-27');

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 10000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 100000]);

        $series = FinancialCalculator::dailySeries(2026, 9);

        // September 2026 punya 30 hari: zero-fill dari tanggal 1 s/d akhir bulan.
        $this->assertCount(30, $series);
        $this->assertSame('01 Sep', $series[1]['label']);
        $this->assertSame('30 Sep', $series[30]['label']);
        $this->assertSame('05 September 2026', $series[5]['full']);
        $this->assertSame(0, $series[1]['omset']);

        // Transaksi jatuh pada tanggal yang tepat.
        $this->assertSame(40000, $series[5]['omset']);
        $this->assertSame(1, $series[5]['transaksi']);
        $this->assertSame(40000, $series[27]['omset']);

        // Rekonsiliasi: penjumlahan per hari == total bulanan.
        $omset = 0;
        $operasional = 0;
        $profit = 0;

        foreach ($series as $day) {
            $omset += $day['omset'];
            $operasional += $day['total_operacional'];
            $profit += $day['net_profit'];
        }

        $this->assertSame(FinancialCalculator::totalOmset(9, 2026), $omset);
        $this->assertSame(FinancialCalculator::totalOperasional(9, 2026), $operasional);
        $this->assertSame(FinancialCalculator::netProfit(9, 2026), $profit);
    }

    public function test_all_time_series_aggregates_per_year_with_zero_filled_gaps(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, '2024-03-10');
        $this->createOrder($customer, $product, '2026-09-05');

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 10000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 100000]);

        $series = FinancialCalculator::allTimeSeries();

        // 2024..2026: tahun 2025 tanpa transaksi tetap muncul dengan angka 0.
        $this->assertSame(['2024', '2025', '2026'], array_column($series, 'label'));
        $this->assertSame('Tahun 2025', $series[2025]['full']);
        $this->assertSame(0, $series[2025]['omset']);
        $this->assertSame(0, $series[2025]['total_operacional']);

        // 2024: hanya order (ongkir 5.000 + HPP 20.000).
        $this->assertSame(40000, $series[2024]['omset']);
        $this->assertSame(25000, $series[2024]['total_operacional']);
        $this->assertSame(15000, $series[2024]['net_profit']);

        // 2026: order + Fix Cost 10.000 + marketing 100.000.
        $this->assertSame(40000, $series[2026]['omset']);
        $this->assertSame(135000, $series[2026]['total_operacional']);
        $this->assertSame(-95000, $series[2026]['net_profit']);
    }

    public function test_arbitrary_years_without_data_return_zero(): void
    {
        // Tahun bebas (lampau maupun jauh ke depan) tidak boleh membuat error.
        $this->assertSame(0, FinancialCalculator::totalOmset(null, 1999));
        $this->assertSame(0, FinancialCalculator::totalOmset(null, 3000));
        $this->assertSame(0, FinancialCalculator::totalTransaksi(null, 3000));
        $this->assertSame(0, FinancialCalculator::totalOperasional(null, 3000));
        $this->assertSame(0, FinancialCalculator::totalOperationalExpenses(null, 1999));
        $this->assertSame(0, FinancialCalculator::marketingSpend(null, 1999));
        $this->assertSame(0.0, FinancialCalculator::mer(null, 1999));
        $this->assertSame(0.0, FinancialCalculator::roi(null, 3000));
        $this->assertSame(0.0, FinancialCalculator::averageOrder(null, 3000));
        $this->assertSame(0, FinancialCalculator::pelangganAktif(null, 3000));

        $split = FinancialCalculator::profitSplit(null, 1999);

        $this->assertSame(0, $split['roni']);
        $this->assertSame(0, $split['rizky']);
    }

    /**
     * Seed one order (2 pcs, ongkir 5000, HPP snapshot 10000) for September 2026,
     * an operational expense of 10000 and a marketing spend of 100000.
     */
    private function prepareMonth(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, '2026-09-05');

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 10000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);
    }

    /**
     * Same order but without a marketing spend entry.
     */
    private function prepareMonthWithoutSpend(): void
    {
        $customer = $this->customer('Fatimah Zahra');
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, '2026-09-05');
    }

    private function customer(string $name): Customer
    {
        return Customer::create([
            'nama_lengkap' => $name,
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
        ]);
    }

    private function createOrder(Customer $customer, Product $product, string $tanggal): Order
    {
        $order = Order::create([
            'customer_id' => $customer->id,
            'tanggal' => $tanggal,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'ongkir' => 5000,
        ]);

        $order->orderItems()->create([
            'product_id' => $product->id,
            'jumlah_pcs' => 2,
            'harga_satuan' => $product->harga_jual,
            'hpp_satuan' => $product->hpp,
            'subtotal' => $product->harga_jual * 2,
        ]);

        OrderObserver::recalcNominal($order);

        return $order;
    }
}