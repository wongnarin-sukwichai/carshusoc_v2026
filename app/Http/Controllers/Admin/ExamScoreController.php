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
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'importPreview' => session('importPreview'),
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

    public function scoreTemplate(Exam $exam): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(
            ['email', 'room', 'seat_number', 'listening', 'reading', 'conversation', 'grammar'],
            null,
            'A1'
        );
        $sheet->fromArray(
            ['somchai.dev@gmail.com', 'A101', '12', 20, 22, 18, 21],
            null,
            'A2'
        );

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'score-template').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, "exam-scores-template-{$exam->code}.xlsx")->deleteFileAfterSend(true);
    }

    /**
     * Same columns as scoreTemplate() so the file can be filled in and
     * re-imported as-is — but with every currently-registered examinee's
     * email pre-filled (instead of one made-up sample row), since the point
     * here is a ready-to-fill roster for this specific exam round, not a
     * blank format example.
     */
    public function exportRegistrants(Exam $exam): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(
            ['email', 'room', 'seat_number', 'listening', 'reading', 'conversation', 'grammar'],
            null,
            'A1'
        );

        $rows = ExamRegistration::with('user')
            ->where('exam_id', $exam->id)
            ->latest()
            ->get()
            ->map(fn (ExamRegistration $registration) => [$registration->user->email, '', '', '', '', '', ''])
            ->all();

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'exam-registrants').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, "exam-registrants-{$exam->code}.xlsx")->deleteFileAfterSend(true);
    }

    public function validateImport(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        try {
            $rows = $this->parseImportRows($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', ['key' => 'flash.examScore.importFileUnreadable']);
        }

        if ($rows === []) {
            return back()->with('error', ['key' => 'flash.examScore.importEmpty']);
        }

        $results = $this->validateImportRows($rows);

        return back()->with('importPreview', $this->summarizePreview($results));
    }

    public function import(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
        ]);

        try {
            $rows = $this->parseImportRows($request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', ['key' => 'flash.examScore.importFileUnreadable']);
        }

        if ($rows === []) {
            return back()->with('error', ['key' => 'flash.examScore.importEmpty']);
        }

        $results = $this->validateImportRows($rows);
        $invalidCount = collect($results)->where('valid', false)->count();

        if ($invalidCount > 0) {
            return back()->with([
                'error' => ['key' => 'flash.examScore.importHasErrors'],
                'importPreview' => $this->summarizePreview($results),
            ]);
        }

        $imported = 0;

        foreach ($results as $row) {
            $user = User::where('email', $row['email'])->first();

            $scores = [
                'listening_score' => (int) $row['listening'],
                'reading_score' => (int) $row['reading'],
                'conversation_score' => (int) $row['conversation'],
                'grammar_score' => (int) $row['grammar'],
            ];

            if ($row['room'] !== '') {
                $scores['room'] = $row['room'];
            }

            if ($row['seat_number'] !== '') {
                $scores['seat_number'] = $row['seat_number'];
            }

            $registration = ExamRegistration::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $user->id],
                ['status' => 'registered']
            );

            $this->applyScore($registration, $scores);
            $imported++;
        }

        return back()->with('status', ['key' => 'flash.examScore.imported', 'params' => ['count' => $imported]]);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function parseImportRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        array_shift($rows);

        return array_values(array_filter(
            $rows,
            fn (array $row) => collect($row)->contains(fn ($cell) => trim((string) $cell) !== '')
        ));
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array{row:int,email:string,listening:string,reading:string,conversation:string,grammar:string,room:string,seat_number:string,errors:array<int,string>,valid:bool}>
     */
    protected function validateImportRows(array $rows): array
    {
        $results = [];

        foreach ($rows as $index => $row) {
            $email = trim((string) ($row[0] ?? ''));
            $room = trim((string) ($row[1] ?? ''));
            $seatNumber = trim((string) ($row[2] ?? ''));
            $listening = trim((string) ($row[3] ?? ''));
            $reading = trim((string) ($row[4] ?? ''));
            $conversation = trim((string) ($row[5] ?? ''));
            $grammar = trim((string) ($row[6] ?? ''));

            $errors = [];
            $sections = ['listening' => $listening, 'reading' => $reading, 'conversation' => $conversation, 'grammar' => $grammar];

            if ($email === '') {
                $errors[] = 'ต้องระบุอีเมล';
            }

            foreach ($sections as $label => $value) {
                if ($value === '') {
                    $errors[] = "ต้องระบุคะแนน {$label}";
                } elseif (! is_numeric($value) || (int) $value < 0 || (int) $value > 25) {
                    $errors[] = "คะแนน {$label} ต้องเป็นตัวเลข 0-25";
                }
            }

            if ($email !== '' && ! User::where('email', $email)->exists()) {
                $errors[] = "ไม่พบผู้ใช้อีเมล {$email}";
            }

            $results[] = [
                'row' => $index + 2,
                'email' => $email,
                'listening' => $listening,
                'reading' => $reading,
                'conversation' => $conversation,
                'grammar' => $grammar,
                'room' => $room,
                'seat_number' => $seatNumber,
                'errors' => $errors,
                'valid' => $errors === [],
            ];
        }

        return $results;
    }

    /**
     * @param  array<int, array{row:int,email:string,listening:string,reading:string,conversation:string,grammar:string,room:string,seat_number:string,errors:array<int,string>,valid:bool}>  $results
     * @return array{rows: array<int, mixed>, validCount: int, invalidCount: int}
     */
    protected function summarizePreview(array $results): array
    {
        return [
            'rows' => $results,
            'validCount' => collect($results)->where('valid', true)->count(),
            'invalidCount' => collect($results)->where('valid', false)->count(),
        ];
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
