<?php

namespace App\Http\Controllers;

use App\Models\FinalTask;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinalTaskController extends Controller
{
    /**
     * TAMPILAN REVIEWER: Daftar pengumpulan mahasiswa per tugas final
     */
    public function index(int|string $final_task_id): View
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
     * SIMPAN / UPDATE TUGAS FINAL (Membuat wadah tugas final baru untuk Course)
     */
    public function store(Request $request, int|string $course_id): RedirectResponse
    {
        $request->validate([
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ]);

        // Menggunakan FinalTask::query() memutus salah paham stubs di Intelephense
        FinalTask::query()->updateOrCreate(
            ['course_id' => $course_id],
            [
                'description' => $request->description,
                'deadline'    => $request->deadline,
            ]
        );

        return redirect()->back()->with('success', 'Tugas Final berhasil diperbarui!');
    }

    /**
     * PROSES MAHASISWA: Simpan Tugas Baru
     */
    public function submit(Request $request, int|string $final_task_id): RedirectResponse
    {
        $request->validate([
            'submission_link' => 'required|url',
        ]);

        $finalTask = FinalTask::findOrFail($final_task_id);

        if ($finalTask->deadline && now()->gt($finalTask->deadline)) {
            return back()->with('error', 'Waktu pengumpulan sudah ditutup.');
        }

        /** @var Submission|null $submission */
        $submission = Submission::where('final_task_id', $final_task_id)
                                ->where('student_id', Auth::id())
                                ->where('is_final', true)
                                ->first();

        // Jika sudah ada record, alihkan ke fungsi update
        if ($submission) {
            return $this->update($request, $submission->id);
        }

        $now = now();

        Submission::create([
            'final_task_id'   => $final_task_id,
            'student_id'      => Auth::id(),
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'is_final'        => true,
            'first_upload_at' => $now,
            'last_upload_at'  => $now,
            'aslab_status'    => 'PENDING',
            'laboran_status'  => 'PENDING',
            'dosen_status'    => 'PENDING',
            'is_completed'    => false,
        ]);

        return redirect()->route('courses.show', $finalTask->course_id)
                         ->with('success', 'Laprak Final berhasil dikumpulkan.');
    }

    /**
     * PROSES MAHASISWA: Update atau Kirim Revisi
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'submission_link' => 'required|url',
        ]);

        /** @var Submission $submission */
        $submission = Submission::with('finalTask')->where('id', $id)
                                ->where('student_id', Auth::id())
                                ->firstOrFail();

        // LOGIKA: Jika sudah ACC, pertahankan. Jika REVISI/PENDING, ubah jadi PENDING.
        $newAslabStatus   = (strtoupper((string) $submission->aslab_status) === 'ACC') ? 'ACC' : 'PENDING';
        $newLaboranStatus = (strtoupper((string) $submission->laboran_status) === 'ACC') ? 'ACC' : 'PENDING';
        $newDosenStatus   = (strtoupper((string) $submission->dosen_status) === 'ACC') ? 'ACC' : 'PENDING';

        /** @var \Illuminate\Database\Eloquent\Model $submissionModel */
        $submissionModel = $submission;
        $submissionModel->update([
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'aslab_status'    => $newAslabStatus,
            'laboran_status'  => $newLaboranStatus,
            'dosen_status'    => $newDosenStatus,
            'last_upload_at'  => now(),
        ]);

        return redirect()->route('courses.show', $submission->finalTask->course_id)
                         ->with('success', 'Tugas perbaikan berhasil dikirim.');
    }

    /**
     * PROSES REVIEWER: Memberi ACC atau REVISI
     */
    public function approve(Request $request, int|string $id): mixed
    {
        $statusInput = strtoupper((string) $request->status);

        $request->validate([
            'status' => 'required|in:ACC,REVISI',
            'notes'  => 'required_if:status,REVISI|nullable|string'
        ]);

        return DB::transaction(function () use ($request, $id, $statusInput) {
            /** @var Submission $submission */
            $submission = Submission::lockForUpdate()->findOrFail($id);
            $user = $request->user();
            $now = now();
            
            $role = strtoupper((string) ($user->active_role ?? $user->role));

            $currentStatus = match($role) {
                'ASLAB'   => strtoupper((string) $submission->aslab_status),
                'LABORAN' => strtoupper((string) $submission->laboran_status),
                'DOSEN'   => strtoupper((string) $submission->dosen_status),
                default   => ''
            };

            if ($currentStatus === $statusInput && $statusInput === 'REVISI') {
                return redirect()->back()->with('info', "Status sudah ditandai sebagai REVISI.");
            }

            if ($role === 'ASLAB') {
                if ($statusInput === 'ACC') {
                    $submission->aslab_status = 'ACC';
                    $submission->aslab_acc_at = $now;
                } else {
                    $this->createHistory($submission, $request->notes);
                    $submission->aslab_status = 'REVISI';
                    $submission->aslab_acc_at = null;
                    $submission->laboran_status = 'PENDING';
                    $submission->laboran_acc_at = null;
                    $submission->dosen_status = 'PENDING';
                    $submission->dosen_acc_at = null;
                }
            } elseif ($role === 'LABORAN') {
                if (strtoupper((string) $submission->aslab_status) !== 'ACC') {
                    return back()->with('error', 'Menunggu verifikasi Asisten Laboratorium.');
                }

                if ($statusInput === 'ACC') {
                    $submission->laboran_status = 'ACC';
                    $submission->laboran_acc_at = $now;
                } else {
                    $this->createHistory($submission, $request->notes);
                    $submission->laboran_status = 'REVISI';
                    $submission->laboran_acc_at = null;
                    $submission->dosen_status = 'PENDING';
                    $submission->dosen_acc_at = null;
                }
            } elseif ($role === 'DOSEN') {
                if (strtoupper((string) $submission->aslab_status) !== 'ACC' || strtoupper((string) $submission->laboran_status) !== 'ACC') {
                    return back()->with('error', 'Menunggu verifikasi Aslab & Laboran.');
                }

                if ($statusInput === 'ACC') {
                    $submission->laboran_status = 'ACC';
                    $submission->laboran_acc_at = $now;
                    
                    // TAMBAHKAN BARIS INI JIKA INGIN DOSEN OTOMATIS ACC:
                    $submission->dosen_status = 'ACC';
                    $submission->dosen_acc_at = $now;
                } else {
                    $this->createHistory($submission, $request->notes);
                    $submission->dosen_status = 'REVISI';
                    $submission->dosen_acc_at = null;
                }
            }

            $submission->is_completed = (
                strtoupper((string) $submission->aslab_status) === 'ACC' && 
                strtoupper((string) $submission->laboran_status) === 'ACC' && 
                strtoupper((string) $submission->dosen_status) === 'ACC'
            );

            $submission->save();

            return redirect()->back()->with('success', "Status berhasil diupdate menjadi $statusInput");
        });
    }

    public function handler(int|string $id): View
    {
        $submission = Submission::with(['student', 'histories'])->findOrFail($id);
        $finalTask = $submission->finalTask;
        return view('final-tasks.handler', compact('submission', 'finalTask'));
    }

    public function manage(int|string $id): View
    {
        $finalTask = FinalTask::with('course')->findOrFail($id);
        $submission = Submission::where('final_task_id', $id)
                                ->where('student_id', Auth::id())
                                ->where('is_final', true)
                                ->first();
        $histories = $submission ? $submission->histories()->orderBy('created_at', 'desc')->get() : collect();
        return view('mahasiswa.final-tasks.handler', compact('finalTask', 'submission', 'histories'));
    }

    /**
     * UPDATE DEADLINE
     */
    public function updateDeadline(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'deadline' => 'required|date' 
        ]);
        
        $finalTask = FinalTask::findOrFail($id);

        /** @var \Illuminate\Database\Eloquent\Model $finalTaskModel */
        $finalTaskModel = $finalTask;
        $finalTaskModel->update(['deadline' => $request->deadline]);
        
        return redirect()->back()->with('success', 'Deadline diperbarui!');
    }

    /**
     * HELPER: Simpan Riwayat
     */
    private function createHistory(Submission $submission, ?string $notes): SubmissionHistory
    {
        return SubmissionHistory::create([
            'submission_id' => $submission->id,
            'drive_link'    => $submission->submission_link,
            'feedback'      => $notes,
            'iteration'     => $submission->histories()->count() + 1,
            'reviewed_by'   => Auth::id(),
        ]);
    }

    /**
     * Update hanya deskripsi tugas final.
     */
    public function updateDescription(Request $request, FinalTask $finalTask): RedirectResponse
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        /** @var \Illuminate\Database\Eloquent\Model $finalTaskModel */
        $finalTaskModel = $finalTask;
        $finalTaskModel->update([
            'description' => $request->description,
        ]);

        return back()->with('success', 'Deskripsi laporan final berhasil diperbarui!');
    }
}