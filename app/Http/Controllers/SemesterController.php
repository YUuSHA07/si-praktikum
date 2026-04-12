<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    // Menampilkan daftar semester
    public function index()
    {
        $semesters = Semester::orderBy('created_at', 'desc')->get();
        return view('semesters.index', compact('semesters'));
    }

    // Menyimpan semester baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Semester::create([
            'name' => $request->name,
            'is_active' => false, // Default selalu false saat baru dibuat
        ]);

        return back()->with('success', 'Semester baru berhasil ditambahkan.');
    }

    // Mengupdate nama semester
    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $semester->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Nama semester berhasil diperbarui.');
    }

    // Menghapus semester
    public function destroy(Semester $semester)
    {
        if ($semester->is_active) {
            return back()->with('error', 'Tidak dapat menghapus semester yang sedang aktif!');
        }

        // Opsional: Cek apakah semester ini sudah dipakai di tabel courses sebelum dihapus
        // if ($semester->courses()->exists()) { return back()->with('error', 'Semester sedang digunakan.'); }

        $semester->delete();
        return back()->with('success', 'Semester berhasil dihapus.');
    }

    // MENGAKTIFKAN SEMESTER (Ini fungsi paling penting)
    public function setActive(Semester $semester)
    {
        // 1. Matikan semua semester yang sedang aktif
        Semester::where('is_active', true)->update(['is_active' => false]);

        // 2. Aktifkan semester yang dipilih
        $semester->update(['is_active' => true]);

        return back()->with('success', 'Semester ' . $semester->name . ' sekarang aktif!');
    }
}