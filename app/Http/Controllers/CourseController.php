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
    public function index(Request $request)
    {
        $activeSemester = Semester::where('is_active', true)->first();
        
        // Jika tidak ada semester aktif
        if (!$activeSemester) {
            return view('courses.index', ['courses' => collect(), 'activeSemester' => null]);
        }

        $user = Auth::user();
        $role = strtoupper($user->active_role);

        // Query dasar: Hanya kelas di semester aktif
        $query = Course::with(['dosen', 'aslab', 'laboran'])->where('semester_id', $activeSemester->id)->latest();

        // Logika Filter
        if ($role === 'MAHASISWA') {
            $query->whereHas('students', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($role === 'DOSEN') {
            $query->where('dosen_id', $user->id);
        } elseif ($role === 'ASLAB') {
            $query->where('aslab_id', $user->id);
        } elseif ($role === 'LABORAN') {
            // Cek parameter 'view' dari URL
            $viewType = $request->query('view', 'my_classes'); // Default: kelas milik dia sendiri

            if ($viewType === 'my_classes') {
                $query->where('laboran_id', $user->id);
            }
            // Jika view === 'all', query tidak di-where laboran_id, jadi tampil semua
        }

        $courses = $query->get();

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

    public function show($id)
    {
        // Eager loading sangat penting agar 'finalTask' tidak bernilai null di view
        $course = Course::with([
            'meetings.submissions', 
            'meetings.attendances', 
            'finalTask', // <--- Ini harus dipanggil
            'dosen', 
            'aslab', 
            'laboran'
        ])->findOrFail($id);

        return view('courses.show', compact('course'));
    }

    // Method untuk menampilkan daftar mahasiswa
    public function students(\App\Models\Course $course)
    {
        // Pastikan relasi students sudah diload dan diurutkan berdasarkan nama
        $students = $course->students()->orderBy('id', 'asc')->get();
        return view('attendance.students', compact('course', 'students'));
    }

    // Method untuk mengeluarkan mahasiswa dari kelas
// Method untuk mengeluarkan mahasiswa dari kelas
    public function removeStudent(Request $request, \App\Models\Course $course, \App\Models\User $student)
    {
        // Gunakan $request->user() sebagai ganti auth()->user()
        $user = $request->user();

        // Hanya Aslab, Laboran, atau Dosen yang boleh mengeluarkan
        if (in_array(strtoupper($user->role), ['ASLAB', 'LABORAN', 'DOSEN'])) {
            $course->students()->detach($student->id);
            return back()->with('success', 'Mahasiswa ' . $student->name . ' berhasil dikeluarkan dari kelas.');
        }
        
        return back()->with('error', 'Anda tidak memiliki hak akses untuk aksi ini.');
    }
}