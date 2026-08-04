<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Canonical low-to-high order — exam_registrations.cefr_level is a free
     * string column (whatever ScoreScaleBand said at scoring time), so this
     * is display-only and never assumed to be the exhaustive set of levels
     * that could ever appear.
     */
    protected const CEFR_ORDER = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    protected const REVENUE_TREND_MONTHS = 6;

    public function index(): Response
    {
        $languageBreakdown = CourseEnrollment::join('courses', 'courses.id', '=', 'course_enrollments.course_id')
            ->select('courses.language', DB::raw('count(*) as total'))
            ->groupBy('courses.language')
            ->orderByDesc('total')
            ->get();

        $maxLanguageCount = max($languageBreakdown->max('total'), 1);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'revenue' => (float) Payment::where('status', 'approved')->sum('amount'),
                'registrants' => User::count(),
                'enrollments' => CourseEnrollment::count(),
                'examRegistrations' => ExamRegistration::count(),
                'translationJobs' => TranslationRequest::count(),
                'certificatesIssued' => Certificate::count(),
            ],
            'languageBreakdown' => $languageBreakdown->map(fn ($row) => [
                'language' => $row->language,
                'count' => (int) $row->total,
                'percent' => (int) round(($row->total / $maxLanguageCount) * 100),
            ]),
            'revenueTrend' => $this->revenueTrend(),
            'revenueByCenter' => $this->revenueByCenter(),
            'paymentStatusBreakdown' => $this->paymentStatusBreakdown(),
            'cefrDistribution' => $this->cefrDistribution(),
        ]);
    }

    /**
     * @return array<int, array{month: string, total: float}>
     */
    protected function revenueTrend(): array
    {
        $start = Carbon::now()->subMonths(self::REVENUE_TREND_MONTHS - 1)->startOfMonth();

        // Grouped in PHP rather than a DB-specific date-format function
        // (MySQL's DATE_FORMAT vs SQLite's strftime) so this works unchanged
        // against both the app's MySQL database and the SQLite test suite.
        $rows = Payment::where('status', 'approved')
            ->where('approved_at', '>=', $start)
            ->get(['amount', 'approved_at'])
            ->groupBy(fn (Payment $payment) => $payment->approved_at->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $months = [];

        for ($i = self::REVENUE_TREND_MONTHS - 1; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $months[] = [
                'month' => $key,
                'total' => (float) ($rows[$key] ?? 0),
            ];
        }

        return $months;
    }

    /**
     * @return array<int, array{center: string, total: float}>
     */
    protected function revenueByCenter(): array
    {
        $totals = Payment::where('status', 'approved')
            ->select('payable_type', DB::raw('sum(amount) as total'))
            ->groupBy('payable_type')
            ->pluck('total', 'payable_type');

        return [
            ['center' => 'training', 'total' => (float) ($totals['course_enrollment'] ?? 0)],
            ['center' => 'exam', 'total' => (float) ($totals['exam_registration'] ?? 0)],
            ['center' => 'translation', 'total' => (float) ($totals['translation_request'] ?? 0)],
        ];
    }

    /**
     * @return array<int, array{status: string, count: int}>
     */
    protected function paymentStatusBreakdown(): array
    {
        $counts = Payment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            ['status' => 'pending', 'count' => (int) ($counts['pending'] ?? 0)],
            ['status' => 'approved', 'count' => (int) ($counts['approved'] ?? 0)],
            ['status' => 'rejected', 'count' => (int) ($counts['rejected'] ?? 0)],
        ];
    }

    /**
     * @return array<int, array{level: string, count: int}>
     */
    protected function cefrDistribution(): array
    {
        $counts = ExamRegistration::whereNotNull('cefr_level')
            ->select('cefr_level', DB::raw('count(*) as total'))
            ->groupBy('cefr_level')
            ->pluck('total', 'cefr_level');

        $levels = collect(self::CEFR_ORDER)
            ->merge($counts->keys()->diff(self::CEFR_ORDER))
            ->map(fn ($level) => ['level' => $level, 'count' => (int) ($counts[$level] ?? 0)]);

        return $levels->filter(fn ($row) => $row['count'] > 0)->values()->all();
    }
}
