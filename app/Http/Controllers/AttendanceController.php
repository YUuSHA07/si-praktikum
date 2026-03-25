<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Halaman untuk Aslab mengabsen mahasiswa
    public function index($meeting_id)
    {
        $meeting = Meeting::with('course.students')->findOrFail($meeting_id);
        
        // Ambil data absensi yang sudah ada untuk meeting ini
        $existingAttendances = \App\Models\Attendance::where('meeting_id', $meeting_id)
                                ->get()
                                ->keyBy('student_id'); // Kunci berdasarkan student_id agar mudah dipanggil di blade

        return view('attendance.index', compact('meeting', 'existingAttendances'));
    }

    // Simpan data absen massal
    public function store(Request $request, $meeting_id)
    {
        $meeting = Meeting::findOrFail($meeting_id);
        $today = \Carbon\Carbon::now()->toDateString();

        foreach ($request->attendances as $studentId => $status) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'meeting_id' => $meeting_id, 
                    'student_id' => $studentId
                ],
                [
                    'status' => $status,
                    'attendance_date' => $today
                ]
            );
        }

        return redirect()->back()->with('success', 'Presensi berhasil diperbarui!');
    }

    public function report($course_id)
    {
        $course = Course::with(['students', 'meetings.attendances'])->findOrFail($course_id);

        // Menghitung rekap untuk setiap mahasiswa
        $report = $course->students->map(function ($student) use ($course) {
            $attendances = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('meeting_id', $course->meetings->pluck('id'))
                ->get();

            return (object) [
                'id' => $student->id,
                'name' => $student->name,
                'hadir' => $attendances->where('status', 'Hadir')->count(),
                'sakit' => $attendances->where('status', 'Sakit')->count(),
                'izin' => $attendances->where('status', 'Izin')->count(),
                'alpha' => $attendances->where('status', 'Alpha')->count(),
                'total_pertemuan' => $course->meetings->count(),
                // Hitung persentase kehadiran
                'percentage' => $course->meetings->count() > 0 
                    ? round(($attendances->where('status', 'Hadir')->count() / $course->meetings->count()) * 100) 
                    : 0
            ];
        });

        return view('attendance.report', compact('course', 'report'));
    }
}