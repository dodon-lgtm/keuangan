<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;

class OrderAutomationTest extends AuthenticatedTestCase
{
    public function test_nominal_is_auto_calculated_from_items_when_created(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $response = $this->post('/orders', $this->validPayload($customer, $product));

        $response->assertRedirectToRoute('orders.index');

        $order = Order::query()->sole();

        $this->assertSame($product->harga_jual * 2, $order->nominal);
        $this->assertSame(1, $order->orderItems()->count());
    }

    public function test_multi_product_order_sums_all_item_subtotals(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];
        $productB = $data['productB'];

        $payload = $this->validPayload($customer, $product, [
            'items' => [
                ['product_id' => $product->id, 'jumlah_pcs' => 2],
                ['product_id' => $productB->id, 'jumlah_pcs' => 3],
            ],
        ]);

        $this->post('/orders', $payload);

        $order = Order::query()->sole();

        // 26900*2 + 15000*3 = 53800 + 45000 = 98800
        $this->assertSame(98800, $order->nominal);
    }

    public function test_empty_tanggal_defaults_to_today(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $this->post('/orders', $this->validPayload($customer, $product, ['tanggal' => '']));

        $order = Order::query()->sole();

        $this->assertSame(
            Carbon::today()->format('Y-m-d'),
            $order->tanggal->format('Y-m-d')
        );
    }

    public function test_update_recalculates_nominal_from_new_items(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];
        $productB = $data['productB'];

        $order = $this->post('/orders', $this->validPayload($customer, $product))->assertRedirectToRoute('orders.index');

        $savedOrder = Order::query()->sole();

        $response = $this->from("/orders/{$savedOrder->id}/edit")
            ->put("/orders/{$savedOrder->id}", $this->validPayload(
                $customer,
                $product,
                [
                    'items' => [
                        ['product_id' => $productB->id, 'jumlah_pcs' => 5],
                    ],
                ]
            ));

        $response->assertRedirectToRoute('orders.index');

        $savedOrder->refresh();

        $this->assertSame(15000 * 5, $savedOrder->nominal);
        $this->assertSame(1, $savedOrder->orderItems()->count());
    }

    public function test_items_with_invalid_product_are_rejected(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $payload = $this->validPayload($customer, $product, [
            'items' => [
                ['product_id' => 999999, 'jumlah_pcs' => 2],
            ],
        ]);

        $response = $this->from('/orders/create')->post('/orders', $payload);

        $response->assertRedirectBackWithErrors(['items.0.product_id']);

        $this->assertSame(0, Order::query()->count());
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
}