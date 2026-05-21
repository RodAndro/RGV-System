<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Request Details - RGV Multi-Tech Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
        }
        .sidebar-link-mantis {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            color: #64748b;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .sidebar-link-mantis:hover {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
            transform: translateX(4px);
        }
        .sidebar-link-active-mantis {
            display: flex;
            align-items: justify-between;
            padding: 12px 16px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(116, 195, 101, 0.3);
        }
        .dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            padding-left: 48px;
        }
        .dropdown-menu.open {
            max-height: 500px;
            transition: max-height 0.3s ease-in;
        }
        .dropdown-item {
            padding: 10px 16px;
            color: #64748b;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            display: block;
        }
        .dropdown-item:hover {
            background: #eff6ff;
            color: #2563eb;
        }
        .chevron-icon {
            transition: transform 0.3s ease;
        }
        .chevron-icon.rotate {
            transform: rotate(180deg);
        }
        .nav-section {
            margin-bottom: 8px;
        }
        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            padding: 8px 16px;
            margin-top: 16px;
        }
        .card-mantis {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .card-mantis:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .btn-mantis {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(116, 195, 101, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }
        .btn-mantis:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(116, 195, 101, 0.4);
        }
        .badge-mantis-warning {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }
        .badge-mantis-success {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
        }
        .badge-mantis-danger {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-72 bg-white border-r border-gray-200 fixed h-full shadow-xl z-50 flex flex-col">
            <div class="p-6">
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-xl flex items-center justify-center shadow-lg shadow-[#2563eb]/30">
                        <i class="fas fa-cogs text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-800 ml-3">RGV Employee</span>
                </div>
                <nav>
                    <!-- Main Navigation -->
                    <a href="{{ route('employee.dashboard') }}" class="sidebar-link-mantis">
                        <i class="fas fa-tachometer-alt mr-3 w-5"></i>Dashboard
                    </a>

                    <div class="nav-section-title">Borrow Management</div>
                    
                    <div class="nav-section">
                        <div class="sidebar-link-mantis" onclick="toggleDropdown('borrow-dropdown')">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <i class="fas fa-hand-holding mr-3 w-5"></i>Borrow Requests
                                </span>
                                <i class="fas fa-chevron-down chevron-icon text-xs" id="borrow-chevron"></i>
                            </div>
                        </div>
                        <div class="dropdown-menu" id="borrow-dropdown">
                            <a href="{{ route('employee.borrow-requests.index') }}" class="dropdown-item">My Requests</a>
                            <a href="{{ route('employee.borrow-requests.create') }}" class="dropdown-item">New Request</a>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="mt-auto p-6 border-t border-gray-100">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition-all duration-300 w-full text-left font-medium">
                        <i class="fas fa-sign-out-alt mr-3 w-5"></i>Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-72">
            <!-- Top Navigation -->
            <header class="bg-white/80 backdrop-blur-xl border-b border-gray-200 sticky top-0 z-40">
                <div class="flex justify-between items-center px-8 py-5">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('employee.borrow-requests.index') }}" class="text-gray-600 hover:text-[#2563eb] transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                        </a>
                        <h1 class="text-2xl font-bold text-gray-800">Borrow Request Details</h1>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] border border-[#2563eb] text-[#1e40af] px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

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
                                <form action="{{ route('employee.borrow-requests.mark-borrowed', $borrowRequest) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full btn-mantis mb-4" onclick="return confirm('Confirm that you have received all items?')">
                                        <i class="fas fa-hand-holding mr-2"></i>Mark as Borrowed
                                    </button>
                                </form>
                            @endif

                            @if($borrowRequest->status == 'borrowed')
                                <button onclick="showReturnModal()" class="w-full btn-mantis mb-4">
                                    <i class="fas fa-undo mr-2"></i>Return Items
                                </button>
                            @endif

                            @if($borrowRequest->status == 'pending')
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                                    <p class="text-yellow-800 text-sm">
                                        <i class="fas fa-clock mr-2"></i>Your request is pending approval.
                                    </p>
                                </div>
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
                                <form action="{{ route('employee.borrow-requests.destroy', $borrowRequest) }}" method="POST" onsubmit="return confirm('Delete this borrow request? It can be restored by an admin if needed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                                        <i class="fas fa-trash mr-2"></i>Delete Request
                                    </button>
                                </form>
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
        </main>
    </div>

    <!-- Return Modal -->
    <div id="returnModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm" onclick="if(event.target === this) hideReturnModal()">
        <div class="card-mantis p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Return Items</h3>
                <button type="button" onclick="hideReturnModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
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
                </div>
                <div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" onclick="hideReturnModal()" class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-all font-medium text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="btn-mantis">
                        <i class="fas fa-check mr-2"></i>Submit Return
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showReturnModal() {
            document.getElementById('returnModal').classList.remove('hidden');
            document.getElementById('returnModal').classList.add('flex');
        }

        function hideReturnModal() {
            document.getElementById('returnModal').classList.add('hidden');
            document.getElementById('returnModal').classList.remove('flex');
        }

        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const chevron = document.getElementById(dropdownId.replace('-dropdown', '-chevron'));
            
            dropdown.classList.toggle('open');
            chevron.classList.toggle('rotate');
        }

        // Keep dropdowns open based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            // Open relevant dropdown based on current route
            if (currentPath.includes('borrow-requests')) {
                document.getElementById('borrow-dropdown').classList.add('open');
                document.getElementById('borrow-chevron').classList.add('rotate');
            }
        });
    </script>
</body>
</html>
