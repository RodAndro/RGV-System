@extends('layouts.admin')

@section('title', 'Users - Admin Dashboard')

@section('header', 'Users')

@section('content')
<div class="p-8">


            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-users"></i>All Users</h2>
                <a href="{{ route('admin.users.create') }}" class="btn-mantis text-sm px-4 py-2">
                    <i class="fas fa-user-plus mr-1"></i>Add User
                </a>
            </div>

            <div class="card-mantis overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-r border-gray-200">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 border-b border-r border-gray-100 text-sm font-medium text-gray-800">{{ $user->name }}</td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 text-sm text-gray-600">{{ $user->roles->pluck('name')->first() ?? 'N/A' }}</td>
                                <td class="px-4 py-3 border-b border-r border-gray-100 text-sm">
                                    @if($user->is_active)
                                        <span class="badge-mantis-success">Active</span>
                                    @else
                                        <span class="badge-mantis-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-b border-gray-100 text-sm font-medium">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-[#2563eb] hover:text-[#1d4ed8]">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
@endsection
