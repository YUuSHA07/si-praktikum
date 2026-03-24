<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'student_id',
        'meeting_id',
        'final_task_id',
        'is_final',
        'aslab_status',
        'laboran_status',
        'dosen_status',
        'is_completed',
        'first_upload_at',
        'last_upload_at',
        'aslab_acc_at',
        'laboran_acc_at',
        'dosen_acc_at'
    ];

    // Relasi ke Mahasiswa
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Relasi ke Pertemuan (Jika ini laprak mingguan)
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    // Relasi ke Tugas Final (Jika ini pengumpulan final)
    public function finalTask()
    {
        return $this->belongsTo(FinalTask::class);
    }

    // Relasi ke Riwayat (Sangat penting untuk melihat semua link revisi)
    public function histories()
    {
        return $this->hasMany(SubmissionHistory::class)->orderBy('iteration', 'desc');
    }
}