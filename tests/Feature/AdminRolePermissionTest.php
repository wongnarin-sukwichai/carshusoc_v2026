<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_restricted_pages()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.staff'))->assertOk();
        $this->get(route('admin.email-templates'))->assertOk();
        $this->get(route('admin.certificate-templates'))->assertOk();
    }

    public function test_staff_is_forbidden_from_restricted_pages()
    {
        $admin = Admin::factory()->create(['role' => 'staff']);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.staff'))->assertForbidden();
        $this->get(route('admin.email-templates'))->assertForbidden();
        $this->get(route('admin.certificate-templates'))->assertForbidden();
    }

    public function test_staff_can_still_access_day_to_day_pages()
    {
        $admin = Admin::factory()->create(['role' => 'staff']);

        $this->actingAs($admin, 'admin');

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.payments'))->assertOk();
        $this->get(route('admin.registrants'))->assertOk();
        $this->get(route('admin.courses-exams'))->assertOk();
        $this->get(route('admin.exam-scores'))->assertOk();
        $this->get(route('admin.course-grading'))->assertOk();
        $this->get(route('admin.translation-quotes'))->assertOk();
        $this->get(route('admin.manual'))->assertOk();
    }
}
