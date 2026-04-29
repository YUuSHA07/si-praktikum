<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void // <--- Pastikan namanya "run"
    {
        // 1. Akun Laboran
        User::create([
            'id' => '123',
            'name' => 'Admin Laboratorium',
            'email' => 'laboran@uinsu.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'laboran',
        ]);

    }
}