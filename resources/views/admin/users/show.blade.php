<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
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
<body class="bg-gray-50" x-data>
    <div class="flex min-h-screen">
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

        <main class="flex-1 ml-72 p-8">
            <div class="mb-8">
                <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-[#74c365] transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
            </div>

            <div class="card-mantis p-6">
                <div class="flex justify-between items-start mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all font-medium">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-6 py-2 rounded-xl font-medium
                                    @if($user->is_active) bg-red-500 text-white hover:bg-red-600 transition-all
                                    @else btn-mantis
                                    @endif">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @if(!$user->is_active)
                            <button @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" type="button" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all font-medium">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                            @else
                            <span class="px-4 py-2 bg-gray-300 text-gray-500 rounded-xl font-medium cursor-not-allowed" title="Deactivate the user before deleting">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </span>
                            @endif
                            @if(!$user->mfa_enabled)
                            <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" onsubmit="return confirm('Impersonate {{ $user->name }}? You can return to your account at any time.');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all font-medium">
                                    <i class="fas fa-user-secret mr-2"></i>Impersonate
                                </button>
                            </form>
                            @else
                            <span class="px-4 py-2 bg-gray-300 text-gray-500 rounded-xl font-medium cursor-not-allowed" title="Cannot impersonate users with two-factor authentication enabled">
                                <i class="fas fa-user-secret mr-2"></i>Impersonate
                            </span>
                            @endif
                            <form action="{{ route('admin.users.force-logout', $user) }}" method="POST" onsubmit="return confirm('Force logout {{ $user->name }} from all devices?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all font-medium">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Force Logout
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Email</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Phone</h3>
                        <p class="text-gray-600">{{ $user->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Role</h3>
                        <p class="text-gray-600">{{ $user->roles->pluck('name')->first() ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Status</h3>
                        <span class="badge-mantis-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-span-2">
                        <h3 class="font-semibold text-gray-700 mb-2">Address</h3>
                        <p class="text-gray-600">{{ $user->address ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-2">Activity</h3>
                    <p class="text-gray-600">Last Login: {{ $user->last_login_at ? $user->last_login_at->format('F d, Y - g:i A') : 'Never' }}</p>
                    <p class="text-gray-600">Created: {{ $user->created_at->format('F d, Y - g:i A') }}</p>
                </div>

                @if(isset($loginHistory) && $loginHistory->count() > 0)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-3">Recent Login History</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($loginHistory as $entry)
                            <div class="flex items-center justify-between text-sm p-2 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="text-gray-600">{{ $entry->logged_in_at?->format('M d, Y g:i A') ?? 'N/A' }}</span>
                                    @if($entry->logged_out_at)
                                        <span class="text-gray-400"> → {{ $entry->logged_out_at->format('g:i A') }}</span>
                                    @else
                                        <span class="text-green-600 text-xs"> (active)</span>
                                    @endif
                                    @if($entry->is_impersonation)
                                        <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">impersonation</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 truncate ml-2 max-w-[180px]" title="{{ $entry->ip_address }}">{{ $entry->ip_address }}</span>
                            </div>
                        @endforeach
                    </div>
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

    <x-confirm-delete-modal name="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" itemName="user '{{ $user->name }}'" />
</body>
</html>
