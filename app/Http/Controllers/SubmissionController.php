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
     * DASHBOARD MAHASISWA: Rekap semua tugas (termasuk yang belum kumpul)
     */
    public function mySubmissions()
    {
        $userId = Auth::id();

        // 1. Ambil semua Pertemuan dari kelas yang diikuti mahasiswa
        $meetings = \App\Models\Meeting::whereHas('course.students', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->with(['course', 'submissions' => function($q) use ($userId) {
            $q->where('student_id', $userId);
        }])->get();

        // 2. Ambil semua Tugas Final dari kelas yang diikuti mahasiswa
        $finalTasks = \App\Models\FinalTask::whereHas('course.students', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->with(['course', 'submissions' => function($q) use ($userId) {
            $q->where('student_id', $userId)->where('is_final', true);
        }])->get();

        $allTasks = collect();

        // 3. Masukkan pertemuan ke koleksi
        foreach ($meetings as $meeting) {
            $allTasks->push((object)[
                'is_final'   => false,
                'course'     => $meeting->course,
                'title'      => $meeting->title ?? 'Tugas Tanpa Judul',
                'number'     => 'Pertemuan ' . $meeting->meeting_number,
                'deadline'   => $meeting->deadline,
                'submission' => $meeting->submissions->first(), // Ambil submission jika ada
            ]);
        }

        // 4. Masukkan tugas final ke koleksi
        foreach ($finalTasks as $ft) {
            $allTasks->push((object)[
                'is_final'   => true,
                'course'     => $ft->course,
                'title'      => 'Laporan Final',
                'number'     => 'TUGAS FINAL',
                'deadline'   => $ft->deadline,
                'submission' => $ft->submissions->first(), // Ambil submission jika ada
            ]);
        }

        // 5. Urutkan berdasarkan deadline terdekat
        $allTasks = $allTasks->sortByDesc('deadline');

        return view('mahasiswa.submissions.index', compact('allTasks'));
    }

    /**
     * UPDATE DEADLINE PERTEMUAN (Baru ditambahkan)
     */
    public function updateDeadline(Request $request, $id)
    {
        $request->validate([
            'deadline' => 'required|date'
        ]);

        $meeting = Meeting::findOrFail($id);
        $meeting->update(['deadline' => $request->deadline]);

        return redirect()->back()->with('success', 'Batas waktu pertemuan berhasil diperbarui!');
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


    /**
     * Menampilkan daftar tugas pending dengan sistem Hierarki Waterfall
     * Alur: ASLAB -> LABORAN (Jika Normal) -> DOSEN (Jika Final)
     */
    public function pending(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->active_role);

        // Eager Load untuk performa
        $query = \App\Models\Submission::with(['student', 'meeting.course', 'finalTask.course']);

        if ($role === 'ASLAB') {
            /**
             * 1. ASLAB: Garda terdepan. 
             * Melihat semua tugas (Biasa & Final) yang dikirim mahasiswa di kelasnya.
             */
            $query->where('aslab_status', 'PENDING')
                ->where(function($q) use ($user) {
                    $q->whereHas('meeting.course', fn($c) => $c->where('aslab_id', $user->id))
                        ->orWhereHas('finalTask.course', fn($c) => $c->where('aslab_id', $user->id));
                });
        } 
        elseif ($role === 'LABORAN') {
            /**
             * 2. LABORAN: Filter kedua.
             * Hanya tampil jika ASLAB sudah ACC (aslab_status = 'ACC').
             */
            $query->where('aslab_status', 'ACC')
                ->where('laboran_status', 'PENDING');
        } 
        elseif ($role === 'DOSEN') {
            /**
             * 3. DOSEN: Filter akhir (Hanya untuk Final Task).
             * Hanya tampil jika: ASLAB ACC + LABORAN ACC + JENIS FINAL.
             */
            $query->where('aslab_status', 'ACC')
                ->where('laboran_status', 'ACC')
                ->where('dosen_status', 'PENDING')
                ->where('is_final', true) // Hanya untuk tugas yang is_final = 1
                ->whereHas('finalTask.course', function($q) use ($user) {
                    $q->where('dosen_id', $user->id);
                });
        }

        $submissions = $query->latest()->get();

        return view('submissions.pending', compact('submissions'));
    }
}