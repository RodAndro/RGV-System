<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - RGV Multi-Tech Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --mantis: #74c365;
            --mantis-dark: #468a3f;
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
            background: linear-gradient(135deg, #f0f9ef 0%, #e0f3df 100%);
            color: #74c365;
            transform: translateX(4px);
        }
        .sidebar-link-active-mantis {
            display: flex;
            align-items: justify-between;
            padding: 12px 16px;
            background: linear-gradient(135deg, #74c365 0%, #5dad4f 100%);
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
            background: #f0f9ef;
            color: #74c365;
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
            background: linear-gradient(135deg, #74c365 0%, #5dad4f 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(116, 195, 101, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-mantis:hover {
            background: linear-gradient(135deg, #5dad4f 0%, #468a3f 100%);
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
            background: linear-gradient(135deg, #f0f9ef 0%, #e0f3df 100%);
            color: #74c365;
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
                    <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center shadow-lg shadow-[#74c365]/30">
                        <i class="fas fa-cogs text-white text-lg"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-800 ml-3">RGV Admin</span>
                </div>
                <nav>
                    <!-- Main Navigation -->
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link-mantis">
                        <i class="fas fa-tachometer-alt mr-3 w-5"></i>Dashboard
                    </a>

                    <div class="nav-section-title">Management</div>
                    
                    <div class="nav-section">
                        <div class="sidebar-link-mantis" onclick="toggleDropdown('bookings-dropdown')">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-3 w-5"></i>Bookings
                                </span>
                                <i class="fas fa-chevron-down chevron-icon text-xs" id="bookings-chevron"></i>
                            </div>
                        </div>
                        <div class="dropdown-menu" id="bookings-dropdown">
                            <a href="{{ route('admin.bookings.index') }}" class="dropdown-item">All Bookings</a>
                            <a href="{{ route('admin.bookings.calendar') }}" class="dropdown-item">Calendar View</a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="sidebar-link-mantis" onclick="toggleDropdown('inventory-dropdown')">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <i class="fas fa-boxes mr-3 w-5"></i>Inventory
                                </span>
                                <i class="fas fa-chevron-down chevron-icon text-xs" id="inventory-chevron"></i>
                            </div>
                        </div>
                        <div class="dropdown-menu" id="inventory-dropdown">
                            <a href="{{ route('admin.inventories.index') }}" class="dropdown-item">All Items</a>
                            <a href="{{ route('admin.inventories.low-stock') }}" class="dropdown-item">Low Stock Alerts</a>
                            <a href="{{ route('admin.inventories.create') }}" class="dropdown-item">Add New Item</a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <a href="{{ route('admin.borrow-requests.index') }}" class="sidebar-link-mantis">
                            <i class="fas fa-hand-holding mr-3 w-5"></i>Borrow Requests
                        </a>
                    </div>

                    <div class="nav-section-title">Administration</div>

                    <div class="nav-section">
                        <div class="sidebar-link-mantis" onclick="toggleDropdown('users-dropdown')">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <i class="fas fa-users mr-3 w-5"></i>Users
                                </span>
                                <i class="fas fa-chevron-down chevron-icon text-xs" id="users-chevron"></i>
                            </div>
                        </div>
                        <div class="dropdown-menu" id="users-dropdown">
                            <a href="{{ route('admin.users.index') }}" class="dropdown-item">All Users</a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="sidebar-link-mantis" onclick="toggleDropdown('reports-dropdown')">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-bar mr-3 w-5"></i>Reports
                                </span>
                                <i class="fas fa-chevron-down chevron-icon text-xs" id="reports-chevron"></i>
                            </div>
                        </div>
                        <div class="dropdown-menu" id="reports-dropdown">
                            <a href="{{ route('admin.reports.index') }}" class="dropdown-item">All Reports</a>
                            <a href="{{ route('admin.reports.bookings') }}" class="dropdown-item">Bookings Report</a>
                            <a href="{{ route('admin.reports.inventory') }}" class="dropdown-item">Inventory Report</a>
                            <a href="{{ route('admin.reports.borrow-requests') }}" class="dropdown-item">Borrow Requests</a>
                            <a href="{{ route('admin.reports.users') }}" class="dropdown-item">Users Report</a>
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
                        <a href="{{ route('admin.bookings.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Bookings
                        </a>
                        <h1 class="text-2xl font-bold text-gray-800">Booking Details</h1>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="bg-gradient-to-r from-[#f0f9ef] to-[#e0f3df] border border-[#74c365] text-[#468a3f] px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Booking Details -->
                    <div class="lg:col-span-2">
                        <div class="card-mantis p-6 mb-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Reference Number</p>
                                    <p class="font-mono text-2xl font-bold text-[#74c365]">{{ $booking->reference_number }}</p>
                                </div>
                                <span class="badge-mantis-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'approved' ? 'success' : ($booking->status == 'rejected' ? 'danger' : ($booking->status == 'completed' ? 'success' : 'warning'))) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Full Name</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Email</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Contact Number</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->contact_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Purpose Category</p>
                                    <p class="font-semibold text-gray-800">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Preferred Date</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->preferred_date->format('F d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Preferred Time</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->preferred_time }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500 mb-1">Address</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->address }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500 mb-1">Reason</p>
                                    <p class="font-semibold text-gray-800">{{ $booking->reason }}</p>
                                </div>
                            </div>

                            @if($booking->attachment_path)
                                <div class="mt-6">
                                    <p class="text-sm text-gray-500 mb-2">Attachment</p>
                                    <a href="{{ asset('storage/' . $booking->attachment_path) }}" target="_blank"
                                        class="inline-flex items-center text-[#74c365] hover:text-[#5dad4f] font-medium">
                                        <i class="fas fa-file-alt mr-2"></i>View Attachment
                                    </a>
                                </div>
                            @endif

                            @if($booking->remarks)
                                <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-500 mb-1">Remarks</p>
                                    <p class="text-gray-800">{{ $booking->remarks }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Timeline -->
                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Booking Timeline</h3>
                            <div class="relative">
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                
                                <div class="relative pl-10 pb-6">
                                    <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-full border-4 border-white shadow-lg shadow-[#74c365]/30"></div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Booking Submitted</p>
                                        <p class="text-sm text-gray-500">{{ $booking->created_at->format('F d, Y - g:i A') }}</p>
                                    </div>
                                </div>

                                @if($booking->approved_at)
                                    <div class="relative pl-10 pb-6">
                                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-full border-4 border-white shadow-lg shadow-[#74c365]/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Booking Approved</p>
                                            <p class="text-sm text-gray-500">{{ $booking->approved_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($booking->completed_at)
                                    <div class="relative pl-10 pb-6">
                                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-full border-4 border-white shadow-lg shadow-[#74c365]/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Booking Completed</p>
                                            <p class="text-sm text-gray-500">{{ $booking->completed_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($booking->cancelled_at)
                                    <div class="relative pl-10">
                                        <div class="absolute left-2 w-5 h-5 bg-gray-500 rounded-full border-4 border-white shadow-lg shadow-gray-500/30"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800">Booking Cancelled</p>
                                            <p class="text-sm text-gray-500">{{ $booking->cancelled_at->format('F d, Y - g:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div>
                        <div class="card-mantis p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>
                            
                            @if($booking->status == 'pending')
                                <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Approve this work request?');">
                                    @csrf
                                    <button type="submit" class="w-full btn-mantis">
                                        <i class="fas fa-check mr-2"></i>Approve Work Request
                                    </button>
                                </form>
                                
                                <button onclick="showRejectModal()" class="w-full px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium mb-4">
                                    <i class="fas fa-times mr-2"></i>Reject Work Request
                                </button>
                            @endif

                            @if($booking->status == 'approved')
                                <form action="{{ route('admin.bookings.complete', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Mark this work request as completed?');">
                                    @csrf
                                    <button type="submit" class="w-full btn-mantis">
                                        <i class="fas fa-check-circle mr-2"></i>Mark as Completed
                                    </button>
                                </form>
                            @endif

                            @if(in_array($booking->status, ['pending', 'approved']))
                                <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="mb-4" onsubmit="return confirm('Cancel this work request?');">
                                    @csrf
                                    <button type="submit" class="w-full px-6 py-3 bg-gray-700 text-white rounded-xl hover:bg-gray-800 transition-all font-medium">
                                        <i class="fas fa-ban mr-2"></i>Cancel Booking
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Assign Employee -->
                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Assign Employee</h3>
                            <form action="{{ route('admin.bookings.assign', $booking) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
                                    <select name="employee_id" required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                        <option value="">Unassigned</option>
                                        @foreach(App\Models\User::role('employee')->get() as $employee)
                                            <option value="{{ $employee->id }}" {{ $booking->employee_id == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="w-full btn-mantis">
                                    <i class="fas fa-user-plus mr-2"></i>Assign Employee
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm" onclick="if(event.target === this) hideRejectModal()">
        <div class="card-mantis p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reject Work Request</h3>
                <button type="button" onclick="hideRejectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
                    <textarea name="remarks" rows="4" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 transition-all bg-gray-50"
                        placeholder="Please provide a reason for rejecting this booking"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideRejectModal()" class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-all font-medium text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium text-sm shadow-lg shadow-red-500/25">
                        <i class="fas fa-times mr-2"></i>Reject Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
        }

        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
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
            if (currentPath.includes('bookings')) {
                document.getElementById('bookings-dropdown').classList.add('open');
                document.getElementById('bookings-chevron').classList.add('rotate');
            } else if (currentPath.includes('inventory')) {
                document.getElementById('inventory-dropdown').classList.add('open');
                document.getElementById('inventory-chevron').classList.add('rotate');
            } else if (currentPath.includes('reports')) {
                document.getElementById('reports-dropdown').classList.add('open');
                document.getElementById('reports-chevron').classList.add('rotate');
            } else if (currentPath.includes('users')) {
                document.getElementById('users-dropdown').classList.add('open');
                document.getElementById('users-chevron').classList.add('rotate');
            }
        });
    </script>
</body>
</html>
