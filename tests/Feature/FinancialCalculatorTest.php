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
    public function test_totals_use_order_items_and_operational_expenses(): void
    {
        $this->prepareMonth();

        // Total Omset = sum(orders.nominal)
        $this->assertSame(40000, FinancialCalculator::totalOmset(9, 2026));
        $this->assertSame(1, FinancialCalculator::totalTransaksi(9, 2026));

        // Total HPP = sum(hpp_satuan * jumlah_pcs) = 10000 * 2
        $this->assertSame(20000, FinancialCalculator::totalHPP(9, 2026));
        $this->assertSame(5000, FinancialCalculator::totalOngkir(9, 2026));
        $this->assertSame(10000, FinancialCalculator::totalOperationalExpenses(9, 2026));

        // Total Operasional = ongkir + HPP + operasional = 5000 + 20000 + 10000
        $this->assertSame(35000, FinancialCalculator::totalOperasional(9, 2026));
        $this->assertSame(5000, FinancialCalculator::netProfit(9, 2026));
    }

    public function test_mer_roi_average_and_profit_split(): void
    {
        $this->prepareMonth();

        $this->assertSame(100000, FinancialCalculator::marketingSpend(9, 2026));
        $this->assertSame(250.0, FinancialCalculator::mer(9, 2026));
        $this->assertSame(5.0, FinancialCalculator::roi(9, 2026));
        $this->assertSame(40000.0, FinancialCalculator::averageOrder(9, 2026));

        $split = FinancialCalculator::profitSplit(9, 2026);

        $this->assertSame(3000, $split['roni']);
        $this->assertSame(2000, $split['rizky']);
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
    }

    public function test_mer_returns_zero_when_omset_is_zero_even_with_spend(): void
    {
        MarketingSpend::create([
            'bulan' => 6,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $this->assertSame(0.0, FinancialCalculator::mer(6, 2026));
        $this->assertSame(0.0, FinancialCalculator::roi(6, 2026));
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