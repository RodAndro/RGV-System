<aside class="w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 h-full shadow-xl flex flex-col overflow-y-auto transition-colors duration-300">
    <div class="p-6">
        <div class="flex items-center mb-8">
            <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center shadow-lg shadow-[#74c365]/30">
                <i class="fas fa-cogs text-white text-lg"></i>
            </div>
            <span class="font-bold text-lg text-gray-800 dark:text-gray-100 ml-3 transition-colors">RGV Admin</span>
        </div>
        <nav aria-label="Admin Navigation">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active-mantis' : '' }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                <i class="fas fa-tachometer-alt mr-3 w-5" aria-hidden="true"></i>Dashboard
            </a>

            <div class="nav-section-title">Management</div>

            <div class="nav-section">
                <div class="sidebar-link-mantis {{ request()->routeIs('admin.bookings.*') ? 'sidebar-link-active-mantis' : '' }}" onclick="toggleDropdown('bookings-dropdown')">
                    <div class="flex items-center justify-between w-full">
                        <span class="flex items-center"><i class="fas fa-calendar-alt mr-3 w-5"></i>Work Request</span>
                        <i class="fas fa-chevron-down chevron-icon text-xs" id="bookings-chevron"></i>
                    </div>
                </div>
                <div class="dropdown-menu" id="bookings-dropdown">
                    <a href="{{ route('admin.bookings.index') }}" class="dropdown-item">All Request</a>
                </div>
            </div>

            <div class="nav-section">
                <div class="sidebar-link-mantis {{ request()->routeIs('admin.inventories.*') ? 'sidebar-link-active-mantis' : '' }}" onclick="toggleDropdown('inventory-dropdown')">
                    <div class="flex items-center justify-between w-full">
                        <span class="flex items-center"><i class="fas fa-boxes mr-3 w-5"></i>Inventory</span>
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
                <a href="{{ route('admin.borrow-requests.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.borrow-requests.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-hand-holding mr-3 w-5"></i>Borrow Requests
                </a>
            </div>

            <div class="nav-section-title">Administration</div>

            <div class="nav-section">
                <div class="sidebar-link-mantis {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active-mantis' : '' }}" onclick="toggleDropdown('users-dropdown')">
                    <div class="flex items-center justify-between w-full">
                        <span class="flex items-center"><i class="fas fa-users mr-3 w-5"></i>Users</span>
                        <i class="fas fa-chevron-down chevron-icon text-xs" id="users-chevron"></i>
                    </div>
                </div>
                <div class="dropdown-menu" id="users-dropdown">
                    <a href="{{ route('admin.users.index') }}" class="dropdown-item">All Users</a>
                </div>
            </div>

            <div class="nav-section">
                <div class="sidebar-link-mantis {{ request()->routeIs('admin.reports.*') ? 'sidebar-link-active-mantis' : '' }}" onclick="toggleDropdown('reports-dropdown')">
                    <div class="flex items-center justify-between w-full">
                        <span class="flex items-center"><i class="fas fa-chart-bar mr-3 w-5"></i>Reports</span>
                        <i class="fas fa-chevron-down chevron-icon text-xs" id="reports-chevron"></i>
                    </div>
                </div>
                <div class="dropdown-menu" id="reports-dropdown">
                    <a href="{{ route('admin.reports.index') }}" class="dropdown-item">All Reports</a>
                    <a href="{{ route('admin.reports.bookings') }}" class="dropdown-item">Work Request Report</a>
                    <a href="{{ route('admin.reports.inventory') }}" class="dropdown-item">Inventory Report</a>
                    <a href="{{ route('admin.reports.borrow-requests') }}" class="dropdown-item">Borrow Requests</a>
                    <a href="{{ route('admin.reports.users') }}" class="dropdown-item">Users Report</a>
                </div>
            </div>

            <div class="nav-section-title">System</div>

            <div class="nav-section">
                <a href="{{ route('admin.backups.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.backups.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-database mr-3 w-5"></i>Backups
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.system-health') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.system-health') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-heartbeat mr-3 w-5"></i>System Health
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.settings.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-cog mr-3 w-5"></i>Settings
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.trash.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.trash.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-trash-restore mr-3 w-5"></i>Trash
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.audit.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.audit.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-history mr-3 w-5"></i>Audit Logs
                </a>
            </div>
        </nav>
    </div>
    <div class="mt-auto p-6 border-t border-gray-100 dark:border-gray-800">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all duration-300 w-full text-left font-medium" aria-label="Log out">
            <i class="fas fa-sign-out-alt mr-3 w-5" aria-hidden="true"></i>Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</aside>
