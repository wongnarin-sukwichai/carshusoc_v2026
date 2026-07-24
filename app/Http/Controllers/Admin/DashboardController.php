<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $languageBreakdown = CourseEnrollment::join('courses', 'courses.id', '=', 'course_enrollments.course_id')
            ->select('courses.language', DB::raw('count(*) as total'))
            ->groupBy('courses.language')
            ->orderByDesc('total')
            ->get();

        $maxLanguageCount = max($languageBreakdown->max('total'), 1);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'revenue' => (float) Payment::where('status', 'approved')->sum('amount'),
                'registrants' => User::count(),
                'enrollments' => CourseEnrollment::count(),
                'translationJobs' => TranslationRequest::count(),
            ],
            'languageBreakdown' => $languageBreakdown->map(fn ($row) => [
                'language' => $row->language,
                'count' => (int) $row->total,
                'percent' => (int) round(($row->total / $maxLanguageCount) * 100),
            ]),
        ]);
    }
}
