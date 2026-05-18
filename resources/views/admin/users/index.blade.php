@extends('layouts.admin')

@section('title', 'Users - Admin Dashboard')

@section('header', 'Users')

@section('content')
<div class="p-8">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-users"></i>All Users</h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Showing {{ $users->total() }} users</span>
                    <x-per-page-selector />
                    <a href="{{ route('admin.users.create') }}" class="btn-mantis text-sm px-4 py-2">
                        <i class="fas fa-user-plus mr-1"></i>Add User
                    </a>
                </div>
            </div>

            <div class="card-mantis overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->roles->pluck('name')->first() ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="badge-mantis-success">Active</span>
                                    @else
                                        <span class="badge-mantis-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-[#74c365] hover:text-[#5dad4f] mr-3 font-medium">View</a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-{{ $user->is_active ? 'red-500' : '[#74c365]' }} hover:text-{{ $user->is_active ? 'red-600' : '[#5dad4f]' }} font-medium">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        @if(!$user->is_active)
                                            <a href="{{ route('admin.users.show', $user) }}" class="text-red-500 hover:text-red-700 font-medium ml-3">Delete</a>
                                        @endif
                                    @endif
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
