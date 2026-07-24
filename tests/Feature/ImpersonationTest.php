<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_a_user()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.impersonate', $user))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->assertDatabaseHas('impersonation_logs', [
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'ended_at' => null,
        ]);
    }

    public function test_staff_can_also_impersonate()
    {
        $admin = Admin::factory()->create(['role' => 'staff']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.impersonate', $user))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_stopping_impersonation_logs_out_of_web_guard_but_keeps_admin_session()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.impersonate', $user));

        $this->post(route('admin.impersonate.stop'))->assertRedirect(route('admin.registrants'));

        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');

        $log = ImpersonationLog::where('admin_id', $admin->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($log->ended_at);
    }

    public function test_switching_impersonation_target_closes_the_previous_log()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.registrants.impersonate', $firstUser));
        $this->post(route('admin.registrants.impersonate', $secondUser));

        $this->assertAuthenticatedAs($secondUser, 'web');

        $firstLog = ImpersonationLog::where('user_id', $firstUser->id)->firstOrFail();
        $this->assertNotNull($firstLog->ended_at);

        $secondLog = ImpersonationLog::where('user_id', $secondUser->id)->firstOrFail();
        $this->assertNull($secondLog->ended_at);
    }
}
