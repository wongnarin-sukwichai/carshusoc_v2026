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
use App\Models\Payment;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    // Kept as the bare "dashboard" name (not "user.dashboard") so the existing
    // auth flow controllers/components that call route('dashboard') still resolve.
    // There's no standalone dashboard page in the design — it redirects straight
    // into the training center, the first item in the user sidebar.
    Route::get('dashboard', fn () => redirect()->route('user.training'))->name('dashboard');

    // Shared by the training/exam/translation pages below: for the given
    // payable type, works out (a) which payables still have a slip awaiting
    // review, so the upload button can hide, and (b) the rejection reason of
    // the most recent attempt for payables whose latest slip was rejected
    // and hasn't been resubmitted yet, so the card can explain why.
    $paymentStatusFor = function (string $payableType) {
        // Ordered by id (always monotonically increasing) rather than
        // created_at — attempts made in quick succession in tests/CI can
        // share the same created_at second, which would make sortByDesc()
        // pick an arbitrary one instead of the truly latest attempt.
        $latestPerPayable = Payment::where('user_id', auth()->id())
            ->where('payable_type', $payableType)
            ->orderBy('id')
            ->get()
            ->groupBy('payable_id')
            ->map(fn ($payments) => $payments->last());

        return [
            'pendingIds' => $latestPerPayable->filter(fn (Payment $p) => $p->status === 'pending')->keys()->values(),
            'rejectionReasons' => $latestPerPayable
                ->filter(fn (Payment $p) => $p->status === 'rejected' && $p->rejected_reason)
                ->map(fn (Payment $p) => $p->rejected_reason),
        ];
    };

    Route::prefix('user')->name('user.')->group(function () use ($paymentStatusFor) {
        Route::get('training', function () use ($paymentStatusFor) {
            $courses = Course::with('prerequisite')->where('is_visible', true)->orderBy('level')->get();
            $enrollments = auth()->user()->courseEnrollments()->get()->keyBy('course_id');
            $paymentStatus = $paymentStatusFor('course_enrollment');

            return Inertia::render('user/Training', [
                'courses' => $courses,
                'enrollments' => $enrollments,
                'pendingPaymentIds' => $paymentStatus['pendingIds'],
                'rejectionReasons' => $paymentStatus['rejectionReasons'],
            ]);
        })->name('training');

        Route::get('exam', function () use ($paymentStatusFor) {
            $exams = Exam::where('is_visible', true)->orderBy('exam_date')->get();
            $registrations = auth()->user()->examRegistrations()->get()->keyBy('exam_id');
            $paymentStatus = $paymentStatusFor('exam_registration');

            return Inertia::render('user/Exam', [
                'exams' => $exams,
                'registrations' => $registrations,
                'pendingPaymentIds' => $paymentStatus['pendingIds'],
                'rejectionReasons' => $paymentStatus['rejectionReasons'],
            ]);
        })->name('exam');

        Route::get('translation', function () use ($paymentStatusFor) {
            $requests = auth()->user()->translationRequests()->latest()->paginate(5)->withQueryString();
            $paymentStatus = $paymentStatusFor('translation_request');

            return Inertia::render('user/Translation', [
                'requests' => $requests,
                'pendingPaymentIds' => $paymentStatus['pendingIds'],
                'rejectionReasons' => $paymentStatus['rejectionReasons'],
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
