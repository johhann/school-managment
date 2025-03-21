<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_subject', 'subject_id', 'student_id')
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id')
            ->withTimestamps();
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
