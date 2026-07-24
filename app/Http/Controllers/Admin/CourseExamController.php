<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ScoreScale;
use Inertia\Inertia;
use Inertia\Response;

class CourseExamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/CoursesExams', [
            'courses' => Course::with('prerequisite:id,name_th')->orderBy('code')->get(),
            'exams' => Exam::orderBy('code')->get(),
            'certificateTemplates' => CertificateTemplate::orderBy('service_center_code')->get(['id', 'service_center_code', 'name']),
            'scoreScales' => ScoreScale::orderByDesc('effective_from')->get(['id', 'name', 'version']),
        ]);
    }
}
