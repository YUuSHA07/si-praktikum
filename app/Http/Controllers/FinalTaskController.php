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
     * TAMPILAN REVIEWER: Daftar pengumpulan mahasiswa per tugas final
     */
    public function index($final_task_id)
    {
        $finalTask = FinalTask::with('course.students')->findOrFail($final_task_id);
        
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

        if ($finalTask->deadline && now()->gt($finalTask->deadline)) {
            return back()->with('error', 'Waktu pengumpulan sudah ditutup.');
        }

        $request->validate([
            'submission_link' => 'required|url',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $submission = Submission::where('final_task_id', $final_task_id)
                                ->where('student_id', $user->id)
                                ->where('is_final', true)
                                ->first();

        try {
            return DB::transaction(function () use ($request, $submission, $final_task_id, $user, $finalTask) {
                if ($submission) {
                    $submission->update([
                        'submission_link' => $request->submission_link,
                        'notes'           => $request->notes,
                        'aslab_status'    => ($submission->aslab_status === 'ACC') ? 'ACC' : 'PENDING',
                        'laboran_status'  => ($submission->laboran_status === 'ACC') ? 'ACC' : 'PENDING',
                        'dosen_status'    => 'PENDING', 
                        'last_upload_at'  => now(),
                    ]);
                    $msg = 'Tugas perbaikan berhasil diperbarui.';
                } else {
                    $submission = Submission::create([
                        'final_task_id'   => $final_task_id,
                        'student_id'      => $user->id,
                        'submission_link' => $request->submission_link,
                        'notes'           => $request->notes,
                        'is_final'        => true,
                        'aslab_status'    => 'PENDING',
                        'laboran_status'  => 'PENDING',
                        'dosen_status'    => 'PENDING',
                        'is_completed'    => false,
                        'first_upload_at' => now(),
                        'last_upload_at'  => now(),
                    ]);
                    $msg = 'Laprak Final berhasil dikumpulkan.';
                }

                $this->createHistory($submission, 'Mahasiswa mengunggah file.');

                return redirect()->route('courses.show', $finalTask->course_id)->with('success', $msg);
            });

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
            'status' => 'required|in:ACC,REVISI',
            'notes'  => 'required_if:status,REVISI|nullable|string'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $submission = Submission::lockForUpdate()->findOrFail($id);
            $user = Auth::user();
            $statusValue = $request->status;
            $now = now();
            $role = strtolower($user->role);

            // 1. CEK DOUBLE SUBMIT: Ambil status saat ini berdasarkan role
            $currentStatus = '';
            if ($role === 'aslab') $currentStatus = $submission->aslab_status;
            elseif ($role === 'laboran') $currentStatus = $submission->laboran_status;
            elseif ($role === 'dosen') $currentStatus = $submission->dosen_status;

            // Jika status baru sama dengan status lama (khusus REVISI), batalkan proses agar tidak double history
            // Untuk ACC biasanya boleh lewat jika ingin mengupdate timestamp ACC
            if ($currentStatus === $statusValue && $statusValue === 'REVISI') {
                return redirect()->back()->with('info', "Status sudah ditandai sebagai REVISI.");
            }

            // 2. Update Status
            if ($role === 'aslab') {
                $submission->aslab_status = $statusValue;
                $submission->aslab_acc_at = ($statusValue === 'ACC' ? $now : null);
            } elseif ($role === 'laboran') {
                $submission->laboran_status = $statusValue;
                $submission->laboran_acc_at = ($statusValue === 'ACC' ? $now : null);
            } elseif ($role === 'dosen') {
                $submission->dosen_status = $statusValue;
                $submission->dosen_acc_at = ($statusValue === 'ACC' ? $now : null);
            }

            $submission->is_completed = (
                $submission->aslab_status === 'ACC' && 
                $submission->laboran_status === 'ACC' && 
                $submission->dosen_status === 'ACC'
            );

            $submission->save();

            // 3. Buat History
            $this->createHistory($submission, $request->notes ?? "Status diubah menjadi $statusValue oleh $user->role");

            return redirect()->back()->with('success', "Status berhasil diupdate menjadi $statusValue");
        });
    }

    public function handler($id)
    {
        $submission = Submission::with(['student', 'histories'])->findOrFail($id);
        $finalTask = $submission->finalTask;
        return view('final-tasks.handler', compact('submission', 'finalTask'));
    }

    public function manage($id)
    {
        $finalTask = FinalTask::with('course')->findOrFail($id);
        $submission = Submission::where('final_task_id', $id)
                                ->where('student_id', Auth::id())
                                ->where('is_final', true)
                                ->first();
        $histories = $submission ? $submission->histories()->orderBy('created_at', 'desc')->get() : collect();
        return view('mahasiswa.final-tasks.handler', compact('finalTask', 'submission', 'histories'));
    }

    public function updateDeadline(Request $request, $id)
    {
        $request->validate(['deadline' => 'required|date|after:now']);
        $finalTask = FinalTask::findOrFail($id);
        $finalTask->update(['deadline' => $request->deadline]);
        return redirect()->back()->with('success', 'Deadline diperbarui!');
    }

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