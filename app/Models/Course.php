<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'language',
        'level',
        'price',
        'prerequisite_course_id',
        'location',
        'start_date',
        'end_date',
        'registration_open_at',
        'registration_close_at',
        'requires_receipt',
        'is_visible',
        'certificate_template_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_open_at' => 'date',
            'registration_close_at' => 'date',
            'requires_receipt' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    public function dependentCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'prerequisite_course_id');
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
