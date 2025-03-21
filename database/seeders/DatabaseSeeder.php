<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@school.com',
        ]);

        $names = ['Math', 'English', 'Software Engineering', 'Statistics', 'Chemistry', 'Physics', 'Biology', 'Geography'];
        foreach ($names as $subject) {
            Subject::create([
                'name' => $subject,
            ]);
        }

        $subjects = Subject::all();
        $teachers = User::factory(10)->create();
        $students = User::factory(10)->create();

        foreach ($teachers as $teacher) {
            $teacher->teacherSubjects()->sync($subjects->random());
        }

        foreach ($students as $student) {
            $student->studentSubjects()->sync($subjects->random());
        }

        $studentSubjects = DB::table('student_subject')->get();
        $teacherSubjects = DB::table('teacher_subject')->get();

        foreach ($studentSubjects as $studentSubject) {
            // Get teachers for this subject
            $availableTeachers = $teacherSubjects->where('subject_id', $studentSubject->subject_id);

            // Ensure there is at least one teacher before calling random()
            if ($availableTeachers->isNotEmpty()) {
                $teacher = $availableTeachers->random();

                Grade::create([
                    'student_id' => $studentSubject->student_id,
                    'subject_id' => $studentSubject->subject_id,
                    'teacher_id' => $teacher->teacher_id,
                    'grade' => rand(50, 100),
                ]);
            }
        }

        DB::commit();
    }
}
