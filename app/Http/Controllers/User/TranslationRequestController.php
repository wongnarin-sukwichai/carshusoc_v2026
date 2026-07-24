<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TranslationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TranslationRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_lang' => ['required', 'string', 'max:50'],
            'target_lang' => ['required', 'string', 'max:50'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $document = $request->file('document');

        // Uploaded source documents may be sensitive — private disk only,
        // admin-readable via Admin\TranslationRequestController::source().
        $path = $document->store('translations/source/'.Auth::id(), 'local');

        TranslationRequest::create([
            'user_id' => Auth::id(),
            'file_name' => $document->getClientOriginalName(),
            'source_lang' => $data['source_lang'],
            'target_lang' => $data['target_lang'],
            'status' => 'submitted',
            'source_file_path' => $path,
        ]);

        return back()->with('status', ['key' => 'flash.translation.submitted']);
    }

    public function download(TranslationRequest $translationRequest)
    {
        abort_unless($translationRequest->user_id === Auth::id(), 403);
        abort_unless($translationRequest->translated_file_path, 404);

        return Storage::disk('public')->download($translationRequest->translated_file_path, $translationRequest->file_name);
    }
}
