<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ScoreScale;
use App\Models\TranslationRequest;
use App\Models\User;
use App\Services\CertificateIssuer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TestUserDemoDataSeeder extends Seeder
{
    /**
     * Gives the documented manual-testing account (test@example.com — see
     * CLAUDE.md) a non-empty translation history and portfolio out of the
     * box, so user/Translation.vue and user/Portfolio.vue have something to
     * show the first time someone logs in with it instead of every list
     * looking empty. Mirrors TranslationRequestSeeder/CourseEnrollmentSeeder/
     * ExamRegistrationSeeder's role for the admin-side pages, but scoped to
     * this one demo account's own visible history rather than random users.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (! $user) {
            return;
        }

        $this->seedTranslationHistory($user);
        $this->seedCourseCertificate($user);
        $this->seedExamCertificate($user);
    }

    private function seedTranslationHistory(User $user): void
    {
        $requests = [
            [
                'file_name' => 'ใบแสดงผลการเรียน.pdf',
                'source_lang' => 'ไทย',
                'target_lang' => 'อังกฤษ',
                'status' => 'submitted',
            ],
            [
                'file_name' => 'หนังสือรับรองการทำงาน.pdf',
                'source_lang' => 'ไทย',
                'target_lang' => 'อังกฤษ',
                'status' => 'quote_sent',
                'estimated_price' => 550,
                'delivery_date' => now()->addDays(4),
            ],
            [
                'file_name' => 'Research Proposal.docx',
                'source_lang' => 'อังกฤษ',
                'target_lang' => 'ไทย',
                'status' => 'completed',
                'estimated_price' => 700,
                'delivery_date' => now()->subDays(2),
            ],
        ];

        foreach ($requests as $data) {
            $request = TranslationRequest::firstOrCreate(
                ['user_id' => $user->id, 'file_name' => $data['file_name']],
                $data
            );

            if (! $request->source_file_path) {
                $sourcePath = 'translations/source/'.$user->id.'/'.$request->id.'.txt';
                Storage::disk('local')->put($sourcePath, 'Demo source document content for seeding.');
                $request->update(['source_file_path' => $sourcePath]);
            }

            if ($request->status === 'completed' && ! $request->translated_file_path) {
                $translatedPath = 'translations/delivered/'.$request->id.'-translated.txt';
                Storage::disk('public')->put($translatedPath, 'Demo translated document content for seeding.');
                $request->update(['translated_file_path' => $translatedPath]);
            }
        }
    }

    private function seedCourseCertificate(User $user): void
    {
        $course = Course::where('code', 'EN-01')->first();

        if (! $course) {
            return;
        }

        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'passed', 'enrolled_at' => now()->subMonths(2), 'graded_at' => now()->subMonth()]
        );

        if ($enrollment->status !== 'passed') {
            $enrollment->update(['status' => 'passed', 'graded_at' => now()->subMonth()]);
        }

        app(CertificateIssuer::class)->issueForCourseEnrollment($enrollment);
    }

    private function seedExamCertificate(User $user): void
    {
        $exam = Exam::where('code', 'EX-EPT')->first();

        if (! $exam) {
            return;
        }

        $scale = $exam->score_scale_id
            ? ScoreScale::find($exam->score_scale_id)
            : ScoreScale::where('is_active', true)->first();

        $scores = ['listening_score' => 20, 'reading_score' => 22, 'conversation_score' => 20, 'grammar_score' => 20];
        $total = array_sum($scores);
        $band = $scale?->resolveBand($total, $exam->type);

        $registration = ExamRegistration::firstOrCreate(
            ['user_id' => $user->id, 'exam_id' => $exam->id],
            array_merge($scores, [
                'status' => 'scored',
                'total_score' => $total,
                'cefr_level' => $band?->cefr_level,
                'score_scale_id' => $scale?->id,
                'scored_at' => now()->subWeeks(2),
                'certificate_issued' => true,
            ])
        );

        app(CertificateIssuer::class)->issueForExamRegistration($registration);
    }
}
