<?php

namespace Tests\Feature;

use App\Models\MarketingSpend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingSpendControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_marketing_spends(): void
    {
        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 250000,
        ]);

        $response = $this->get('/marketing-spends');

        $response->assertStatus(200);
        $response->assertSee('Marketing Spends');
        $response->assertSee('September');
        $response->assertSee('2026');
        $response->assertSee('Rp 250000');
    }

    public function test_marketing_spend_can_be_created(): void
    {
        $response = $this->post('/marketing-spends', [
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 300000,
        ]);

        $response->assertRedirectToRoute('marketing-spends.index');

        $spend = MarketingSpend::query()
            ->where('bulan', 9)
            ->where('tahun', 2026)
            ->sole();

        $this->assertSame(300000, $spend->nominal);
    }

    public function test_validation_rejects_invalid_bulan(): void
    {
        $response = $this->from('/marketing-spends/create')->post('/marketing-spends', [
            'bulan' => 13,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $response->assertRedirectBackWithErrors(['bulan']);

        $this->assertSame(0, MarketingSpend::query()->count());
    }

    public function test_validation_rejects_missing_nominal(): void
    {
        $response = $this->from('/marketing-spends/create')->post('/marketing-spends', [
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => '',
        ]);

        $response->assertRedirectBackWithErrors(['nominal']);

        $this->assertSame(0, MarketingSpend::query()->count());
    }

    public function test_duplicate_month_year_pair_is_rejected(): void
    {
        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $response = $this->from('/marketing-spends/create')->post('/marketing-spends', [
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 200000,
        ]);

        $response->assertRedirectBackWithErrors(['bulan']);

        $this->assertSame(1, MarketingSpend::query()->count());
        $this->assertSame(100000, MarketingSpend::query()->sole()->nominal);
    }

    public function test_marketing_spend_can_be_updated(): void
    {
        $spend = MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $uri = "/marketing-spends/{$spend->id}/edit";

        $response = $this->from($uri)->put("/marketing-spends/{$spend->id}", [
            'bulan' => 10,
            'tahun' => 2026,
            'nominal' => 450000,
        ]);

        $response->assertRedirectToRoute('marketing-spends.index');

        $spend->refresh();

        $this->assertSame(10, $spend->bulan);
        $this->assertSame(450000, $spend->nominal);
    }

    public function test_update_rejects_switching_to_existing_period(): void
    {
        $spend = MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        MarketingSpend::create([
            'bulan' => 10,
            'tahun' => 2026,
            'nominal' => 200000,
        ]);

        $response = $this->from("/marketing-spends/{$spend->id}/edit")
            ->put("/marketing-spends/{$spend->id}", [
                'bulan' => 10,
                'tahun' => 2026,
                'nominal' => 300000,
            ]);

        $response->assertRedirectBackWithErrors(['bulan']);

        $spend->refresh();

        $this->assertSame(9, $spend->bulan);
        $this->assertSame(100000, $spend->nominal);
    }

    public function test_marketing_spend_can_be_deleted(): void
    {
        $spend = MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $response = $this->from('/marketing-spends')->delete("/marketing-spends/{$spend->id}");

        $response->assertRedirectToRoute('marketing-spends.index');

        $this->assertFalse(MarketingSpend::query()->whereKey($spend->id)->exists());
    }
}