<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payable_type',
        'payable_id',
        'amount',
        'slip_path',
        'status',
        'wants_receipt',
        'receipt_number',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'wants_receipt' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
