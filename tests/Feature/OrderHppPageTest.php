<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;

/**
 * Regresi halaman tunggal "Log Order" + "Laporan HPP & Profit"
 * (resources/views/orders/index.blade.php yang di-render oleh
 * OrderController@index).
 */
class OrderHppPageTest extends AuthenticatedTestCase
{
    public function test_orders_page_renders_log_order_and_hpp_sections(): void
    {
        $this->prepareMonth();

        $response = $this->get('/orders?month=9&year=2026');

        $response->assertStatus(200);

        // Section 1: Log Order (KPI, tabel, grafik).
        $response->assertSee('Log Order');
        $response->assertSee('Total Keseluruhan Omset');
        $response->assertSee('Total Keseluruhan Pcs Terjual');
        $response->assertSee('Analisis Grafik Order');
        $response->assertSee('Fatimah Zahra');
        $response->assertSee('(x2)');
        $response->assertSee('Full Payment');
        $response->assertSee('Ready Stock');
        $response->assertSee('Rp 40.000'); // omset log order & rincian HPP

        // Section 2: Laporan HPP & Profit (KPI financial, tabel, grafik).
        $response->assertSee('Laporan HPP');
        $response->assertSee('Margin Profit');
        $response->assertSee('Voal Test');
        $response->assertSee('Total Operasional');
        $response->assertSee('Net Profit');
        $response->assertSee('50.00%'); // margin % per produk
        $response->assertSee('Analisis Grafik HPP');
        $response->assertSee('September 2026'); // label periode laporan
    }

    public function test_log_order_filter_and_period_filter_live_on_one_page(): void
    {
        $this->prepareMonth();

        // Filter log order (tipe bayar DP) menyaring tabel & KPI omset,
        // sementara section HPP tetap mengikuti periode September 2026.
        $response = $this->get('/orders?tipe_bayar=DP&month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Filter aktif');
        $response->assertSee('value="DP" selected', false);
        $response->assertSee('Rp 0');     // omset log order setelah filter
        $response->assertSee('Voal Test'); // rincian HPP periode tetap tampil
        $response->assertDontSee('Fatimah Zahra'); // order tidak cocok filter
    }

    public function test_empty_period_renders_both_empty_states_without_errors(): void
    {
        // Tanpa data sama sekali: guard null-coalescing pada variabel array
        // grafik menjaga halaman tetap 200 (bukan TypeError/ErrorException).
        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Belum ada transaksi untuk periode ini.');
        $response->assertSee('Belum ada data untuk periode ini. Ubah filter atau tambahkan data.');
    }

    public function test_legacy_hpp_profit_url_renders_the_merged_page(): void
    {
        $this->prepareMonth();

        $this->get('/reports/hpp-profit?month=9&year=2026')
            ->assertStatus(200)
            ->assertSee('Log Order')
            ->assertSee('Laporan HPP')
            ->assertSee('Voal Test');
    }

    /**
     * Seed one order of 2 pcs (price 20000, hpp 10000) in September 2026.
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

        $order = Order::create([
            'customer_id' => $customer->id,
            'tanggal' => '2026-09-05',
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'ongkir' => 0,
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
