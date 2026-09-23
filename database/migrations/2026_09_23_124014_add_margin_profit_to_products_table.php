<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Margin profit (laba kotor) = harga_jual - hpp. Dibuat signed agar
            // produk dengan HPP di atas harga jual tetap tersimpan sebagai minus.
            $table->bigInteger('margin_profit')->default(0)->after('hpp');
        });

        // Isi margin profit untuk produk lama (harga_jual - hpp). Cast ke SIGNED
        // supaya kolom unsigned tidak error saat HPP lebih besar dari harga jual.
        DB::table('products')->update([
            'margin_profit' => DB::raw('CAST(harga_jual AS SIGNED) - CAST(hpp AS SIGNED)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('margin_profit');
        });
    }
};
