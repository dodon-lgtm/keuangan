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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nama_brand');
            $table->string('no_whatsapp');
            $table->string('domisili');
            $table->enum('sumber', ['Instagram Organik', 'Meta Ads', 'CRM Whatsapp']);
            $table->date('tanggal_masuk_chat');
            $table->date('tanggal_order_pertama')->nullable();
            $table->enum('status_pelanggan', ['new', 'repeat'])->default('new');
            $table->enum('segment', ['A', 'B', 'C']);
            $table->text('catatan')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
