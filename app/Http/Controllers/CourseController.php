<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Tambahkan ini agar Auth::id() dikenali

class CourseController extends Controller
{
    /**
     * Menampilkan daftar semua kelas pada semester yang sedang aktif.
     */
    public function index()
    {
        // Mengambil semester yang statusnya 'is_active' = true
        $activeSemester = Semester::where('is_active', true)->first();

        // Jika tidak ada semester aktif, berikan pesan error atau arahkan ke setting semester
        if (!$activeSemester) {
            return redirect()->back()->with('error', 'Tidak ada semester yang aktif saat ini.');
        }

        // Mengambil kelas yang ada di semester aktif beserta relasi user-nya
        $courses = Course::with(['dosen', 'aslab', 'laboran'])
            ->where('semester_id', $activeSemester->id)
            ->get();

        return view('courses.index', compact('courses', 'activeSemester'));
    }

    /**
     * Menyimpan kelas praktikum baru ke database.
     * Biasanya diakses oleh Laboran atau Dosen.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'course_name'     => 'required|string|max:255',
            'class_group'     => 'required|string|max:50',
            'target_semester' => 'required|integer',
            'dosen_id'        => 'required|exists:users,id',
            'aslab_id'        => 'required|exists:users,id',
        ]);

        // Cari semester yang sedang aktif
        $activeSemester = Semester::where('is_active', true)->first();

        if (!$activeSemester) {
            return redirect()->back()->with('error', 'Gagal membuat kelas: Semester aktif tidak ditemukan.');
        }

        // Simpan data kelas
        Course::create([
            'semester_id'     => $activeSemester->id,
            'course_name'     => $request->course_name,
            'class_group'     => $request->class_group,
            'target_semester' => $request->target_semester,
            'dosen_id'        => $request->dosen_id,
            'aslab_id'        => $request->aslab_id,
            'laboran_id'      => Auth::id(), // Menggunakan Facade Auth agar Intelephense tidak error
            'enrollment_code' => strtoupper(Str::random(6)), // Kode acak 6 digit, misal: AB12XY
        ]);

        return redirect()->route('courses.index')->with('success', 'Kelas praktikum baru berhasil dibuat!');
    }

    /**
     * Form untuk membuat kelas (opsional jika menggunakan modal di index)
     */
    public function create()
    {
        // Mengambil data user berdasarkan role untuk pilihan di form dropdown
        $dosens  = User::where('role', 'Dosen')->get();
        $aslabs  = User::where('role', 'Aslab')->get();

        return view('courses.create', compact('dosens', 'aslabs'));
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'enrollment_code' => 'required|string|exists:courses,enrollment_code',
        ]);

        // Cari kelas berdasarkan kode
        $course = Course::where('enrollment_code', $request->enrollment_code)->first();
        $user = Auth::user();

        // Cek apakah mahasiswa sudah bergabung di kelas ini sebelumnya
        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return redirect()->back()->with('error', 'Kamu sudah terdaftar di kelas ini.');
        }

        // Gabungkan mahasiswa ke kelas (Insert ke tabel pivot)
        $user->courses()->attach($course->id);

        return redirect()->route('courses.index')->with('success', 'Berhasil bergabung ke kelas: ' . $course->course_name);
    }
}