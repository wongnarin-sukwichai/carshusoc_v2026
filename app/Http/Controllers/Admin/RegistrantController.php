<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailNotifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegistrantController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');

        $users = User::orderBy('name')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->paginate(10, ['id', 'name', 'email', 'phone', 'identity_type', 'identity_number'])
            ->withQueryString();

        return Inertia::render('admin/Registrants', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Unlike self-registration, an admin adding an account here sets the
        // password directly — no reset-link email round-trip needed since
        // staff can hand the credentials to the person right away.
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        app(EmailNotifier::class)->send('welcome', $user, ['{{name}}' => $user->name]);

        return back()->with('status', ['key' => 'flash.registrant.created', 'params' => ['name' => $user->name]]);
    }
}
