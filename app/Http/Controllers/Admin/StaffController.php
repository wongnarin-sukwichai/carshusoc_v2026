<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Staff', [
            'admins' => Admin::orderBy('name')->paginate(10, ['id', 'name', 'email', 'role']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ]);

        $admin = Admin::create($data);

        return back()->with('status', ['key' => 'flash.staff.created', 'params' => ['name' => $admin->name]]);
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ]);

        // The staff page is itself gated to role:admin — demoting the last
        // admin would lock everyone out of ever managing this page again.
        if ($data['role'] !== 'admin' && $admin->role === 'admin' && $this->isLastAdmin($admin)) {
            throw ValidationException::withMessages([
                'role' => 'ต้องมีผู้ดูแลระบบ (Admin) อย่างน้อย 1 คนเสมอ',
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $admin->update($data);

        return back()->with('status', ['key' => 'flash.staff.updated', 'params' => ['name' => $admin->name]]);
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        // No last-admin check needed here: this route is itself gated to
        // role:admin, so the acting admin is always a distinct row from any
        // *other* admin being deleted — only self-deletion can ever remove
        // the last one, and that's the check above.
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', ['key' => 'flash.staff.deleteBlockedSelf']);
        }

        $name = $admin->name;
        $admin->delete();

        return back()->with('status', ['key' => 'flash.staff.deleted', 'params' => ['name' => $name]]);
    }

    private function isLastAdmin(Admin $admin): bool
    {
        return Admin::where('role', 'admin')->where('id', '!=', $admin->id)->doesntExist();
    }
}
