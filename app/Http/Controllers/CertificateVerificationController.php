<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerificationController extends Controller
{
    public function show(Certificate $certificate): Response
    {
        $certificate->loadMissing('user', 'certificateTemplate', 'certifiable');

        $certifiable = $certificate->certifiable;
        $certifiable->loadMissing($certifiable instanceof CourseEnrollment ? 'course' : 'exam');

        return Inertia::render('CertificateVerify', [
            'certificate' => [
                'recipient_name' => $certificate->user->name,
                'service_center_code' => $certificate->certificateTemplate->service_center_code,
                'issued_at' => $certificate->issued_at->toDateString(),
                'item_name' => match (true) {
                    $certifiable instanceof CourseEnrollment => $certifiable->course->name_th,
                    $certifiable instanceof ExamRegistration => $certifiable->exam->name_th,
                    default => null,
                },
                'total_score' => $certifiable instanceof ExamRegistration ? $certifiable->total_score : null,
                'cefr_level' => $certifiable instanceof ExamRegistration ? $certifiable->cefr_level : null,
            ],
        ]);
    }
}
