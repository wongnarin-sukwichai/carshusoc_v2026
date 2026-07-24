<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        // Switching targets mid-impersonation (or a stray leftover session)
        // shouldn't orphan the previous log row — close it out first.
        $this->closeActiveLog($request);

        $log = ImpersonationLog::create([
            'admin_id' => auth('admin')->id(),
            'user_id' => $user->id,
        ]);

        $request->session()->put('impersonation_log_id', $log->id);

        // Only the "web" guard's session entry changes here — the "admin"
        // guard session is untouched, so the admin stays signed in throughout.
        Auth::guard('web')->login($user);

        return to_route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $this->closeActiveLog($request);

        Auth::guard('web')->logout();

        return to_route('admin.registrants');
    }

    private function closeActiveLog(Request $request): void
    {
        $logId = $request->session()->pull('impersonation_log_id');

        if ($logId) {
            ImpersonationLog::whereKey($logId)->whereNull('ended_at')->update(['ended_at' => now()]);
        }
    }
}
