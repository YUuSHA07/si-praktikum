<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    
    public function show($id)
    {
        // 1. Ambil data course beserta relasinya dalam SATU query saja
        // Kita tambahkan 'meetings.attendances' dengan filter agar hanya mengambil absen milik user yang sedang login
        $course = Course::with([
            'laboran', 
            'dosen', 
            'aslab', 
            'meetings.attendances' => function($query) {
                $query->where('student_id', Auth::id());
            }
        ])->findOrFail($id);

        // 2. Cek Akses khusus Mahasiswa
        if (Auth::user()->role === 'Mahasiswa') {
            $isEnrolled = Auth::user()->courses()->where('course_id', $id)->exists();
            if (!$isEnrolled) {
                abort(403, 'Anda belum terdaftar di kelas ini.');
            }
        }

        return view('courses.show', compact('course'));
    }

    // Fungsi store yang sudah kita buat sebelumnya
    public function store(Request $request, $course_id)
    {
        $request->validate([
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module_drive_link' => 'nullable|url',
            'deadline' => 'nullable|date',
        ]);

        $exists = Meeting::where('course_id', $course_id)
                         ->where('meeting_number', $request->meeting_number)
                         ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Pertemuan ke-' . $request->meeting_number . ' sudah ada.');
        }

        Meeting::create([
            'course_id' => $course_id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
            'module_drive_link' => $request->module_drive_link,
            'deadline' => $request->deadline,
        ]);

        return redirect()->back()->with('success', 'Pertemuan berhasil ditambahkan!');
    }
}