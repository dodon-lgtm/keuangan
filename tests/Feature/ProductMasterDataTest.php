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

    public function test_product_creation_stores_margin_profit(): void
    {
        // margin_profit tidak diinput user, harus terisi otomatis (harga_jual - hpp).
        $response = $this->post('/products', [
            'nama_produk' => 'Voal Margin Baru',
            'harga_jual' => 50000,
            'hpp' => 20000,
        ]);

        $response->assertRedirectToRoute('products.index');

        $this->assertDatabaseHas('products', [
            'nama_produk' => 'Voal Margin Baru',
            'margin_profit' => 30000,
        ]);

        $product = Product::query()->where('nama_produk', 'Voal Margin Baru')->firstOrFail();

        $this->assertSame(30000, $product->margin_profit);
        $this->assertEqualsWithDelta(60.0, $product->margin_percent, 0.001);
    }

    public function test_product_update_recalculates_margin_profit(): void
    {
        $product = Product::create([
            'nama_produk' => 'Voal Margin Edit',
            'harga_jual' => 30000,
            'hpp' => 12000,
        ]);

        $response = $this->from("/products/{$product->id}/edit")->put("/products/{$product->id}", [
            'nama_produk' => 'Voal Margin Edit',
            'harga_jual' => 29900,
            'hpp' => 16000,
        ]);

        $response->assertRedirectToRoute('products.index');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'margin_profit' => 13900,
        ]);

        $product->refresh();

        $this->assertEqualsWithDelta(46.49, $product->margin_percent, 0.01);
    }

    public function test_product_margin_percent_is_zero_without_harga_jual(): void
    {
        $product = Product::create([
            'nama_produk' => 'Hijab Bonus',
            'harga_jual' => 0,
            'hpp' => 5000,
        ]);

        $this->assertSame(-5000, $product->margin_profit);
        $this->assertSame(0.0, $product->margin_percent);
    }

    public function test_products_index_displays_margin_profit_and_percentage(): void
    {
        Product::create(['nama_produk' => 'Voal Margin A', 'harga_jual' => 50000, 'hpp' => 20000]);
        Product::create(['nama_produk' => 'Voal Margin B', 'harga_jual' => 40000, 'hpp' => 30000]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Margin Profit');
        $response->assertSee('Margin %');
        // Baris produk: margin 30000 (60.00%) dan margin 10000 (25.00%).
        $response->assertSee('Rp 30.000');
        $response->assertSee('60.00%');
        $response->assertSee('Rp 10.000');
        $response->assertSee('25.00%');
        // Footer rata-rata: margin 20000 dan margin % (60 + 25) / 2 = 42.50.
        $response->assertSee('Rp 20.000');
        $response->assertSee('42.50%');
    }
}
