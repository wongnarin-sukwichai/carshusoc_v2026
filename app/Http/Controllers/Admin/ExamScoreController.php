<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ScoreScale;
use App\Models\User;
use App\Services\CertificateIssuer;
use App\Services\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamScoreController extends Controller
{
    public function index(Request $request): Response
    {
        $exams = Exam::orderByDesc('exam_date')->get(['id', 'code', 'name_th', 'type']);

        $selectedExam = $exams->firstWhere('id', (int) $request->query('exam')) ?? $exams->first();

        $registrations = $selectedExam
            ? ExamRegistration::with('user')
                ->where('exam_id', $selectedExam->id)
                ->latest()
                ->get()
                ->map(fn (ExamRegistration $registration) => [
                    'id' => $registration->id,
                    'user_name' => $registration->user->name,
                    'user_email' => $registration->user->email,
                    'status' => $registration->status,
                    'room' => $registration->room,
                    'seat_number' => $registration->seat_number,
                    'listening_score' => $registration->listening_score,
                    'reading_score' => $registration->reading_score,
                    'conversation_score' => $registration->conversation_score,
                    'grammar_score' => $registration->grammar_score,
                    'total_score' => $registration->total_score,
                    'cefr_level' => $registration->cefr_level,
                    'certificate_issued' => $registration->certificate_issued,
                ])
            : collect();

        return Inertia::render('admin/ExamScores', [
            'exams' => $exams,
            'selectedExamId' => $selectedExam?->id,
            'registrations' => $registrations,
        ]);
    }

    public function updateScore(Request $request, ExamRegistration $registration): RedirectResponse
    {
        $data = $request->validate([
            'listening_score' => ['required', 'integer', 'min:0', 'max:25'],
            'reading_score' => ['required', 'integer', 'min:0', 'max:25'],
            'conversation_score' => ['required', 'integer', 'min:0', 'max:25'],
            'grammar_score' => ['required', 'integer', 'min:0', 'max:25'],
            'room' => ['nullable', 'string', 'max:100'],
            'seat_number' => ['nullable', 'string', 'max:50'],
        ]);

        $this->applyScore($registration, $data);

        return back()->with('status', ['key' => 'flash.examScore.saved']);
    }

    public function importCsv(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'csv' => ['required', 'string'],
        ]);

        $imported = 0;
        $errors = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($data['csv'])) as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(',', $line));

            if (count($parts) < 5) {
                $errors[] = 'บรรทัด '.($lineNumber + 1).': รูปแบบไม่ถูกต้อง (ต้องการ email,listening,reading,conversation,grammar)';

                continue;
            }

            [$email, $listening, $reading, $conversation, $grammar] = $parts;
            $room = $parts[5] ?? null;
            $seatNumber = $parts[6] ?? null;

            $user = User::where('email', $email)->first();

            if (! $user) {
                $errors[] = 'บรรทัด '.($lineNumber + 1).": ไม่พบผู้ใช้อีเมล {$email}";

                continue;
            }

            $scores = [
                'listening_score' => (int) $listening,
                'reading_score' => (int) $reading,
                'conversation_score' => (int) $conversation,
                'grammar_score' => (int) $grammar,
            ];

            if ($room !== null && $room !== '') {
                $scores['room'] = $room;
            }

            if ($seatNumber !== null && $seatNumber !== '') {
                $scores['seat_number'] = $seatNumber;
            }

            if (collect($scores)->contains(fn ($score) => $score < 0 || $score > 25)) {
                $errors[] = 'บรรทัด '.($lineNumber + 1).': คะแนนต้องอยู่ระหว่าง 0-25';

                continue;
            }

            $registration = ExamRegistration::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $user->id],
                ['status' => 'registered']
            );

            $this->applyScore($registration, $scores);
            $imported++;
        }

        return back()->with([
            'status' => [
                'key' => $errors ? 'flash.examScore.importedWithErrors' : 'flash.examScore.imported',
                'params' => ['count' => $imported],
            ],
            'importErrors' => $errors,
        ]);
    }

    /**
     * @param  array{listening_score:int,reading_score:int,conversation_score:int,grammar_score:int,room?:?string,seat_number?:?string}  $scores
     */
    protected function applyScore(ExamRegistration $registration, array $scores): void
    {
        $registration->loadMissing('exam', 'user');

        $total = $scores['listening_score'] + $scores['reading_score'] + $scores['conversation_score'] + $scores['grammar_score'];

        $scale = $registration->exam->score_scale_id
            ? ScoreScale::find($registration->exam->score_scale_id)
            : ScoreScale::where('is_active', true)->orderByDesc('effective_from')->first();

        $band = $scale?->resolveBand($total, $registration->exam->type);

        $registration->fill($scores);
        $registration->total_score = $total;
        $registration->cefr_level = $band?->cefr_level;
        $registration->score_scale_id = $scale?->id;
        $registration->status = 'scored';
        $registration->scored_at = now();
        $registration->certificate_issued = true;
        $registration->save();

        app(CertificateIssuer::class)->issueForExamRegistration($registration);

        app(EmailNotifier::class)->send('score_released', $registration->user, [
            '{{name}}' => $registration->user->name,
            '{{exam_name}}' => $registration->exam->name_th,
            '{{score}}' => (string) $total,
            '{{cefr_level}}' => $band?->cefr_level ?? '-',
        ], $registration);
    }
}
