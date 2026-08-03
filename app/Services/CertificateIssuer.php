<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateIssuer
{
    public function issueForCourseEnrollment(CourseEnrollment $enrollment): Certificate
    {
        $enrollment->loadMissing('course', 'user');

        $template = $this->defaultTemplateFor('training');
        $issuedAt = now();

        $pdfPath = $this->render('certificates.training', [
            'template' => $template,
            'enrollment' => $enrollment,
            'course' => $enrollment->course,
            'user' => $enrollment->user,
            'issuedAt' => $issuedAt,
        ], 'landscape');

        return $this->upsertCertificate($enrollment, $enrollment->user_id, $template, null, $pdfPath, $issuedAt);
    }

    public function issueForExamRegistration(ExamRegistration $registration): Certificate
    {
        $registration->loadMissing('exam', 'user', 'scoreScale.bands');

        $template = $this->defaultTemplateFor('exam');
        $issuedAt = now();

        $pdfPath = $this->render('certificates.exam', [
            'template' => $template,
            'registration' => $registration,
            'exam' => $registration->exam,
            'user' => $registration->user,
            'issuedAt' => $issuedAt,
        ], 'portrait');

        return $this->upsertCertificate($registration, $registration->user_id, $template, $registration->score_scale_id, $pdfPath, $issuedAt);
    }

    protected function defaultTemplateFor(string $serviceCenterCode): CertificateTemplate
    {
        return CertificateTemplate::where('service_center_code', $serviceCenterCode)
            ->where('is_default', true)
            ->firstOrFail();
    }

    protected function render(string $view, array $data, string $orientation = 'landscape'): string
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        $path = 'certificates/'.Str::uuid().'.pdf';

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    protected function upsertCertificate(
        CourseEnrollment|ExamRegistration $certifiable,
        int $userId,
        CertificateTemplate $template,
        ?int $scoreScaleId,
        string $pdfPath,
        Carbon $issuedAt
    ): Certificate {
        return Certificate::updateOrCreate(
            [
                'certifiable_type' => $certifiable->getMorphClass(),
                'certifiable_id' => $certifiable->id,
            ],
            [
                'user_id' => $userId,
                'certificate_template_id' => $template->id,
                'score_scale_id' => $scoreScaleId,
                'verification_hash' => Str::random(40),
                'issued_at' => $issuedAt,
                'pdf_path' => $pdfPath,
            ]
        );
    }
}
