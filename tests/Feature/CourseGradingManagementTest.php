<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CourseGradingManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * gradeBulk() issues a certificate for every "passed" row (App\Services\CertificateIssuer),
     * which requires a default CertificateTemplate for the 'training' center to exist.
     */
    private function seedDefaultTrainingTemplate(): void
    {
        CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);
    }

    public function test_index_lists_real_enrollments_for_the_selected_course()
    {
        $admin = Admin::factory()->create();
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create(['course_id' => $course->id, 'status' => 'studying']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.course-grading', ['course' => $course->id]));

        $response->assertInertia(
            fn ($page) => $page->component('admin/CourseGrading')
                ->where('selectedCourseId', $course->id)
                ->where('enrollments.0.id', $enrollment->id)
                ->where('enrollments.0.status', 'studying')
        );
    }

    public function test_admin_can_export_the_course_roster_with_real_names_emails_and_status()
    {
        $admin = Admin::factory()->create();
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create(['course_id' => $course->id, 'status' => 'studying']);
        $enrollment->loadMissing('user');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.courses.export-roster', $course));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

        $this->assertSame('name', $sheet->getCell('A1')->getValue());
        $this->assertSame('email', $sheet->getCell('B1')->getValue());
        $this->assertSame('status', $sheet->getCell('C1')->getValue());
        $this->assertSame($enrollment->user->name, $sheet->getCell('A2')->getValue());
        $this->assertSame($enrollment->user->email, $sheet->getCell('B2')->getValue());
        $this->assertSame('กำลังเรียน', $sheet->getCell('C2')->getValue());
    }

    public function test_bulk_grade_marks_selected_enrollments_passed_and_others_failed()
    {
        $this->seedDefaultTrainingTemplate();

        $admin = Admin::factory()->create();
        $course = Course::factory()->create();
        $passing = CourseEnrollment::factory()->create(['course_id' => $course->id, 'status' => 'studying']);
        $failing = CourseEnrollment::factory()->create(['course_id' => $course->id, 'status' => 'studying']);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.course-grading.save'), [
            'grades' => [
                ['id' => $passing->id, 'status' => 'passed'],
                ['id' => $failing->id, 'status' => 'failed'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame('passed', $passing->fresh()->status);
        $this->assertSame('failed', $failing->fresh()->status);
        $this->assertNotNull($passing->fresh()->graded_at);
        $this->assertNotNull($passing->fresh()->certificate);
    }

    public function test_bulk_grade_skips_an_enrollment_still_awaiting_payment()
    {
        $this->seedDefaultTrainingTemplate();

        $admin = Admin::factory()->create();
        $enrollment = CourseEnrollment::factory()->create(['status' => 'pending_payment']);

        $this->actingAs($admin, 'admin')->post(route('admin.course-grading.save'), [
            'grades' => [
                ['id' => $enrollment->id, 'status' => 'failed'],
            ],
        ]);

        $this->assertSame('pending_payment', $enrollment->fresh()->status);
    }

    public function test_a_passed_enrollment_can_be_flipped_to_failed_and_its_certificate_is_revoked()
    {
        $this->seedDefaultTrainingTemplate();

        $admin = Admin::factory()->create();
        $enrollment = CourseEnrollment::factory()->create(['status' => 'studying']);

        $this->actingAs($admin, 'admin')->post(route('admin.course-grading.save'), [
            'grades' => [['id' => $enrollment->id, 'status' => 'passed']],
        ]);

        $this->assertNotNull($enrollment->fresh()->certificate);

        $this->actingAs($admin, 'admin')->post(route('admin.course-grading.save'), [
            'grades' => [['id' => $enrollment->id, 'status' => 'failed']],
        ]);

        $this->assertSame('failed', $enrollment->fresh()->status);
        $this->assertNull($enrollment->fresh()->certificate);
    }

    public function test_a_failed_enrollment_can_be_flipped_back_to_passed()
    {
        $this->seedDefaultTrainingTemplate();

        $admin = Admin::factory()->create();
        $enrollment = CourseEnrollment::factory()->create(['status' => 'failed', 'graded_at' => now()]);

        $this->actingAs($admin, 'admin')->post(route('admin.course-grading.save'), [
            'grades' => [['id' => $enrollment->id, 'status' => 'passed']],
        ]);

        $this->assertSame('passed', $enrollment->fresh()->status);
        $this->assertNotNull($enrollment->fresh()->certificate);
    }
}
