<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Services\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $payments = Payment::with(['user', 'payable'])
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'user_name' => $payment->user->name,
                'user_email' => $payment->user->email,
                'label' => $this->labelFor($payment),
                'amount' => (float) $payment->amount,
                'has_slip' => (bool) $payment->slip_path,
                'status' => $payment->status,
                'wants_receipt' => $payment->wants_receipt,
                'wants_mail_delivery' => $payment->payable instanceof ExamRegistration ? $payment->payable->wants_mail_delivery : false,
                'mail_delivery_fee_charged' => $payment->payable instanceof ExamRegistration ? $payment->payable->mail_delivery_fee_charged : null,
                'created_at' => $payment->created_at->toDateTimeString(),
            ]);

        return Inertia::render('admin/Payments', [
            'payments' => $payments,
        ]);
    }

    public function slip(Payment $payment)
    {
        abort_unless($payment->slip_path, 404);

        return Storage::disk('local')->response($payment->slip_path);
    }

    public function approve(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'รายการนี้ถูกดำเนินการแล้ว');

        $payment->update([
            'status' => 'approved',
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
        ]);

        $payable = $payment->payable;

        if ($payable instanceof CourseEnrollment) {
            $payable->update(['status' => 'studying']);
        } elseif ($payable instanceof ExamRegistration) {
            $payable->update(['status' => 'registered']);
        } elseif ($payable instanceof TranslationRequest) {
            $payable->update(['status' => 'translating']);
        }

        app(EmailNotifier::class)->send('payment_approved', $payment->user, [
            '{{name}}' => $payment->user->name,
            '{{item_name}}' => $this->labelFor($payment),
        ], $payment);

        return back()->with('status', ['key' => 'flash.payment.approved']);
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'รายการนี้ถูกดำเนินการแล้ว');

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'approved_by' => auth('admin')->id(),
            'approved_at' => now(),
            'rejected_reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', ['key' => 'flash.payment.rejected']);
    }

    protected function labelFor(Payment $payment): string
    {
        $payable = $payment->payable;

        if ($payable instanceof CourseEnrollment) {
            return $payable->course->name_th ?? 'หลักสูตร';
        }

        if ($payable instanceof ExamRegistration) {
            return $payable->exam->name_th ?? 'รอบสอบ';
        }

        if ($payable instanceof TranslationRequest) {
            return 'งานแปล: '.$payable->file_name;
        }

        return 'รายการอื่น';
    }
}
