<?php

namespace Database\Seeders;

use App\Models\MarketingSpend;
use Illuminate\Database\Seeder;

/**
 * Budget iklan bulanan untuk data demo (10 bulan terakhir sampai bulan ini),
 * supaya metrik MER, ROI dan grafik budget iklan tidak nol.
 *
 * Memakai firstOrCreate sehingga budget yang sudah pernah diisi lewat halaman
 * Pengeluaran TIDAK pernah ditimpa oleh data demo ini.
 */
class MarketingSpendSeeder extends Seeder
{
    /**
     * Nominal budget (Rp) per bulan; key = jumlah bulan ke belakang.
     * Proporsional terhadap omset demo (± 28% MER) supaya net profit & ROI
     * tampil masuk akal di Dashboard/Laporan.
     *
     * @var array<int, int>
     */
    private const BUDGETS = [
        10 => 1200000,
        9 => 1350000,
        8 => 1100000,
        7 => 1250000,
        6 => 1400000,
        5 => 1150000,
        4 => 1300000,
        3 => 1200000,
        2 => 1250000,
        1 => 1100000,
        0 => 1300000,
    ];

    public function run(): void
    {
        foreach (self::BUDGETS as $monthsAgo => $nominal) {
            $period = now()->startOfMonth()->subMonths($monthsAgo);

            MarketingSpend::firstOrCreate(
                [
                    'bulan' => (int) $period->month,
                    'tahun' => (int) $period->year,
                ],
                ['nominal' => $nominal]
            );
        }
    }
}
