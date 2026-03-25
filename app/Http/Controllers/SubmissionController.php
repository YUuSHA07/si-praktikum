<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\SubmissionHistory;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    /**
     * Menampilkan daftar tugas di sisi Aslab/Staf
     */
    public function index($meeting_id)
    {
        $meeting = Meeting::with('course.students')->findOrFail($meeting_id);
        
        // Ambil semua tugas yang masuk untuk pertemuan ini, dikelompokkan berdasarkan student_id
        $submissions = Submission::where('meeting_id', $meeting_id)
                        ->get()
                        ->keyBy('student_id');

        return view('submissions.index', compact('meeting', 'submissions'));
    }

    /**
     * Menampilkan rekap semua tugas milik mahasiswa yang sedang login
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
     * Pengumpulan pertama kali (Tombol "Kumpul Tugas")
     */
    public function store(Request $request, $meeting_id)
    {
        $request->validate([
            'submission_link' => 'required|url',
        ]);

        $now = now();

        // Cek apakah sudah pernah kumpul (safety check)
        $existing = Submission::where('meeting_id', $meeting_id)
                                ->where('student_id', Auth::id())
                                ->first();

        if ($existing) {
            return $this->update($request, $existing->id);
        }

        Submission::create([
            'meeting_id'      => $meeting_id,
            'student_id'      => Auth::id(),
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'first_upload_at' => $now,
            'last_upload_at'  => $now,
            'aslab_status'    => 'Pending', 
            'laboran_status'  => 'Pending',
            'is_completed'    => false
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil dikirim.');
    }

    /**
     * Memperbarui tugas (Tombol "Edit Tugas")
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'submission_link' => 'required|url'
        ]);

        /** @var \App\Models\Submission $submission */
        $submission = Submission::findOrFail($id);

        // 1. Simpan data SEBELUM diubah ke dalam History
        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'drive_link'    => $submission->submission_link,
            'iteration'     => ($submission->histories()->max('iteration') ?? 0) + 1,
            'feedback'      => $submission->notes ?? 'Versi sebelum revisi mahasiswa',
            'action_type'   => 'Revision',
        ]);

        // 2. Update data UTAMA dengan data baru
        $submission->update([
            'submission_link' => $request->submission_link,
            'notes'           => $request->notes,
            'aslab_status'    => 'Pending', // Reset status agar aslab cek ulang
            'last_upload_at'  => now(),
            'is_completed'    => false // Reset status completion jika ada perubahan
        ]);

        return back()->with('success', 'Tugas berhasil diperbarui dan riwayat tersimpan.');
    }

    /**
     * Proses Approval oleh Aslab/Laboran
     */
    public function approve(Request $request, $id)
    {
        /** @var \App\Models\Submission $submission */
        $submission = Submission::findOrFail($id);
        $status = $request->status; // 'ACC' atau 'Revisi'

        if ($status == 'Revisi') {
            // Catat ke history bahwa ini adalah permintaan revisi dari staf
            SubmissionHistory::create([
                'submission_id' => $submission->id,
                'drive_link'    => $submission->submission_link,
                'iteration'     => ($submission->histories()->max('iteration') ?? 0) + 1,
                'feedback'      => $request->feedback ?? 'Diminta revisi oleh Aslab',
                'action_type'   => 'Revision_Request',
                'reviewed_by'   => Auth::id(),
            ]);

            $submission->update([
                'aslab_status' => 'Revisi',
                'is_completed' => false
            ]);
        } else {
            // Jika ACC
            $submission->update([
                'aslab_status' => 'ACC',
                'aslab_acc_at' => now(),
                'is_completed' => true
            ]);
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }
}