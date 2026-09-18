<?php

namespace Database\Seeders;

use App\Models\OperationalExpense;
use Illuminate\Database\Seeder;

class OperationalExpenseSeeder extends Seeder
{
    /**
     * Initial Fix Cost data taken from the monthly business PDF.
     */
    public function run(): void
    {
        $expenses = [
            [
                'nama_pengeluaran' => 'Listrik',
                'kategori' => OperationalExpense::KATEGORI_FIX_COST,
                'nominal' => 400000,
            ],
            [
                'nama_pengeluaran' => 'Internet',
                'kategori' => OperationalExpense::KATEGORI_FIX_COST,
                'nominal' => 444000,
            ],
            [
                'nama_pengeluaran' => 'Cutting & Mesin',
                'kategori' => OperationalExpense::KATEGORI_FIX_COST,
                'nominal' => 184500,
            ],
        ];

        $month = (int) now()->month;
        $year = (int) now()->year;

        foreach ($expenses as $expense) {
            OperationalExpense::updateOrCreate(
                [
                    'nama_pengeluaran' => $expense['nama_pengeluaran'],
                    'bulan' => $month,
                    'tahun' => $year,
                ],
                $expense
            );
        }
    }
}