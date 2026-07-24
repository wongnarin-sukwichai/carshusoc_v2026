<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/EmailTemplates', [
            'templates' => EmailTemplate::orderBy('key')->get(['id', 'key', 'subject', 'body']),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $emailTemplate->update($data);

        return back()->with('status', ['key' => 'flash.emailTemplate.updated']);
    }
}
