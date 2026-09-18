<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
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
            ->assertSee('Produk Jaubah');
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

    public function test_customer_create_view_renders(): void
    {
        $this->get('/customers/create')
            ->assertStatus(200)
            ->assertSee('Pelanggan Jaubah');
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
            ->assertSee('Order Jaubah')
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

    public function test_marketing_spend_create_view_renders(): void
    {
        $this->get('/marketing-spends/create')
            ->assertStatus(200)
            ->assertSee('Marketing Spend Jaubah');
    }

    public function test_marketing_spend_edit_view_renders(): void
    {
        $spend = MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $this->get("/marketing-spends/{$spend->id}/edit")
            ->assertStatus(200)
            ->assertSee('Marketing Spend Edit');
    }

    public function test_operational_expense_create_view_renders(): void
    {
        $this->get('/operational-expenses/create')
            ->assertStatus(200)
            ->assertSee('Pengeluaran Operasional Jaubah');
    }

    public function test_operational_expense_edit_view_renders(): void
    {
        $expense = OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $this->get("/operational-expenses/{$expense->id}/edit")
            ->assertStatus(200)
            ->assertSee('Pengeluaran Operasional Edit');
    }

    public function test_forms_render_validation_errors_without_parse_failures(): void
    {
        // Post invalid payloads from the form pages so the views render again
        // with the error bags populated (the regression scenario).
        $this->from('/products/create')->post('/products', ['nama_produk' => ''])->assertRedirectBackWithErrors(['nama_produk']);
        $this->from('/customers/create')->post('/customers', ['nama_lengkap' => ''])->assertRedirectBackWithErrors(['nama_lengkap']);
        $this->from('/marketing-spends/create')->post('/marketing-spends', ['bulan' => ''])->assertRedirectBackWithErrors(['bulan']);
        $this->from('/operational-expenses/create')->post('/operational-expenses', ['nama_pengeluaran' => ''])->assertRedirectBackWithErrors(['nama_pengeluaran']);

        $this->get('/products/create')->assertStatus(200);
        $this->get('/customers/create')->assertStatus(200);
        $this->get('/marketing-spends/create')->assertStatus(200);
        $this->get('/operational-expenses/create')->assertStatus(200);
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