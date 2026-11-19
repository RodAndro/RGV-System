@extends('layouts.employee')

@section('title', 'Borrow Request Details - RGV Multi-Tech Services')

@section('header', 'Borrow Request Details')

@section('content')
<div class="p-8">
    <div class="mb-6">
        <a href="{{ route('employee.borrow-requests.index') }}" class="inline-flex items-center text-gray-600 hover:text-[#2563eb] transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Requests
        </a>
    </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Request Details -->
                    <div class="lg:col-span-2">
                        <div class="card-mantis p-6 mb-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Request Number</p>
                                    <p class="font-mono text-2xl font-bold text-[#2563eb]">{{ $borrowRequest->request_number }}</p>
                                </div>
                                <span class="badge-mantis-{{ $borrowRequest->status == 'pending' ? 'warning' : ($borrowRequest->status == 'approved' ? 'success' : ($borrowRequest->status == 'borrowed' ? 'warning' : ($borrowRequest->status == 'returned' ? 'success' : 'danger'))) }}">
                                    {{ ucfirst($borrowRequest->status) }}
                                </span>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Borrow Date</p>
                                    <p class="font-semibold text-gray-800">{{ $borrowRequest->borrow_date->format('F d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Due Date</p>
                                    <p class="font-semibold text-gray-800">{{ $borrowRequest->due_date->format('F d, Y') }}</p>
                                </div>
                                @if($borrowRequest->return_date)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Return Date</p>
                                        <p class="font-semibold text-gray-800">{{ $borrowRequest->return_date->format('F d, Y') }}</p>
                                    </div>
                                @endif
                                @if($borrowRequest->returned_at)
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                                <i class="fas fa-map-marker-alt mr-1 text-[#2563eb]"></i>Returned at: {{ $borrowRequest->return_location ?: 'Not specified' }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-6">
                                <p class="text-sm text-gray-500 mb-1">Reason</p>
                                <p class="font-semibold text-gray-800">{{ $borrowRequest->reason }}</p>
                            </div>

                            @if($borrowRequest->admin_remarks)
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-500 mb-1">Admin Remarks</p>
                                    <p class="text-gray-800">{{ $borrowRequest->admin_remarks }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Borrowed Items -->
                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Borrowed Items</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left text-gray-500 text-sm">
                                            <th class="pb-3">Item</th>
                                            <th class="pb-3">Quantity</th>
                                            <th class="pb-3">Condition Borrowed</th>
                                            <th class="pb-3">Condition Returned</th>
                                            <th class="pb-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($borrowRequest->borrowItems as $item)
                                            <tr class="border-t border-gray-100">
                                                <td class="py-3">
                                                    <p class="font-semibold text-gray-800">{{ $item->inventory->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->inventory->item_code }}</p>
                                                </td>
                                                <td class="py-3 text-gray-600">{{ $item->quantity }} {{ $item->inventory->unit }}</td>
                                                <td class="py-3">
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                        {{ ucfirst($item->condition_borrowed) }}
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    @if($item->condition_returned)
                                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                            @if($item->condition_returned == 'new') bg-blue-100 text-blue-800
                                                            @elseif($item->condition_returned == 'good') bg-green-100 text-green-800
                                                            @elseif($item->condition_returned == 'fair') bg-yellow-100 text-yellow-800
                                                            @elseif($item->condition_returned == 'damaged') bg-red-100 text-red-800
                                                            @endif">
                                                            {{ ucfirst($item->condition_returned) }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 text-sm">Not returned</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @if($item->is_returned)
                                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Returned</span>
                                                    @else
                                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Borrowed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div>
                        <div class="card-mantis p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>

                            @if($borrowRequest->status == 'approved')
                                <button type="button" @click="$dispatch('open-modal', 'confirm-borrow-receipt')" class="w-full btn-mantis mb-4">
                                    <i class="fas fa-hand-holding mr-2"></i>Mark as Borrowed
                                </button>
                            @endif

                            @if($borrowRequest->status == 'borrowed')
                                <button onclick="showReturnModal()" class="w-full btn-mantis mb-4">
                                    <i class="fas fa-undo mr-2"></i>Return Items
                                </button>
                            @endif

                            @if($borrowRequest->status == 'pending')
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4 text-center">
                                    <p class="text-yellow-800 text-sm">
                                        <i class="fas fa-clock mr-2"></i>Your request is pending approval.
                                    </p>
                                </div>
                                <button type="button" @click="$dispatch('open-modal', 'confirm-borrow-cancel')" class="w-full px-6 py-3 border border-red-200 text-red-700 bg-white rounded-xl hover:bg-red-50 transition-all font-medium">
                                    <i class="fas fa-times-circle mr-2"></i>Cancel Request
                                </button>
                            @endif

                            @if($borrowRequest->status == 'rejected')
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                                    <p class="text-red-800 text-sm">
                                        <i class="fas fa-times-circle mr-2"></i>Your request has been rejected.
                                    </p>
                                </div>
                            @endif

                            @if($borrowRequest->status == 'returned')
                                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                                    <p class="text-green-800 text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>All items have been returned.
                                    </p>
                                </div>
                                <button type="button" @click="$dispatch('open-modal', 'confirm-borrow-delete')" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                                    <i class="fas fa-trash mr-2"></i>Delete Request
                                </button>
                            @endif
                        </div>

                        <!-- Timeline -->
                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Timeline</h3>
                            <div class="relative">
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                                <div class="relative pl-10 pb-6">
                                    <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Request Submitted</p>
                                        <p class="text-sm text-gray-500">{{ $borrowRequest->created_at->format('F d, Y - g:i A') }}</p>
                                    </div>
                                </div>

                                @if($borrowRequest->approved_at)
                                    <div class="relative pl-10 pb-6">
                                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Request Approved</p>
                                            <p class="text-sm text-gray-500">{{ $borrowRequest->approved_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($borrowRequest->borrowed_at)
                                    <div class="relative pl-10 pb-6">
                                        <div class="absolute left-2 w-5 h-5 bg-orange-500 rounded-full border-4 border-white shadow-lg shadow-orange-500/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Items Borrowed</p>
                                            <p class="text-sm text-gray-500">{{ $borrowRequest->borrowed_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($borrowRequest->returned_at)
                                    <div class="relative pl-10">
                                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Items Returned</p>
                                            <p class="text-sm text-gray-500">{{ $borrowRequest->returned_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <!-- Return Details Modal -->
    <x-modal name="employee-return-items" maxWidth="3xl" panelClass="bg-white max-h-[90vh] overflow-y-auto">
        <div class="p-7 sm:p-8 text-gray-900">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg" aria-hidden="true"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Record Item Return</h3>
            </div>
            <p class="text-base leading-6 text-gray-600 mb-5">Review each item's condition and return location before submitting.</p>
            <form action="{{ route('employee.borrow-requests.return', $borrowRequest) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    @foreach($borrowRequest->borrowItems->where('is_returned', false) as $item)
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                            <p class="font-semibold text-gray-800 mb-2">{{ $item->inventory->name }} <span class="text-gray-500 font-normal">(Qty: {{ $item->quantity }} {{ $item->inventory->unit }})</span></p>
                            <input type="hidden" name="items[{{ $loop->index }}][borrow_item_id]" value="{{ $item->id }}">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Condition Returned *</label>
                                    <select name="items[{{ $loop->index }}][condition_returned]" required
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white">
                                        <option value="new">New</option>
                                        <option value="good" selected>Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="damaged">Damaged</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Damage Notes (if damaged)</label>
                                <textarea name="items[{{ $loop->index }}][damage_notes]" rows="2"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white"
                                    placeholder="Describe any damage"></textarea>
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Return Location</label>
                        @if($locations->isNotEmpty())
                            <select name="return_location" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white">
                                <option value="" disabled selected>Select a return location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}">{{ $location }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="return_location" required maxlength="255"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-white"
                                placeholder="Enter return location">
                        @endif
                        <p class="text-xs text-gray-500 mt-1">Where are these items being returned?</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" @click="$dispatch('close-modal', 'employee-return-items')" class="px-5 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors text-sm font-semibold">
                        <i class="fas fa-check mr-2"></i>Submit Return
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    @if($borrowRequest->status == 'approved')
        <x-confirm-action-modal name="confirm-borrow-receipt" action="{{ route('employee.borrow-requests.mark-borrowed', $borrowRequest) }}" title="Confirm Receipt" message="Confirm that you have received all items in this borrow request?" confirmText="Mark as Borrowed" icon="fa-hand-holding" confirmClass="bg-blue-600 hover:bg-blue-700" />
    @endif
    @if($borrowRequest->status == 'pending')
        <x-confirm-action-modal name="confirm-borrow-cancel" action="{{ route('employee.borrow-requests.cancel', $borrowRequest) }}" title="Confirm Cancellation" message="Cancel this pending borrow request? Its reserved items will become available again." confirmText="Cancel Request" icon="fa-times" />
    @endif
    @if($borrowRequest->status == 'returned')
        <x-confirm-action-modal name="confirm-borrow-delete" action="{{ route('employee.borrow-requests.destroy', $borrowRequest) }}" method="DELETE" title="Confirm Deletion" message="Delete this borrow request? An administrator can restore it if needed." confirmText="Delete Request" icon="fa-trash" />
    @endif

    @push('scripts')
    <script>
        function showReturnModal() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-return-items' }));
        }
    </script>
    @endpush
@endsection
