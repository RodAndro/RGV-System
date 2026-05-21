<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - RGV Employee</title>
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .card-mantis:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 24px 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eff6ff;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
            color: #2563eb;
        }
        .notification-item {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item.unread {
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
            border-left: 4px solid #2563eb;
        }
        .notification-item:hover {
            background: #f8fafc;
        }
        .btn-mantis {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(116, 195, 101, 0.3);
        }
        .btn-mantis:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(116, 195, 101, 0.4);
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
                        <a href="{{ route('employee.dashboard') }}" class="text-gray-600 hover:text-[#2563eb] transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                        <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($unreadCount > 0)
                            <form action="{{ route('employee.notifications.mark-all-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-[#2563eb] hover:text-[#1d4ed8] font-semibold transition-colors">
                                    <i class="fas fa-check-double mr-2"></i>Mark All Read
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('employee.notifications.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear all notifications?');">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold transition-colors">
                                <i class="fas fa-trash mr-2"></i>Clear All
                            </button>
                        </form>
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

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Notifications</p>
                                <p class="text-3xl font-bold text-gray-800">{{ $notifications->total() }}</p>
                            </div>
                            <div class="bg-[#eff6ff] w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-bell text-[#2563eb] text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-mantis p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Unread</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ $unreadCount }}</p>
                            </div>
                            <div class="bg-yellow-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                <i class="fas fa-envelope text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Notifications List Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-list"></i>All Notifications</h2>
                    <div class="text-sm text-gray-500">Showing {{ $notifications->total() }} notifications</div>
                </div>

                <div class="card-mantis overflow-hidden">
                    @forelse($notifications as $notification)
                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start flex-1">
                                    <div class="{{ $notification->read_at ? 'bg-gray-100' : 'bg-[#eff6ff]' }} w-10 h-10 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                        <i class="fas {{ $notification->read_at ? 'fa-envelope-open text-gray-500' : 'fa-envelope text-[#2563eb]' }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800 mb-1">{{ $notification->title ?? 'Notification' }}</p>
                                        <p class="text-gray-600 text-sm">{{ $notification->message ?? '' }}</p>
                                        <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                        @if($notification->link)
                                            <a href="{{ route('employee.notifications.open', $notification->id) }}" class="inline-flex items-center mt-3 text-[#2563eb] text-sm font-semibold hover:underline">
                                                <i class="fas fa-external-link-alt mr-1"></i>View Details
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <form action="{{ route('employee.notifications.mark-read', $notification->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-9 h-9 bg-[#eff6ff] text-[#2563eb] rounded-lg flex items-center justify-center hover:bg-[#dbeafe] transition-colors" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('employee.notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-9 h-9 bg-red-100 text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-bell-slash text-gray-400 text-2xl"></i>
                            </div>
                            <p class="font-medium">No notifications yet</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                @if($notifications->hasPages())
                    <div class="mt-6">
                        {{ $notifications->links() }}
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
    </script>
</body>
</html>
