<aside class="w-72 bg-gradient-to-b from-[#2563eb]/5 to-white dark:from-gray-900 dark:to-gray-900 border-r border-blue-100 dark:border-gray-700 h-full shadow-xl flex flex-col transition-colors duration-300">
    <div class="px-4 pt-4 pb-2 flex-1 overflow-y-auto">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-cogs text-white text-sm"></i>
            </div>
            <span class="font-bold text-base text-gray-800 dark:text-gray-100 ml-2 transition-colors">RGV Employee</span>
        </div>
        <nav class="space-y-0.5">
            <a href="{{ route('employee.dashboard') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.dashboard') ? 'sidebar-link-active-mantis' : '' }}">
                <i class="fas fa-tachometer-alt mr-3 w-5"></i>Dashboard
            </a>

            <div class="nav-section-title">Work</div>

            <div class="nav-section">
                <a href="{{ route('employee.bookings.index') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.bookings.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-clipboard-list mr-3 w-5"></i>Assigned Work
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('employee.inventories.index') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.inventories.index') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-boxes mr-3 w-5"></i>Available Inventory
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('employee.inventories.low-stock') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.inventories.low-stock') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-exclamation-triangle mr-3 w-5 text-red-500"></i>Low Stock Alerts
                </a>
            </div>

            <div class="nav-section-title">Borrow Management</div>

            <div class="nav-section">
                <a href="{{ route('employee.borrow-requests.index') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.borrow-requests.index') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-hand-holding mr-3 w-5"></i>My Requests
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('employee.borrow-requests.create') }}" class="sidebar-link-mantis {{ request()->routeIs('employee.borrow-requests.create') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-plus-circle mr-3 w-5"></i>New Request
                </a>
            </div>
        </nav>
    </div>
    <div class="flex-shrink-0 p-4 border-t border-gray-100 dark:border-gray-800">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all duration-300 w-full text-left font-medium">
            <i class="fas fa-sign-out-alt mr-3 w-5"></i>Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</aside>
