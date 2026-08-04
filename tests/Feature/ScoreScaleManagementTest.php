<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ScoreScale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreScaleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function bandsPayload(): array
    {
        return [
            ['cefr_level' => 'B2', 'toeic_min' => 785, 'toeic_max' => 940, 'ept_min' => 68, 'ept_max' => 95],
            ['cefr_level' => 'B1', 'toeic_min' => 550, 'toeic_max' => 665, 'ept_min' => 56, 'ept_max' => 67],
        ];
    }

    public function test_admin_can_view_the_score_scales_page()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')->get(route('admin.score-scales'))->assertOk();
    }

    public function test_admin_can_create_a_score_scale_with_bands()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')->post(route('admin.score-scales.store'), [
            'name' => 'ตารางเทียบคะแนน 2569',
            'version' => 1,
            'effective_from' => '2026-01-01',
            'is_active' => true,
            'bands' => $this->bandsPayload(),
        ])->assertRedirect();

        $scale = ScoreScale::firstOrFail();
        $this->assertSame('ตารางเทียบคะแนน 2569', $scale->name);
        $this->assertCount(2, $scale->bands);
        $this->assertSame('B2', $scale->bands->first()->cefr_level);
    }

    public function test_admin_can_update_an_unused_score_scale()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $scale = ScoreScale::create(['name' => 'Old name', 'version' => 1, 'is_active' => true, 'effective_from' => '2026-01-01']);
        $scale->bands()->create(['cefr_level' => 'A1', 'toeic_min' => 120, 'toeic_max' => 170, 'sort_order' => 1]);

        $this->actingAs($admin, 'admin')->put(route('admin.score-scales.update', $scale), [
            'name' => 'New name',
            'version' => 1,
            'effective_from' => '2026-02-01',
            'is_active' => false,
            'bands' => $this->bandsPayload(),
        ])->assertRedirect();

        $scale->refresh();
        $this->assertSame('New name', $scale->name);
        $this->assertFalse($scale->is_active);
        $this->assertCount(2, $scale->bands);
    }

    public function test_editing_is_blocked_once_the_scale_has_scored_a_registration()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $scale = ScoreScale::create(['name' => 'Used scale', 'version' => 1, 'is_active' => true, 'effective_from' => '2026-01-01']);
        $scale->bands()->create(['cefr_level' => 'B2', 'toeic_min' => 785, 'toeic_max' => 940, 'sort_order' => 1]);

        $exam = Exam::factory()->create();
        ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'scored',
            'score_scale_id' => $scale->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.score-scales.update', $scale), [
            'name' => 'Should not apply',
            'version' => 1,
            'effective_from' => '2026-01-01',
            'is_active' => true,
            'bands' => $this->bandsPayload(),
        ]);

        $response->assertRedirect();
        $this->assertNotNull($response->getSession()->get('error'));
        $this->assertSame('Used scale', $scale->fresh()->name);
    }

    public function test_admin_can_toggle_active_even_when_the_scale_is_in_use()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $scale = ScoreScale::create(['name' => 'Used scale', 'version' => 1, 'is_active' => true, 'effective_from' => '2026-01-01']);

        $exam = Exam::factory()->create();
        ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'scored',
            'score_scale_id' => $scale->id,
        ]);

        $this->actingAs($admin, 'admin')->patch(route('admin.score-scales.toggle-active', $scale))->assertRedirect();

        $this->assertFalse($scale->fresh()->is_active);
    }

    public function test_deleting_is_blocked_when_the_scale_is_in_use()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $scale = ScoreScale::create(['name' => 'Used scale', 'version' => 1, 'is_active' => true, 'effective_from' => '2026-01-01']);

        $exam = Exam::factory()->create();
        ExamRegistration::create([
            'user_id' => User::factory()->create()->id,
            'exam_id' => $exam->id,
            'status' => 'scored',
            'score_scale_id' => $scale->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.score-scales.destroy', $scale));

        $response->assertRedirect();
        $this->assertNotNull($response->getSession()->get('error'));
        $this->assertNotNull($scale->fresh());
    }

    public function test_admin_can_delete_an_unused_score_scale()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $scale = ScoreScale::create(['name' => 'Unused scale', 'version' => 1, 'is_active' => true, 'effective_from' => '2026-01-01']);
        $scale->bands()->create(['cefr_level' => 'A1', 'toeic_min' => 120, 'toeic_max' => 170, 'sort_order' => 1]);

        $this->actingAs($admin, 'admin')->delete(route('admin.score-scales.destroy', $scale))->assertRedirect();

        $this->assertNull($scale->fresh());
    }
}
