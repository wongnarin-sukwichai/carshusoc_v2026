<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

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
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'signatory1_name' => ['required', 'string', 'max:255'],
            'signatory1_title' => ['required', 'string', 'max:255'],
            'signatory2_name' => ['nullable', 'string', 'max:255'],
            'signatory2_title' => ['nullable', 'string', 'max:255'],
            'signatory3_name' => ['nullable', 'string', 'max:255'],
            'signatory3_title' => ['nullable', 'string', 'max:255'],
            'border_color' => ['required', 'string', 'max:9'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }
}
