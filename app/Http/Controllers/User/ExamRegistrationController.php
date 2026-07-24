<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ExamRegistrationController extends Controller
{
    public function store(Exam $exam): RedirectResponse
    {
        abort_unless($exam->is_visible, 404);

        $userId = Auth::id();

        $alreadyRegistered = ExamRegistration::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->exists();

        abort_if($alreadyRegistered, 422, 'ท่านได้ลงทะเบียนรอบสอบนี้ไปแล้ว');

        ExamRegistration::create([
            'user_id' => $userId,
            'exam_id' => $exam->id,
            'status' => 'pending_payment',
        ]);

        return back()->with('status', ['key' => 'flash.exam.registered']);
    }
}
