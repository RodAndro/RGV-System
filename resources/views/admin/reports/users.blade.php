@extends('layouts.admin')

@section('title', 'Users Report - Admin Dashboard')

@section('header', 'Users Report')

@section('content')
<div class="p-8">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Reports
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-purple-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Admins</p>
                    <p class="text-3xl font-bold text-[#74c365]">{{ $stats['admins'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-[#74c365] text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Employees</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['employees'] }}</p>
                </div>
                <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-tie text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card-mantis p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Active</p>
                    <p class="text-3xl font-bold text-[#74c365]">{{ $stats['active'] }}</p>
                </div>
                <div class="bg-[#f0f9ef] w-12 h-12 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-[#74c365] text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="section-divider"></div>

    <!-- Users Table Section -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-list"></i>All Users Data</h2>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $users->total() }} records</span>
            <x-per-page-selector />
        </div>
    </div>

    <div class="card-mantis overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->roles->pluck('name')->first() ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge-mantis-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y') : 'Never' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<x-chatbot pageType="users" />
@endpush
