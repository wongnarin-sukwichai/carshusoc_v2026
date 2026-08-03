<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseEnrollmentSeeder extends Seeder
{
    /**
     * Five sample "studying" enrollees for the first seeded course, so
     * admin/CourseGrading.vue has real data to grade out of the box.
     */
    public function run(): void
    {
        $course = Course::orderBy('id')->first();

        if (! $course) {
            return;
        }

        $students = [
            ['name' => 'สมชาย ใจดี', 'email' => 'student1@example.com'],
            ['name' => 'สมหญิง รักเรียน', 'email' => 'student2@example.com'],
            ['name' => 'วิชัย ตั้งใจ', 'email' => 'student3@example.com'],
            ['name' => 'มาลี สุขสันต์', 'email' => 'student4@example.com'],
            ['name' => 'ประยุทธ์ มานะดี', 'email' => 'student5@example.com'],
        ];

        foreach ($students as $student) {
            $user = User::firstOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );

            CourseEnrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['status' => 'studying', 'enrolled_at' => now()]
            );
        }
    }
}
