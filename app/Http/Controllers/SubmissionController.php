<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    /**
     * TAMPILAN ASLAB/LABORAN: Daftar semua tugas masuk per pertemuan (Index)
     */
    public function index($meeting_id)
    {
        $meeting = Meeting::with(['course.students'])->findOrFail($meeting_id);
        
        $submissions = Submission::where('meeting_id', $meeting_id)
                        ->with('histories')
                        ->get()
                        ->keyBy('student_id');

        return view('submissions.index', compact('meeting', 'submissions'));
    }

    /**
     * TAMPILAN ASLAB/LABORAN: Halaman Review Spesifik (Handler)
     */
    public function handler(Submission $submission)
    {
        $submission->load(['student', 'histories' => function($q) {
            $q->latest();
        }]);

        return view('submissions.handler', compact('submission'));
    }

    /**
     * TAMPILAN MAHASISWA: Halaman kumpul/edit tugas
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
     * PROSES: Simpan Tugas Baru (Mahasiswa)
     */
    public function store(Request $request, $meeting_id)
    {
        $request->validate([
            'submission_link' => 'required|url',
        ]);

        $meeting = Meeting::findOrFail($meeting_id);

        $submission = Submission::where('meeting_id', $meeting->id)
                                ->where('student_id', Auth::id())
                                ->first();

        // Jika sudah ada record, alihkan ke fungsi update
        if ($submission) {
            return $this->update($request, $submission->id);
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
            'laboran_status'  => 'PENDING',
            'is_completed'    => false
        ]);

        return redirect()->route('courses.show', $meeting->course_id)
                        ->with('success', 'Tugas berhasil dikirim.');
    }

    /**
     * PROSES: Update atau Kirim Revisi (Mahasiswa)
     */
    public function update(Request $request, $id)
    {
        $submission = Submission::with('meeting')->where('id', $id)
                                ->where('student_id', Auth::id())
                                ->firstOrFail();

        // LOGIKA: Cek status Aslab saat ini
        $currentAslabStatus = strtoupper($submission->aslab_status);
        $newAslabStatus = 'PENDING';
        $newLaboranStatus = 'PENDING';

        // Jika Aslab sudah pernah memberikan ACC, jangan turunkan ke PENDING lagi.
        // Ini agar tugas langsung bisa diperiksa Laboran tanpa lewat Aslab lagi.
        if ($currentAslabStatus === 'ACC') {
            $newAslabStatus = 'ACC';
        }

        $submission->update([
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'aslab_status'    => $newAslabStatus, 
            'laboran_status'  => $newLaboranStatus,
            'last_upload_at'  => now(),
        ]);

        return redirect()->route('courses.show', $submission->meeting->course_id)
                        ->with('success', 'Tugas perbaikan berhasil dikirim.');
    }

    /**
     * PROSES ASLAB/LABORAN: Memberi ACC atau REVISI (Approve)
     */
    public function approve(Request $request, $id)
    {
        // Standarisasi status ke uppercase untuk pengecekan validasi
        $statusInput = strtoupper($request->status);

        $request->validate([
            'status'   => 'required|in:ACC,REVISI',
            'feedback' => 'required_if:status,REVISI' 
        ]);

        $submission = Submission::findOrFail($id);
        $user = Auth::user();
        $role = strtoupper($user->role);
        $now = now();

        if ($role === 'ASLAB') {
            if ($statusInput === 'ACC') {
                $submission->aslab_status = 'ACC';
                $submission->aslab_acc_at = $now;
            } else {
                // Jika Aslab REVISI, simpan ke history dan reset semua level di bawahnya
                $this->createHistory($submission, $request->feedback);
                $submission->aslab_status = 'REVISI';
                $submission->aslab_acc_at = null;
                $submission->laboran_status = 'PENDING';
                $submission->laboran_acc_at = null;
            }
        } 
        
        elseif ($role === 'LABORAN') {
            // Proteksi: Laboran hanya bisa akses jika Aslab sudah ACC
            if (strtoupper($submission->aslab_status) !== 'ACC') {
                return back()->with('error', 'Menunggu verifikasi Asisten Laboratorium.');
            }

            if ($statusInput === 'ACC') {
                $submission->laboran_status = 'ACC';
                $submission->laboran_acc_at = $now;
            } else {
                // LOGIKA KRUSIAL: Jika Laboran REVISI, aslab_status tetap 'ACC'
                $this->createHistory($submission, $request->feedback);
                $submission->laboran_status = 'REVISI';
                $submission->laboran_acc_at = null;
                
                // Note: aslab_status tidak diubah agar tetap ACC (Hijau)
            }
        }

        // Hitung apakah tugas sudah benar-benar selesai (Double ACC)
        $submission->is_completed = (strtoupper($submission->aslab_status) === 'ACC' && strtoupper($submission->laboran_status) === 'ACC');
        $submission->save();

        return redirect()->route('submissions.index', $submission->meeting_id)
                        ->with('success', 'Status '. $statusInput .' berhasil disimpan.');
    }

    /**
     * DASHBOARD MAHASISWA: Rekap semua tugas (My Submissions)
     */
    public function mySubmissions()
    {
        $submissions = Submission::where('student_id', Auth::id())
            ->with(['meeting.course'])
            ->latest('last_upload_at')
            ->get();

        return view('mahasiswa.submissions.index', compact('submissions'));
    }

    /**
     * HELPER: Simpan Riwayat Revisi ke tabel SubmissionHistory
     */
    private function createHistory($submission, $notes) {
        return SubmissionHistory::create([
            'submission_id' => $submission->id,
            'drive_link'    => $submission->submission_link,
            'feedback'      => $notes,
            'iteration'     => $submission->histories()->count() + 1,
            'reviewed_by'   => Auth::id(),
        ]);
    }
}