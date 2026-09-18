<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(OrderObserver::class)]
#[Fillable([
    'customer_id',
    'tanggal',
    'nominal',
    'tipe_bayar',
    'jenis_order',
    'metode_bayar',
    'pic_admin',
    'link_desain',
    'ongkir',
    'alamat_kirim',
    'ukuran_hijab',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const TIPE_FULL_PAYMENT = 'Full Payment';

    public const TIPE_DP = 'DP';

    public const TIPE_PELUNASAN = 'Pelunasan';

    public const JENIS_CUSTOM_DESIGN = 'Custom Design';

    public const JENIS_READY_STOCK = 'Ready Stock';

    public const METODE_TRANSFER_BANK = 'Transfer Bank';

    public const METODE_QRIS = 'QRIS';

    public const METODE_CASH = 'Cash';

    public const TIPES = [
        self::TIPE_FULL_PAYMENT,
        self::TIPE_DP,
        self::TIPE_PELUNASAN,
    ];

    public const JENISES = [
        self::JENIS_CUSTOM_DESIGN,
        self::JENIS_READY_STOCK,
    ];

    public const METODES = [
        self::METODE_TRANSFER_BANK,
        self::METODE_QRIS,
        self::METODE_CASH,
    ];

    /**
     * The customer that placed this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The products (with quantities) purchased in this order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
            'ongkir' => 'integer',
        ];
    }
}
