<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan halaman manajemen user
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        
        // Ambil input roles dari checkbox (berupa array)
        // Jika kosong (pertama kali buka), default isi semua role
        $selectedRoles = $request->input('roles', ['Mahasiswa', 'Dosen', 'Laboran', 'Aslab']);

        $query = User::query();

        // Filter Berdasarkan Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Berdasarkan Checkbox Role
        if (!empty($selectedRoles)) {
            $query->whereIn('role', $selectedRoles);
        }

        // Eksekusi Query
        $users = ($limit === 'all') ? $query->get() : $query->paginate($limit)->withQueryString();

        return view('laboran.users.index', compact('users', 'selectedRoles'));
    }

    /**
     * Logika Reset Password: Mengubah password kembali menjadi ID
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // Update password menjadi ID-nya sendiri
        // Ubah is_first_login menjadi true (1) agar sistem tahu user harus ganti pass
        $user->update([
            'password' => Hash::make($user->id),
            'is_first_login' => true
        ]);

        return back()->with('status', "Password user {$user->name} berhasil direset menggunakan ID.");
    }
}