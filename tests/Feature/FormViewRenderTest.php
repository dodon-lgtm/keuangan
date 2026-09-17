<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MarketingSpend;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every form view must render without Blade parse errors. The master-data
 * tests only post to these URLs (from() never renders the view), so the
 * broken "@error('field', 'is-invalid')" class attribute pattern previously
 * slipped through unnoticed.
 */
class FormViewRenderTest extends TestCase
{
    use RefreshDatabase;

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

        $this->get("/customers/{$customer->id}/edit")
            ->assertStatus(200)
            ->assertSee('Pelanggan Edit');
    }

    public function test_order_create_view_renders(): void
    {
        $this->prepareOrderFixtures();

        $this->get('/orders/create')
            ->assertStatus(200)
            ->assertSee('Order Jaubah');
    }

    public function test_order_edit_view_renders(): void
    {
        $data = $this->prepareOrderFixtures();

        $order = Order::create([
            'customer_id' => $data['customer']->id,
            'product_id' => $data['product']->id,
            'tanggal' => '2026-09-05',
            'nominal' => 40000,
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'jumlah_pcs' => 2,
            'status' => Order::STATUS_LUNAS,
            'link_desain' => '',
            'ongkir' => 5000,
            'alamat_kirim' => '',
            'ukuran_hijab' => '110x110',
        ]);

        $this->get("/orders/{$order->id}/edit")
            ->assertStatus(200)
            ->assertSee('Order Edit');
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

    public function test_forms_render_validation_errors_without_parse_failures(): void
    {
        // Post invalid payloads from the form pages so the views render again
        // with the error bags populated (the regression scenario).
        $this->from('/products/create')->post('/products', ['nama_produk' => ''])->assertRedirectBackWithErrors(['nama_produk']);
        $this->from('/customers/create')->post('/customers', ['nama_lengkap' => ''])->assertRedirectBackWithErrors(['nama_lengkap']);
        $this->from('/marketing-spends/create')->post('/marketing-spends', ['bulan' => ''])->assertRedirectBackWithErrors(['bulan']);

        $this->get('/products/create')->assertStatus(200);
        $this->get('/customers/create')->assertStatus(200);
        $this->get('/marketing-spends/create')->assertStatus(200);
    }

    /**
     * Create a customer and a product fixture for order forms.
     */
    private function prepareOrderFixtures(): array
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

        return compact('customer', 'product');
    }
}