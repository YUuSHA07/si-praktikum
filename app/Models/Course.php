<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'semester_id',
        'course_name',
        'class_group',
        'target_semester',
        'dosen_id',
        'laboran_id',
        'aslab_id',
        'enrollment_code'
    ];

    // Relasi ke Semester
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Relasi ke Dosen
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    // Relasi ke Laboran
    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }

    // Relasi ke Aslab
    public function aslab()
    {
        return $this->belongsTo(User::class, 'aslab_id');
    }

    // Relasi ke Mahasiswa (Many to Many)
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
                    ->withPivot('joined_at');
    }

    // Relasi ke Pertemuan
    public function meetings()
    {
        return $this->hasMany(Meeting::class)->orderBy('meeting_number', 'asc');
    }
}