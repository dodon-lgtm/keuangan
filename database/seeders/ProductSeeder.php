<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'nama_produk' => 'Voal Latte Premium',
                'harga_jual' => 26900,
                'hpp' => 15500,
            ],
            [
                'nama_produk' => 'Premium RR 110 & 115',
                'harga_jual' => 33900,
                'hpp' => 21700,
            ],
            [
                'nama_produk' => 'Premium RR 125 & 130',
                'harga_jual' => 47900,
                'hpp' => 26940,
            ],
            [
                'nama_produk' => 'Premium RR 140 & 145',
                'harga_jual' => 15000,
                'hpp' => 0,
            ],
            [
                'nama_produk' => 'Hijab Polos',
                'harga_jual' => 15000,
                'hpp' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['nama_produk' => $product['nama_produk']],
                $product
            );
        }
    }
}
