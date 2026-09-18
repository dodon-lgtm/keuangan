<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;

class ReportControllerTest extends AuthenticatedTestCase
{
    public function test_dashboard_renders_current_period(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Total Omset');
        $response->assertSee('Pelanggan Aktif');
    }

    public function test_dashboard_has_no_segment_cards(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertDontSee('Segment A');
        $response->assertDontSee('Segment B');
        $response->assertDontSee('Segment C');
    }

    public function test_dashboard_respects_month_filter(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Rp 40.000');
    }

    public function test_mer_roi_report_renders_figures_and_profit_split(): void
    {
        $this->prepareMonth();

        $response = $this->get('/reports/mer-roi?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan MER');
        $response->assertSee('Rp 40.000');
        $response->assertSee('Rp 35.000'); // total operasional
        $response->assertSee('Rp 5.000');  // net profit & ongkir & rincian
        $response->assertSee('Rp 100.000'); // marketing spend
        $response->assertSee('A Roni');
        $response->assertSee('Rizky');
        $response->assertSee('Rp 3.000');
        $response->assertSee('Rp 2.000');
    }

    public function test_hpp_profit_report_renders_per_product_breakdown(): void
    {
        $this->prepareMonth();

        $response = $this->get('/reports/hpp-profit?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan HPP');
        $response->assertSee('Voal Test');
        $response->assertSee('Margin Profit');
        $response->assertSee('2'); // qty
        $response->assertSee('Rp 20.000'); // total HPP
        $response->assertSee('Rp 20.000'); // margin profit
    }

    public function test_reports_handle_empty_periods_without_errors(): void
    {
        $this->get('/dashboard?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/mer-roi?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/hpp-profit?month=1&year=2020')->assertStatus(200);
    }

    /**
     * Seed one order of 2 pcs (price 20000, hpp 10000, ongkir 5000),
     * an operational expense of 10000 and a marketing spend of 100000
     * for September 2026.
     */
    private function prepareMonth(): void
    {
        $customer = Customer::create([
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
        ]);

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

    private function createOrder(Customer $customer, Product $product, string $tanggal): void
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
    }
}