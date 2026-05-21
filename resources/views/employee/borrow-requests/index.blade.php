@extends('layouts.employee')

@section('title', 'My Borrow Requests - RGV Multi-Tech Services')

@section('header', 'My Borrow Requests')

@section('content')
<div class="p-8">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('employee.borrow-requests.create') }}" class="btn-mantis">
            <i class="fas fa-plus mr-2"></i>New Request
        </a>
    </div>
                @if(session('success'))
                    <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] border border-[#2563eb] text-[#1e40af] px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Requests</p>
                                <p class="text-3xl font-bold text-gray-800">{{ $borrowRequests->total() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-hand-holding text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pending</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ $borrowRequests->where('status', 'pending')->count() }}</p>
                            </div>
                            <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Borrowed</p>
                                <p class="text-3xl font-bold text-blue-600">{{ $borrowRequests->where('status', 'borrowed')->count() }}</p>
                            </div>
                            <div class="bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Returned</p>
                                <p class="text-3xl font-bold text-[#2563eb]">{{ $borrowRequests->where('status', 'returned')->count() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-undo text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Borrow Requests Table Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i>My Requests</h2>
                    <div class="text-sm text-gray-500">Showing {{ $borrowRequests->total() }} requests</div>
                </div>

                <div class="card-mantis overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-[#eff6ff] to-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Request #</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Borrow Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Due Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Items</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($borrowRequests as $request)
                                    <tr class="hover:bg-[#eff6ff]/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-sm font-semibold text-[#2563eb]">{{ $request->request_number }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->borrow_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->due_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $request->borrowItems->count() }} items</td>
                                        <td class="px-6 py-4">
                                            <span class="badge-mantis-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'approved' ? 'success' : ($request->status == 'borrowed' ? 'warning' : ($request->status == 'returned' ? 'success' : 'danger'))) }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('employee.borrow-requests.show', $request) }}" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status == 'borrowed')
                                                    <a href="{{ route('employee.borrow-requests.show', $request) }}#return" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="Return Items">
                                                        <i class="fas fa-undo"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="w-16 h-16 bg-[#eff6ff] rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-hand-holding text-[#2563eb] text-2xl"></i>
                                            </div>
                                            <p class="font-medium">No borrow requests yet</p>
                                            <a href="{{ route('employee.borrow-requests.create') }}" class="btn-mantis mt-4 inline-block">Create your first request</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($borrowRequests->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $borrowRequests->links() }}
                        </div>
                    @endif
                </div>
@endsection
