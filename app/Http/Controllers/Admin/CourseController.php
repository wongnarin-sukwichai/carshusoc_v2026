<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $course = Course::create($this->validated($request));

        return back()->with('status', ['key' => 'flash.course.saved', 'params' => ['code' => $course->code]]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $oldLocation = $course->location;

        $course->update($this->validated($request, $course));

        if ($course->wasChanged('location')) {
            $this->notifyLocationChanged($course, $oldLocation, $course->location);
        }

        return back()->with('status', ['key' => 'flash.course.updated', 'params' => ['code' => $course->code]]);
    }

    protected function notifyLocationChanged(Course $course, ?string $oldLocation, ?string $newLocation): void
    {
        $notifier = app(EmailNotifier::class);

        $course->enrollments()->with('user')->get()->each(
            fn (CourseEnrollment $enrollment) => $notifier->send('location_changed', $enrollment->user, [
                '{{name}}' => $enrollment->user->name,
                '{{item_name}}' => $course->name_th,
                '{{old_location}}' => $oldLocation ?: '-',
                '{{new_location}}' => $newLocation ?: '-',
            ], $course)
        );
    }

    public function toggleVisibility(Course $course): RedirectResponse
    {
        $course->update(['is_visible' => ! $course->is_visible]);

        return back()->with('status', ['key' => 'flash.course.visibilityToggled', 'params' => ['code' => $course->code]]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Course $course = null): array
    {
        $prerequisiteRule = ['nullable', 'integer', 'exists:courses,id'];

        if ($course) {
            $prerequisiteRule[] = Rule::notIn([$course->id]);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course?->id)],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:50'],
            'level' => ['required', 'integer', 'min:1', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'prerequisite_course_id' => $prerequisiteRule,
            'location' => ['nullable', 'string', 'max:255'],
            'requires_receipt' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
        ]);

        if ($course && $data['prerequisite_course_id'] ?? null) {
            if ($this->wouldCreateCycle($course->id, $data['prerequisite_course_id'])) {
                throw ValidationException::withMessages([
                    'prerequisite_course_id' => 'ไม่สามารถตั้งวิชาก่อนหน้านี้ได้ เนื่องจากจะทำให้เกิดการอ้างอิงวนกลับ (A→B→A)',
                ]);
            }
        }

        return $data;
    }

    /**
     * Would setting $courseId's prerequisite to $candidatePrerequisiteId create a
     * cycle (direct or transitive)? Walks the candidate's own ancestor chain
     * looking for $courseId, with a visited-set guard in case pre-existing data
     * already loops.
     */
    private function wouldCreateCycle(int $courseId, ?int $candidatePrerequisiteId): bool
    {
        $visited = [];
        $currentId = $candidatePrerequisiteId;

        while ($currentId !== null) {
            if ($currentId === $courseId) {
                return true;
            }

            if (isset($visited[$currentId])) {
                break;
            }

            $visited[$currentId] = true;
            $currentId = Course::whereKey($currentId)->value('prerequisite_course_id');
        }

        return false;
    }
}
