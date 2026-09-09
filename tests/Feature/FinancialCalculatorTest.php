<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\Order;
use App\Models\Product;
use App\Services\FinancialCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_ignore_cancelled_orders(): void
    {
        $this->prepareMonth();

        $this->assertSame(40000, FinancialCalculator::totalOmset(9, 2026));
        $this->assertSame(1, FinancialCalculator::totalTransaksi(9, 2026));
        $this->assertSame(25000, FinancialCalculator::totalOperasional(9, 2026));
        $this->assertSame(15000, FinancialCalculator::netProfit(9, 2026));
    }

    public function test_mer_roi_average_and_profit_split(): void
    {
        $this->prepareMonth();

        $this->assertSame(100000, FinancialCalculator::marketingSpend(9, 2026));
        $this->assertSame(250.0, FinancialCalculator::mer(9, 2026));
        $this->assertSame(15.0, FinancialCalculator::roi(9, 2026));
        $this->assertSame(40000.0, FinancialCalculator::averageOrder(9, 2026));

        $split = FinancialCalculator::profitSplit(9, 2026);

        $this->assertSame(9000, $split['roni']);
        $this->assertSame(6000, $split['rizky']);
    }

    public function test_guards_avoid_division_by_zero_when_period_is_empty(): void
    {
        $this->assertSame(0, FinancialCalculator::totalOmset(1, 2025));
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

        // There is net profit, but without a marketing spend the ROI must not divide by zero.
        $this->assertSame(15000, FinancialCalculator::netProfit(9, 2026));
        $this->assertSame(0.0, FinancialCalculator::roi(9, 2026));
    }

    /**
     * Seed one paid order and one cancelled order for September 2026,
     * plus a marketing spend for the same month.
     */
    private function prepareMonth(): void
    {
        $customer = Customer::create([
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'domisili' => 'Amsterdam',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_A,
        ]);

        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        // Paid order: omset 40000, shipping 5000, HPP 2 * 10000 = 20000.
        $this->createOrder($customer, $product, Order::STATUS_LUNAS, '2026-09-05');

        // Cancelled order: must not be counted anywhere.
        $this->createOrder($customer, $product, Order::STATUS_DIBATALKAN, '2026-09-10');

        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);
    }

    /**
     * Seed one paid order and one cancelled order for September 2026,
     * but no marketing spend entry.
     */
    private function prepareMonthWithoutSpend(): void
    {
        $customer = Customer::create([
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'domisili' => 'Amsterdam',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_A,
        ]);

        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, Order::STATUS_LUNAS, '2026-09-05');
        $this->createOrder($customer, $product, Order::STATUS_DIBATALKAN, '2026-09-10');
    }

    private function createOrder(Customer $customer, Product $product, string $status, string $tanggal): void
    {
        Order::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'tanggal' => $tanggal,
            'nominal' => $product->harga_jual * 2,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'jumlah_pcs' => 2,
            'status' => $status,
            'link_desain' => '',
            'ongkir' => 5000,
            'alamat_kirim' => '',
            'ukuran_hijab' => '110x110',
        ]);
    }
}