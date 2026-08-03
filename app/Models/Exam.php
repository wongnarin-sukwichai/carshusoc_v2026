<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'name_th',
        'name_en',
        'price',
        'exam_date',
        'registration_open_at',
        'registration_close_at',
        'location',
        'requires_receipt',
        'mail_delivery_available',
        'mail_delivery_fee',
        'is_visible',
        'certificate_template_id',
        'score_scale_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'exam_date' => 'date',
            'registration_open_at' => 'date',
            'registration_close_at' => 'date',
            'requires_receipt' => 'boolean',
            'mail_delivery_available' => 'boolean',
            'mail_delivery_fee' => 'decimal:2',
            'is_visible' => 'boolean',
        ];
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    public function scoreScale(): BelongsTo
    {
        return $this->belongsTo(ScoreScale::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }
}
