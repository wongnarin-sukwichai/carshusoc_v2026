<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCenterPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_hitting_admin_area_is_redirected_to_home()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/');
    }

    public function test_user_guard_and_admin_guard_are_independent()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        // A "web" session should not grant access to admin routes.
        $response = $this->get('/admin');
        $response->assertRedirect('/');
    }

    public function test_authenticated_user_can_view_training_and_exam_centers()
    {
        $user = User::factory()->create();
        Course::factory()->create(['is_visible' => true]);
        Exam::factory()->create(['is_visible' => true]);

        $this->actingAs($user);

        $this->get('/user/training')->assertOk();
        $this->get('/user/exam')->assertOk();
        $this->get('/user/portfolio')->assertOk();
    }

    public function test_authenticated_admin_can_view_the_new_management_pages()
    {
        // certificate-templates is admin-only (see AdminRolePermissionTest)
        // — use that role here since this test is about page reachability, not gating.
        $admin = Admin::factory()->create(['role' => 'admin']);
        Course::factory()->create();
        Exam::factory()->create();

        $this->actingAs($admin, 'admin');

        $this->get('/admin/payments')->assertOk();
        $this->get('/admin/exam-scores')->assertOk();
        $this->get('/admin/course-grading')->assertOk();
        $this->get('/admin/certificate-templates')->assertOk();
    }
}
