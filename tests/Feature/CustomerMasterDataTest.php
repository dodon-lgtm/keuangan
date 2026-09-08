<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_index_returns_ok(): void
    {
        $response = $this->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Pelanggan');
    }

    public function test_customer_can_be_created(): void
    {
        $response = $this->post('/customers', [
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'domisili' => 'Amsterdam',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
            'tanggal_order_pertama' => '2026-09-05',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_A,
            'catatan' => 'Nieuwe klant via ads.',
            'email' => 'fatimah@example.com',
        ]);

        $response->assertRedirectToRoute('customers.index');

        $customer = Customer::query()->where('nama_lengkap', 'Fatimah Zahra')->sole();

        $this->assertSame(Customer::SUMBER_META_ADS, $customer->sumber);
        $this->assertSame(Customer::SEGMENT_A, $customer->segment);
        $this->assertSame(Customer::STATUS_NEW, $customer->status_pelanggan);
    }

    public function test_customer_validation_rejects_unknown_sumber(): void
    {
        $response = $this->from('/customers/create')->post('/customers', [
            'nama_lengkap' => 'Amina Bint',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'domisili' => 'Rotterdam',
            'sumber' => 'TikTok',
            'tanggal_masuk_chat' => '2026-09-01',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_B,
        ]);

        $response->assertRedirectBackWithErrors(['sumber']);

        $this->assertSame(
            0,
            Customer::query()->where('nama_lengkap', 'Amina Bint')->count()
        );
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::query()->create([
            'nama_lengkap' => 'Zaynab Ali',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08129876543',
            'domisili' => 'Den Haag',
            'sumber' => Customer::SUMBER_INSTAGRAM_ORGANIK,
            'tanggal_masuk_chat' => '2026-09-02',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_C,
        ]);

        $uri = "/customers/{$customer->id}/edit";

        $response = $this->from($uri)->put("/customers/{$customer->id}", [
            'nama_lengkap' => 'Zaynab Ali',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08129876543',
            'domisili' => 'Den Haag',
            'sumber' => Customer::SUMBER_CRM_WHATSAPP,
            'tanggal_masuk_chat' => '2026-09-02',
            'status_pelanggan' => Customer::STATUS_REPEAT,
            'segment' => Customer::SEGMENT_C,
        ]);

        $response->assertRedirectToRoute('customers.index');

        $customer->refresh();

        $this->assertSame(Customer::SUMBER_CRM_WHATSAPP, $customer->sumber);
        $this->assertSame(Customer::STATUS_REPEAT, $customer->status_pelanggan);
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::query()->create([
            'nama_lengkap' => 'Khadija Omar',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08126543210',
            'domisili' => 'Utrecht',
            'sumber' => Customer::SUMBER_INSTAGRAM_ORGANIK,
            'tanggal_masuk_chat' => '2026-09-03',
            'status_pelanggan' => Customer::STATUS_NEW,
            'segment' => Customer::SEGMENT_A,
        ]);

        $response = $this->from('/customers')->delete("/customers/{$customer->id}");

        $response->assertRedirectToRoute('customers.index');

        $this->assertFalse(Customer::query()->whereKey($customer->id)->exists());
    }
}
