<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\FirstLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\FinalTaskController;

// 1. Rute Publik
Route::get('/', function () {
    return view('welcome');
});

// 2. Grup Rute Auth
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute Force Change Password
    Route::get('/force-change-password', [FirstLoginController::class, 'showChangePasswordForm'])->name('first.login.form');
    Route::post('/force-change-password', [FirstLoginController::class, 'updatePassword'])->name('first.login.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- RUTE PROFILE (Pastikan ada 3 rute ini) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Import (Khusus Laboran)
    Route::get('/import-users', [UserImportController::class, 'showImportForm'])->name('user.import.form');
    Route::post('/import-users', [UserImportController::class, 'import'])->name('user.import');

    // user management (Khusus laboran)
    Route::get('/users-management', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users-management/{id}/reset', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Route Manajemen Kelas (Courses) laboran
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');

    // Route untuk mahasiswa enroll ke kelas (mahasiswa)
    Route::post('/courses/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');

    // Route untuk menambahkan pertemuan ke kelas (aslab)
    Route::get('/courses/{id}', [MeetingController::class, 'show'])->name('courses.show');
    Route::post('/courses/{id}/meetings', [MeetingController::class, 'store'])->name('meetings.store');

    // Route Presensi Aslab
    Route::get('/meetings/{meeting_id}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/meetings/{meeting_id}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/courses/{course_id}/attendance-report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Route untuk simpan tugas oleh (mahasiswa)
    Route::post('/meetings/{meeting_id}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
    
    // Route untuk approval oleh (Aslab/Laboran/Dosen)
    Route::post('/submissions/{id}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');

    // Halaman daftar tugas per  (aslab)
    Route::get('/meetings/{meeting_id}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    // Aksi Approval (sudah kita buat di tahap sebelumnya) aslab
    Route::post('/submissions/{id}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');

    // Rute untuk pengiriman pertama kali (Store)
    Route::post('/submissions/{meeting_id}', [SubmissionController::class, 'store'])->name('submissions.store');
    // Rute untuk update/revisi (Update)
    Route::put('/submissions/{id}', [SubmissionController::class, 'update'])->name('submissions.update');
    // Rute untuk pengiriman tugas (Store)
    Route::post('/meetings/{meeting_id}/submit', [SubmissionController::class, 'store'])->name('submissions.store');;

    // Halaman Rekap Tugas Mahasiswa
    Route::get('/my-submissions', [SubmissionController::class, 'mySubmissions'])->name('submissions.my-index');

    // Route untuk halaman kelola tugas (Satu halaman untuk baru/edit/revisi)
    Route::get('/mahasiswa/meetings/{meeting}/submission', [SubmissionController::class, 'manage'])
        ->name('mahasiswa.submissions.manage');
        
    // Route untuk simpan data baru
    Route::post('/mahasiswa/meetings/{meeting}/submit', [SubmissionController::class, 'store'])
        ->name('meetings.submit');

    // Route untuk update data lama (termasuk kirim revisi)
    Route::put('/mahasiswa/submissions/{submission}', [SubmissionController::class, 'update'])
        ->name('submissions.update');

    // Halaman daftar semua submission per meeting
    Route::get('/submissions/meeting/{meeting}', [SubmissionController::class, 'index'])->name('submissions.index');

    // Halaman baru untuk proses review (Handler)
    Route::get('/submissions/{submission}/handler', [SubmissionController::class, 'handler'])->name('submissions.handler');

    // Proses Simpan Approval (Revisi/ACC)
    Route::post('/submissions/{submission}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');

    // Rute untuk Final Task (Laporan Praktikum Final)
    Route::post('/courses/{course}/final-tasks', [FinalTaskController::class, 'store'])->name('final-tasks.store');
    Route::get('/final-tasks/{final_task}', [FinalTaskController::class, 'index'])->name('final-tasks.index');
    
    // Rute untuk sisi Mahasiswa (sesuaikan dengan nama route yang ada di view)
    Route::get('/mahasiswa/final-tasks/{final_task}', [FinalTaskController::class, 'manage'])->name('mahasiswa.final-tasks.manage');
    // Route untuk memproses pengiriman tugas
    Route::post('/mahasiswa/final-tasks/{final_task}/submit', [App\Http\Controllers\FinalTaskController::class, 'submit'])->name('final-tasks.submit');
    // Rute untuk update deadline 

    // Route untuk Update Deadline (Baris 11 di Blade)
    Route::put('/final-tasks/{final_task}/deadline', [FinalTaskController::class, 'updateDeadline'])->name('final-tasks.update-deadline');

    // Route untuk Tombol Periksa (Baris 90 di Blade)
    Route::get('/final-tasks/submission/{submission}', [FinalTaskController::class, 'handler'])->name('final-tasks.handler');
    // Route untuk Proses Approval (Baris 120 di Blade)
    Route::patch('/final-tasks/submission/{submission}/approve', [App\Http\Controllers\FinalTaskController::class, 'approve'])->name('final-tasks.approve');
    
    // Route untuk Update Link/Revisi (Baris 130 di Blade)
    Route::put('/final-tasks/submissions/{id}', [FinalTaskController::class, 'update'])->name('final-tasks.update');

    // Route untuk Update Deadline (Baris 11 di Blade)
    Route::put('/meetings/{id}/deadline', [App\Http\Controllers\SubmissionController::class, 'updateDeadline'])->name('meetings.update-deadline');

    // Route untuk Update Link/Revisi (Baris 130 di Blade)
    Route::put('/meetings/{meeting}', [App\Http\Controllers\MeetingController::class, 'update'])->name('meetings.update');

    // Route untuk Update Deskripsi (Baris 11 di Blade)
    Route::put('/final-tasks/{finalTask}/update-description', [App\Http\Controllers\FinalTaskController::class, 'updateDescription'])->name('final-tasks.update-description');

    // Rute Manajemen Semester
    Route::resource('semesters', App\Http\Controllers\SemesterController::class)->except(['create', 'show', 'edit']);
    Route::patch('semesters/{semester}/set-active', [App\Http\Controllers\SemesterController::class, 'setActive'])->name('semesters.set-active');

    // Route untuk halaman Arsip
    Route::get('/arsip', [App\Http\Controllers\ArchiveController::class, 'index'])->name('archives.index');

    // Route untuk halaman daftar mahasiswa per kelas (semua role bisa akses)
    Route::get('/courses/{course}/students', [App\Http\Controllers\CourseController::class, 'students'])->name('courses.students');
    // Route untuk mengeluarkan mahasiswa dari kelas (Hanya Laboran/Dosen/aslab)
    Route::delete('/courses/{course}/students/{student}', [App\Http\Controllers\CourseController::class, 'removeStudent'])->name('courses.remove-student');
});

require __DIR__.'/auth.php';