<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductMasterDataTest extends AuthenticatedTestCase
{
    use RefreshDatabase;

    public function test_products_index_lists_seeded_products_with_summary(): void
    {
        $this->seed(ProductSeeder::class);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Voal Latte Premium');
        $response->assertSee('Hijab Polos');
        // Revisi: kolom & ringkasan keuntungan tersedia.
        $response->assertSee('Keuntungan');
        $response->assertSee('Total Jenis Produk');
        $response->assertSee('Rata-rata Harga Jual');
        $response->assertSee('Rata-rata Keuntungan');
    }

    public function test_products_index_shows_profit_footer_summary(): void
    {
        Product::create(['nama_produk' => 'Produk A', 'harga_jual' => 30000, 'hpp' => 10000]);
        Product::create(['nama_produk' => 'Produk B', 'harga_jual' => 50000, 'hpp' => 20000]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        // AVG(harga_jual) = 40000, AVG(hpp) = 15000, AVG(harga_jual - hpp) = 25000
        $response->assertSee('Rp 40.000');
        $response->assertSee('Rp 15.000');
        $response->assertSee('Rp 25.000');
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->post('/products', [
            'nama_produk' => 'Voal Test N',
            'harga_jual' => 50000,
            'hpp' => 20000,
        ]);

        $response->assertRedirectToRoute('products.index');

        $this->assertTrue(
            Product::query()->where('nama_produk', 'Voal Test N')->exists()
        );
    }

    public function test_product_validation_rejects_missing_name(): void
    {
        $response = $this->from('/products/create')->post('/products', [
            'nama_produk' => '',
            'harga_jual' => 50000,
            'hpp' => 20000,
        ]);

        $response->assertRedirectBackWithErrors(['nama_produk']);

        $this->assertSame(
            0,
            Product::query()->where('harga_jual', 50000)->count()
        );
    }

    public function test_product_can_be_updated(): void
    {
        $product = Product::create([
            'nama_produk' => 'Voal Test Edit',
            'harga_jual' => 30000,
            'hpp' => 12000,
        ]);

        $uri = "/products/{$product->id}/edit";

        $response = $this->from($uri)->put("/products/{$product->id}", [
            'nama_produk' => 'Voal Latte Premium Updated',
            'harga_jual' => 29900,
            'hpp' => 16000,
        ]);

        $response->assertRedirectToRoute('products.index');

        $product->refresh();

        $this->assertSame('Voal Latte Premium Updated', $product->nama_produk);
        $this->assertSame(29900, $product->harga_jual);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::create([
            'nama_produk' => 'Voal Test Hapus',
            'harga_jual' => 31000,
            'hpp' => 13000,
        ]);

        $response = $this->from('/products')->delete("/products/{$product->id}");

        $response->assertRedirectToRoute('products.index');

        $this->assertFalse(Product::query()->whereKey($product->id)->exists());
    }
}