<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_center_code',
        'name',
        'background_image_path',
        'title',
        'subtitle',
        'signatory1_name',
        'signatory1_title',
        'signatory2_name',
        'signatory2_title',
        'signatory3_name',
        'signatory3_title',
        'border_color',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
