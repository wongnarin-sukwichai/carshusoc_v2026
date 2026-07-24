<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TranslationRequest;
use App\Services\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TranslationRequestController extends Controller
{
    public function index(): Response
    {
        $requests = TranslationRequest::with(['user', 'payments'])
            ->latest()
            ->get()
            ->map(fn (TranslationRequest $r) => [
                'id' => $r->id,
                'user_name' => $r->user->name,
                'user_email' => $r->user->email,
                'file_name' => $r->file_name,
                'source_lang' => $r->source_lang,
                'target_lang' => $r->target_lang,
                'status' => $r->status,
                'estimated_price' => $r->estimated_price,
                'delivery_date' => $r->delivery_date?->toDateString(),
                'has_translated_file' => (bool) $r->translated_file_path,
                'payment_status' => $r->payments->sortByDesc('id')->first()?->status,
            ]);

        return Inertia::render('admin/TranslationQuotes', [
            'requests' => $requests,
        ]);
    }

    public function source(TranslationRequest $translationRequest)
    {
        abort_unless($translationRequest->source_file_path, 404);

        return Storage::disk('local')->response($translationRequest->source_file_path, $translationRequest->file_name);
    }

    public function quote(Request $request, TranslationRequest $translationRequest): RedirectResponse
    {
        abort_if($translationRequest->status !== 'submitted', 422, 'คำขอนี้ถูกดำเนินการแล้ว');

        $data = $request->validate([
            'estimated_price' => ['required', 'numeric', 'min:0'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $translationRequest->update([
            'estimated_price' => $data['estimated_price'],
            'delivery_date' => now()->addDays($data['delivery_days']),
            'status' => 'quote_sent',
        ]);

        $translationRequest->loadMissing('user');

        app(EmailNotifier::class)->send('translation_quote_sent', $translationRequest->user, [
            '{{name}}' => $translationRequest->user->name,
            '{{price}}' => number_format((float) $translationRequest->estimated_price, 2),
            '{{delivery_date}}' => $translationRequest->delivery_date->format('d/m/Y'),
        ], $translationRequest);

        return back()->with('status', ['key' => 'flash.translationQuote.sent']);
    }

    public function deliver(Request $request, TranslationRequest $translationRequest): RedirectResponse
    {
        abort_if($translationRequest->status !== 'translating', 422, 'ยังไม่สามารถส่งมอบงานได้ในสถานะนี้');

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $request->file('file')->store('translations/delivered', 'public');

        $translationRequest->update([
            'translated_file_path' => $path,
            'status' => 'completed',
            'assigned_admin_id' => Auth::guard('admin')->id(),
        ]);

        $translationRequest->loadMissing('user');

        app(EmailNotifier::class)->send('translation_delivered', $translationRequest->user, [
            '{{name}}' => $translationRequest->user->name,
            '{{file_name}}' => $translationRequest->file_name,
        ], $translationRequest);

        return back()->with('status', ['key' => 'flash.translationQuote.delivered']);
    }
}
