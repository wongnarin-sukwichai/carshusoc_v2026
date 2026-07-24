<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_staff_account()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.staff.store'), [
            'name' => 'ทดสอบ ระบบ',
            'email' => 'test.staff@cars.ac.th',
            'password' => 'password123',
            'role' => 'staff',
        ])->assertRedirect();

        $this->assertDatabaseHas('admins', ['email' => 'test.staff@cars.ac.th', 'role' => 'staff']);

        $created = Admin::where('email', 'test.staff@cars.ac.th')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_admin_can_update_a_staff_account_without_changing_password()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $target = Admin::factory()->create(['role' => 'staff', 'name' => 'เดิม']);
        $originalHash = $target->password;

        $this->actingAs($admin, 'admin');

        $this->put(route('admin.staff.update', $target), [
            'name' => 'ใหม่',
            'email' => $target->email,
            'password' => '',
            'role' => 'staff',
        ])->assertRedirect();

        $target->refresh();
        $this->assertSame('ใหม่', $target->name);
        $this->assertSame($originalHash, $target->password);
    }

    public function test_admin_can_delete_another_staff_account()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $target = Admin::factory()->create(['role' => 'staff']);

        $this->actingAs($admin, 'admin');

        $this->delete(route('admin.staff.destroy', $target))->assertRedirect();

        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_their_own_account()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');

        $this->delete(route('admin.staff.destroy', $admin))->assertRedirect();

        $this->assertDatabaseHas('admins', ['id' => $admin->id]);
    }

    public function test_the_only_admin_cannot_demote_themselves()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin');

        $this->put(route('admin.staff.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $admin->refresh();
        $this->assertSame('admin', $admin->role);
    }

    public function test_an_admin_can_be_demoted_when_another_admin_remains()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $other = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($other, 'admin');

        $this->put(route('admin.staff.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'role' => 'staff',
        ])->assertSessionDoesntHaveErrors('role');

        $admin->refresh();
        $this->assertSame('staff', $admin->role);
    }
}
