<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'akun1@gmail.com'],
            [
                'name' => 'Akun 1',
                'password' => '12345678',
            ]
        );
    }
}
