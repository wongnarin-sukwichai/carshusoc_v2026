<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_dashboard()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
    }

    public function test_dashboard_reflects_real_revenue_registration_and_grading_data()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $course = Course::factory()->create(['language' => 'อังกฤษ']);
        $enrollment = CourseEnrollment::create([
            'user_id' => User::factory()->create()->id,
            'course_id' => $course->id,
            'status' => 'studying',
        ]);

        $exam = Exam::factory()->create();
        $registration = ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'scored',
            'cefr_level' => 'B2',
        ]);

        $translation = TranslationRequest::create([
            'user_id' => User::factory()->create()->id,
            'file_name' => 'resume.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'translating',
        ]);

        $coursePayment = new Payment(['user_id' => $enrollment->user_id, 'amount' => 1000, 'status' => 'approved', 'approved_at' => now()]);
        $coursePayment->payable()->associate($enrollment);
        $coursePayment->save();

        $examPayment = new Payment(['user_id' => $registration->user_id, 'amount' => 500, 'status' => 'approved', 'approved_at' => now()]);
        $examPayment->payable()->associate($registration);
        $examPayment->save();

        $translationPayment = new Payment(['user_id' => $translation->user_id, 'amount' => 300, 'status' => 'pending']);
        $translationPayment->payable()->associate($translation);
        $translationPayment->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('stats.revenue', 1500)
            ->where('stats.registrants', 3)
            ->where('stats.enrollments', 1)
            ->where('stats.examRegistrations', 1)
            ->where('stats.translationJobs', 1)
            ->where('stats.certificatesIssued', 0)
            ->where('revenueByCenter.0.center', 'training')
            ->where('revenueByCenter.0.total', 1000)
            ->where('revenueByCenter.1.center', 'exam')
            ->where('revenueByCenter.1.total', 500)
            ->where('revenueByCenter.2.center', 'translation')
            ->where('revenueByCenter.2.total', 0)
            ->where('cefrDistribution.0.level', 'B2')
            ->where('cefrDistribution.0.count', 1)
        );
    }
}
