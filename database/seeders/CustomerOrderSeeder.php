<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data demo: pelanggan (customer) beserta order dan item order-nya.
 *
 * Tanggal order dibuat relatif terhadap bulan berjalan (10 bulan terakhir
 * sampai bulan ini) sehingga hasil penjumlahan selalu terlihat di Dashboard &
 * Laporan tanpa bergantung pada tahun berjalan, dan otomatis melewati batas
 * tahun sehingga filter "Rentang Kustom" (mis. Nov - Jan) juga ada isinya.
 *
 * nominal setiap order = SUM(order_items.subtotal), sama seperti alur form
 * order di aplikasi (lihat OrderObserver::recalcNominal).
 *
 * Seeder ini idempoten: order di-key berdasarkan (customer_id, tanggal) dan
 * item hanya dibuat ketika order-nya benar-benar baru dibuat.
 */
class CustomerOrderSeeder extends Seeder
{
    /**
     * Pelanggan demo: [nama_lengkap, nama_brand, no_whatsapp, domisili,
     * sumber, email, bulan_masuk_chat].
     *
     * @var array<int, array<int, mixed>>
     */
    private const CUSTOMERS = [
        ['Siti Nurhaliza', 'Hijab Siti', '081234500001', 'Bandung', Customer::SUMBER_META_ADS, 'siti@example.com', 12],
        ['Dewi Lestari', 'Dely Hijab', '081234500002', 'Cimahi', Customer::SUMBER_INSTAGRAM_ORGANIK, 'dewi@example.com', 11],
        ['Rina Marlina', 'Rina Collection', '081234500003', 'Garut', Customer::SUMBER_CRM_WHATSAPP, 'rina@example.com', 11],
        ['Nur Aisyah', 'Aisyah Store', '081234500004', 'Tasikmalaya', Customer::SUMBER_META_ADS, 'aisyah@example.com', 10],
        ['Fitri Handayani', 'Fitri Hijab', '081234500005', 'Bekasi', Customer::SUMBER_INSTAGRAM_ORGANIK, 'fitri@example.com', 8],
        ['Anisa Rahmawati', 'Anisa Modest', '081234500006', 'Jakarta', Customer::SUMBER_META_ADS, 'anisa@example.com', 7],
        ['Maya Sari', 'Maya Scarves', '081234500007', 'Surabaya', Customer::SUMBER_CRM_WHATSAPP, 'maya@example.com', 5],
        ['Lina Kusuma', 'Lina Hijab', '081234500008', 'Yogyakarta', Customer::SUMBER_INSTAGRAM_ORGANIK, 'lina@example.com', 3],
    ];

    /**
     * Jadwal order demo: 10 order per bulan pada tanggal berikut, untuk 10
     * bulan terakhir sampai bulan ini. Tanggal yang jatuh di masa depan
     * dilewati sehingga bulan berjalan hanya berisi order sampai hari ini.
     *
     * @var array<int, int>
     */
    private const ORDER_DAYS = [2, 5, 8, 11, 14, 17, 20, 23, 26, 29];

    /**
     * Jumlah pcs per order (diulang mengikuti index order).
     *
     * @var array<int, int>
     */
    private const ORDER_PCS = [12, 20, 8, 30, 15, 25, 10, 18, 6, 22];

    /**
     * Ongkir per order (diulang mengikuti index order).
     *
     * @var array<int, int>
     */
    private const ORDER_ONGKIR = [0, 18000, 22000, 25000, 20000, 0, 25000, 18000, 22000, 30000];

