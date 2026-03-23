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
    // Properti untuk menampung laporan progres import
    public $successCount = 0;
    public $failMessages = [];
    public $successMessages = [];

    public function model(array $row)
    {
        // 1. LOGIKA VALIDASI: Skip jika kolom ID di excel kosong
        if (!isset($row['id']) || empty($row['id'])) {
            return null;
        }

        // Ambil data dari baris excel berdasarkan header kolomnya
        $id = $row['id'];
        $email = $row['email'];
        $name = $row['name'] ?? 'No Name';

        // 2. LOGIKA DUPLIKASI: Cek apakah ID atau Email sudah terdaftar di database
        $existingUser = User::where('id', $id)->orWhere('email', $email)->first();

        if ($existingUser) {
            // Jika data sudah ada, catat pesan gagal dan hentikan proses untuk baris ini
            $this->failMessages[] = "Baris ID {$id} ({$name}) gagal: ID atau Email sudah terdaftar.";
            return null; 
        }

        // 3. LOGIKA ROLE: Membersihkan penulisan role (contoh: "admin " -> "Admin")
        $roleClean = Str::ucfirst(strtolower(trim($row['role'])));

        // 4. PEMBUATAN USER: Membuat instance model User baru
        $user = new User([
            'id'             => $id,
            'name'           => $name,
            'email'          => $email,
            
            /* PERUBAHAN DI SINI:
               Password tidak lagi mengambil dari $row['password'], 
               tapi langsung menggunakan nilai dari variabel $id.
            */
            'password'       => Hash::make($id), 
            
            'role'           => $roleClean,
            'is_first_login' => true,
        ]);

        // Tambahkan data ke laporan sukses untuk ditampilkan di view nanti
        $this->successMessages[] = "Baris ID {$id} ({$name}) berhasil didaftarkan. Password default adalah ID user.";
        $this->successCount++;

        return $user;
    }
}