<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function gradedEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'graded_by');
    }

    public function assignedTranslationRequests(): HasMany
    {
        return $this->hasMany(TranslationRequest::class, 'assigned_admin_id');
    }

    public function approvedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'approved_by');
    }
}
