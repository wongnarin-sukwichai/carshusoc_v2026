<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentController extends Controller
{
    public function store(Course $course): RedirectResponse
    {
        abort_unless($course->is_visible, 404);

        /** @var User $user */
        $user = Auth::user();

        [$eligible, $reason] = $this->eligibility($course, $user);

        abort_if(! $eligible, 422, $reason);

        CourseEnrollment::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'pending_payment', 'enrolled_at' => now()]
        );

        return back()->with('status', ['key' => 'flash.course.enrolled']);
    }

    /**
     * Mirrors checkCourseEligibility() from the original mockup.
     *
     * @return array{0: bool, 1: ?string}
     */
    protected function eligibility(Course $course, User $user): array
    {
        $existing = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing?->status === 'passed') {
            return [false, 'ผ่านการอบรมหลักสูตรนี้เรียบร้อยแล้ว'];
        }

        if ($existing && in_array($existing->status, ['studying', 'pending_payment'], true)) {
            return [false, 'อยู่ระหว่างการอบรมหรือรอตรวจสอบสลิป'];
        }

        if ($course->prerequisite_course_id) {
            $prerequisitePassed = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->prerequisite_course_id)
                ->where('status', 'passed')
                ->exists();

            if (! $prerequisitePassed) {
                return [false, 'ต้องผ่านหลักสูตรก่อนหน้าก่อนจึงจะสมัครวิชานี้ได้'];
            }
        }

        return [true, null];
    }
}
