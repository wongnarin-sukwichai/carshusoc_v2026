<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registrants_page()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');

        $this->get(route('admin.registrants'))->assertOk();
    }

    public function test_staff_can_also_access_registrants_page()
    {
        $admin = Admin::factory()->create(['role' => 'staff']);
        $this->actingAs($admin, 'admin');

        $this->get(route('admin.registrants'))->assertOk();
    }

    public function test_admin_can_add_a_user_account_with_a_password_and_no_reset_link_is_sent()
    {
        Notification::fake();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.store'), [
            'name' => 'ทดสอบ ผู้เรียน',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $user->password));

        Notification::assertNothingSent();
    }

    public function test_duplicate_email_is_rejected()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        User::factory()->create(['email' => 'dup@example.com']);
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.store'), [
            'name' => 'Someone',
            'email' => 'dup@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_is_required_when_adding_a_user()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.store'), [
            'name' => 'Someone',
            'email' => 'someone@example.com',
        ])->assertSessionHasErrors('password');
    }
}
