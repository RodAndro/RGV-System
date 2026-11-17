<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Services\SupabaseAuthAdminService;
use App\Services\SupabaseAuthSyncException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ASSIGNABLE_ROLES = ['admin', 'employee'];

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by letter
        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->where('name', 'like', $letter.'%');
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $users = $query->latest()->paginate($perPage)->appends($request->except('page'));

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::query()->whereIn('name', self::ASSIGNABLE_ROLES)->where('guard_name', 'web')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, SupabaseAuthAdminService $supabaseAuth)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        try {
            $user = DB::transaction(function () use ($validated, $supabaseAuth) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'is_active' => true,
                ]);
                $user->assignRole($validated['role']);

                if ($validated['role'] === 'employee') {
                    $authId = $supabaseAuth->syncUser($user, $validated['password']);
                    $user->forceFill(['supabase_auth_id' => $authId])->save();
                }

                return $user;
            });
        } catch (SupabaseAuthSyncException $exception) {
            return back()->withInput()->withErrors([
                'supabase' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User '{$user->name}' has been created successfully.");
    }

    public function show(User $user)
    {
        $user->load('roles');
        $loginHistory = LoginHistory::where('user_id', $user->id)->latest()->take(10)->get();

        return view('admin.users.show', compact('user', 'loginHistory'));
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::query()->whereIn('name', self::ASSIGNABLE_ROLES)->where('guard_name', 'web')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(
        Request $request,
        User $user,
        SupabaseAuthAdminService $supabaseAuth,
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($user->isAdmin()
            && $validated['role'] !== 'admin'
            && ($this->isLastAdministrator($user) || $this->isLastActiveAdministrator($user))) {
            return back()->withInput()->with('error', 'The last administrator cannot be removed or deactivated.');
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $credentialsChanged = $validated['email'] !== $user->email
            || ! empty($validated['password']);
        $previousRole = $user->roles()->value('name');
        $supabaseAuthId = null;
        if (
            $validated['role'] === 'employee'
            || ($previousRole === 'employee' && $user->supabase_auth_id !== null)
        ) {
            $supabaseUser = clone $user;
            $supabaseUser->forceFill($data);
            try {
                $supabaseAuthId = $supabaseAuth->syncUser(
                    $supabaseUser,
                    $validated['password'] ?? null,
                    role: $validated['role'],
                );
            } catch (SupabaseAuthSyncException $exception) {
                return back()->withInput()->withErrors([
                    'supabase' => $exception->getMessage(),
                ]);
            }
        }

        DB::transaction(function () use ($user, $data, $validated, $supabaseAuthId) {
            $user->update($data);
            $user->syncRoles([$validated['role']]);

            if ($supabaseAuthId !== null) {
                $user->forceFill(['supabase_auth_id' => $supabaseAuthId])->save();
            }
        });

        if (! empty($validated['password'])) {
            $user->tokens()->delete();
        }

        $successMessage = $credentialsChanged
            ? "Credentials for '{$user->name}' have been updated successfully."
            : "User '{$user->name}' has been updated successfully.";

        return redirect()->route('admin.users.show', $user)
            ->with('success', $successMessage);
    }

    public function destroy(
        Request $request,
        User $user,
        SupabaseAuthAdminService $supabaseAuth,
    ) {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->isLastAdministrator($user)) {
            return back()->with('error', 'You cannot delete the last administrator account.');
        }

        if ($user->is_active) {
            return back()->with('error', 'You must deactivate the user before deleting.');
        }

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        if ($user->supabase_auth_id !== null) {
            try {
                $supabaseAuth->deleteUser($user->supabase_auth_id);
            } catch (SupabaseAuthSyncException $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' has been deleted successfully.");
    }

    public function toggleStatus(
        User $user,
        SupabaseAuthAdminService $supabaseAuth,
    ) {
        $newStatus = ! $user->is_active;

        if ($newStatus === false && $this->isLastActiveAdministrator($user)) {
            return back()->with('error', 'You cannot deactivate the last active administrator account.');
        }

        if ($user->isEmployee() && $user->supabase_auth_id !== null) {
            try {
                $supabaseAuth->syncUser($user, isActive: $newStatus);
            } catch (SupabaseAuthSyncException $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        $user->update([
            'is_active' => $newStatus,
        ]);
        if (! $newStatus) {
            $user->tokens()->delete();
        }

        // Send notification to user about status change
        $user->notify(new UserStatusChanged($user, $newStatus));

        return back()->with('success', 'User status updated successfully.');
    }

    public function forceLogout(User $user)
    {
        \DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', "User '{$user->name}' has been forcefully logged out.");
    }

    public function analytics()
    {
        return view('admin.users.analytics');
    }

    private function isLastAdministrator(User $user): bool
    {
        return $user->isAdmin()
            && ! User::role('admin')->whereKeyNot($user->getKey())->exists();
    }

    private function isLastActiveAdministrator(User $user): bool
    {
        return $user->is_active
            && $user->isAdmin()
            && ! User::role('admin')
                ->where('is_active', true)
                ->whereKeyNot($user->getKey())
                ->exists();
    }
}
