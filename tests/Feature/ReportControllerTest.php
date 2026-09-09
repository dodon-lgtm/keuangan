<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_current_period(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Total Omset');
        $response->assertSee('Pelanggan Aktif');
    }

    public function test_dashboard_respects_month_filter(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Rp 40000');
    }

    public function test_mer_roi_report_renders_figures_and_profit_split(): void
    {
        $this->prepareMonth();

        $response = $this->get('/reports/mer-roi?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan MER');
        $response->assertSee('Rp 40000');
        $response->assertSee('Rp 25000');
        $response->assertSee('Rp 15000');
        $response->assertSee('Rp 100000');
        $response->assertSee('A Roni');
        $response->assertSee('Rizky');
        $response->assertSee('Rp 9000');
        $response->assertSee('Rp 6000');
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
        $response->assertSee('Rp 20000'); // total HPP
        $response->assertSee('Rp 15000'); // margin profit
    }

    public function test_reports_handle_empty_periods_without_errors(): void
    {
        $this->get('/dashboard?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/mer-roi?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/hpp-profit?month=1&year=2020')->assertStatus(200);
    }

    /**
     * Seed one paid order of 2 pcs (price 20000, hpp 10000) plus a cancelled
     * order and a marketing spend for September 2026.
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

        $this->createOrder($customer, $product, Order::STATUS_LUNAS, '2026-09-05');
        $this->createOrder($customer, $product, Order::STATUS_DIBATALKAN, '2026-09-10');

        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);
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