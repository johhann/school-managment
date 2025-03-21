<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function studentSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id')
            ->withTimestamps();
    }

    public function teacherSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
            ->withTimestamps();
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function assignedGrades(): HasMany
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    public static function scopeAdmin(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        });
    }

    public static function scopeStudent(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'student');
        });
    }

    public static function scopeTeacher(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasAnyRole('teacher');
    }

    public function isStudent(): bool
    {
        return $this->hasAnyRole('student');
    }
}
