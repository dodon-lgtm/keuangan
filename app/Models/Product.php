<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ProductObserver::class)]
#[Fillable(['nama_produk', 'harga_jual', 'hpp', 'margin_profit'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_jual' => 'integer',
            'hpp' => 'integer',
            'margin_profit' => 'integer',
        ];
    }

    /**
     * The order line items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Margin profit (laba kotor) = harga jual - HPP.
     */
    public function hitungMarginProfit(): int
    {
        return (int) $this->harga_jual - (int) $this->hpp;
    }

    /**
     * Margin profit (laba kotor) per produk.
     *
     * Nilainya selalu dihitung dari harga jual & HPP supaya baris lama yang
     * belum sempat terisi tetap menampilkan angka yang benar.
     */
    public function getMarginProfitAttribute(): int
    {
        return $this->hitungMarginProfit();
    }

    /**
     * Persentase margin profit terhadap harga jual.
     */
    public function getMarginPercentAttribute(): float
    {
        $hargaJual = (int) $this->harga_jual;

        if ($hargaJual <= 0) {
            return 0.0;
        }

        return (($hargaJual - (int) $this->hpp) / $hargaJual) * 100;
    }
}
