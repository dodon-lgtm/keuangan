<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vendorhijabbandung.com'],
            [
                'name' => 'Admin Vendor Hijab Bandung',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]
        );
    }
}
