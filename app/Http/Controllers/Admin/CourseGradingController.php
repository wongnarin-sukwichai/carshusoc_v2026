<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\CertificateIssuer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function grade(Request $request, CourseEnrollment $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:passed,failed'],
        ]);

        $enrollment->update([
            'status' => $data['status'],
            'graded_at' => now(),
            'graded_by' => auth('admin')->id(),
        ]);

        if ($data['status'] === 'passed') {
            app(CertificateIssuer::class)->issueForCourseEnrollment($enrollment);
        }

        return back()->with('status', ['key' => 'flash.courseGrading.saved']);
    }
}
