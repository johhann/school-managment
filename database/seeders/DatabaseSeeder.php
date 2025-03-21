<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();

        $roles = ['admin', 'teacher', 'student'];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        $superAdmin = User::factory()->create([
            'name' => 'super_admin',
            'email' => 'admin@school.com',
        ]);

        $superAdmin->assignRole('admin');

        $names = ['Math', 'English', 'Software Engineering', 'Statistics', 'Chemistry', 'Physics', 'Biology', 'Geography'];
        foreach ($names as $subject) {
            Subject::create([
                'name' => $subject,
            ]);
        }

        $subjects = Subject::all();
        $teachers = User::factory(10)->create();
        $students = User::factory(10)->create();

        foreach ($subjects as $subject) {
            $subject->teachers()->attach(fake()->randomElements($teachers->pluck('id'), rand(1, 4)));
            $subject->students()->attach(fake()->randomElements($students->pluck('id'), rand(3, 7)));
        }

        foreach ($teachers as $teacher) {
            $teacher->assignRole('teacher');
        }

        foreach ($students as $student) {
            $student->assignRole('student');
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
