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
        $selectedRoles = $request->input('roles', []);
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $query = User::query();

        // --- LOGIKA TAMBAHAN DISINI ---
        $queryRoles = $selectedRoles;
        if (in_array('Mahasiswa', $selectedRoles)) {
            // Jika Mahasiswa dipilih, masukkan juga Aslab ke dalam pencarian database
            if (!in_array('Aslab', $queryRoles)) {
                $queryRoles[] = 'Aslab';
            }
        }
        // ------------------------------

        if (!empty($queryRoles)) {
            $query->whereIn('role', $queryRoles);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

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

    public function makeAslab(Request $request, \App\Models\User $user)
    {
        // 1. Pastikan pengecekan menggunakan active_role (karena kita sudah ganti sistemnya)
        if (strtoupper($request->user()->active_role) === 'LABORAN') {
            
            // 2. Ubah role langsung pada atribut modelnya (melewati proteksi mass-assignment)
            $user->role = 'Aslab';
            
            // 3. Simpan perubahan ke database
            $user->save();
            
            return back()->with('success', 'Mahasiswa ' . $user->name . ' berhasil diangkat menjadi Aslab!');
        }
        
        return back()->with('error', 'Akses ditolak. Hanya Laboran yang dapat melakukan aksi ini.');
    }

    public function revokeAslab(Request $request, \App\Models\User $user)
    {
        // Pastikan hanya laboran yang bisa akses
        if (strtoupper($request->user()->active_role) === 'LABORAN') {
            
            $user->role = 'Mahasiswa';
            $user->save();

            // Bersihkan session active_role jika user tersebut sedang login
            // (opsional, tapi bagus untuk keamanan)
            session()->forget('active_role');

            return back()->with('success', 'Jabatan Aslab ' . $user->name . ' telah dicabut.');
        }
        
        return back()->with('error', 'Akses ditolak.');
    }
}