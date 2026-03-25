<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id', 'name', 'email', 'password', 'role', 'is_first_login', 'avatar'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // PENTING: Karena ID Anda adalah string(20)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_first_login' => 'boolean',
        ];
    }

    // Relasi: Mahasiswa memiliki banyak kelas
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

}