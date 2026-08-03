<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\CertificateIssuer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CourseGradingController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::orderBy('code')->get(['id', 'code', 'name_th', 'level']);

        $selectedCourse = $courses->firstWhere('id', (int) $request->query('course')) ?? $courses->first();

        $enrollments = $selectedCourse
            ? CourseEnrollment::with('user')
                ->where('course_id', $selectedCourse->id)
                ->latest()
                ->get()
                ->map(fn (CourseEnrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'user_name' => $enrollment->user->name,
                    'user_email' => $enrollment->user->email,
                    'status' => $enrollment->status,
                ])
            : collect();

        return Inertia::render('admin/CourseGrading', [
            'courses' => $courses,
            'selectedCourseId' => $selectedCourse?->id,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Commits the admin's pass/fail selection for whichever enrollments they
     * toggled in admin/CourseGrading.vue, in one request. Grading is editable
     * indefinitely — not just while "studying" — so an already-passed or
     * already-failed enrollment can be flipped later too; only rows still
     * awaiting payment are excluded, since there's nothing to grade yet.
     * Flipping away from "passed" revokes the previously issued certificate
     * (deletes the row + PDF) so a downgraded result can't still be verified
     * or downloaded.
     */
    public function gradeBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.id' => ['required', 'integer', 'exists:course_enrollments,id'],
            'grades.*.status' => ['required', 'in:passed,failed'],
        ]);

        $issuer = app(CertificateIssuer::class);
        $adminId = auth('admin')->id();

        foreach ($data['grades'] as $entry) {
            $enrollment = CourseEnrollment::where('id', $entry['id'])
                ->whereIn('status', ['studying', 'passed', 'failed'])
                ->first();

            if (! $enrollment) {
                continue;
            }

            $enrollment->update([
                'status' => $entry['status'],
                'graded_at' => now(),
                'graded_by' => $adminId,
            ]);

            if ($entry['status'] === 'passed') {
                $issuer->issueForCourseEnrollment($enrollment);
            } else {
                $this->revokeCertificateIfAny($enrollment);
            }
        }

        return back()->with('status', ['key' => 'flash.courseGrading.saved']);
    }

    private function revokeCertificateIfAny(CourseEnrollment $enrollment): void
    {
        $certificate = $enrollment->certificate()->first();

        if (! $certificate) {
            return;
        }

        Storage::disk('public')->delete($certificate->pdf_path);
        $certificate->delete();
    }
}
