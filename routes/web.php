<?php

use App\Http\Controllers\CertificateVerificationController;
use App\Models\Course;
use App\Models\Exam;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'courses' => Course::with('prerequisite')->where('is_visible', true)->orderBy('level')->get(),
        'exams' => Exam::where('is_visible', true)->orderBy('exam_date')->get(),
    ]);
})->name('home');

Route::get('certificates/verify/{certificate:verification_hash}', [CertificateVerificationController::class, 'show'])
    ->name('certificates.verify');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
