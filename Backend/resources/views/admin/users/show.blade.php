@extends('layouts.admin')

@section('title', 'User Details - Admin Dashboard')

@section('header', 'User Details')

@section('content')
<div class="p-4 md:p-8">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors text-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to Users
    </a>
    <div class="card-mantis p-6">
        <div class="flex justify-between items-start mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $user->name }}</h1>
            <div class="flex items-center space-x-3 flex-wrap gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all font-medium">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                @if($user->id !== auth()->id())
                    <button type="button" @click="$dispatch('open-modal', 'confirm-user-status')" class="px-6 py-2 rounded-xl font-medium
                            @if($user->is_active) bg-red-500 text-white hover:bg-red-600 transition-all
                            @else btn-mantis
                            @endif">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    @if(!$user->is_active)
                    <button type="button" @click="$dispatch('open-modal', 'confirm-user-delete')" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                    @else
                    <span class="px-4 py-2 bg-gray-300 text-gray-500 rounded-xl font-medium cursor-not-allowed" title="Deactivate the user before deleting">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </span>
                    @endif
                    @if(!$user->mfa_enabled)
                    <button type="button" @click="$dispatch('open-modal', 'confirm-user-impersonation')" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all font-medium">
                        <i class="fas fa-user-secret mr-2"></i>Impersonate
                    </button>
                    @else
                    <span class="px-4 py-2 bg-gray-300 text-gray-500 rounded-xl font-medium cursor-not-allowed" title="Cannot impersonate users with two-factor authentication enabled">
                        <i class="fas fa-user-secret mr-2"></i>Impersonate
                    </span>
                    @endif
                    <button type="button" @click="$dispatch('open-modal', 'confirm-force-logout')" class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>Force Logout
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div><h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</h3><p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p></div>
            <div><h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone</h3><p class="text-gray-600 dark:text-gray-400">{{ $user->phone ?? 'N/A' }}</p></div>
            <div><h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Role</h3><p class="text-gray-600 dark:text-gray-400">{{ $user->roles->pluck('name')->first() ?? 'N/A' }}</p></div>
            <div><h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</h3>
                <span class="badge-mantis-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="col-span-2"><h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Address</h3><p class="text-gray-600 dark:text-gray-400">{{ $user->address ?? 'N/A' }}</p></div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Activity</h3>
            <p class="text-gray-600 dark:text-gray-400">Last Login: {{ $user->last_login_at ? $user->last_login_at->format('F d, Y - g:i A') : 'Never' }}</p>
            <p class="text-gray-600 dark:text-gray-400">Created: {{ $user->created_at->format('F d, Y - g:i A') }}</p>
        </div>

        @if(isset($loginHistory) && $loginHistory->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="font-semibold text-gray-700 mb-3">Recent Login History</h3>
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-4 py-2 border-b border-r border-gray-200">Logged In</th>
                        <th class="px-4 py-2 border-b border-r border-gray-200">Logged Out</th>
                        <th class="px-4 py-2 border-b border-r border-gray-200">IP Address</th>
                        <th class="px-4 py-2 border-b border-gray-200">Flags</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loginHistory as $entry)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 border-b border-r border-gray-100">{{ $entry->logged_in_at?->format('M d, Y g:i A') ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border-b border-r border-gray-100">
                                @if($entry->logged_out_at)
                                    {{ $entry->logged_out_at->format('g:i A') }}
                                @else
                                    <span class="text-blue-600 text-xs">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border-b border-r border-gray-100 text-xs font-mono">{{ $entry->ip_address }}</td>
                            <td class="px-4 py-2 border-b border-gray-100">
                                @if($entry->is_impersonation)
                                    <span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">impersonation</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@if($user->id !== auth()->id())
    <x-confirm-action-modal name="confirm-user-status" action="{{ route('admin.users.toggle-status', $user) }}" title="Confirm Account Status Change" :message="'Enter your password to ' . ($user->is_active ? 'deactivate' : 'activate') . ' ' . $user->name . '.'" confirmText="{{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}" icon="fa-user-shield" passwordRequired confirmationKey="user-status" :show="$errors->has('password') && old('_confirmation_action') === 'user-status'" />
    @if(!$user->is_active)
        <x-confirm-action-modal name="confirm-user-delete" action="{{ route('admin.users.destroy', $user) }}" method="DELETE" title="Confirm Deletion" :message="'Are you sure you want to permanently delete ' . $user->name . '? This action cannot be undone.'" confirmText="Delete Permanently" icon="fa-trash" passwordRequired confirmationKey="user-delete" :show="$errors->has('password') && old('_confirmation_action') === 'user-delete'" />
    @endif
    @if(!$user->mfa_enabled)
        <x-confirm-action-modal name="confirm-user-impersonation" action="{{ route('admin.users.impersonate', $user) }}" title="Confirm Impersonation" :message="'Enter your password to start impersonating ' . $user->name . '.'" confirmText="Start Impersonation" icon="fa-user-secret" passwordRequired confirmationKey="user-impersonate" :show="$errors->has('password') && old('_confirmation_action') === 'user-impersonate'" />
    @endif
    <x-confirm-action-modal name="confirm-force-logout" action="{{ route('admin.users.force-logout', $user) }}" title="Confirm Force Logout" :message="'Are you sure you want to log ' . $user->name . ' out from all devices?'" confirmText="Force Logout" icon="fa-sign-out-alt" />
@endif

@endsection
