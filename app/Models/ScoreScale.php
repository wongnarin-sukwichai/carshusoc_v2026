<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoreScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'is_active',
        'effective_from',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'effective_from' => 'date',
        ];
    }

    public function bands(): HasMany
    {
        return $this->hasMany(ScoreScaleBand::class)->orderBy('sort_order');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function examRegistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Find the band this score falls into for the given exam type.
     * EPT-type exams are compared against the ept_min/ept_max columns,
     * everything else (TOEIC, IELTS, ...) against toeic_min/toeic_max.
     */
    public function resolveBand(int $totalScore, string $examType): ?ScoreScaleBand
    {
        $useEptColumns = str_contains(strtoupper($examType), 'EPT');

        $minColumn = $useEptColumns ? 'ept_min' : 'toeic_min';
        $maxColumn = $useEptColumns ? 'ept_max' : 'toeic_max';

        return $this->bands()
            ->whereNotNull($minColumn)
            ->where($minColumn, '<=', $totalScore)
            ->where($maxColumn, '>=', $totalScore)
            ->first();
    }
}
