<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enroll_in_a_normal_open_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'registration_open_at' => now()->subWeek(),
            'registration_close_at' => now()->addWeek(),
        ]);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_user_cannot_enroll_before_registration_opens()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'registration_open_at' => now()->addWeek(),
            'registration_close_at' => now()->addMonth(),
        ]);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertStatus(422);

        $this->assertDatabaseMissing('course_enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function test_user_cannot_enroll_after_registration_closes()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'registration_open_at' => now()->subMonth(),
            'registration_close_at' => now()->subWeek(),
        ]);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertStatus(422);

        $this->assertDatabaseMissing('course_enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function test_registration_closing_today_is_still_open()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'registration_open_at' => now()->subWeek(),
            'registration_close_at' => now(),
        ]);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function test_user_cannot_enroll_without_passing_the_prerequisite()
    {
        $user = User::factory()->create();
        $prerequisite = Course::factory()->create();
        $course = Course::factory()->create([
            'prerequisite_course_id' => $prerequisite->id,
            'registration_open_at' => now()->subWeek(),
            'registration_close_at' => now()->addWeek(),
        ]);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertStatus(422);

        $this->assertDatabaseMissing('course_enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function test_user_can_enroll_after_passing_the_prerequisite()
    {
        $user = User::factory()->create();
        $prerequisite = Course::factory()->create();
        $course = Course::factory()->create([
            'prerequisite_course_id' => $prerequisite->id,
            'registration_open_at' => now()->subWeek(),
            'registration_close_at' => now()->addWeek(),
        ]);

        CourseEnrollment::create(['user_id' => $user->id, 'course_id' => $prerequisite->id, 'status' => 'passed']);

        $this->actingAs($user)->post(route('user.courses.enroll', $course))->assertRedirect();

        $this->assertDatabaseHas('course_enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }
}
