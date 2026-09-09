<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['bulan', 'tahun', 'nominal'])]
class MarketingSpend extends Model
{
    /**
     * The minimum valid month number.
     */
    public const BULAN_MIN = 1;

    /**
     * The maximum valid month number.
     */
    public const BULAN_MAX = 12;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'nominal' => 'integer',
        ];
    }
}