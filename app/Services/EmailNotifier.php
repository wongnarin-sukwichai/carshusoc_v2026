<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class EmailNotifier
{
    /**
     * Look up the template by key, substitute placeholders, send it, and log it.
     * Silently no-ops if the template key isn't seeded — a missing template
     * shouldn't block the business action that triggered it.
     *
     * @param  array<string, string>  $replacements  e.g. ['{{name}}' => 'Somchai']
     * @param  ?string  $batchId  Shared across a fan-out (e.g. notifying every registrant of
     *                            a location change) so the admin email log can collapse the
     *                            whole send into a single expandable row.
     */
    public function send(string $templateKey, User $user, array $replacements = [], ?Model $related = null, ?string $batchId = null): void
    {
        $template = EmailTemplate::where('key', $templateKey)->first();

        if (! $template) {
            return;
        }

        $subject = strtr($template->subject, $replacements);
        $body = strtr($template->body, $replacements);

        Mail::to($user->email)->queue(new TemplatedMail($subject, $body));

        EmailLog::create([
            'user_id' => $user->id,
            'to_email' => $user->email,
            'subject' => $subject,
            'body' => $body,
            'related_type' => $related?->getMorphClass(),
            'related_id' => $related?->id,
            'batch_id' => $batchId,
            'sent_at' => now(),
        ]);
    }
}
