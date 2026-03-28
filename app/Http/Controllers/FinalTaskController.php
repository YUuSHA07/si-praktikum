<?php

namespace App\Http\Controllers;

use App\Models\FinalTask;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinalTaskController extends Controller
{
    /**
     * TAMPILAN REVIEWER: Daftar pengumpulan
     */
    public function index($final_task_id)
    {
        $finalTask = FinalTask::with('course.students')->findOrFail($final_task_id);
        
        // Ambil submission yang benar-benar is_final = true
        $submissions = Submission::where('final_task_id', $final_task_id)
                        ->where('is_final', true)
                        ->with('histories')
                        ->get()
                        ->keyBy('student_id');

        return view('final-tasks.index', compact('finalTask', 'submissions'));
    }

    /**
     * PROSES MAHASISWA: Simpan atau Perbarui Laprak Final
     */
    public function submit(Request $request, $final_task_id)
    {
        $finalTask = FinalTask::findOrFail($final_task_id);

        // PROTEKSI DEADLINE
        if ($finalTask->deadline && now()->gt($finalTask->deadline)) {
            return back()->with('error', 'Waktu pengumpulan sudah ditutup.');
        }

        $request->validate([
            'submission_link' => 'required|url',
        ]);

        // Cari data lama jika ada
        $submission = Submission::where('final_task_id', $final_task_id)
                                ->where('student_id', Auth::id())
                                ->where('is_final', true)
                                ->first();

        try {
            if ($submission) {
                // UPDATE
                $submission->update([
                    'submission_link' => $request->submission_link,
                    'notes'           => $request->notes,
                    'aslab_status'    => ($submission->aslab_status === 'ACC') ? 'ACC' : 'PENDING',
                    'laboran_status'  => ($submission->laboran_status === 'ACC') ? 'ACC' : 'PENDING',
                    'dosen_status'    => 'PENDING', // Reset dosen setiap ada update
                    'last_upload_at'  => now(),
                ]);
                $msg = 'Tugas perbaikan berhasil diperbarui.';
            } else {
                // CREATE BARU
                Submission::create([
                    'final_task_id'   => $final_task_id,
                    'student_id'      => Auth::id(),
                    'submission_link' => $request->submission_link,
                    'notes'           => $request->notes,
                    'is_final'        => true, // CRITICAL: Pastikan ini true
                    'aslab_status'    => 'PENDING',
                    'laboran_status'  => 'PENDING',
                    'dosen_status'    => 'PENDING',
                    'is_completed'    => false,
                    'first_upload_at' => now(),
                    'last_upload_at'  => now(),
                ]);
                $msg = 'Laprak Final berhasil dikumpulkan.';
            }

            return redirect()->route('courses.show', $finalTask->course_id)->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * PROSES REVIEWER: Memberi ACC atau REVISI
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:ACC,REVISI',
            'feedback' => 'required_if:status,REVISI' 
        ]);

        $submission = Submission::findOrFail($id);
        $role = strtoupper(Auth::user()->role);
        $statusInput = strtoupper($request->status);
        $now = now();

        if ($role === 'ASLAB') {
            $submission->aslab_status = $statusInput;
            $submission->aslab_acc_at = ($statusInput === 'ACC') ? $now : null;
        } 
        elseif ($role === 'LABORAN') {
            if ($submission->aslab_status !== 'ACC') return back()->with('error', 'Aslab belum ACC.');
            $submission->laboran_status = $statusInput;
            $submission->laboran_acc_at = ($statusInput === 'ACC') ? $now : null;
        }
        elseif ($role === 'DOSEN') {
            if ($submission->laboran_status !== 'ACC') return back()->with('error', 'Laboran belum ACC.');
            $submission->dosen_status = $statusInput;
            $submission->dosen_acc_at = ($statusInput === 'ACC') ? $now : null;
        }

        // Jika ada REVISI, buat history
        if ($statusInput === 'REVISI') {
            $this->createHistory($submission, $request->feedback);
        }

        // Update status kelulusan otomatis
        $submission->is_completed = (
            $submission->aslab_status === 'ACC' && 
            $submission->laboran_status === 'ACC' &&
            $submission->dosen_status === 'ACC'
        );
        
        $submission->save();

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }

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
 * Tampilan untuk Mahasiswa mengelola pengumpulan Laprak Final
 */
    public function manage($id)
    {
        // Ambil data Final Task berdasarkan ID
        $finalTask = FinalTask::with('course')->findOrFail($id);

        // Ambil data pengumpulan mahasiswa yang sedang login (jika sudah ada)
        $submission = Submission::where('final_task_id', $id)
                                ->where('student_id', Auth::id())
                                ->where('is_final', true)
                                ->first();

        // Ambil riwayat revisi (jika ada)
        $histories = $submission ? $submission->histories()->orderBy('created_at', 'desc')->get() : collect();

        return view('mahasiswa.final-tasks.handler', compact('finalTask', 'submission', 'histories'));
    }

    public function updateDeadline(Request $request, $id)
    {
        $request->validate([
            'deadline' => 'required|date|after:now',
        ]);

        $finalTask = \App\Models\FinalTask::findOrFail($id);
        $finalTask->update([
            'deadline' => $request->deadline
        ]);

        return redirect()->back()->with('success', 'Deadline berhasil diperbarui!');
    }

    // app/Http/Controllers/FinalTaskController.php
    public function handler($id)
    {
        // Ubah 'submissionHistories' menjadi 'histories' sesuai nama di Model
        $submission = Submission::with(['student', 'histories'])->findOrFail($id);

        $finalTask = $submission->finalTask;

        return view('final-tasks.handler', compact('submission', 'finalTask'));
    }
}