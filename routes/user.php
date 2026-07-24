<?php

use App\Http\Controllers\User\CertificateController;
use App\Http\Controllers\User\CourseEnrollmentController;
use App\Http\Controllers\User\ExamRegistrationController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\TranslationRequestController;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamRegistration;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    // Kept as the bare "dashboard" name (not "user.dashboard") so the existing
    // auth flow controllers/components that call route('dashboard') still resolve.
    // There's no standalone dashboard page in the design — it redirects straight
    // into the training center, the first item in the user sidebar.
    Route::get('dashboard', fn () => redirect()->route('user.training'))->name('dashboard');

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('training', function () {
            $courses = Course::with('prerequisite')->where('is_visible', true)->orderBy('level')->get();
            $enrollments = auth()->user()->courseEnrollments()->get()->keyBy('course_id');

            return Inertia::render('user/Training', [
                'courses' => $courses,
                'enrollments' => $enrollments,
            ]);
        })->name('training');

        Route::get('exam', function () {
            $exams = Exam::where('is_visible', true)->orderBy('exam_date')->get();
            $registrations = auth()->user()->examRegistrations()->get()->keyBy('exam_id');

            return Inertia::render('user/Exam', [
                'exams' => $exams,
                'registrations' => $registrations,
            ]);
        })->name('exam');

        Route::get('translation', function () {
            $requests = auth()->user()->translationRequests()->latest()->get();

            return Inertia::render('user/Translation', [
                'requests' => $requests,
            ]);
        })->name('translation');

        Route::get('portfolio', function () {
            $certificates = auth()->user()->certificates()
                ->with(['certifiable' => fn ($morphTo) => $morphTo->morphWith([
                    CourseEnrollment::class => ['course'],
                    ExamRegistration::class => ['exam'],
                ])])
                ->latest('issued_at')
                ->get()
                ->map(fn (Certificate $certificate) => [
                    'id' => $certificate->id,
                    'kind' => $certificate->certifiable instanceof CourseEnrollment ? 'course' : 'exam',
                    'title' => $certificate->certifiable instanceof CourseEnrollment
                        ? $certificate->certifiable->course->name_th
                        : $certificate->certifiable->exam->name_th,
                    'issued_at' => $certificate->issued_at->toDateString(),
                ]);

            return Inertia::render('user/Portfolio', [
                'certificates' => $certificates,
            ]);
        })->name('portfolio');

        Route::post('courses/{course}/enroll', [CourseEnrollmentController::class, 'store'])->name('courses.enroll');
        Route::post('exams/{exam}/register', [ExamRegistrationController::class, 'store'])->name('exams.register');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

        Route::post('translations', [TranslationRequestController::class, 'store'])->name('translations.store');
        Route::get('translations/{translationRequest}/download', [TranslationRequestController::class, 'download'])->name('translations.download');
    });
});
