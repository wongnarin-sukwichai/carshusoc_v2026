<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\CourseEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
