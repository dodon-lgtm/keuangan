<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\Product;

/**
 * Notifikasi aplikasi: komponen flash (partials/flash.blade.php) dengan pesan
 * yang menyebut data terkait, serta modal konfirmasi bertema yang menggantikan
 * window.confirm bawaan browser pada tombol hapus.
 */
class NotificationFlashTest extends AuthenticatedTestCase
{
    public function test_success_flash_renders_component_with_entity_name(): void
    {
        $response = $this->post(route('customers.store'), [
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
        ]);

        $response->assertSessionHas('success', 'Pelanggan Fatimah Zahra berhasil ditambahkan.');

        $this->followRedirects($response)
            ->assertStatus(200)
            ->assertSee('data-flash', false)
            ->assertSee('flash-success', false)
            ->assertSee('data-flash-close', false)
            ->assertSee('flash-progress', false)
            ->assertSee('data-auto-dismiss="4500"', false)
            ->assertSee('role="status"', false)
            ->assertSee('Berhasil')
            ->assertSee('Pelanggan Fatimah Zahra berhasil ditambahkan.')
            ->assertSee('Tutup notifikasi');
    }

    public function test_flash_renders_error_channel(): void
    {
        $this->withSession(['error' => 'Data gagal disimpan.'])
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('flash-error', false)
            ->assertSee('Gagal')
            ->assertSee('Data gagal disimpan.');
    }

    public function test_flash_renders_warning_channel(): void
    {
        $this->withSession(['warning' => 'Data belum lengkap.'])
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('flash-warning', false)
            ->assertSee('Perhatian')
            ->assertSee('Data belum lengkap.');
    }

    public function test_flash_renders_info_channel(): void
    {
        $this->withSession(['info' => 'Laporan sudah diperbarui.'])
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('flash-info', false)
            ->assertSee('Informasi')
            ->assertSee('Laporan sudah diperbarui.');
    }

    public function test_update_and_delete_messages_include_entity_names(): void
    {
        $customer = $this->customer();

        $this->from('/customers')->put(route('customers.update', $customer), [
            'nama_lengkap' => 'Fatimah Az-Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
        ])->assertSessionHas('success', 'Pelanggan Fatimah Az-Zahra berhasil diperbarui.');

        $this->from('/customers')->delete(route('customers.destroy', $customer))
            ->assertSessionHas('success', 'Pelanggan Fatimah Az-Zahra berhasil dihapus.');

        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->from('/products')->delete(route('products.destroy', $product))
            ->assertSessionHas('success', 'Produk Voal Test berhasil dihapus.');
    }

    public function test_order_and_expense_messages_include_details(): void
    {
        $customer = $this->customer();

        $order = Order::create([
            'customer_id' => $customer->id,
            'tanggal' => '2026-09-05',
            'nominal' => 40000,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
        ]);

        $this->from('/orders')->delete(route('orders.destroy', $order))
            ->assertSessionHas('success', "Order #{$order->id} (Fatimah Zahra) berhasil dihapus.");

        $spend = MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 250000]);

        $this->from('/expenses')->delete(route('expenses.marketing.destroy', $spend))
            ->assertSessionHas('success', 'Budget iklan September 2026 berhasil dihapus.');

        $expense = OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $this->from('/expenses')->delete(route('expenses.operational.destroy', $expense))
            ->assertSessionHas('success', 'Pengeluaran Listrik berhasil dihapus.');
    }

    public function test_delete_forms_use_themed_confirm_modal(): void
    {
        $customer = $this->customer();

        Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        Order::create([
            'customer_id' => $customer->id,
            'tanggal' => '2026-09-05',
            'nominal' => 40000,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
        ]);

        MarketingSpend::create(['bulan' => 9, 'tahun' => 2026, 'nominal' => 250000]);

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $pages = [
            '/customers' => ['Pelanggan Fatimah Zahra'],
            '/orders' => ['Order #'],
            '/products' => ['Produk Voal Test'],
            '/expenses' => ['Budget iklan September 2026', 'Pengeluaran Listrik'],
        ];

        foreach ($pages as $path => $needles) {
            $response = $this->get($path)->assertStatus(200);

            // Konfirmasi bawaan browser sudah tidak dipakai lagi.
            $response->assertDontSee("confirm('Hapus", false);

            foreach ($needles as $needle) {
                $response->assertSee('data-confirm="'.$needle, false);
            }

            $response->assertSee('id="confirmModal"', false)
                ->assertSee('data-confirm-ok', false)
                ->assertSee('data-confirm-cancel', false);
        }
    }

    private function customer(): Customer
    {
        return Customer::create([
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
        ]);
    }
}
