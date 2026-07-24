<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailNotifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
        ]);

        // No password field at registration — the account is created with an
        // unusable random password, then a normal password-reset link is
        // emailed immediately so the user sets their own via the existing
        // reset-password flow, instead of ever emailing a plaintext password.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(40)),
        ]);

        event(new Registered($user));

        app(EmailNotifier::class)->send('welcome', $user, ['{{name}}' => $user->name]);

        Password::sendResetLink(['email' => $user->email]);

        return to_route('home')->with('status', 'สร้างบัญชีผู้ใช้เรียบร้อยแล้ว ระบบได้ส่งอีเมลสำหรับตั้งรหัสผ่านไปที่ '.$user->email.' กรุณาตรวจสอบกล่องจดหมายเพื่อตั้งรหัสผ่านก่อนเข้าสู่ระบบ');
    }
}
