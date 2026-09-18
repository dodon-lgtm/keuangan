<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama_pengeluaran',
    'kategori',
    'nominal',
    'bulan',
    'tahun',
])]
class OperationalExpense extends Model
{
    public const KATEGORI_FIX_COST = 'Fix Cost';

    public const KATEGORI_VARIABLE_COST = 'Variable Cost';

    public const KATEGORIS = [
        self::KATEGORI_FIX_COST,
        self::KATEGORI_VARIABLE_COST,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kategori' => 'string',
            'nominal' => 'integer',
            'bulan' => 'integer',
            'tahun' => 'integer',
        ];
    }
}