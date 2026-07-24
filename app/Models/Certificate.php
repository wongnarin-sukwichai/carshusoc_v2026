<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'certifiable_type',
        'certifiable_id',
        'certificate_template_id',
        'score_scale_id',
        'verification_hash',
        'issued_at',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    public function scoreScale(): BelongsTo
    {
        return $this->belongsTo(ScoreScale::class);
    }
}
