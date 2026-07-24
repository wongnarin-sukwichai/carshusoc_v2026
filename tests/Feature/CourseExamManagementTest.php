<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Course;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseExamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_course()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.courses.store'), [
            'code' => 'TH-01',
            'name_th' => 'ภาษาไทยเพื่อการสื่อสาร ระดับ 1',
            'name_en' => 'Thai Communication Level 1',
            'language' => 'ไทย',
            'level' => 1,
            'price' => 1500,
            'is_visible' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('courses', ['code' => 'TH-01']);
    }

    public function test_course_code_must_be_unique()
    {
        $admin = Admin::factory()->create();
        Course::factory()->create(['code' => 'EN-01']);

        $this->actingAs($admin, 'admin')->post(route('admin.courses.store'), [
            'code' => 'EN-01',
            'name_th' => 'ซ้ำ',
            'name_en' => 'Dup',
            'language' => 'อังกฤษ',
            'level' => 1,
            'price' => 1000,
        ])->assertSessionHasErrors('code');
    }

    public function test_a_course_cannot_be_its_own_prerequisite()
    {
        $admin = Admin::factory()->create();
        $course = Course::factory()->create(['code' => 'EN-01']);

        $this->actingAs($admin, 'admin')->put(route('admin.courses.update', $course), [
            'code' => $course->code,
            'name_th' => $course->name_th,
            'name_en' => $course->name_en,
            'language' => $course->language,
            'level' => $course->level,
            'price' => $course->price,
            'prerequisite_course_id' => $course->id,
        ])->assertSessionHasErrors('prerequisite_course_id');
    }

    public function test_admin_can_toggle_course_visibility()
    {
        $admin = Admin::factory()->create();
        $course = Course::factory()->create(['is_visible' => true]);

        $this->actingAs($admin, 'admin')->patch(route('admin.courses.toggle-visibility', $course));

        $this->assertFalse($course->fresh()->is_visible);
    }

    public function test_admin_can_create_and_update_an_exam()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->post(route('admin.exams.store'), [
            'code' => 'EX-TOEIC',
            'type' => 'TOEIC',
            'name_th' => 'TOEIC รอบพิเศษ',
            'name_en' => 'TOEIC Special',
            'price' => 1800,
            'exam_date' => now()->addMonth()->toDateString(),
            'mail_delivery_available' => true,
            'mail_delivery_fee' => 60,
        ])->assertRedirect();

        $exam = Exam::where('code', 'EX-TOEIC')->firstOrFail();

        $this->actingAs($admin, 'admin')->put(route('admin.exams.update', $exam), [
            'code' => 'EX-TOEIC',
            'type' => 'TOEIC',
            'name_th' => 'TOEIC รอบพิเศษ (แก้ไข)',
            'name_en' => 'TOEIC Special (edited)',
            'price' => 1900,
            'exam_date' => $exam->exam_date->toDateString(),
        ])->assertRedirect();

        $this->assertSame('TOEIC รอบพิเศษ (แก้ไข)', $exam->fresh()->name_th);
    }
}
