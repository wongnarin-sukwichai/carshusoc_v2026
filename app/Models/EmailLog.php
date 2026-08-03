<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'to_email',
        'subject',
        'body',
        'related_type',
        'related_id',
        'batch_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Latest $limit entries for the AdminSidebar panel, with same-batch sends
     * (e.g. one "location changed" notification fanned out to every registrant)
     * collapsed into a single row carrying its own recipient list.
     *
     * @return array<int, array{id: int|string, subject: string, body: string, sent_at: ?string, recipient_count: int, recipients: array<int, string>}>
     */
    public static function recentGrouped(int $limit = 5): array
    {
        return static::latest('sent_at')
            ->limit(200)
            ->get(['id', 'to_email', 'subject', 'body', 'sent_at', 'batch_id'])
            ->groupBy(fn (self $log) => $log->batch_id ?? 'single-'.$log->id)
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'id' => $first->batch_id ?? $first->id,
                    'subject' => $first->subject,
                    'body' => $first->body,
                    'sent_at' => $first->sent_at?->toJSON(),
                    'recipient_count' => $group->count(),
                    'recipients' => $group->pluck('to_email')->values()->all(),
                ];
            })
            ->sortByDesc('sent_at')
            ->take($limit)
            ->values()
            ->all();
    }
}
