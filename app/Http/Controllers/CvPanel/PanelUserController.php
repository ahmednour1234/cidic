<?php

namespace App\Http\Controllers\CvPanel;

use App\Enums\Department;
use App\Http\Controllers\Controller;
use App\Models\Nationality;
use App\Models\User;
use App\Support\CvPanel\CvPanelPermissions;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PanelUserController extends Controller implements HasMiddleware
{
    /** Managing panel staff is for branch managers and super admins only. */
    public static function middleware(): array
    {
        return [
            function (Request $request, $next) {
                abort_unless(CvPanelPermissions::canManageUsers($request->user()), 403);

                return $next($request);
            },
        ];
    }

    public function index(): View
    {
        return view('cv-panel.users', [
            'users' => User::query()
                ->whereNotNull('department')
                ->orderBy('name')
                ->get(),
            'departments' => Department::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'department' => ['required', Rule::in(Department::values())],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department' => $data['department'],
            // A new user inherits the creator's branch.
            'branch_id' => $request->user()->branch_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إنشاء المستخدم.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Optional on update: blank means "leave the password alone".
            'password' => ['nullable', 'string', 'min:8'],
            'department' => ['required', Rule::in(Department::values())],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Nationality links only mean anything for coordinators.
        if ($data['department'] !== Department::Coordination->value) {
            $user->nationalities()->detach();
        }

        return back()->with('success', 'تم تحديث المستخدم.');
    }

    /** Users are disabled, never deleted. */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك إيقاف حسابك الخاص.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.');
    }

    public function coordinators(): View
    {
        return view('cv-panel.coordinators', [
            'coordinators' => User::query()
                ->active()
                ->where('department', Department::Coordination->value)
                ->with('nationalities')
                ->orderBy('name')
                ->get(),
            'nationalities' => Nationality::query()->ordered()->get(),
        ]);
    }

    public function syncNationalities(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'nationality_ids' => ['nullable', 'array'],
            'nationality_ids.*' => ['integer', 'exists:nationalities,id'],
        ]);

        $user->nationalities()->sync($data['nationality_ids'] ?? []);

        return back()->with('success', 'تم تحديث جنسيات المنسّق.');
    }
}
