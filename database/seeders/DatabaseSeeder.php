<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sales;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Akun Admin
        $adminUser = User::create([
            'name' => 'Administrator RuteSales',
            'email' => 'admin@rutesales.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Buat Akun Sales 1
        $salesUser1 = User::create([
            'name' => 'Budi Santoso (Sales)',
            'email' => 'budi@rutesales.test',
            'password' => Hash::make('password123'),
            'role' => 'sales',
        ]);

        Sales::create([
            'user_id' => $salesUser1->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'status' => 'active',
        ]);
    }
}