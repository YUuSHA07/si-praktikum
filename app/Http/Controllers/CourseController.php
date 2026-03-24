<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Menampilkan daftar kelas berdasarkan Role User
     */
    public function index()
    {
        // 1. Ambil semester yang sedang aktif
        $activeSemester = Semester::where('is_active', true)->first();
        
        // Jika tidak ada semester aktif, buat koleksi kosong agar tidak error
        if (!$activeSemester) {
            return view('courses.index', [
                'courses' => collect([]),
                'activeSemester' => (object)['name' => 'Tidak Ada Semester Aktif']
            ]);
        }

        $user = Auth::user();

        // 2. Filter data berdasarkan Role
        if ($user->role === 'Mahasiswa') {
            // Mahasiswa hanya melihat kelas yang sudah di-JOIN (melalui tabel pivot)
            $courses = $user->courses()
                            ->where('semester_id', $activeSemester->id)
                            ->with(['dosen', 'aslab']) // Eager loading agar ringan
                            ->get();
        } elseif ($user->role === 'Laboran') {
            // Laboran melihat semua kelas di semester aktif
            $courses = Course::where('semester_id', $activeSemester->id)
                            ->with(['dosen', 'aslab'])
                            ->get();
        } else {
            // Dosen & Aslab melihat kelas di mana mereka ditugaskan
            $courses = Course::where('semester_id', $activeSemester->id)
                            ->where(function($query) use ($user) {
                                $query->where('dosen_id', $user->id)
                                      ->orWhere('aslab_id', $user->id);
                            })
                            ->with(['dosen', 'aslab'])
                            ->get();
        }

        return view('courses.index', compact('courses', 'activeSemester'));
    }

    /**
     * Form buat kelas (Hanya Laboran/Dosen)
     */
    public function create()
    {
        $dosens = User::where('role', 'Dosen')->get();
        $aslabs = User::where('role', 'Aslab')->get();
        return view('courses.create', compact('dosens', 'aslabs'));
    }

    /**
     * Simpan kelas baru & Generate Kode Otomatis
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'class_group' => 'required|string|max:50',
            'target_semester' => 'required|integer',
            'dosen_id' => 'required|exists:users,id',
            'aslab_id' => 'required|exists:users,id',
        ]);

        $activeSemester = Semester::where('is_active', true)->first();

        // Pencegahan Double Insert manual
        $exists = Course::where('course_name', $request->course_name)
            ->where('class_group', $request->class_group)
            ->where('semester_id', $activeSemester->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Kelas ini sudah ada!');
        }

        Course::create([
            'semester_id' => $activeSemester->id,
            'course_name' => $request->course_name,
            'class_group' => $request->class_group,
            'target_semester' => $request->target_semester,
            'dosen_id' => $request->dosen_id,
            'aslab_id' => $request->aslab_id,
            'enrollment_code' => strtoupper(Str::random(6)), // Generate kode 6 digit
        ]);

        return redirect()->route('courses.index')->with('success', 'Kelas berhasil dibuat!');
    }

    /**
     * Fitur Join Kelas untuk Mahasiswa
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'enrollment_code' => 'required|string|exists:courses,enrollment_code',
        ]);

        $course = Course::where('enrollment_code', $request->enrollment_code)->first();
        $user = Auth::user();

        // Cek jika sudah pernah join
        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return redirect()->back()->with('error', 'Anda sudah bergabung di kelas ini.');
        }

        // Attach ke tabel pivot course_user
        $user->courses()->attach($course->id);

        return redirect()->route('courses.index')->with('success', 'Berhasil bergabung ke kelas ' . $course->course_name);
    }
}