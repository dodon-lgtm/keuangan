<?php

namespace Tests\Feature;

use App\Models\OperationalExpense;
use Database\Seeders\OperationalExpenseSeeder;

class OperationalExpenseControllerTest extends AuthenticatedTestCase
{
    public function test_index_lists_operational_expenses_with_totals(): void
    {
        OperationalExpense::create([
            'nama_pengeluaran' => 'Listrik',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 400000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response = $this->get('/operational-expenses');

        $response->assertStatus(200);
        $response->assertSee('Pengeluaran Operasional');
        $response->assertSee('Listrik');
        $response->assertSee('Fix Cost');
        $response->assertSee('September');
        $response->assertSee('Rp 400.000');
        $response->assertSee('Total Fix Cost');
    }

    public function test_operational_expense_can_be_created(): void
    {
        $response = $this->post('/operational-expenses', [
            'nama_pengeluaran' => 'Internet',
            'kategori' => OperationalExpense::KATEGORI_FIX_COST,
            'nominal' => 444000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response->assertRedirectToRoute('operational-expenses.index');

        $expense = OperationalExpense::query()
            ->where('nama_pengeluaran', 'Internet')
            ->sole();

        $this->assertSame(444000, $expense->nominal);
        $this->assertSame(OperationalExpense::KATEGORI_FIX_COST, $expense->kategori);
    }

    public function test_validation_rejects_invalid_kategori(): void
    {
        $response = $this->from('/operational-expenses/create')->post('/operational-expenses', [
            'nama_pengeluaran' => 'Listrik',
            'kategori' => 'Investasi',
            'nominal' => 100000,
            'bulan' => 9,
            'tahun' => 2026,
        ]);

        $response->assertRedirectBackWithErrors(['kategori']);

        $this->assertSame(0, OperationalExpense::query()->count());
    }

    public function test_validation_rejects_missing_nominal(): void
    {
        $response = $this->from('/operational-expenses/create')->post('/operational-expenses', [
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

        $uri = "/operational-expenses/{$expense->id}/edit";

        $response = $this->from($uri)->put("/operational-expenses/{$expense->id}", [
            'nama_pengeluaran' => 'Listrik & Air',
            'kategori' => OperationalExpense::KATEGORI_VARIABLE_COST,
            'nominal' => 450000,
            'bulan' => 10,
            'tahun' => 2026,
        ]);

        $response->assertRedirectToRoute('operational-expenses.index');

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

        $response = $this->from('/operational-expenses')->delete("/operational-expenses/{$expense->id}");

        $response->assertRedirectToRoute('operational-expenses.index');

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