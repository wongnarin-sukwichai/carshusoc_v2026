<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TranslationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'source_lang',
        'target_lang',
        'status',
        'estimated_price',
        'delivery_date',
        'source_file_path',
        'translated_file_path',
        'assigned_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_price' => 'decimal:2',
            'delivery_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
