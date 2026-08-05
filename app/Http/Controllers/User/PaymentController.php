<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use App\Models\Payment;
use App\Models\TranslationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Maps the short, client-facing type code to the model class. The client
     * never gets to name an arbitrary class directly.
     */
    protected const ALLOWED_TYPES = [
        'course_enrollment' => CourseEnrollment::class,
        'exam_registration' => ExamRegistration::class,
        'translation_request' => TranslationRequest::class,
    ];

    /**
     * Each payable type reaches "awaiting payment" differently: courses/exams
     * sit in pending_payment from the moment of enrollment, translation
     * requests only become payable once the admin has sent a quote.
     */
    protected const AWAITING_PAYMENT_STATUS = [
        'course_enrollment' => 'pending_payment',
        'exam_registration' => 'pending_payment',
        'translation_request' => 'quote_sent',
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payable_type' => ['required', 'string', 'in:course_enrollment,exam_registration,translation_request'],
            'payable_id' => ['required', 'integer'],
            'slip' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'wants_receipt' => ['sometimes', 'boolean'],
            'wants_mail_delivery' => ['sometimes', 'boolean'],
        ]);

        $modelClass = self::ALLOWED_TYPES[$data['payable_type']];
        $awaitingStatus = self::AWAITING_PAYMENT_STATUS[$data['payable_type']];

        /** @var CourseEnrollment|ExamRegistration|TranslationRequest $payable */
        $payable = $modelClass::where('id', $data['payable_id'])
            ->where('user_id', Auth::id())
            ->where('status', $awaitingStatus)
            ->firstOrFail();

        $hasPendingPayment = Payment::where('payable_type', $payable->getMorphClass())
            ->where('payable_id', $payable->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingPayment) {
            return back()->with('error', ['key' => 'flash.payment.alreadyPending']);
        }

        $amount = match (true) {
            $payable instanceof CourseEnrollment => $payable->course->price,
            $payable instanceof ExamRegistration => $payable->exam->price,
            $payable instanceof TranslationRequest => $payable->estimated_price,
        };

        $wantsMailDelivery = $payable instanceof ExamRegistration
            && $payable->exam->mail_delivery_available
            && $request->boolean('wants_mail_delivery');

        if ($wantsMailDelivery) {
            $amount += $payable->exam->mail_delivery_fee;

            $payable->update([
                'wants_mail_delivery' => true,
                'mail_delivery_fee_charged' => $payable->exam->mail_delivery_fee,
            ]);
        }

        // Slips are financial evidence, not public assets — keep them on the
        // private disk, admin-readable only via Admin\PaymentController::slip().
        $slipPath = $request->file('slip')->store('slips/'.Auth::id(), 'local');

        Payment::create([
            'user_id' => Auth::id(),
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->id,
            'amount' => $amount,
            'slip_path' => $slipPath,
            'status' => 'pending',
            'wants_receipt' => $data['wants_receipt'] ?? false,
        ]);

        return back()->with('status', ['key' => 'flash.payment.submitted']);
    }
}
