@extends('layouts.admin')

@section('title', 'User Analytics - Admin Dashboard')

@section('header', 'User Activity Analytics')

@section('content')
<div class="p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card-mantis p-6">
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-3xl font-bold text-gray-800">{{ \App\Models\User::count() }}</p>
        </div>
        <div class="card-mantis p-6">
            <p class="text-sm text-gray-500">Active Today</p>
            <p class="text-3xl font-bold text-[#2563eb]">{{ \App\Models\LoginHistory::whereDate('logged_in_at', today())->distinct('user_id')->count() }}</p>
        </div>
        <div class="card-mantis p-6">
            <p class="text-sm text-gray-500">Failed Logins Today</p>
            <p class="text-3xl font-bold text-red-600">{{ \App\Models\AuditLog::where('event', 'failed login')->whereDate('created_at', today())->count() }}</p>
        </div>
        <div class="card-mantis p-6">
            <p class="text-sm text-gray-500">Total Sessions</p>
            <p class="text-3xl font-bold text-blue-600">{{ \DB::table('sessions')->count() }}</p>
        </div>
    </div>

    <div class="card-mantis p-6 mb-8">
        <h3 class="font-semibold text-gray-800 mb-4">Recent User Activity</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">User</th><th class="px-4 py-2 text-left">Action</th><th class="px-4 py-2 text-left">Time</th></tr></thead>
            <tbody>
                @foreach(\App\Models\AuditLog::with('user')->whereNotNull('user_id')->latest()->take(20)->get() as $log)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-4 py-2">{{ $log->event }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-mantis p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Users by Login Count (Last 30 Days)</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">User</th><th class="px-4 py-2 text-left">Role</th><th class="px-4 py-2 text-right">Logins</th><th class="px-4 py-2 text-left">Last Active</th></tr></thead>
            <tbody>
                @foreach(\App\Models\User::with('roles')->withCount(['loginHistory' => fn($q) => $q->where('logged_in_at', '>=', now()->subDays(30))])->orderByDesc('login_history_count')->get() as $user)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->roles->pluck('name')->first() }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ $user->login_history_count }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
