<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alerts - RGV Multi-Tech Services</title>
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
                    <h1 class="text-2xl font-bold text-gray-800">Low Stock Alerts</h1>
                    <a href="{{ route('admin.inventories.index') }}" class="btn-mantis">
                        <i class="fas fa-boxes mr-2"></i>View All Inventory
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <!-- Alert Banner -->
                <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-2xl p-6 mb-8 shadow-lg">
                    <div class="flex items-center">
                        <div class="w-14 h-14 bg-red-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/30 mr-5">
                            <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Low Stock Alert</h3>
                            <p class="text-red-600 font-medium">{{ $inventories->count() }} item(s) are below their low stock threshold</p>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Items -->
                @if($inventories->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($inventories as $inventory)
                            <div class="card-mantis border-l-4 border-red-500 hover:shadow-2xl hover:shadow-red-500/10 transition-all duration-300 transform hover:-translate-y-1">
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center">
                                            @if($inventory->image_path)
                                                <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-16 h-16 object-cover rounded-xl mr-4">
                                            @else
                                                <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-orange-100 rounded-xl flex items-center justify-center mr-4">
                                                    <i class="fas fa-box text-red-600 text-xl"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-mono text-sm text-[#74c365]">{{ $inventory->item_code }}</p>
                                                <p class="font-bold text-gray-800">{{ $inventory->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $inventory->category->name }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="flex justify-between items-center mb-2 p-3 bg-red-50 rounded-lg">
                                            <span class="text-gray-600 font-medium">Current Stock</span>
                                            <span class="font-bold text-red-600">{{ $inventory->quantity }} {{ $inventory->unit }}</span>
                                        </div>
                                        <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                            <span class="text-gray-600 font-medium">Threshold</span>
                                            <span class="font-bold text-yellow-600">{{ $inventory->low_stock_threshold }} {{ $inventory->unit }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 p-3 bg-gray-50 rounded-lg">
                                        <span>Need to order:</span>
                                        <span class="font-bold text-red-600">{{ $inventory->low_stock_threshold - $inventory->quantity + 5 }} {{ $inventory->unit }}</span>
                                    </div>

                                    <div class="flex space-x-3">
                                        <a href="{{ route('admin.inventories.show', $inventory) }}" class="flex-1 text-center px-4 py-3 bg-gradient-to-r from-[#74c365] to-[#5dad4f] text-white rounded-xl hover:from-[#5dad4f] hover:to-[#468a3f] transition-all duration-300 shadow-lg shadow-[#74c365]/30">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                        <a href="{{ route('admin.inventories.edit', $inventory) }}" class="flex-1 text-center px-4 py-3 bg-gradient-to-r from-gray-700 to-gray-800 text-white rounded-xl hover:from-gray-800 hover:to-gray-900 transition-all duration-300 shadow-lg">
                                            <i class="fas fa-edit mr-1"></i>Restock
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card-mantis p-16 text-center">
                        <div class="w-24 h-24 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-[#74c365]/30">
                            <i class="fas fa-check-circle text-white text-5xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">All Stocks Are Healthy</h3>
                        <p class="text-gray-600 mb-6">No items are currently below their low stock threshold.</p>
                        <a href="{{ route('admin.inventories.index') }}" class="btn-mantis">
                            <i class="fas fa-boxes mr-2"></i>View Inventory
                        </a>
                    </div>
                @endif
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
