<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreScaleBand extends Model
{
    use HasFactory;

    protected $fillable = [
        'score_scale_id',
        'cefr_level',
        'toeic_min',
        'toeic_max',
        'ept_min',
        'ept_max',
        'sort_order',
    ];

    public function scoreScale(): BelongsTo
    {
        return $this->belongsTo(ScoreScale::class);
    }
}
