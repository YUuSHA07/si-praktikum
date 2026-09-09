<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use App\Models\Course;
use App\Models\Meeting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceController extends Controller
{
    public function index(int|string $meeting_id): View
    {
        $meeting = Meeting::with('course.students')->findOrFail($meeting_id);
        
        $existingAttendances = \App\Models\Attendance::where('meeting_id', $meeting_id)
                                ->get()
                                ->keyBy('student_id');

        return view('attendance.index', compact('meeting', 'existingAttendances'));
    }

    public function store(Request $request, int|string $meeting_id): RedirectResponse
    {
        $request->validate([
            'attendances' => 'required|array',
        ]);

        $meeting = Meeting::findOrFail($meeting_id);
        $today = now()->toDateString();

        foreach ($request->attendances as $studentId => $status) {
            \App\Models\Attendance::query()->updateOrCreate(
                [
                    'meeting_id' => $meeting->id, 
                    'student_id' => $studentId
                ],
                [
                    'status'          => $status,
                    'attendance_date' => $today
                ]
            );
        }

        return redirect()->back()->with('success', 'Presensi berhasil diperbarui!');
    }

    public function report(int|string $course_id): View
    {
        $course = Course::with(['students', 'meetings.attendances'])->findOrFail($course_id);
        $reportData = $this->calculateAttendanceReport($course);

        return view('attendance.report', [
            'course'   => $course,
            'meetings' => $reportData['meetings'],
            'report'   => $reportData['report']
        ]);
    }

    public function exportPdf(int|string $course_id)
    {
        $course = Course::findOrFail($course_id);
        $reportData = $this->calculateAttendanceReport($course);

        $pdf = Pdf::loadView('attendance.report_pdf', [
            'course'   => $course,
            'meetings' => $reportData['meetings'],
            'report'   => $reportData['report']
        ])->setPaper('a4', 'landscape'); // Landscape agar kolom per-pertemuan muat dengan rapi

        return $pdf->download('Rekap_Presensi_' . Str::slug($course->course_name) . '.pdf');
    }

    public function exportExcel(int|string $course_id): BinaryFileResponse
    {
        $course = Course::findOrFail($course_id);
        $reportData = $this->calculateAttendanceReport($course);

        return Excel::download(
            new AttendanceReportExport($course, $reportData['report'], $reportData['meetings']), 
            'Rekap_Presensi_' . Str::slug($course->course_name) . '.xlsx'
        );
    }

    /**
     * HELPER PRIVAT: Menghitung rekap presensi beserta rincian per-pertemuan
     */
    private function calculateAttendanceReport(Course $course)
    {
        $meetings = $course->meetings()->orderBy('meeting_number', 'asc')->get();
        $totalMeetings = $meetings->count();

        $report = $course->students->map(function ($student) use ($meetings, $totalMeetings) {
            $attendances = \App\Models\Attendance::where('student_id', $student->id)
                ->whereIn('meeting_id', $meetings->pluck('id'))
                ->get()
                ->keyBy('meeting_id');

            $perMeetingStatus = [];
            foreach ($meetings as $m) {
                $statusObj = $attendances->get($m->id);
                if ($statusObj) {
                    $st = strtoupper((string)$statusObj->status);
                    $perMeetingStatus[$m->id] = match($st) {
                        'HADIR' => 'H',
                        'SAKIT' => 'S',
                        'IZIN'  => 'I',
                        'TANPA KETERANGAN', 'ALPHA', 'TK', 'A' => 'TK', // Menghasilkan TK
                        default => substr($st, 0, 1) ?: '0'
                    };
                } else {
                    $perMeetingStatus[$m->id] = '0';
                }
            }

            $hadir = (int) $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['hadir', 'h']))->count();
            $sakit = (int) $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['sakit', 's']))->count();
            $izin  = (int) $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['izin', 'i']))->count();
            $alpha = (int) $attendances->filter(fn($a) => in_array(strtolower((string)$a->status), ['tanpa keterangan', 'alpha', 'tk', 'a']))->count();

            $percentage = $totalMeetings > 0 
                ? (int) round(($hadir / $totalMeetings) * 100) 
                : 0;

            return (object) [
                'id'                 => $student->id,
                'name'               => $student->name,
                'per_meeting_status' => $perMeetingStatus,
                'hadir'              => $hadir,
                'sakit'              => $sakit,
                'izin'               => $izin,
                'alpha'              => $alpha,
                'total_pertemuan'    => $totalMeetings,
                'percentage'         => $percentage,
            ];
        });

        return [
            'meetings' => $meetings,
            'report'   => $report
        ];
    }
}