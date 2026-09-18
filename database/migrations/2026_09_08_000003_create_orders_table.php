<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained();

            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('nominal')->default(0);
            $table->enum('tipe_bayar', ['Full Payment', 'DP', 'Pelunasan']);
            $table->enum('jenis_order', ['Custom Design', 'Ready Stock']);
            $table->enum('metode_bayar', ['Transfer Bank', 'QRIS', 'Cash']);
            $table->string('pic_admin');
            $table->text('link_desain')->nullable();
            $table->unsignedBigInteger('ongkir')->default(0);
            $table->text('alamat_kirim')->nullable();
            $table->string('ukuran_hijab')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
