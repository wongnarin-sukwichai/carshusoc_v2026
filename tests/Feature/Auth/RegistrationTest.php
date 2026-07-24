<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_without_a_password_and_receive_a_reset_link()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertGuest();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
