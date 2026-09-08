<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_index_lists_relation_data(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        Order::create($this->validPayload($customer, $product));

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Fatimah Zahra');
        $response->assertSee('Voal Test');
        $response->assertSee('Full Payment');
        $response->assertSee('Ready Stock');
    }

    public function test_order_can_be_created(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $response = $this->post('/orders', $this->validPayload($customer, $product));

        $response->assertRedirectToRoute('orders.index');

        $order = Order::query()->where('customer_id', $customer->id)->sole();

        $this->assertSame($product->id, $order->product_id);
        $this->assertSame(Order::TIPE_FULL_PAYMENT, $order->tipe_bayar);
    }

    public function test_order_validation_rejects_invalid_tipe_bayar(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $payload = $this->validPayload($customer, $product, ['tipe_bayar' => 'Krediet']);

        $response = $this->from('/orders/create')->post('/orders', $payload);

        $response->assertRedirectBackWithErrors(['tipe_bayar']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_order_validation_rejects_missing_customer(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $payload = $this->validPayload($customer, $product, ['customer_id' => '']);

        $response = $this->from('/orders/create')->post('/orders', $payload);

        $response->assertRedirectBackWithErrors(['customer_id']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_order_can_be_updated(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $order = Order::create($this->validPayload($customer, $product));

        $response = $this->from("/orders/{$order->id}/edit")
            ->put("/orders/{$order->id}", $this->validPayload(
                $customer,
                $product,
                ['status' => Order::STATUS_DIBATALKAN, 'jumlah_pcs' => 4]
            ));

        $response->assertRedirectToRoute('orders.index');

        $order->refresh();

        $this->assertSame(Order::STATUS_DIBATALKAN, $order->status);
        $this->assertSame(4, $order->jumlah_pcs);
    }

    public function test_order_can_be_deleted(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $order = Order::create($this->validPayload($customer, $product));

        $response = $this->from('/orders')->delete("/orders/{$order->id}");

        $response->assertRedirectToRoute('orders.index');

        $this->assertFalse(Order::query()->whereKey($order->id)->exists());
    }

    /**
     * Create a customer and a product fixture.
     */
    private function prepare(): array
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
            'harga_jual' => 26900,
            'hpp' => 15500,
        ]);

        return compact('customer', 'product');
    }

    /**
     * Build a valid order payload, optionally overriding attributes.
     */
    private function validPayload($customer, $product, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'tanggal' => '2026-09-08',
            'nominal' => '',
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'jumlah_pcs' => 2,
            'status' => Order::STATUS_LUNAS,
            'link_desain' => '',
            'ongkir' => '',
            'alamat_kirim' => '',
            'ukuran_hijab' => '110x110',
        ], $overrides);
    }
}
