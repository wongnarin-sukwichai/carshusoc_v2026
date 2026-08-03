<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Services\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $exam = Exam::create($this->validated($request));

        return back()->with('status', ['key' => 'flash.exam.saved', 'params' => ['code' => $exam->code]]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $oldLocation = $exam->location;

        $exam->update($this->validated($request, $exam));

        if ($exam->wasChanged('location')) {
            $this->notifyLocationChanged($exam, $oldLocation, $exam->location);
        }

        return back()->with('status', ['key' => 'flash.exam.updated', 'params' => ['code' => $exam->code]]);
    }

    protected function notifyLocationChanged(Exam $exam, ?string $oldLocation, ?string $newLocation): void
    {
        $notifier = app(EmailNotifier::class);
        $batchId = (string) Str::uuid();

        $exam->registrations()->with('user')->get()->each(
            fn (ExamRegistration $registration) => $notifier->send('location_changed', $registration->user, [
                '{{name}}' => $registration->user->name,
                '{{item_name}}' => $exam->name_th,
                '{{old_location}}' => $oldLocation ?: '-',
                '{{new_location}}' => $newLocation ?: '-',
            ], $exam, $batchId)
        );
    }

    public function toggleVisibility(Exam $exam): RedirectResponse
    {
        $exam->update(['is_visible' => ! $exam->is_visible]);

        return back()->with('status', ['key' => 'flash.exam.visibilityToggled', 'params' => ['code' => $exam->code]]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Exam $exam = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('exams', 'code')->ignore($exam?->id)],
            'type' => ['required', 'string', 'max:50'],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'exam_date' => ['required', 'date'],
            'registration_open_at' => ['nullable', 'date'],
            'registration_close_at' => ['nullable', 'date', 'after_or_equal:registration_open_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'requires_receipt' => ['sometimes', 'boolean'],
            'mail_delivery_available' => ['sometimes', 'boolean'],
            'mail_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'is_visible' => ['sometimes', 'boolean'],
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],
            'score_scale_id' => ['nullable', 'integer', 'exists:score_scales,id'],
        ]);
    }
}
