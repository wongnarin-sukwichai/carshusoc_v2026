<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ExamRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'status',
        'listening_score',
        'reading_score',
        'conversation_score',
        'grammar_score',
        'total_score',
        'cefr_level',
        'score_scale_id',
        'wants_mail_delivery',
        'mail_delivery_fee_charged',
        'scored_at',
        'certificate_issued',
    ];

    protected function casts(): array
    {
        return [
            'wants_mail_delivery' => 'boolean',
            'mail_delivery_fee_charged' => 'decimal:2',
            'scored_at' => 'datetime',
            'certificate_issued' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function scoreScale(): BelongsTo
    {
        return $this->belongsTo(ScoreScale::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function certificate(): MorphOne
    {
        return $this->morphOne(Certificate::class, 'certifiable');
    }
}
