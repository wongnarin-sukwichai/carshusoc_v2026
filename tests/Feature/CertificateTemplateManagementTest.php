<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): Admin
    {
        return Admin::factory()->create(['role' => 'admin']);
    }

    public function test_first_template_created_for_a_center_is_forced_default()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.store'), [
            'service_center_code' => 'training',
            'name' => 'แบบฟอร์มแรก',
            'title' => 'Certificate',
            'signatory1_name' => 'A',
            'signatory1_title' => 'B',
            'border_color' => '#000000',
            'is_default' => false,
        ])->assertRedirect();

        $template = CertificateTemplate::where('name', 'แบบฟอร์มแรก')->firstOrFail();
        $this->assertTrue($template->is_default, 'the only template for a center must be forced default');
    }

    public function test_marking_a_new_template_default_unmarks_the_sibling()
    {
        $admin = $this->superAdmin();
        $existing = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.store'), [
            'service_center_code' => 'training',
            'name' => 'แบบฟอร์มใหม่',
            'title' => 'Certificate',
            'signatory1_name' => 'A',
            'signatory1_title' => 'B',
            'border_color' => '#000000',
            'is_default' => true,
        ])->assertRedirect();

        $this->assertFalse($existing->fresh()->is_default);
        $this->assertTrue(CertificateTemplate::where('name', 'แบบฟอร์มใหม่')->first()->is_default);
    }

    public function test_cannot_unset_default_when_it_is_the_only_default_for_the_center()
    {
        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $this->actingAs($admin, 'admin')->put(route('admin.certificate-templates.update', $template), [
            'name' => $template->name,
            'title' => $template->title,
            'signatory1_name' => $template->signatory1_name,
            'signatory1_title' => $template->signatory1_title,
            'border_color' => $template->border_color,
            'is_default' => false,
        ])->assertSessionHasErrors('is_default');

        $this->assertTrue($template->fresh()->is_default);
    }

    public function test_cannot_delete_the_last_template_for_a_center()
    {
        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $this->actingAs($admin, 'admin')->delete(route('admin.certificate-templates.destroy', $template))->assertRedirect();

        $this->assertModelExists($template);
    }

    public function test_cannot_delete_a_template_that_has_issued_certificates()
    {
        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);
        CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => false]);

        $enrollment = CourseEnrollment::factory()->create();
        Certificate::factory()->create([
            'certificate_template_id' => $template->id,
            'certifiable_type' => $enrollment->getMorphClass(),
            'certifiable_id' => $enrollment->id,
        ]);

        $this->actingAs($admin, 'admin')->delete(route('admin.certificate-templates.destroy', $template))->assertRedirect();

        $this->assertModelExists($template);
    }

    public function test_deleting_the_default_promotes_another_template_automatically()
    {
        $admin = $this->superAdmin();
        $default = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);
        $other = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => false]);

        $this->actingAs($admin, 'admin')->delete(route('admin.certificate-templates.destroy', $default))->assertRedirect();

        $this->assertModelMissing($default);
        $this->assertTrue($other->fresh()->is_default);
    }

    public function test_preview_draft_renders_training_template_with_mock_data()
    {
        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.preview-draft', $template), []);

        $response->assertOk();
        $response->assertSee('ชื่อผู้อบรม');
    }

    public function test_preview_draft_renders_exam_template_with_mock_data()
    {
        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'exam', 'is_default' => true]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.preview-draft', $template), []);

        $response->assertOk();
        $response->assertSee('ชื่อผู้เข้าสอบ');
        $response->assertSee('ห้องประชุมตัวอย่าง');
        $response->assertSee('B2');
    }

    public function test_uploading_a_background_image_persists_the_path_and_shows_in_preview_draft()
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $this->actingAs($admin, 'admin')->put(route('admin.certificate-templates.update', $template), [
            'name' => $template->name,
            'title' => $template->title,
            'signatory1_name' => $template->signatory1_name,
            'signatory1_title' => $template->signatory1_title,
            'signatory1_signature' => UploadedFile::fake()->image('sig1.png', 200, 80),
            'background_image' => UploadedFile::fake()->image('bg.png', 1600, 1131),
            'border_color' => $template->border_color,
            'is_default' => true,
        ])->assertRedirect();

        $template->refresh();
        $this->assertNotNull($template->background_image_path);
        $this->assertNotNull($template->signatory1_signature_path);
        Storage::disk('public')->assertExists($template->background_image_path);

        // Reopening "ดูตัวอย่างก่อนบันทึก" without changing anything must show
        // exactly the saved background/signature — it's a strict superset of
        // the old dedicated "preview saved version" flow, which is why that
        // flow was removed instead of kept alongside this one.
        $preview = $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.preview-draft', $template), []);
        $preview->assertOk();
        $preview->assertSee('class="background"', false);
        $preview->assertSee('data:image', false);
    }

    /**
     * Regression guard: the .name margin-top on the training certificate is
     * a fixed 250px (calibrated to a standardized background design — see
     * training.blade.php), so unlike the other spacing it can't be loosened
     * to make room if content overflows. Everything below it must stay tight
     * enough that even a worst-case template (all 4 signatories, a
     * near-max-length subtitle, long names) still renders on a single PDF
     * page — a second page would silently strand some signatories there.
     */
    public function test_training_certificate_stays_on_one_page_even_with_maximum_content()
    {
        $template = CertificateTemplate::factory()->create([
            'service_center_code' => 'training',
            'is_default' => true,
            'subtitle' => str_repeat('ก', 250),
            'signatory2_name' => 'ศาสตราจารย์ ดร.ทดสอบ ระบบสอง',
            'signatory2_title' => 'รองอธิการบดีฝ่ายวิชาการ',
            'signatory3_name' => 'ศาสตราจารย์ ดร.ทดสอบ ระบบสาม',
            'signatory3_title' => 'อธิการบดีมหาวิทยาลัยมหาสารคาม',
            'signatory4_name' => 'ผู้ช่วยศาสตราจารย์ ดร.ทดสอบ ระบบสี่',
            'signatory4_title' => 'ผู้อำนวยการศูนย์บริการวิชาการ',
        ]);

        $pdf = Pdf::loadView('certificates.training', [
            'template' => $template,
            'enrollment' => new CourseEnrollment(['graded_at' => now()]),
            'course' => new Course([
                'name_th' => 'หลักสูตรภาษาอังกฤษเพื่อการสื่อสารระดับสูงสำหรับบุคลากรทางการศึกษาและนิสิตระดับบัณฑิตศึกษา',
                'code' => 'MOCK-01',
                'start_date' => now()->subDays(14),
                'end_date' => now()->subDays(1),
            ]),
            'user' => new User(['name' => 'ชื่อผู้อบรมที่มีชื่อยาวมากเป็นพิเศษ นามสกุลยาวเช่นกัน']),
            'issuedAt' => now(),
        ])->setPaper('a4', 'landscape');

        $pdf->render();

        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    /**
     * Same regression guard as the training one above, but for the exam
     * center's portrait layout (see App\Services\CertificateIssuer — exam
     * certificates render 'a4'/'portrait', not landscape) and its 2-per-row
     * "pyramid" signature layout (partials/signatures-pyramid.blade.php),
     * which stacks vertically instead of spreading across one wide row —
     * worth checking separately since that's more at risk of pushing onto
     * a second page than training's single row ever was.
     */
    public function test_exam_certificate_stays_on_one_page_even_with_maximum_content()
    {
        $template = CertificateTemplate::factory()->create([
            'service_center_code' => 'exam',
            'is_default' => true,
            'signatory2_name' => 'ศาสตราจารย์ ดร.ทดสอบ ระบบสอง',
            'signatory2_title' => 'รองอธิการบดีฝ่ายวิชาการ',
            'signatory3_name' => 'ศาสตราจารย์ ดร.ทดสอบ ระบบสาม',
            'signatory3_title' => 'อธิการบดีมหาวิทยาลัยมหาสารคาม',
            'signatory4_name' => 'ผู้ช่วยศาสตราจารย์ ดร.ทดสอบ ระบบสี่',
            'signatory4_title' => 'ผู้อำนวยการศูนย์บริการวิชาการ',
        ]);

        $pdf = Pdf::loadView('certificates.exam', [
            'template' => $template,
            'registration' => new ExamRegistration([
                'room' => 'อาคารเฉลิมพระเกียรติ ห้อง 204',
                'seat_number' => 'A123456',
                'listening_score' => 20,
                'reading_score' => 18,
                'conversation_score' => 22,
                'grammar_score' => 19,
                'total_score' => 79,
                'cefr_level' => 'B2',
            ]),
            'exam' => new Exam([
                'name_th' => 'MSU-EPT',
                'location' => 'ห้องประชุมชั้น 5 อาคารเฉลิมพระเกียรติ คณะมนุษยศาสตร์และสังคมศาสตร์ มหาวิทยาลัยมหาสารคาม',
                'exam_date' => now(),
            ]),
            'user' => new User(['name' => 'ชื่อผู้เข้าสอบที่มีชื่อยาวมากเป็นพิเศษ นามสกุลยาวเช่นกัน']),
            'issuedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $pdf->render();

        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_preview_draft_shows_pending_unsaved_uploads_without_persisting_them()
    {
        Storage::fake('public');

        $admin = $this->superAdmin();
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.certificate-templates.preview-draft', $template), [
            'title' => $template->title,
            'subtitle' => 'ทดสอบยังไม่บันทึก',
            'signatory1_name' => $template->signatory1_name,
            'signatory1_title' => $template->signatory1_title,
            'background_image' => UploadedFile::fake()->image('draft-bg.png', 1600, 1131),
            'border_color' => $template->border_color,
        ]);

        $response->assertOk();
        $response->assertSee('ทดสอบยังไม่บันทึก');
        $response->assertSee('data:image', false);

        // Nothing about the real, saved row may have changed.
        $template->refresh();
        $this->assertNull($template->background_image_path);
        $this->assertNotEquals('ทดสอบยังไม่บันทึก', $template->subtitle);

        // The temp file used to build the response must not be left behind.
        Storage::disk('public')->assertDirectoryEmpty('certificate-templates/drafts');
    }

    public function test_staff_cannot_access_certificate_template_preview_draft()
    {
        $staff = Admin::factory()->create(['role' => 'staff']);
        $template = CertificateTemplate::factory()->create(['service_center_code' => 'training', 'is_default' => true]);

        $this->actingAs($staff, 'admin')
            ->post(route('admin.certificate-templates.preview-draft', $template), [])
            ->assertForbidden();
    }
}
