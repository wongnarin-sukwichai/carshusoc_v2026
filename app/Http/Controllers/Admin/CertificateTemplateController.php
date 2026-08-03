<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CertificateTemplateController extends Controller
{
    /**
     * The only two centers that actually issue certificates
     * (see App\Services\CertificateIssuer) — translation delivers a
     * document, not a certificate.
     */
    protected const CENTER_CODES = ['training', 'exam'];

    public function index(): Response
    {
        return Inertia::render('admin/CertificateTemplates', [
            'templates' => CertificateTemplate::orderBy('service_center_code')->orderByDesc('is_default')->get(),
        ]);
    }

    /**
     * Renders this template through the real certificate blade view (as
     * plain HTML, not a PDF) with made-up recipient/course/score data, using
     * whatever is currently sitting in the admin's edit form — including
     * pending file uploads — without saving any of it. Loaded in an
     * <iframe> by admin/CertificateTemplates.vue's "ดูตัวอย่างก่อนบันทึก"
     * button, so admins can check the actual layout (name, statement, score
     * table, signatures) before committing to "บันทึก" (save). Nothing here
     * touches the database — if the form hasn't been touched yet, this just
     * reflects exactly what's already saved, since replicate() below starts
     * from the real row. Browser rendering isn't pixel-identical to
     * dompdf's PDF output, but it's close enough to catch real layout
     * problems.
     *
     * Uploaded files are written to a "drafts" folder just long enough to
     * build the response, then deleted immediately after — never merged
     * onto the real template, and never left behind on disk. The existing
     * row's own image paths (background/signatures) are only used as a
     * fallback for fields the admin hasn't touched — never deleted, since
     * this must never mutate the real saved template.
     *
     * Explicitly uncacheable — the <iframe> reuses the same URL every time,
     * so without this a browser could serve a stale response.
     */
    public function previewDraft(Request $request, CertificateTemplate $certificateTemplate): HttpResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'signatory1_name' => ['nullable', 'string', 'max:255'],
            'signatory1_title' => ['nullable', 'string', 'max:255'],
            'signatory1_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory1_signature' => ['sometimes', 'boolean'],
            'signatory2_name' => ['nullable', 'string', 'max:255'],
            'signatory2_title' => ['nullable', 'string', 'max:255'],
            'signatory2_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory2_signature' => ['sometimes', 'boolean'],
            'signatory3_name' => ['nullable', 'string', 'max:255'],
            'signatory3_title' => ['nullable', 'string', 'max:255'],
            'signatory3_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory3_signature' => ['sometimes', 'boolean'],
            'signatory4_name' => ['nullable', 'string', 'max:255'],
            'signatory4_title' => ['nullable', 'string', 'max:255'],
            'signatory4_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory4_signature' => ['sometimes', 'boolean'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'remove_background_image' => ['sometimes', 'boolean'],
            'border_color' => ['nullable', 'string', 'max:9'],
        ]);

        $draft = $certificateTemplate->replicate();
        $draft->fill(collect($data)->except([
            'background_image', 'remove_background_image',
            'signatory1_signature', 'remove_signatory1_signature',
            'signatory2_signature', 'remove_signatory2_signature',
            'signatory3_signature', 'remove_signatory3_signature',
            'signatory4_signature', 'remove_signatory4_signature',
        ])->all());

        $tempPaths = [];

        $draft->background_image_path = $this->draftImagePath(
            $data['background_image'] ?? null,
            (bool) ($data['remove_background_image'] ?? false),
            $draft->background_image_path,
            'certificate-templates/drafts',
            $tempPaths,
        );

        foreach ([1, 2, 3, 4] as $n) {
            $draft->{"signatory{$n}_signature_path"} = $this->draftImagePath(
                $data["signatory{$n}_signature"] ?? null,
                (bool) ($data["remove_signatory{$n}_signature"] ?? false),
                $draft->{"signatory{$n}_signature_path"},
                'certificate-templates/drafts',
                $tempPaths,
            );
        }

        [$view, $viewData] = $this->mockRenderData($draft);
        $html = view($view, $viewData)->render();

        foreach ($tempPaths as $path) {
            Storage::disk('public')->delete($path);
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->setCache(['no_store' => true, 'no_cache' => true, 'must_revalidate' => true]);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function mockRenderData(CertificateTemplate $template): array
    {
        $issuedAt = now();

        if ($template->service_center_code === 'training') {
            $mockUser = new User(['name' => 'ชื่อผู้อบรม']);
            $course = new Course([
                'name_th' => 'หลักสูตร...',
                'code' => 'MOCK-01',
                'start_date' => now()->subDays(14),
                'end_date' => now()->subDays(1),
            ]);
            $enrollment = new CourseEnrollment(['graded_at' => $issuedAt]);

            return ['certificates.training', [
                'template' => $template,
                'enrollment' => $enrollment,
                'course' => $course,
                'user' => $mockUser,
                'issuedAt' => $issuedAt,
            ]];
        }

        $mockUser = new User(['name' => 'ชื่อผู้เข้าสอบ']);
        $exam = new Exam([
            'name_th' => 'การทดสอบตัวอย่าง (Mock Data)',
            'location' => 'ห้องประชุมตัวอย่าง',
            'exam_date' => now()->subDays(3),
        ]);
        $registration = new ExamRegistration([
            'room' => 'ตัวอย่าง 101',
            'seat_number' => 'A01',
            'listening_score' => 20,
            'reading_score' => 18,
            'conversation_score' => 22,
            'grammar_score' => 19,
            'total_score' => 79,
            'cefr_level' => 'B2',
        ]);

        return ['certificates.exam', [
            'template' => $template,
            'registration' => $registration,
            'exam' => $exam,
            'user' => $mockUser,
            'issuedAt' => $issuedAt,
        ]];
    }

    /**
     * Like resolveImagePath(), but for previewDraft() — never deletes the
     * existing file (it belongs to the real, saved template, which this
     * request must not mutate) and records every file it writes into
     * $tempPaths so the caller can clean them up once the view is rendered.
     */
    protected function draftImagePath(?UploadedFile $file, bool $remove, ?string $existingPath, string $directory, array &$tempPaths): ?string
    {
        if ($file) {
            $path = $file->store($directory, 'public');
            $tempPaths[] = $path;

            return $path;
        }

        if ($remove) {
            return null;
        }

        return $existingPath;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->withImagePaths($this->validated($request));

        $template = CertificateTemplate::create($data);

        $this->ensureSingleDefault($template);

        return back()->with('status', ['key' => 'flash.certificateTemplate.created', 'params' => ['name' => $template->name]]);
    }

    public function update(Request $request, CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $data = $this->validated($request, $certificateTemplate);

        // Can't uncheck "default" on the only default a center has — that
        // would leave CertificateIssuer::defaultTemplateFor() with nothing.
        if (array_key_exists('is_default', $data) && ! $data['is_default'] && $certificateTemplate->is_default) {
            $hasOtherDefault = CertificateTemplate::where('service_center_code', $certificateTemplate->service_center_code)
                ->where('id', '!=', $certificateTemplate->id)
                ->where('is_default', true)
                ->exists();

            if (! $hasOtherDefault) {
                throw ValidationException::withMessages([
                    'is_default' => 'ต้องมีแบบฟอร์มเริ่มต้นอย่างน้อย 1 แบบต่อศูนย์บริการ ตั้งแบบฟอร์มอื่นเป็นค่าเริ่มต้นก่อน',
                ]);
            }
        }

        $data = $this->withImagePaths($data, $certificateTemplate);

        $certificateTemplate->update($data);

        $this->ensureSingleDefault($certificateTemplate);

        return back()->with('status', ['key' => 'flash.certificateTemplate.updated']);
    }

    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        if ($certificateTemplate->certificates()->exists()) {
            return back()->with('error', ['key' => 'flash.certificateTemplate.deleteBlockedHasCertificates']);
        }

        $siblingCount = CertificateTemplate::where('service_center_code', $certificateTemplate->service_center_code)->count();

        if ($siblingCount <= 1) {
            return back()->with('error', ['key' => 'flash.certificateTemplate.deleteBlockedLastOne']);
        }

        $wasDefault = $certificateTemplate->is_default;
        $centerCode = $certificateTemplate->service_center_code;
        $name = $certificateTemplate->name;

        // Historical certificates only depend on the *snapshotted* rendered
        // PDF, not this row's image files, so it's safe to clean these up —
        // the exists()-check above already ruled out any certificates still
        // referencing this template.
        collect([
            $certificateTemplate->background_image_path,
            $certificateTemplate->signatory1_signature_path,
            $certificateTemplate->signatory2_signature_path,
            $certificateTemplate->signatory3_signature_path,
            $certificateTemplate->signatory4_signature_path,
        ])->filter()->each(fn (string $path) => Storage::disk('public')->delete($path));

        $certificateTemplate->delete();

        if ($wasDefault) {
            CertificateTemplate::where('service_center_code', $centerCode)->oldest('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('status', ['key' => 'flash.certificateTemplate.deleted', 'params' => ['name' => $name]]);
    }

    /**
     * A center must always have exactly one default template — force the
     * first template a center ever gets to be it, and if this one was just
     * marked default, un-mark every sibling.
     */
    protected function ensureSingleDefault(CertificateTemplate $template): void
    {
        if ($template->is_default) {
            CertificateTemplate::where('service_center_code', $template->service_center_code)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);

            return;
        }

        $hasDefault = CertificateTemplate::where('service_center_code', $template->service_center_code)
            ->where('is_default', true)
            ->exists();

        if (! $hasDefault) {
            $template->update(['is_default' => true]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?CertificateTemplate $template = null): array
    {
        return $request->validate([
            'service_center_code' => [$template ? 'sometimes' : 'required', Rule::in(self::CENTER_CODES)],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'signatory1_name' => ['required', 'string', 'max:255'],
            'signatory1_title' => ['required', 'string', 'max:255'],
            'signatory1_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory1_signature' => ['sometimes', 'boolean'],
            'signatory2_name' => ['nullable', 'string', 'max:255'],
            'signatory2_title' => ['nullable', 'string', 'max:255'],
            'signatory2_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory2_signature' => ['sometimes', 'boolean'],
            'signatory3_name' => ['nullable', 'string', 'max:255'],
            'signatory3_title' => ['nullable', 'string', 'max:255'],
            'signatory3_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory3_signature' => ['sometimes', 'boolean'],
            'signatory4_name' => ['nullable', 'string', 'max:255'],
            'signatory4_title' => ['nullable', 'string', 'max:255'],
            'signatory4_signature' => ['nullable', 'image', 'max:2048'],
            'remove_signatory4_signature' => ['sometimes', 'boolean'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'remove_background_image' => ['sometimes', 'boolean'],
            'border_color' => ['required', 'string', 'max:9'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * Turns the uploaded-file/remove-flag pairs from validated() into the
     * actual *_path columns to persist, falling back to whatever the
     * template already had on disk when neither a new file nor a removal
     * was submitted. Keyed loop over 1..4 so adding a 5th signatory later
     * is a one-line change here.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withImagePaths(array $data, ?CertificateTemplate $existing = null): array
    {
        $data['background_image_path'] = $this->resolveImagePath(
            $data['background_image'] ?? null,
            (bool) ($data['remove_background_image'] ?? false),
            $existing?->background_image_path,
            'certificate-templates/backgrounds',
        );

        foreach ([1, 2, 3, 4] as $n) {
            $data["signatory{$n}_signature_path"] = $this->resolveImagePath(
                $data["signatory{$n}_signature"] ?? null,
                (bool) ($data["remove_signatory{$n}_signature"] ?? false),
                $existing?->{"signatory{$n}_signature_path"},
                'certificate-templates/signatures',
            );
        }

        return collect($data)
            ->except([
                'background_image', 'remove_background_image',
                'signatory1_signature', 'remove_signatory1_signature',
                'signatory2_signature', 'remove_signatory2_signature',
                'signatory3_signature', 'remove_signatory3_signature',
                'signatory4_signature', 'remove_signatory4_signature',
            ])
            ->all();
    }

    protected function resolveImagePath(?UploadedFile $file, bool $remove, ?string $existingPath, string $directory): ?string
    {
        if ($file) {
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            return $file->store($directory, 'public');
        }

        if ($remove) {
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            return null;
        }

        return $existingPath;
    }
}
