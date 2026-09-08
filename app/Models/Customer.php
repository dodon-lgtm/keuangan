<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama_lengkap',
    'nama_brand',
    'no_whatsapp',
    'domisili',
    'sumber',
    'tanggal_masuk_chat',
    'tanggal_order_pertama',
    'status_pelanggan',
    'segment',
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

    public const STATUS_NEW = 'new';

    public const STATUS_REPEAT = 'repeat';

    public const SEGMENT_A = 'A';

    public const SEGMENT_B = 'B';

    public const SEGMENT_C = 'C';

    public const SUMBERS = [
        self::SUMBER_INSTAGRAM_ORGANIK,
        self::SUMBER_META_ADS,
        self::SUMBER_CRM_WHATSAPP,
    ];

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_REPEAT,
    ];

    public const SEGMENTS = [
        self::SEGMENT_A,
        self::SEGMENT_B,
        self::SEGMENT_C,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_masuk_chat' => 'date',
            'tanggal_order_pertama' => 'date',
        ];
    }
}
