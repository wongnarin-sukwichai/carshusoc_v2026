<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function download(Certificate $certificate)
    {
        abort_unless($certificate->user_id === Auth::id(), 403);
        abort_unless($certificate->pdf_path, 404);

        return Storage::disk('public')->download($certificate->pdf_path, 'certificate.pdf');
    }
}