    public function run(): void
    {
        // Produk dibutuhkan sebagai sumber harga jual & HPP (snapshot).
        if (Product::query()->count() === 0) {
            $this->call(ProductSeeder::class);
        }

        $products = Product::query()->orderBy('id')->get()->values();

        if ($products->isEmpty()) {
            return;
        }

        $customers = $this->seedCustomers();

        $tipeBayar = Order::TIPES;
        $jenisOrder = Order::JENISES;
        $metodeBayar = Order::METODES;
        $picAdmin = ['Admin A', 'Admin B'];

        // 11 bulan x 10 order: volume yang cukup agar hasil penjumlahan
        // (omset, HPP, ongkir, net profit) proporsional dengan biaya iklan.
        foreach (range(10, 0) as $monthsAgo) {
            foreach (self::ORDER_DAYS as $slot => $day) {
                $index = ($monthsAgo * count(self::ORDER_DAYS)) + $slot;
                $variant = $monthsAgo + $slot;

                $this->seedOrder(
                    $customers[$index % count($customers)],
                    $products[$index % $products->count()],
                    $monthsAgo,
                    $day,
                    self::ORDER_PCS[$variant % count(self::ORDER_PCS)],
                    self::ORDER_ONGKIR[$variant % count(self::ORDER_ONGKIR)],
                    $tipeBayar[$variant % count($tipeBayar)],
                    $jenisOrder[$variant % count($jenisOrder)],
                    $metodeBayar[$variant % count($metodeBayar)],
                    $picAdmin[$variant % count($picAdmin)]
                );
            }
        }
    }

    /**
     * Buat pelanggan demo bila belum ada (idempoten berdasarkan no_whatsapp).
     *
     * @return array<int, Customer>
     */
    private function seedCustomers(): array
    {
        $customers = [];

        foreach (self::CUSTOMERS as $index => $row) {
            [$nama, $brand, $wa, $domisili, $sumber, $email, $bulanMasuk] = $row;

            $customers[$index] = Customer::firstOrCreate(
                ['no_whatsapp' => $wa],
                [
                    'nama_lengkap' => $nama,
                    'nama_brand' => $brand,
                    'domisili' => $domisili,
                    'sumber' => $sumber,
                    'email' => $email,
                    'tanggal_masuk_chat' => now()->startOfMonth()->subMonths($bulanMasuk)->addDays(2)->toDateString(),
                    'catatan' => 'Pelanggan demo seeder.',
                ]
            );
        }

        return $customers;
    }

    /**
     * Buat satu order beserta item-nya; lewati bila order (customer + tanggal)
     * sudah pernah dibuat agar seeder aman dijalankan berulang.
     */
    private function seedOrder(
        Customer $customer,
        Product $product,
        int $monthsAgo,
        int $day,
        int $pcs,
        int $ongkir,
        string $tipeBayar,
        string $jenisOrder,
        string $metodeBayar,
        string $picAdmin
    ): void {
        $tanggal = $this->orderDate($monthsAgo, $day);

        // Bulan berjalan: tanggal setelah hari ini belum terjadi.
        if ($tanggal->isFuture()) {
            return;
        }

        // Kolom `tanggal` menyimpan "Y-m-d 00:00:00", sehingga pencarian
        // memakai whereDate agar order (customer + tanggal) yang sama tidak
        // dibuat dua kali ketika seeder dijalankan ulang.
        $order = Order::query()
            ->where('customer_id', $customer->id)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->first();

        if ($order !== null) {
            return;
        }

        $order = Order::create([
            'customer_id' => $customer->id,
            'tanggal' => $tanggal->toDateString(),
            'nominal' => 0,
            'tipe_bayar' => $tipeBayar,
            'jenis_order' => $jenisOrder,
            'metode_bayar' => $metodeBayar,
            'pic_admin' => $picAdmin,
            'ongkir' => $ongkir,
            'ukuran_hijab' => 'Standar',
        ]);

        $order->orderItems()->create([
            'product_id' => $product->id,
            'jumlah_pcs' => $pcs,
            'harga_satuan' => $product->harga_jual,
            'hpp_satuan' => $product->hpp,
            'subtotal' => $product->harga_jual * $pcs,
        ]);

        // nominal = SUM(order_items.subtotal): inti "hasil penjumlahan".
        OrderObserver::recalcNominal($order);
    }

    /**
     * Tanggal order = tanggal ke-$day di bulan N ke belakang. Bila tanggal
     * tersebut melewati akhir bulan (mis. 29 Februari), dipakai hari terakhir
     * bulan itu supaya order tidak bergeser ke bulan berikutnya.
     */
    private function orderDate(int $monthsAgo, int $day): Carbon
    {
        $startOfMonth = now()->startOfMonth()->subMonths($monthsAgo);
        $date = $startOfMonth->copy()->addDays($day - 1);

        return ($date->month === $startOfMonth->month ? $date : $startOfMonth->copy()->endOfMonth())
            ->startOfDay();
    }
}
