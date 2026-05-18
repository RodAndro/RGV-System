<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Item Details - RGV Multi-Tech Services</title>
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
                        <a href="{{ route('admin.inventories.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                        </a>
                        <h1 class="text-2xl font-bold text-gray-800">Inventory Item Details</h1>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Item Details -->
                    <div class="lg:col-span-2">
                        <div class="card-mantis p-6 mb-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Item Code</p>
                                    <p class="font-mono text-2xl font-bold text-[#74c365]">{{ $inventory->item_code }}</p>
                                </div>
                                @if($inventory->image_path)
                                    <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-32 h-32 object-cover rounded-xl">
                                @else
                                    <div class="w-32 h-32 bg-[#f0f9ef] rounded-xl flex items-center justify-center">
                                        <i class="fas fa-box text-[#74c365] text-4xl"></i>
                                    </div>
                                @endif
                            </div>

                            <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $inventory->name }}</h2>
                            
                            @if($inventory->description)
                                <p class="text-gray-600 mb-6">{{ $inventory->description }}</p>
                            @endif

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Category</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->category->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Supplier</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->supplier ? $inventory->supplier->name : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Quantity</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                                    @if($inventory->isLowStock())
                                        <span class="text-xs text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alert</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Unit Cost</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->unit_cost ? '₱' . number_format($inventory->unit_cost, 2) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Status</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($inventory->status == 'available') bg-green-100 text-green-800
                                        @elseif($inventory->status == 'borrowed') bg-yellow-100 text-yellow-800
                                        @elseif($inventory->status == 'maintenance') bg-orange-100 text-orange-800
                                        @elseif($inventory->status == 'damaged') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($inventory->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Condition</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($inventory->condition == 'new') bg-blue-100 text-blue-800
                                        @elseif($inventory->condition == 'good') bg-green-100 text-green-800
                                        @elseif($inventory->condition == 'fair') bg-yellow-100 text-yellow-800
                                        @elseif($inventory->condition == 'poor') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($inventory->condition) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Location</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->location ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Date Added</p>
                                    <p class="font-semibold text-gray-800">{{ $inventory->date_added->format('F d, Y') }}</p>
                                </div>
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <a href="{{ route('admin.inventories.edit', $inventory) }}" class="btn-mantis">
                                    <i class="fas fa-edit mr-2"></i>Edit Item
                                </a>
                                <form action="{{ route('admin.inventories.destroy', $inventory) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-6 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-all font-medium">
                                        <i class="fas fa-trash mr-2"></i>Delete Item
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Borrow History -->
                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Borrow History</h3>
                            @if($inventory->borrowItems->count() > 0)
                                <div class="space-y-3">
                                    @foreach($inventory->borrowItems as $borrowItem)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                            <div>
                                                <p class="font-semibold text-gray-800">{{ $borrowItem->borrowRequest->request_number }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $borrowItem->borrowRequest->employee?->name ?? 'N/A' }} • 
                                                    {{ $borrowItem->borrowRequest->borrow_date->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <span class="badge-mantis-{{ $borrowItem->is_returned ? 'success' : 'warning' }}">
                                                {{ $borrowItem->is_returned ? 'Returned' : 'Borrowed' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center text-gray-500 py-4">No borrow history</p>
                            @endif
                        </div>
                    </div>

                    <!-- Info Panel -->
                    <div>
                        <div class="card-mantis p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Stock Information</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-[#f0f9ef] rounded-xl border border-[#74c365]/20">
                                    <span class="text-gray-700">Current Stock</span>
                                    <span class="font-bold text-[#74c365]">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-xl border border-yellow-200">
                                    <span class="text-gray-700">Low Stock Threshold</span>
                                    <span class="font-bold text-yellow-600">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                                </div>
                                @if($inventory->isLowStock())
                                    <div class="p-3 bg-red-50 rounded-xl border border-red-200">
                                        <p class="text-red-800 font-semibold text-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alert
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-mantis p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.inventories.edit', $inventory) }}" class="block text-center btn-mantis">
                                    <i class="fas fa-edit mr-2"></i>Edit Item
                                </a>
                                <a href="{{ route('admin.inventories.index') }}" class="block text-center px-4 py-3 bg-gray-700 text-white rounded-xl hover:bg-gray-800 transition-all font-medium">
                                    <i class="fas fa-list mr-2"></i>View All Items
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
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
