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
            'id' => 'LAB-001',
            'name' => 'Admin Laboratorium',
            'email' => 'laboran@uinsu.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'Laboran',
        ]);

        // 2. Akun Dosen
        User::create([
            'id' => 'NIP-19850101', 
            'name' => 'Dr. Ilka Zufria, M.Kom',
            'email' => 'ilkazufria@uinsu.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'Dosen',
        ]);

        // 3. Akun Aslab
        User::create([
            'id' => 'ASL-2026-01',
            'name' => 'Asisten Praktikum AI',
            'email' => 'aslab@uinsu.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'Aslab',
        ]);

        // 4. Akun Mahasiswa
        User::create([
            'id' => '0701213000',
            'name' => 'Mahasiswa Praktikan',
            'email' => 'mahasiswa@uinsu.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'Mahasiswa',
        ]);
    }
}