<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExamRegistrationSeeder extends Seeder
{
    /**
     * Five sample "registered" (unscored) examinees for the first seeded exam,
     * so admin/ExamScores.vue has real data to score out of the box — mirrors
     * CourseEnrollmentSeeder's role for admin/CourseGrading.vue.
     */
    public function run(): void
    {
        $exam = Exam::orderBy('id')->first();

        if (! $exam) {
            return;
        }

        $examinees = [
            ['name' => 'กมล แสงทอง', 'email' => 'examinee1@example.com'],
            ['name' => 'ศิริพร บุญมา', 'email' => 'examinee2@example.com'],
            ['name' => 'อดิศักดิ์ ศรีวิไล', 'email' => 'examinee3@example.com'],
            ['name' => 'จันทิมา ผลาผล', 'email' => 'examinee4@example.com'],
            ['name' => 'ธีรพงษ์ กาญจนะ', 'email' => 'examinee5@example.com'],
        ];

        foreach ($examinees as $examinee) {
            $user = User::firstOrCreate(
                ['email' => $examinee['email']],
                [
                    'name' => $examinee['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );

            ExamRegistration::firstOrCreate(
                ['user_id' => $user->id, 'exam_id' => $exam->id],
                ['status' => 'registered']
            );
        }
    }
}
