<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

/**
 * Every form view must render without Blade parse errors. The master-data
 * tests only post to these URLs (from() never renders the view), so broken
 * field markup would otherwise slip through unnoticed.
 */
class FormViewRenderTest extends AuthenticatedTestCase
{
    public function test_product_create_view_renders(): void
    {
        $this->get('/products/create')
            ->assertStatus(200)
            ->assertSee('Produk Hijab');
    }

    public function test_product_edit_view_renders(): void
    {
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        $this->get("/products/{$product->id}/edit")
            ->assertStatus(200)
            ->assertSee('Produk Edit');
    }

    public function test_product_form_shows_live_margin_profit_preview(): void
    {
        // Form Tambah: belum ada isi -> preview dimulai dari nol (bukan NaN).
        $this->get('/products/create')
            ->assertStatus(200)
            ->assertSee('Margin Profit (Estimasi)')
            ->assertSee('Margin %')
            ->assertSee('id="margin-profit-preview"', false)
            ->assertSee('id="margin-percent-preview"', false)
            ->assertSee('updateMarginPreview', false)
            ->assertSee('Rp 0')
            ->assertSee('0.00%');

        // Form Edit: nilai awal preview dihitung dari harga jual & HPP produk.
        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 30000,
            'hpp' => 20000,
        ]);

        $this->get("/products/{$product->id}/edit")
            ->assertStatus(200)
            ->assertSee('id="margin-profit-preview"', false)
            ->assertSee('id="margin-percent-preview"', false)
            ->assertSee('Rp 10.000')
            ->assertSee('33.33%')
            ->assertDontSee('margin-preview is-negative', false);

        // HPP di atas harga jual: estimasi rugi tampil minus & ditandai merah.
        $loss = Product::create([
            'nama_produk' => 'Voal Rugi',
            'harga_jual' => 20000,
            'hpp' => 30000,
        ]);

        $this->get("/products/{$loss->id}/edit")
            ->assertStatus(200)
            ->assertSee('margin-preview is-negative', false)
            ->assertSee('Rp -10.000')
            ->assertSee('-50.00%');
    }

    public function test_customer_create_view_renders(): void
    {
        $this->get('/customers/create')
            ->assertStatus(200)
            ->assertSee('Pelanggan Hijab');
    }

    public function test_customer_edit_view_renders(): void
    {
        $customer = $this->customer();

        $this->get("/customers/{$customer->id}/edit")
            ->assertStatus(200)
            ->assertSee('Pelanggan Edit');
    }

    public function test_order_create_view_renders_with_multi_product_form(): void
    {
        $this->prepareOrderFixtures();

        $this->get('/orders/create')
            ->assertStatus(200)
            ->assertSee('Order Hijab')
            ->assertSee('+ Tambah Produk')
            ->assertSee('Total Nominal Transaksi');
    }

    public function test_order_edit_view_renders(): void
    {
        $order = $this->createOrderWithItem();

        $this->get("/orders/{$order->id}/edit")
            ->assertStatus(200)
            ->assertSee('Order Edit')
            ->assertSee('+ Tambah Produk');
    }

    public function test_expenses_index_renders_both_sections(): void
    {
        $this->get('/expenses')
            ->assertStatus(200)
            ->assertSee('Pengeluaran')
            ->assertSee('Pengeluaran Marketing')
            ->assertSee('Pengeluaran Operasional');
    }

    public function test_forms_render_validation_errors_without_parse_failures(): void
    {
        // Post invalid payloads from the form pages so the views render again
        // with the error bags populated (the regression scenario).
        $this->from('/products/create')->post('/products', ['nama_produk' => ''])->assertRedirectBackWithErrors(['nama_produk']);
        $this->from('/customers/create')->post('/customers', ['nama_lengkap' => ''])->assertRedirectBackWithErrors(['nama_lengkap']);
        $this->from('/expenses')->post('/expenses/marketing', ['bulan' => ''])->assertRedirectBackWithErrors(['bulan']);
        $this->from('/expenses')->post('/expenses/operational', ['nama_pengeluaran' => ''])->assertRedirectBackWithErrors(['nama_pengeluaran']);

        $this->get('/products/create')->assertStatus(200);
        $this->get('/customers/create')->assertStatus(200);
        $this->get('/expenses')->assertStatus(200);
    }

    /**
     * Create a customer and a product fixture for order forms.
     */
    private function prepareOrderFixtures(): array
    {
        $customer = $this->customer();

        $product = Product::create([
            'nama_produk' => 'Voal Test',
            'harga_jual' => 20000,
            'hpp' => 10000,
        ]);

        return compact('customer', 'product');
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

    /**
     * Create an order that already has a line item.
     */
    private function createOrderWithItem(): Order
    {
        $data = $this->prepareOrderFixtures();
        $customer = $data['customer'];
        $product = $data['product'];

        $order = Order::create([
            'customer_id' => $customer->id,
            'tanggal' => '2026-09-05',
            'nominal' => 40000,
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

        return $order;
    }
}
