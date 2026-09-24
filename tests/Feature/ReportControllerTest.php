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

    public function test_dashboard_shows_operasional_breakdown_including_marketing(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Rincian Total Operasional');
        $response->assertSee('Total HPP (order_items)');
        $response->assertSee('Total Ongkir');
        $response->assertSee('Biaya Operasional (Fix/Variable Cost)');
        $response->assertSee('Biaya Marketing (Marketing Spend)');
        $response->assertSee('Rp 20.000');  // HPP
        $response->assertSee('Rp 5.000');   // ongkir
        $response->assertSee('Rp 10.000');  // Fix/Variable Cost
        $response->assertSee('Rp 100.000'); // marketing spend
        $response->assertSee('Rp 135.000'); // total operasional (termasuk marketing)
        $response->assertSee('Rp -95.000'); // net profit
    }

    public function test_dashboard_chart_supports_daily_monthly_and_yearly_granularity(): void
    {
        $this->prepareMonth();

        // Harian: bulan tertentu -> label tanggal & periode lengkap di JSON grafik.
        $daily = $this->get('/dashboard?month=9&year=2026');

        $daily->assertStatus(200);
        $daily->assertSee('Tren Keuangan Harian');
        $daily->assertSee('01 Sep', false);
        $daily->assertSee('05 September 2026', false);

        // Bulanan: Semua Bulan / Full Year -> label bulan Jan-Des.
        $monthly = $this->get('/dashboard?month=all&year=2026');

        $monthly->assertStatus(200);
        $monthly->assertSee('Tren Keuangan Bulanan');

        // Tahunan: Semua Tahun / All Time -> label tahun & opsi filter terpilih.
        $yearly = $this->get('/dashboard?month=alltime&year=2026');

        $yearly->assertStatus(200);
        $yearly->assertSee('Tren Keuangan Tahunan');
        $yearly->assertSee('Semua Tahun (All Time)');
        $yearly->assertSee('value="alltime" selected', false);
        $yearly->assertSee('Tahun 2026', false);
    }

    public function test_dashboard_all_time_without_data_renders_empty_chart(): void
    {
        $this->get('/dashboard?month=alltime')->assertStatus(200);
    }

    public function test_mer_roi_report_renders_figures_and_cost_breakdown(): void
    {
        $this->prepareMonth();

        $response = $this->get('/reports/mer-roi?month=9&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan MER');
        $response->assertSee('Rp 40.000');  // total omset
        $response->assertSee('Rp 5.000');   // total ongkir
        $response->assertSee('Rp 20.000');  // total HPP
        $response->assertSee('Rp 10.000');  // Fix/Variable Cost
        $response->assertSee('Rp 100.000'); // marketing spend
        $response->assertSee('Biaya Marketing (Marketing Spend)');

        // Total Operasional = HPP + ongkir + Fix/Variable Cost + marketing.
        $response->assertSee('Rp 135.000');

        // Net Profit = omset - total operasional (marketing sudah termasuk).
        $response->assertSee('Rp -95.000');

        // MER & ROI tampil dengan 2 angka di belakang koma.
        $response->assertSee('250.00%');
        $response->assertSee('-95.00%');

        // Pembagian profit sengaja dihapus dari halaman laporan (saat menyesuaikan tampilan).
        $response->assertDontSee('Pembagian Profit');
        $response->assertDontSee('A Roni');
        $response->assertDontSee('Rizky');
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
        $response->assertSee('50.00%');    // margin pct dengan 2 angka di belakang koma
    }

    public function test_dashboard_merges_mer_roi_and_hpp_profit_reports(): void
    {
        $this->prepareMonth();

        $response = $this->get('/dashboard?month=9&year=2026');

        $response->assertStatus(200);

        // Ringkasan efisiensi marketing yang semula hanya ada di Laporan MER & ROI.
        $response->assertSee('MER &amp; ROI', false);
        $response->assertSee('MER (Spend / Omset)');
        $response->assertSee('250.00%');
        $response->assertSee('ROI (Net Profit / Spend)');
        $response->assertSee('-95.00%');
        $response->assertSee('Rp 100.000'); // marketing spend

        // Rincian per produk yang semula hanya ada di Laporan HPP & Profit.
        $response->assertSee('HPP &amp; Profit per Produk', false);
        $response->assertSee('Voal Test');
        $response->assertSee('Margin Profit');
        $response->assertSee('50.00%'); // margin pct

        // Grafik lama tetap ada dan grafik gabungan ikut dirender.
        foreach ([
            'chart-trend',
            'chart-mer',
            'chart-mer-spend',
            'chart-mer-roi',
            'chart-hpp-bar',
            'chart-hpp-share',
        ] as $chartId) {
            $response->assertSee('id="'.$chartId.'"', false);
        }
    }

    public function test_reports_handle_empty_periods_without_errors(): void
    {
        $this->get('/dashboard?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/mer-roi?month=1&year=2020')->assertStatus(200);
        $this->get('/reports/hpp-profit?month=1&year=2020')->assertStatus(200);
    }

    public function test_dashboard_full_year_filter_accumulates_every_month(): void
    {
        $this->prepareTwoMonths();

        // Bulan September 2026 saja.
        $this->get('/dashboard?month=9&year=2026')
            ->assertStatus(200)
            ->assertSee('Rp 40.000')
            ->assertDontSee('Rp 80.000');

        // "Semua Bulan": akumulasi Januari - Desember 2026.
        $this->get('/dashboard?month=all&year=2026')
            ->assertStatus(200)
            ->assertSee('Rp 80.000')
            ->assertSee('Semua Bulan (Full Year)')
            ->assertSee('value="all" selected', false)
            ->assertSee('tahun 2026 (Jan-Des)');
    }

    public function test_reports_follow_the_full_year_filter(): void
    {
        $this->prepareTwoMonths();

        $this->get('/reports/mer-roi?month=all&year=2026')
            ->assertStatus(200)
            ->assertSee('Rp 80.000')
            ->assertSee('Rp 100.000')
            ->assertSee('Biaya Marketing (Marketing Spend)')
            ->assertSee('Rp 160.000')  // total operasional full year (termasuk marketing)
            ->assertSee('Rp -80.000'); // net profit full year

        $this->get('/reports/hpp-profit?month=all&year=2026')
            ->assertStatus(200)
            ->assertSee('Voal Test')
            ->assertSee('Rp 80.000');
    }

    public function test_period_filter_forms_expose_full_year_and_free_year_input(): void
    {
        foreach (['/dashboard', '/reports/mer-roi', '/reports/hpp-profit'] as $url) {
            $response = $this->get($url.'?month=all&year=2026');

            $response->assertStatus(200);
            $response->assertSee('Semua Bulan (Full Year)');
            $response->assertSee('name="year"', false);
            $response->assertSee('min="1900"', false);
            $response->assertSee('max="9999"', false);
            $response->assertSee('value="all" selected', false);
        }
    }

    public function test_period_filter_accepts_free_custom_years(): void
    {
        $this->prepareMonth();

        // Tahun lampau dan tahun jauh ke depan: tetap 200 dengan angka 0.
        foreach ([1999, 3000] as $year) {
            $this->get('/dashboard?month=all&year='.$year)
                ->assertStatus(200)
                ->assertSee('Rp 0')
                ->assertSee('value="'.$year.'"', false);

            $this->get('/reports/mer-roi?month=all&year='.$year)
                ->assertStatus(200)
                ->assertSee('Rp 0');

            $this->get('/reports/hpp-profit?month=all&year='.$year)
                ->assertStatus(200);

            $this->get('/expenses?tahun='.$year)
                ->assertStatus(200)
                ->assertSee('value="'.$year.'"', false);
        }
    }

    public function test_dashboard_custom_month_range_accumulates_across_years(): void
    {
        $this->prepareCrossYearRange();

        $response = $this->get('/dashboard?filter_mode=custom_range&start_month=11&start_year=2025&end_month=1&end_year=2026');

        $response->assertStatus(200);
        // Akumulasi dua order (Nov 2025 + Jan 2026) di luar rentang tidak ikut.
        $response->assertSee('Rp 80.000');
        // Label periode bahasa Indonesia dan judul grafik rentang kustom.
        $response->assertSee('Periode November 2025 – Januari 2026');
        $response->assertSee('Tren Keuangan Rentang Kustom');
        $response->assertSee('Nov 2025', false);
        $response->assertSee('Des 2025', false);
        $response->assertSee('Jan 2026', false);
    }

    public function test_period_filter_forms_expose_custom_range_fields(): void
    {
        foreach (['/dashboard', '/reports/mer-roi', '/reports/hpp-profit'] as $url) {
            $response = $this->get($url.'?filter_mode=custom_range&start_month=5&start_year=2026&end_month=7&end_year=2026');

            $response->assertStatus(200);
            $response->assertSee('Bulanan Spesifik');
            $response->assertSee('Full Year');
            $response->assertSee('Rentang Kustom');
            $response->assertSee('value="custom_range" selected', false);
            $response->assertSee('Bulan Mulai');
            $response->assertSee('Tahun Mulai');
            $response->assertSee('Bulan Selesai');
            $response->assertSee('Tahun Selesai');
            $response->assertSee('name="start_month"', false);
            $response->assertSee('name="start_year"', false);
            $response->assertSee('name="end_month"', false);
            $response->assertSee('name="end_year"', false);
            $response->assertSee('Mei 2026 – Juli 2026', false);
        }
    }

    public function test_reports_follow_the_custom_month_range(): void
    {
        $this->prepareCrossYearRange();

        $this->get('/reports/mer-roi?filter_mode=custom_range&start_month=11&start_year=2025&end_month=1&end_year=2026')
            ->assertStatus(200)
            ->assertSee('Rp 80.000')
            ->assertSee('Periode November 2025 – Januari 2026');

        $this->get('/reports/hpp-profit?filter_mode=custom_range&start_month=11&start_year=2025&end_month=1&end_year=2026')
            ->assertStatus(200)
            ->assertSee('Voal Test')
            ->assertSee('Rp 80.000')
            ->assertSee('Periode November 2025 – Januari 2026');
    }

    public function test_custom_range_mode_falls_back_gracefully_without_valid_range(): void
    {
        $this->prepareMonth();

        // filter_mode custom_range tanpa parameter rentang yang valid:
        // kembali ke perilaku bulan/tahun (September 2026) tanpa error.
        $this->get('/dashboard?filter_mode=custom_range&month=9&year=2026')
            ->assertStatus(200)
            ->assertSee('Rp 40.000');
    }

    /**
     * Seed two orders inside a cross-year custom range (Nov 2025 and
     * Jan 2026) plus one order outside of it (Mar 2026), each 2 pcs
     * x 20000 with ongkir 5000 and HPP 10000 per pcs.
     */
    private function prepareCrossYearRange(): void
    {
        $customer = Customer::create([
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2025-11-01',
        ]);

        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->createOrder($customer, $product, '2025-11-20');
        $this->createOrder($customer, $product, '2026-01-10');
        $this->createOrder($customer, $product, '2026-03-08');
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

    /**
     * Seed an order in September and October 2026 (2 pcs x 20000 each),
     * plus one operational expense and one marketing spend for September.
     */
    private function prepareTwoMonths(): void
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
        $this->createOrder($customer, $product, '2026-10-11');

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