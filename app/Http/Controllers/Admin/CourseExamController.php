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
            'courses' => Course::with('prerequisite:id,name_th')->orderBy('code')->paginate(10, ['*'], 'coursesPage')->withQueryString(),
            'exams' => Exam::orderBy('code')->paginate(10, ['*'], 'examsPage')->withQueryString(),
            // Full, unpaginated — used to populate the "prerequisite" dropdown
            // in the create/edit course forms, which needs every course to
            // pick from regardless of which page of the table is showing.
            'allCourses' => Course::orderBy('code')->get(['id', 'code', 'name_th']),
            'certificateTemplates' => CertificateTemplate::orderBy('service_center_code')->get(['id', 'service_center_code', 'name']),
            'scoreScales' => ScoreScale::orderByDesc('effective_from')->get(['id', 'name', 'version']),
        ]);
    }
}
