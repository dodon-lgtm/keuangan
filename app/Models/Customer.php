<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nama_lengkap',
    'nama_brand',
    'no_whatsapp',
    'sumber',
    'tanggal_masuk_chat',
    'catatan',
    'email',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    public const SUMBER_INSTAGRAM_ORGANIK = 'Instagram Organik';

    public const SUMBER_META_ADS = 'Meta Ads';

    public const SUMBER_CRM_WHATSAPP = 'CRM Whatsapp';

    public const SUMBERS = [
        self::SUMBER_INSTAGRAM_ORGANIK,
        self::SUMBER_META_ADS,
        self::SUMBER_CRM_WHATSAPP,
    ];

    /**
     * The orders placed by this customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Whether this customer is "repeat" (has more than one transaction).
     */
    public function isRepeat(): bool
    {
        return $this->orders_count > 1;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_masuk_chat' => 'date',
        ];
    }
}
