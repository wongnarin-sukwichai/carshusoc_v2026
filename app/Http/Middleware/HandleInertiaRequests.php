<?php

namespace App\Http\Middleware;

use App\Models\EmailLog;
use App\Models\ServiceCenter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user('web'),
                'admin' => $request->user('admin'),
            ],
            // Every controller's back()->with('status', ...) / ->with('error', ...)
            // lands here — see components/FlashMessage.vue for where it's rendered.
            'flash' => [
                'status' => Inertia::always($request->session()->get('status')),
                'error' => Inertia::always($request->session()->get('error')),
            ],
            // Drives components/ImpersonationBanner.vue — must survive partial
            // reloads, so it can't be dropped like normal shared data.
            'impersonating' => Inertia::always($request->session()->has('impersonation_log_id')),
            // Keyed by service_centers.code — lets any page (including the public
            // landing page and UserSidebar) hide a center without a per-route query.
            'serviceCenters' => ServiceCenter::pluck('is_visible', 'code'),
            // Powers the "live email log" panel under AdminSidebar (mockup parity) —
            // only queried when an admin is actually authenticated. Fan-out sends
            // (e.g. one location-change notice to every registrant) collapse into
            // a single row via EmailLog::recentGrouped().
            'recentEmailLogs' => $request->user('admin') ? EmailLog::recentGrouped() : [],
            // Badge count is "sent today", not all-time — matches what the panel
            // below it is actually showing (recent activity, not a running total).
            'emailLogCount' => $request->user('admin') ? EmailLog::whereDate('sent_at', today())->count() : 0,
        ]);
    }
}
