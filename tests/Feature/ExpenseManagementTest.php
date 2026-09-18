<?php

namespace Tests\Feature;

use App\Models\MarketingSpend;
use App\Models\OperationalExpense;
use Database\Seeders\OperationalExpenseSeeder;

/**
 * End-to-end coverage of the unified "Pengeluaran" module: one page hosts
 * both the marketing / ad budget (for MER & ROI) and the operational costs
 * (Fix/Variable Cost) used by the Net Profit calculation.
 */
class ExpenseManagementTest extends AuthenticatedTestCase
{
    public function test_index_shows_marketing_and_operational_sections(): void
    {
        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 250000,
        ]);

        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response = $this->get('/expenses');

        $response->assertStatus(200);
        $response->assertSee('Pengeluaran Marketing');
        $response->assertSee('Pengeluaran Operasional');
        $response->assertSee('September');
        $response->assertSee('Rp 250.000');
        $response->assertSee('Listrik');
        $response->assertSee('Rp 400.000');
        $response->assertSee('Total Fix Cost');
        $response->assertSee('Total Variable Cost');
    }

    public function test_marketing_spend_can_be_created(): void
    {
        $response = $this->post('/expenses/marketing', [
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 300000,
        ]);

        $response->assertRedirectToRoute('expenses.index');

        $spend = MarketingSpend::query()
            ->where('bulan', 9)
            ->where('tahun', 2026)
            ->sole();

        $this->assertSame(300000, $spend->nominal);
    }

    public function test_marketing_spend_validation_rejects_invalid_bulan(): void
    {
        $response = $this->from('/expenses')->post('/expenses/marketing', [
            'bulan' => 13,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $response->assertRedirectBackWithErrors(['bulan']);

        $this->assertSame(0, MarketingSpend::query()->count());
    }

    public function test_marketing_spend_duplicate_month_year_pair_is_rejected(): void
    {
        MarketingSpend::create([
            'bulan' => 9,
            'tahun' => 2026,
            'nominal' => 100000,
        ]);

        $response = $this->from('/expenses')->post('/expenses/marketing', [
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

        $response = $this->from('/expenses')->put("/expenses/marketing/{$spend->id}", [
            'bulan' => 10,
            'tahun' => 2026,
            'nominal' => 450000,
        ]);

        $response->assertRedirectToRoute('expenses.index');

        $spend->refresh();

        $this->assertSame(10, $spend->bulan);
        $this->assertSame(450000, $spend->nominal);
    }

    public function test_marketing_spend_update_rejects_switching_to_existing_period(): void
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

        $response = $this->from('/expenses')->put("/expenses/marketing/{$spend->id}", [
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

        $response = $this->from('/expenses')->delete("/expenses/marketing/{$spend->id}");

        $response->assertRedirectToRoute('expenses.index');

        $this->assertFalse(MarketingSpend::query()->whereKey($spend->id)->exists());
    }

    public function test_operational_expense_can_be_created(): void
    {
        $response = $this->post('/expenses/operational', [
            'nama_pengeluaran' => 'Internet',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 444000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response->assertRedirectToRoute('expenses.index');

        $expense = OperationalExpense::query()
            ->where('nama_pengeluaran', 'Internet')
            ->sole();

        $this->assertSame(444000, $expense->nominal);
        $this->assertSame(OperationalExpense::KATEGORI_FIX_COST, $expense->kategori);
    }

    public function test_operational_expense_validation_rejects_invalid_kategori(): void
    {
        $response = $this->from('/expenses')->post('/expenses/operational', [
            'nama_pengeluaran' => 'Listrik',
            'kategori' => 'Investasi',
            'nominal' => 100000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response->assertRedirectBackWithErrors(['kategori']);

        $this->assertSame(0, OperationalExpense::query()->count());
    }

    public function test_operational_expense_validation_rejects_missing_nominal(): void
    {
        $response = $this->from('/expenses')->post('/expenses/operational', [
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => '',
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response->assertRedirectBackWithErrors(['nominal']);

        $this->assertSame(0, OperationalExpense::query()->count());
    }

    public function test_operational_expense_can_be_updated(): void
    {
        $expense = OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response = $this->from('/expenses')->put("/expenses/operational/{$expense->id}", [
            'nama_pengeluaran' => 'Listrik & Air',
            'kategori' => OperationalExpense::KATEGORI_VARIABLE_COST,
            'nominal' => 450000,
            'bulan' => 10,
            'tahun' => 2026,
        ]);

        $response->assertRedirectToRoute('expenses.index');

        $expense->refresh();

        $this->assertSame('Listrik & Air', $expense->nama_pengeluaran);
        $this->assertSame(OperationalExpense::KATEGORI_VARIABLE_COST, $expense->kategori);
        $this->assertSame(450000, $expense->nominal);
        $this->assertSame(10, $expense->bulan);
    }

    public function test_operational_expense_can_be_deleted(): void
    {
        $expense = OperationalExpense::create([
            'nama_pengeluaran' => 'Cutting & Mesin',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 184500,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response = $this->from('/expenses')->delete("/expenses/operational/{$expense->id}");

        $response->assertRedirectToRoute('expenses.index');

        $this->assertFalse(OperationalExpense::query()->whereKey($expense->id)->exists());
    }

    public function test_seeder_creates_fix_costs_from_pdf(): void
    {
        $this->seed(OperationalExpenseSeeder::class);

        $this->assertTrue(OperationalExpense::query()
            ->where('nama_pengeluaran', 'Listrik')
            ->where('nominal', 400000)
            ->exists());

        $this->assertTrue(OperationalExpense::query()
            ->where('nama_pengeluaran', 'Internet')
            ->where('nominal', 444000)
            ->exists());

        $this->assertTrue(OperationalExpense::query()
            ->where('nama_pengeluaran', 'Cutting & Mesin')
            ->where('nominal', 184500)
            ->exists());
    }
}