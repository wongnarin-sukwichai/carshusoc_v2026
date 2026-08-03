<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CertificateTemplate;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamScoreManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * applyScore() always issues a certificate on save (App\Services\CertificateIssuer),
     * which requires a default CertificateTemplate for the 'exam' center to exist —
     * without one every test here would 404 via ModelNotFoundException, not because
     * of anything routing-related.
     */
    private function seedDefaultExamTemplate(): void
    {
        CertificateTemplate::factory()->create(['service_center_code' => 'exam', 'is_default' => true]);
    }

    public function test_admin_can_save_room_and_seat_number_alongside_a_score()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        $registration = ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'registered',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.exam-registrations.update', $registration), [
            'listening_score' => 20,
            'reading_score' => 18,
            'conversation_score' => 22,
            'grammar_score' => 19,
            'room' => '204',
            'seat_number' => 'A12',
        ])->assertRedirect();

        $registration->refresh();
        $this->assertSame('204', $registration->room);
        $this->assertSame('A12', $registration->seat_number);
        $this->assertSame(79, $registration->total_score);
    }

    public function test_room_and_seat_number_are_optional()
    {
        $this->seedDefaultExamTemplate();

        $admin = Admin::factory()->create(['role' => 'admin']);
        $exam = Exam::factory()->create();
        $registration = ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'registered',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.exam-registrations.update', $registration), [
            'listening_score' => 20,
            'reading_score' => 18,
            'conversation_score' => 22,
            'grammar_score' => 19,
        ])->assertRedirect();

        $registration->refresh();
        $this->assertNull($registration->room);
        $this->assertNull($registration->seat_number);
    }
}
