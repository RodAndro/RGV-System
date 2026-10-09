<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use App\Notifications\UserStatusChanged;
use App\Services\SupabaseEmployeeAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use RuntimeException;
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

    public function store(Request $request, SupabaseEmployeeAccountService $supabaseAccounts)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $supabaseAuthId = null;
        if ($validated['role'] === 'employee') {
            try {
                $supabaseAuthId = $supabaseAccounts->sync([
                    ...$validated,
                    'is_active' => true,
                ], password: $validated['password']);
            } catch (RuntimeException $exception) {
                return back()->withInput()->withErrors(['supabase' => $exception->getMessage()]);
            }
        }

        $user = DB::transaction(function () use ($validated, $supabaseAuthId) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => true,
            ]);
            $user->assignRole($validated['role']);

            if ($supabaseAuthId !== null) {
                $user->forceFill(['supabase_auth_id' => $supabaseAuthId])->save();
            }

            return $user;
        });

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

    public function update(Request $request, User $user, SupabaseEmployeeAccountService $supabaseAccounts)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $previousRole = $user->roles()->value('name');
        $supabaseAuthId = null;
        if ($validated['role'] === 'employee' || ($previousRole === 'employee' && $user->supabase_auth_id !== null)) {
            try {
                $supabaseAuthId = $supabaseAccounts->sync([
                    ...$validated,
                    'role' => $validated['role'],
                    'is_active' => $user->is_active,
                ], $user->supabase_auth_id, $validated['password'] ?? null, [$user->email]);
            } catch (RuntimeException $exception) {
                return back()->withInput()->withErrors(['supabase' => $exception->getMessage()]);
            }
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

        DB::transaction(function () use ($user, $data, $validated, $supabaseAuthId) {
            $user->update($data);
            $user->syncRoles([$validated['role']]);

            if ($supabaseAuthId !== null) {
                $user->forceFill(['supabase_auth_id' => $supabaseAuthId])->save();
            }
        });

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User '{$user->name}' has been updated successfully.");
    }

    public function destroy(Request $request, User $user, SupabaseEmployeeAccountService $supabaseAccounts)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->is_active) {
            return back()->with('error', 'You must deactivate the user before deleting.');
        }

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        if ($user->supabase_auth_id !== null) {
            try {
                $supabaseAccounts->delete($user->supabase_auth_id);
            } catch (RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' has been deleted successfully.");
    }

    public function toggleStatus(User $user)
    {
        $newStatus = ! $user->is_active;

        if ($user->isEmployee() && $user->supabase_auth_id !== null) {
            try {
                app(SupabaseEmployeeAccountService::class)->sync([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => 'employee',
                    'is_active' => $newStatus,
                ], $user->supabase_auth_id);
            } catch (RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        $user->update([
            'is_active' => $newStatus,
        ]);

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
}
