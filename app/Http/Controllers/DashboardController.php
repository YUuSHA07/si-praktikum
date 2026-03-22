<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil data user yang sedang login
        $user = Auth::user();

        // 2. PROTEKSI FIRST LOGIN: 
        // Jika user baru pertama kali login, paksa ke halaman ganti password
        if ($user->is_first_login) {
            return redirect()->route('first.login.form');
        }

        // 3. Jika sudah aman, arahkan ke dashboard sesuai role
        // Pastikan file view ada di resources/views/dashboard/index.blade.php
        return view('dashboard.index', compact('user'));
    }
}