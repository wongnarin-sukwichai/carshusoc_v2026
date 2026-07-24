<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'graded_at',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'graded_by');
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
