<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * TAMPILAN ASLAB: Daftar semua tugas masuk per pertemuan
     */
    public function index($meeting_id)
    {
        $meeting = Meeting::with('course.students')->findOrFail($meeting_id);
        
        $submissions = Submission::where('meeting_id', $meeting_id)
                        ->get()
                        ->keyBy('student_id');

        return view('submissions.index', compact('meeting', 'submissions'));
    }

    /**
     * TAMPILAN MAHASISWA: Halaman tunggal untuk kumpul/edit/revisi
     * Mengarah ke: mahasiswa.submissions.handler
     */
    public function manage(Meeting $meeting)
    {
        $submission = Submission::where('meeting_id', $meeting->id)
                                ->where('student_id', Auth::id())
                                ->with(['histories' => function($q) {
                                    $q->latest();
                                }])
                                ->first();

        return view('mahasiswa.submissions.handler', compact('meeting', 'submission'));
    }

    /**
     * PROSES: Simpan Tugas Baru
     */
    public function store(Request $request, Meeting $meeting)
    {
        $request->validate([
            'submission_link' => 'required|url',
        ]);

        // Cek existing untuk menghindari duplikasi
        $submission = Submission::where('meeting_id', $meeting->id)
                                ->where('student_id', Auth::id())
                                ->first();

        if ($submission) {
            return $this->update($request, $submission);
        }

        $now = now();
        Submission::create([
            'meeting_id'      => $meeting->id,
            'student_id'      => Auth::id(),
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'first_upload_at' => $now,
            'last_upload_at'  => $now,
            'aslab_status'    => 'PENDING', 
            'is_completed'    => false
        ]);

        return redirect()->route('courses.show', $meeting->course_id)
                         ->with('success', 'Tugas berhasil dikirim.');
    }

    /**
     * PROSES: Update atau Kirim Revisi
     */
    public function update(Request $request, $id)
    {
        // Menggunakan with('meeting') untuk memastikan relasi ikut terbawa
        $submission = Submission::with('meeting')->findOrFail($id);

        $submission->update([
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'aslab_status'    => 'PENDING', // Otomatis kembali ke PENDING
            'last_upload_at'  => now(),
        ]);

        $meeting = $submission->meeting;

        // Proteksi jika meeting tetap tidak ditemukan
        if (!$meeting) {
            return redirect()->back()->with('error', 'Relasi meeting tidak ditemukan. Periksa database.');
        }

        return redirect()->route('courses.show', $meeting->course_id)
                        ->with('success', 'Tugas perbaikan berhasil dikirim.');
    }

    /**
     * PROSES ASLAB: Memberi ACC atau REVISI
     */
    public function approve(Request $request, $id)
    {
        // Validasi status agar konsisten (Gunakan Huruf Kapital)
        $request->validate([
            'status' => 'required|in:ACC,REVISI',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $submission = Submission::where('id', $id)->lockForUpdate()->firstOrFail();

            // LOGIKA BARU: Jika statusnya REVISI, buat history otomatis
            if ($request->status === 'REVISI') {
                SubmissionHistory::create([
                    'submission_id' => $submission->id,
                    'drive_link'    => $submission->submission_link, // Simpan link lama sebelum diupdate mahasiswa
                    'feedback'      => $request->notes,             // Catatan dari aslab
                    'iteration'     => $submission->histories()->count() + 1,
                    'reviewed_by'   => Auth::id(),
                ]);
            }

            $submission->update([
                'aslab_status' => $request->status,
                'aslab_notes'  => $request->notes,
                'updated_at'   => now(),
            ]);

            return redirect()->back()->with('success', 'Status berhasil diperbarui!');
        });
    }

    /**
     * DASHBOARD MAHASISWA: Rekap semua tugas yang pernah dikumpul
     */
    public function mySubmissions()
    {
        $submissions = Submission::where('student_id', Auth::id())
            ->with(['meeting.course'])
            ->latest('last_upload_at')
            ->get();

        return view('mahasiswa.submissions.index', compact('submissions'));
    }
}