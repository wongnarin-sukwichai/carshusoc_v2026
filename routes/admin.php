<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CertificateTemplateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseExamController;
use App\Http\Controllers\Admin\CourseGradingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamScoreController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RegistrantController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TranslationRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('registrants', [RegistrantController::class, 'index'])->name('registrants');
        Route::post('registrants', [RegistrantController::class, 'store'])->name('registrants.store');
        Route::post('registrants/{user}/impersonate', [ImpersonationController::class, 'start'])->name('registrants.impersonate');
        Route::post('impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
        Route::get('courses-exams', [CourseExamController::class, 'index'])->name('courses-exams');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::patch('courses/{course}/toggle-visibility', [CourseController::class, 'toggleVisibility'])->name('courses.toggle-visibility');

        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::patch('exams/{exam}/toggle-visibility', [ExamController::class, 'toggleVisibility'])->name('exams.toggle-visibility');
        Route::get('translation-quotes', [TranslationRequestController::class, 'index'])->name('translation-quotes');
        Route::get('translation-requests/{translationRequest}/source', [TranslationRequestController::class, 'source'])->name('translation-requests.source');
        Route::post('translation-requests/{translationRequest}/quote', [TranslationRequestController::class, 'quote'])->name('translation-requests.quote');
        Route::post('translation-requests/{translationRequest}/deliver', [TranslationRequestController::class, 'deliver'])->name('translation-requests.deliver');

        Route::get('manual', fn () => Inertia::render('admin/Manual'))->name('manual');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments');
        Route::get('payments/{payment}/slip', [PaymentController::class, 'slip'])->name('payments.slip');
        Route::post('payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

        Route::get('exam-scores', [ExamScoreController::class, 'index'])->name('exam-scores');
        Route::put('exam-registrations/{registration}', [ExamScoreController::class, 'updateScore'])->name('exam-registrations.update');
        Route::post('exams/{exam}/import-scores', [ExamScoreController::class, 'importCsv'])->name('exams.import-scores');

        Route::get('course-grading', [CourseGradingController::class, 'index'])->name('course-grading');
        Route::post('course-enrollments/{enrollment}/grade', [CourseGradingController::class, 'grade'])->name('course-enrollments.grade');

        // Reserved for admin: managing admin accounts, email templates, and
        // certificate templates is system configuration, not day-to-day operations.
        Route::middleware('admin.role:admin')->group(function () {
            Route::get('staff', [StaffController::class, 'index'])->name('staff');
            Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('staff/{admin}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('staff/{admin}', [StaffController::class, 'destroy'])->name('staff.destroy');
            Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates');
            Route::put('email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update');

            Route::get('certificate-templates', [CertificateTemplateController::class, 'index'])->name('certificate-templates');
            Route::post('certificate-templates', [CertificateTemplateController::class, 'store'])->name('certificate-templates.store');
            Route::put('certificate-templates/{certificateTemplate}', [CertificateTemplateController::class, 'update'])->name('certificate-templates.update');
            Route::delete('certificate-templates/{certificateTemplate}', [CertificateTemplateController::class, 'destroy'])->name('certificate-templates.destroy');
        });
    });
});
