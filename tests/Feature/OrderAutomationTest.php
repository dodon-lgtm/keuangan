<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_nominal_is_auto_calculated_when_left_blank(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $response = $this->post('/orders', $this->validPayload($customer, $product));

        $response->assertRedirectToRoute('orders.index');

        $order = Order::query()->sole();

        $this->assertSame($product->harga_jual * 2, $order->nominal);
    }

    public function test_manual_nominal_overrides_auto_calculation(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $payload = $this->validPayload($customer, $product, ['nominal' => 50000]);

        $this->post('/orders', $payload);

        $order = Order::query()->sole();

        $this->assertSame(50000, $order->nominal);
    }

    public function test_first_order_sets_customer_first_order_date_and_status_new(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $this->post('/orders', $this->validPayload($customer, $product, ['tanggal' => '2026-09-08']));

        $customer->refresh();

        $this->assertSame(Customer::STATUS_NEW, $customer->status_pelanggan);
        $this->assertSame('2026-09-08', $customer->tanggal_order_pertama->format('Y-m-d'));
    }

    public function test_second_order_sets_customer_status_repeat(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        // First order via the model (also triggers the observer).
        Order::create($this->validPayload($customer, $product, ['tanggal' => '2026-09-01']));

        // Second order via the HTTP endpoint.
        $response = $this->post('/orders', $this->validPayload($customer, $product, ['tanggal' => '2026-09-08']));

        $response->assertRedirectToRoute('orders.index');

        $customer->refresh();

        $this->assertSame(Customer::STATUS_REPEAT, $customer->status_pelanggan);
        $this->assertSame('2026-09-01', $customer->tanggal_order_pertama->format('Y-m-d'));
    }

    public function test_update_with_blank_nominal_recalculates_from_new_pcs(): void
    {
        $data = $this->prepare();
        $customer = $data['customer'];
        $product = $data['product'];

        $order = Order::create($this->validPayload($customer, $product));

        $response = $this->from("/orders/{$order->id}/edit")
            ->put("/orders/{$order->id}", $this->validPayload(
                $customer,
                $product,
                ['jumlah_pcs' => 3, 'nominal' => '']
            ));

        $response->assertRedirectToRoute('orders.index');

        $order->refresh();

        $this->assertSame($product->harga_jual * 3, $order->nominal);
    }

    public function test_moving_order_to_another_customer_resyncs_both_customers(): void
    {
        $data = $this->prepare();
        $customerA = $data['customer'];
        $product = $data['product'];

        $customerB = Customer::create([
            'nama_lengkap' => 'Amina Bint',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08129876543',
            'domisili' => 'Rotterdam',
            'sumber' => Customer::SUMBER_CRM_WHATSAPP,
            'tanggal_masuk_chat' => '2026-09-02',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_B,
        ]);

        Order::create($this->validPayload($customerA, $product, ['tanggal' => '2026-09-01']));
        $order = Order::create($this->validPayload($customerA, $product, ['tanggal' => '2026-09-05']));

        // Move the second order to customer B (keeping its original date).
        $response = $this->from("/orders/{$order->id}/edit")
            ->put("/orders/{$order->id}", $this->validPayload(
                $customerB,
                $product,
                ['tanggal' => '2026-09-05']
            ));

        $response->assertRedirectToRoute('orders.index');

        $customerA->refresh();
        $customerB->refresh();

        // A lost one order (now only 1 remaining) and B received its first order.
        $this->assertSame(Customer::STATUS_NEW, $customerA->status_pelanggan);
        $this->assertSame(Customer::STATUS_NEW, $customerB->status_pelanggan);
        $this->assertSame('2026-09-05', $customerB->tanggal_order_pertama->format('Y-m-d'));
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
