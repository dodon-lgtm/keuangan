<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Fill the margin profit (laba kotor) when a new product is created.
     */
    public function creating(Product $product): void
    {
        $this->syncMarginProfit($product);
    }

    /**
     * Recalculate the margin profit whenever an existing product is updated.
     */
    public function updating(Product $product): void
    {
        $this->syncMarginProfit($product);
    }

    /**
     * Margin profit selalu diturunkan dari harga jual dan HPP, sehingga user
     * tidak perlu mengisinya manual di form tambah/edit produk.
     */
    private function syncMarginProfit(Product $product): void
    {
        $product->margin_profit = $product->hitungMarginProfit();
    }
}
