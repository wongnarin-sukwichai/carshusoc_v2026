<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CertificateTemplate;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExamScoreManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * applyScore() always issues a certificate on save (App\Services\CertificateIssuer),
     * which requires a default CertificateTemplate for the 'exam' center to exist —
     * without one every test here would 404 via ModelNotFoundException, not because
     * of anything routing-related.
     */
    private function seedDefaultExamTemplate(): void
    {
        CertificateTemplate::factory()->create(['service_center_code' => 'exam', 'is_default' => true]);
    }

    public function test_admin_can_save_room_and_seat_number_alongside_a_score()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        $registration = ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'registered',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.exam-registrations.update', $registration), [
            'listening_score' => 20,
            'reading_score' => 18,
            'conversation_score' => 22,
            'grammar_score' => 19,
            'room' => '204',
            'seat_number' => 'A12',
        ])->assertRedirect();

        $registration->refresh();
        $this->assertSame('204', $registration->room);
        $this->assertSame('A12', $registration->seat_number);
        $this->assertSame(79, $registration->total_score);
    }

    public function test_room_and_seat_number_are_optional()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        $registration = ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'registered',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.exam-registrations.update', $registration), [
            'listening_score' => 20,
            'reading_score' => 18,
            'conversation_score' => 22,
            'grammar_score' => 19,
        ])->assertRedirect();

        $registration->refresh();
        $this->assertNull($registration->room);
        $this->assertNull($registration->seat_number);
    }

    public function test_admin_can_download_the_score_import_template()
    {
        $exam = Exam::factory()->create();
        $admin = Admin::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.exams.score-template', $exam));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_export_registrants_with_real_emails_and_blank_score_columns()
    {
        $exam = Exam::factory()->create();
        $admin = Admin::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['email' => 'examinee@example.com']);
        ExamRegistration::create(['user_id' => $user->id, 'exam_id' => $exam->id, 'status' => 'registered']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.exams.export-registrants', $exam));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

        $this->assertSame('email', $sheet->getCell('A1')->getValue());
        $this->assertSame('grammar', $sheet->getCell('G1')->getValue());
        $this->assertSame('examinee@example.com', $sheet->getCell('A2')->getValue());
        $this->assertSame('', (string) $sheet->getCell('B2')->getValue());
        $this->assertSame('', (string) $sheet->getCell('D2')->getValue());
    }

    public function test_validating_a_file_reports_row_errors_without_saving_anything()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        User::factory()->create(['email' => 'valid.student@example.com']);

        $csv = "email,room,seat_number,listening,reading,conversation,grammar\n"
            ."valid.student@example.com,204,A12,20,18,22,19\n"
            .",,,20,18,22,19\n";

        $file = UploadedFile::fake()->createWithContent('scores.csv', $csv);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.exams.import-scores.validate', $exam), ['file' => $file]);

        $response->assertRedirect();
        $preview = $response->getSession()->get('importPreview');

        $this->assertSame(1, $preview['validCount']);
        $this->assertSame(1, $preview['invalidCount']);
        $this->assertSame(0, ExamRegistration::count());
    }

    public function test_admin_can_import_valid_scores_from_a_file()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        User::factory()->create(['email' => 'valid.student@example.com']);

        $csv = "email,room,seat_number,listening,reading,conversation,grammar\n"
            ."valid.student@example.com,204,A12,20,18,22,19\n";

        $file = UploadedFile::fake()->createWithContent('scores.csv', $csv);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.exams.import-scores', $exam), ['file' => $file]);

        $response->assertRedirect();
        $this->assertNull($response->getSession()->get('error'));

        $registration = ExamRegistration::where('exam_id', $exam->id)->first();
        $this->assertNotNull($registration);
        $this->assertSame(79, $registration->total_score);
        $this->assertSame('204', $registration->room);
        $this->assertSame('A12', $registration->seat_number);
    }

    public function test_import_is_rejected_and_nothing_is_saved_when_a_row_is_invalid()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        User::factory()->create(['email' => 'valid.student@example.com']);

        $csv = "email,room,seat_number,listening,reading,conversation,grammar\n"
            ."valid.student@example.com,204,A12,20,18,22,19\n"
            ."unknown.person@example.com,,,20,18,22,19\n";

        $file = UploadedFile::fake()->createWithContent('scores.csv', $csv);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.exams.import-scores', $exam), ['file' => $file]);

        $response->assertRedirect();
        $this->assertNotNull($response->getSession()->get('error'));
        $this->assertSame(0, ExamRegistration::count());
    }
}
