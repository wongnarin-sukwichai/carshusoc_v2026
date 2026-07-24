<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_home_page()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/');
    }

    public function test_authenticated_users_are_redirected_to_the_training_center()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertRedirect(route('user.training'));
    }
}
