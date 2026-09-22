<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Live database memiliki kolom `domisili` & `segment` (hasil perubahan
     * manual di luar migrasi) sebagai NOT NULL tanpa default, sehingga setiap
     * Customer::create() dari form gagal dengan error
     * "Field 'domisili' doesn't have a default value".
     *
     * Migrasi ini: menambahkan kolomnya bila belum ada (instalasi baru) dan
     * melonggarkan keduanya menjadi nullable (database lama).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'domisili')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('domisili')->nullable()->after('no_whatsapp');
            });
        }

        if (! Schema::hasColumn('customers', 'segment')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->enum('segment', ['A', 'B', 'C'])->nullable()->after('tanggal_masuk_chat');
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('domisili')->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->enum('segment', ['A', 'B', 'C'])->nullable()->change();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('customers')
            ->whereNull('domisili')
            ->update(['domisili' => '']);

        \Illuminate\Support\Facades\DB::table('customers')
            ->whereNull('segment')
            ->update(['segment' => 'A']);

        Schema::table('customers', function (Blueprint $table) {
            $table->string('domisili')->nullable(false)->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->enum('segment', ['A', 'B', 'C'])->nullable(false)->change();
        });
    }
};
