<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoreScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ScoreScaleController extends Controller
{
    public function index(): Response
    {
        $scales = ScoreScale::with('bands')
            ->withCount('examRegistrations')
            ->orderByDesc('effective_from')
            ->orderByDesc('version')
            ->paginate(5)
            ->withQueryString()
            ->through(fn (ScoreScale $scale) => [
                'id' => $scale->id,
                'name' => $scale->name,
                'version' => $scale->version,
                'is_active' => $scale->is_active,
                'effective_from' => $scale->effective_from->toDateString(),
                'in_use' => $scale->exam_registrations_count > 0,
                'bands' => $scale->bands->map(fn ($band) => [
                    'id' => $band->id,
                    'cefr_level' => $band->cefr_level,
                    'toeic_min' => $band->toeic_min,
                    'toeic_max' => $band->toeic_max,
                    'ept_min' => $band->ept_min,
                    'ept_max' => $band->ept_max,
                ])->values(),
            ]);

        return Inertia::render('admin/ScoreScales', [
            'scales' => $scales,
            // The "suggested next version" default in the create form needs
            // the true max across every scale, not just whatever page of the
            // paginated list happens to be loaded.
            'nextVersion' => (ScoreScale::max('version') ?? 0) + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $scale = ScoreScale::create([
            'name' => $data['name'],
            'version' => $data['version'],
            'is_active' => $data['is_active'] ?? false,
            'effective_from' => $data['effective_from'],
        ]);

        $this->syncBands($scale, $data['bands']);

        return back()->with('status', ['key' => 'flash.scoreScale.created', 'params' => ['name' => $scale->name]]);
    }

    public function update(Request $request, ScoreScale $scoreScale): RedirectResponse
    {
        // Score → CEFR lookup is versioned precisely so a historical
        // registration's already-computed result never silently changes —
        // once a scale has actually been used to score someone, it's frozen.
        // Admins who need different bands should create a new version instead.
        if ($scoreScale->examRegistrations()->exists()) {
            return back()->with('error', ['key' => 'flash.scoreScale.editBlockedInUse']);
        }

        $data = $this->validated($request, $scoreScale);

        $scoreScale->update([
            'name' => $data['name'],
            'version' => $data['version'],
            'is_active' => $data['is_active'] ?? false,
            'effective_from' => $data['effective_from'],
        ]);

        $this->syncBands($scoreScale, $data['bands']);

        return back()->with('status', ['key' => 'flash.scoreScale.updated']);
    }

    public function toggleActive(ScoreScale $scoreScale): RedirectResponse
    {
        // Toggling which scales are eligible as the "latest active" fallback
        // (see ExamScoreController::applyScore()) doesn't touch any band
        // values, so this is safe even for an already-used scale.
        $scoreScale->update(['is_active' => ! $scoreScale->is_active]);

        return back()->with('status', ['key' => 'flash.scoreScale.updated']);
    }

    public function destroy(ScoreScale $scoreScale): RedirectResponse
    {
        if ($scoreScale->examRegistrations()->exists() || $scoreScale->certificates()->exists() || $scoreScale->exams()->exists()) {
            return back()->with('error', ['key' => 'flash.scoreScale.deleteBlockedInUse']);
        }

        $name = $scoreScale->name;
        $scoreScale->delete();

        return back()->with('status', ['key' => 'flash.scoreScale.deleted', 'params' => ['name' => $name]]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?ScoreScale $scale = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'integer', 'min:1', Rule::unique('score_scales', 'version')->ignore($scale?->id)],
            'is_active' => ['sometimes', 'boolean'],
            'effective_from' => ['required', 'date'],
            'bands' => ['required', 'array', 'min:1'],
            'bands.*.cefr_level' => ['required', 'string', 'max:20'],
            'bands.*.toeic_min' => ['nullable', 'integer', 'min:0'],
            'bands.*.toeic_max' => ['nullable', 'integer', 'min:0'],
            'bands.*.ept_min' => ['nullable', 'integer', 'min:0'],
            'bands.*.ept_max' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $bands
     */
    protected function syncBands(ScoreScale $scale, array $bands): void
    {
        $scale->bands()->delete();

        foreach ($bands as $index => $band) {
            $scale->bands()->create([
                'cefr_level' => $band['cefr_level'],
                'toeic_min' => $band['toeic_min'] ?? null,
                'toeic_max' => $band['toeic_max'] ?? null,
                'ept_min' => $band['ept_min'] ?? null,
                'ept_max' => $band['ept_max'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
