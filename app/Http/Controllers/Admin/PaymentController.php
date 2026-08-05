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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');

        // Grouped by payable (not one row per Payment) so repeat slip attempts
        // for the same enrollment/registration/request — e.g. a rejected slip
        // followed by a corrected resubmission — read as one case instead of
        // scattered, unrelated-looking rows. Ordered by id (always
        // monotonically increasing), not created_at — attempts made in quick
        // succession can share the same created_at second, which would make
        // "latest" ambiguous.
        $cases = Payment::with(['user', 'payable'])
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->payable_type.':'.$payment->payable_id)
            ->map(function ($payments) {
                $payments = $payments->values();
                $latest = $payments->last();

                return [
                    'case_key' => $latest->payable_type.':'.$latest->payable_id,
                    'user_name' => $latest->user->name,
                    'user_email' => $latest->user->email,
                    'label' => $this->labelFor($latest),
                    'latest_status' => $latest->status,
                    'latest_id' => $latest->id,
                    'payments' => $payments->map(fn (Payment $payment) => [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'has_slip' => (bool) $payment->slip_path,
                        'status' => $payment->status,
                        'wants_receipt' => $payment->wants_receipt,
                        'wants_mail_delivery' => $payment->payable instanceof ExamRegistration ? $payment->payable->wants_mail_delivery : false,
                        'mail_delivery_fee_charged' => $payment->payable instanceof ExamRegistration ? $payment->payable->mail_delivery_fee_charged : null,
                        'rejected_reason' => $payment->rejected_reason,
                        'created_at' => $payment->created_at->toDateTimeString(),
                    ])->values(),
                ];
            })
            ->values()
            // Pending cases first; within each bucket, most recently active case first.
            ->sortByDesc(fn (array $case) => ($case['latest_status'] === 'pending' ? 1 : 0) * 1000000000 + $case['latest_id'])
            ->values();

        // Tab counts computed from the full, unfiltered set of cases — so the
        // badges always reflect true totals rather than just what happens to
        // land on the current page.
        $statusCounts = [
            'all' => $cases->count(),
            'pending' => $cases->where('latest_status', 'pending')->count(),
            'approved' => $cases->where('latest_status', 'approved')->count(),
            'rejected' => $cases->where('latest_status', 'rejected')->count(),
        ];

        if ($status !== 'all') {
            $cases = $cases->where('latest_status', $status)->values();
        }

        // Grouping into cases happens in PHP (not SQL), so pagination can't
        // be a plain ->paginate() on the query — it has to slice the already
        // -grouped collection by hand instead.
        $perPage = 5;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $cases->forPage($currentPage, $perPage)
            ->values()
            ->map(fn (array $case) => collect($case)->except('latest_id')->all());

        $paginated = new LengthAwarePaginator(
            $pageItems,
            $cases->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );
        $paginated->withQueryString();

        return Inertia::render('admin/Payments', [
            'paymentCases' => $paginated,
            'statusCounts' => $statusCounts,
            'activeStatus' => $status,
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
