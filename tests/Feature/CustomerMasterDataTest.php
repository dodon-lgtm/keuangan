<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class CustomerMasterDataTest extends AuthenticatedTestCase
{
    use RefreshDatabase;

    public function test_customers_index_returns_ok(): void
    {
        $response = $this->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Pelanggan');
    }

    public function test_customer_can_be_created_without_removed_fields(): void
    {
        $response = $this->post('/customers', [
            'nama_lengkap' => 'Fatimah Zahra',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => Customer::SUMBER_META_ADS,
            'tanggal_masuk_chat' => '2026-09-01',
            'catatan' => 'Nieuwe klant via ads.',
            'email' => 'fatimah@example.com',
        ]);

        $response->assertRedirectToRoute('customers.index');

        $customer = Customer::query()->where('nama_lengkap', 'Fatimah Zahra')->sole();

        $this->assertSame(Customer::SUMBER_META_ADS, $customer->sumber);
        $this->assertSame('08123456789', $customer->no_whatsapp);
        // Kolom lama sudah tidak ada di skema.
        $this->assertFalse(Schema::hasColumn('customers', 'domisili'));
        $this->assertFalse(Schema::hasColumn('customers', 'segment'));
        $this->assertFalse(Schema::hasColumn('customers', 'status_pelanggan'));
        $this->assertFalse(Schema::hasColumn('customers', 'tanggal_order_pertama'));
    }

    public function test_customer_validation_rejects_unknown_sumber(): void
    {
        $response = $this->from('/customers/create')->post('/customers', [
            'nama_lengkap' => 'Amina Bint',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08123456789',
            'sumber' => 'TikTok',
            'tanggal_masuk_chat' => '2026-09-01',
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
            'sumber' => Customer::SUMBER_INSTAGRAM_ORGANIK,
            'tanggal_masuk_chat' => '2026-09-02',
        ]);

        $uri = "/customers/{$customer->id}/edit";

        $response = $this->from($uri)->put("/customers/{$customer->id}", [
            'nama_lengkap' => 'Zaynab Ali',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08129876543',
            'sumber' => Customer::SUMBER_CRM_WHATSAPP,
            'tanggal_masuk_chat' => '2026-09-02',
        ]);

        $response->assertRedirectToRoute('customers.index');

        $customer->refresh();

        $this->assertSame(Customer::SUMBER_CRM_WHATSAPP, $customer->sumber);
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::query()->create([
            'nama_lengkap' => 'Khadija Omar',
            'nama_brand' => 'Hijab Co',
            'no_whatsapp' => '08126543210',
            'sumber' => Customer::SUMBER_INSTAGRAM_ORGANIK,
            'tanggal_masuk_chat' => '2026-09-03',
        ]);

        $response = $this->from('/customers')->delete("/customers/{$customer->id}");

        $response->assertRedirectToRoute('customers.index');

        $this->assertFalse(Customer::query()->whereKey($customer->id)->exists());
    }
}