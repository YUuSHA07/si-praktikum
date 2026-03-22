<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UserImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    // Properti untuk menampung laporan
    public $successCount = 0;
    public $failMessages = [];
    public $successMessages = [];

    public function model(array $row)
    {
        // 1. Skip jika ID kosong
        if (!isset($row['id']) || empty($row['id'])) {
            return null;
        }

        $id = $row['id'];
        $email = $row['email'];
        $name = $row['name'] ?? 'No Name';

        // 2. Cek apakah ID atau Email sudah ada di database
        $existingUser = User::where('id', $id)->orWhere('email', $email)->first();

        if ($existingUser) {
            // Jika sudah ada, masukkan ke pesan gagal dan SKIP (return null)
            $this->failMessages[] = "Baris ID {$id} ({$name}) gagal: ID atau Email sudah terdaftar.";
            return null; 
        }

        // 3. Jika belum ada, buat user baru
        $roleClean = Str::ucfirst(strtolower(trim($row['role'])));

        $user = new User([
            'id'             => $id,
            'name'           => $name,
            'email'          => $email,
            'password'       => Hash::make($row['password'] ?? 'password123'),
            'role'           => $roleClean,
            'is_first_login' => true,
        ]);

        // Tambahkan ke laporan sukses
        $this->successMessages[] = "Baris ID {$id} ({$name}) berhasil didaftarkan.";
        $this->successCount++;

        return $user;
    }
}