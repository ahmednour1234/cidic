<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $request->only('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(),
            'roles' => UserRole::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(UserRole::values())],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
        ], [], $this->attributes());

        $validated['is_active'] = $request->boolean('is_active');

        $user = User::create($validated);
        $this->activity->log('user.created', $user);

        return redirect()->route('admin.users.index')->with('success', 'تمت إضافة المستخدم بنجاح.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['user' => $user]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(UserRole::values())],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
        ], [], $this->attributes());

        // Never let an admin lock themselves out of their own account.
        if ($user->id === $request->user()->id) {
            $validated['role'] = $user->role->value;
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->boolean('is_active');
        }

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        $this->activity->log('user.updated', $user);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث بيانات المستخدم.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        // Keep at least one super admin in the system.
        if ($user->isSuperAdmin() && User::where('role', UserRole::SuperAdmin->value)->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف آخر مدير عام في النظام.');
        }

        $user->delete();
        $this->activity->log('user.deleted', $user);

        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم.');
    }

    /**
     * @return array<string, string>
     */
    protected function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'role' => 'الصلاحية',
            'phone' => 'رقم الجوال',
        ];
    }
}
