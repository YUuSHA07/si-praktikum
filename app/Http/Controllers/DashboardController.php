<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Mendapatkan data user yang sedang login
        $user = Auth::user();

        // Kita arahkan ke file index.blade.php di dalam folder dashboard
        return view('dashboard.index', compact('user'));
    }
}