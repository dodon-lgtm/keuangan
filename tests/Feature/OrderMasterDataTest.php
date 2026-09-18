<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderMasterDataTest extends AuthenticatedTestCase
{
    public function test_orders_index_lists_multi_product_breakdown(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $productA = $data['product'];
        $productB = $data['productB'];

        $this->createOrderViaPayload($customer, $productA, $productB);

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertSee('Fatimah Zahra');
        $response->assertSee('Voal Test');
        $response->assertSee('Hijab Polos');
        $response->assertSee('Full Payment');
        $response->assertSee('Ready Stock');
        // Revisi: rincian multi-produk ditampilkan sebagai "Nama (x2)"
        $response->assertSee('(x2)');
        $response->assertSee('(x3)');
    }

    public function test_order_can_be_created_with_items_and_nominal_is_computed(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $response = $this->post('/orders', $this->validPayload($customer, $product));

        $response->assertRedirectToRoute('orders.index');

        $order = Order::query()->where('customer_id', $customer->id)->sole();

        // nominal = sum(subtotal item) = 26900 * 2
        $this->assertSame(53800, $order->nominal);
        $this->assertSame(Order::TIPE_FULL_PAYMENT, $order->tipe_bayar);

        $item = OrderItem::query()->where('order_id', $order->id)->sole();
        $this->assertSame(2, $item->jumlah_pcs);
        $this->assertSame(26900, $item->harga_satuan);
        $this->assertSame(15500, $item->hpp_satuan);
        $this->assertSame(53800, $item->subtotal);
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

    public function test_order_validation_rejects_empty_items(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $payload = $this->validPayload($customer, $product, ['items' => []]);

        $response = $this->from('/orders/create')->post('/orders', $payload);

        $response->assertRedirectBackWithErrors(['items']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_order_can_be_updated_and_items_are_replaced(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];
        $productB = $data['productB'];

        $order = $this->createOrderViaPayload($customer, $product);

        $response = $this->from("/orders/{$order->id}/edit")
            ->put("/orders/{$order->id}", $this->validPayload(
                $customer,
                $product,
                ['items' => [
                    ['product_id' => $product->id, 'jumlah_pcs' => 3],
                    ['product_id' => $productB->id, 'jumlah_pcs' => 2],
                ]]
            ));

        $response->assertRedirectToRoute('orders.index');

        $order->refresh();

        // nominal = 26900*3 + 15000*2 (Hijab Polos)
        $this->assertSame(110700, $order->nominal);
        $this->assertSame(2, $order->orderItems()->count());
    }

    public function test_order_can_be_deleted_and_items_cascade(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $order = $this->createOrderViaPayload($customer, $product);

        $response = $this->from('/orders')->delete("/orders/{$order->id}");

        $response->assertRedirectToRoute('orders.index');

        $this->assertFalse(Order::query()->whereKey($order->id)->exists());
        $this->assertSame(0, OrderItem::query()->where('order_id', $order->id)->count());
    }

    /**
     * Create a customer and two products as order form fixtures.
     */
    private function prepare(): array
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
            'harga_jual' => 26900,
            'hpp' => 15500,
        ]);

        $productB = Product::create([
            'nama_produk' => 'Hijab Polos',
            'harga_jual' => 15000,
            'hpp' => 9000,
        ]);

        return compact('customer', 'product', 'productB');
    }

    /**
     * Build a valid order payload with its line items.
     */
    private function validPayload($customer, $product, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'tanggal' => '2026-09-08',
            'tipe_bayar' => Order::TIPE_FULL_PAYMENT,
            'jenis_order' => Order::JENIS_READY_STOCK,
            'metode_bayar' => Order::METODE_TRANSFER_BANK,
            'pic_admin' => 'Admin A',
            'ongkir' => '',
            'alamat_kirim' => '',
            'ukuran_hijab' => '110x110',
            'items' => [
                ['product_id' => $product->id, 'jumlah_pcs' => 2],
            ],
        ], $overrides);
    }

    /**
     * Persist an order through the real controller route so the observer
     * and nominal logic are exercised end-to-end.
     */
    private function createOrderViaPayload($customer, $product, $productB = null): Order
    {
        $items = [
            ['product_id' => $product->id, 'jumlah_pcs' => 2],
        ];

        if ($productB !== null) {
            $items[] = ['product_id' => $productB->id, 'jumlah_pcs' => 3];
        }

        $this->post('/orders', $this->validPayload($customer, $product, ['items' => $items]));

        return Order::query()->where('customer_id', $customer->id)->sole();
    }
}