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
     * TAMPILAN ASLAB/LABORAN: Daftar semua tugas masuk per pertemuan (Index)
     */
    public function index($meeting_id)
    {
        // Load meeting beserta mahasiswa yang terdaftar di course tersebut
        $meeting = Meeting::with(['course.students'])->findOrFail($meeting_id);
        
        // Ambil semua submission untuk meeting ini dan index berdasarkan student_id
        $submissions = Submission::where('meeting_id', $meeting_id)
                        ->with('histories')
                        ->get()
                        ->keyBy('student_id');

        return view('submissions.index', compact('meeting', 'submissions'));
    }

    /**
     * TAMPILAN ASLAB/LABORAN: Halaman Review Spesifik (Handler)
     * Menggantikan fungsi modal agar review lebih fokus
     */
    public function handler(Submission $submission)
    {
        // Eager load data mahasiswa dan riwayat revisi (terbaru di atas)
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

        // Jika sudah ada, arahkan ke update (revisi)
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
            'aslab_status'    => 'Pending', 
            'laboran_status'  => 'Pending',
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

        $submission->update([
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            // Reset status agar Aslab tahu ada revisi masuk
            'aslab_status'    => 'Pending', 
            'laboran_status'  => 'Pending',
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
        $request->validate([
            'status' => 'required|in:ACC,Revisi',
            'notes'  => 'required_if:status,Revisi' 
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $submission = Submission::findOrFail($id);
                $user = Auth::user(); 
                $now = now();

                if ($user->role === 'ASLAB') {
                    if ($request->status === 'Revisi') {
                        $this->createHistory($submission, $request->notes);
                        $submission->aslab_status = 'Revisi';
                        $submission->aslab_acc_at = null;
                    } else {
                        $submission->aslab_status = 'ACC';
                        $submission->aslab_acc_at = $now;
                    }
                } 
                elseif ($user->role === 'LABORAN') {
                    if ($request->status === 'Revisi') {
                        $this->createHistory($submission, $request->notes);
                        $submission->laboran_status = 'Revisi';
                        $submission->laboran_acc_at = null;
                        
                        // Jika laboran minta revisi, alur balik ke Aslab (Pending)
                        $submission->aslab_status = 'Pending'; 
                        $submission->aslab_acc_at = null; 
                    } else {
                        $submission->laboran_status = 'ACC';
                        $submission->laboran_acc_at = $now;
                        $submission->is_completed = true;
                    }
                }

                $submission->save();
            });

            // Setelah review selesai, arahkan kembali ke daftar mahasiswa (Index)
            $submission = Submission::find($id);
            return redirect()->route('submissions.index', $submission->meeting_id)
                            ->with('success', 'Status mahasiswa ' . $submission->student->name . ' berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
     * HELPER: Simpan Riwayat Revisi (Private)
     */
    private function createHistory($submission, $notes) 
    {
        return SubmissionHistory::create([
            'submission_id' => $submission->id,
            'drive_link'    => $submission->submission_link,
            'feedback'      => $notes,
            'iteration'     => $submission->histories()->count() + 1,
            'reviewed_by'   => Auth::id(),
        ]);
    }
}