<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Ambil role aktif dan ubah ke huruf kapital untuk perbandingan
        $activeRole = strtoupper($request->user()->active_role);
        $allowedRoles = array_map('strtoupper', $roles);

        // Cek apakah role user ada dalam daftar yang diizinkan
        if (!in_array($activeRole, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}