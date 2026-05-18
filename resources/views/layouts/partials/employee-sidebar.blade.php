<aside class="w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 h-full shadow-xl flex flex-col overflow-y-auto transition-colors duration-300">
    <div class="p-6">
        <div class="flex items-center mb-8">
            <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center shadow-lg shadow-[#74c365]/30">
                <i class="fas fa-cogs text-white text-lg"></i>
            </div>
            <span class="font-bold text-lg text-gray-800 dark:text-gray-100 ml-3 transition-colors">RGV Employee</span>
        </div>
        <nav>
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
    <div class="mt-auto p-6 border-t border-gray-100 dark:border-gray-800">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all duration-300 w-full text-left font-medium">
            <i class="fas fa-sign-out-alt mr-3 w-5"></i>Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</aside>
