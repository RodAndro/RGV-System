<aside class="w-72 bg-gradient-to-b from-[#2563eb]/5 to-white dark:from-gray-900 dark:to-gray-900 border-r border-blue-100 dark:border-gray-700 h-full shadow-xl flex flex-col transition-colors duration-300">
    <div class="px-4 pt-4 pb-2 flex-1 overflow-y-auto">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/30 flex-shrink-0">
                <i class="fas fa-cogs text-white text-sm"></i>
            </div>
            <span class="font-bold text-base text-gray-800 dark:text-gray-100 ml-2 transition-colors">RGV Admin</span>
        </div>
        <nav aria-label="Admin Navigation" class="space-y-0.5">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active-mantis' : '' }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                <i class="fas fa-tachometer-alt mr-3 w-5" aria-hidden="true"></i>Dashboard
            </a>

            <div class="nav-section-title">Management</div>

            <div class="nav-section">
                <a href="{{ route('admin.bookings.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.bookings.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-calendar-alt mr-3 w-5"></i>Work Request
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.inventories.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.inventories.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-boxes mr-3 w-5"></i>Inventory
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.borrow-requests.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.borrow-requests.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-hand-holding mr-3 w-5"></i>Borrow Requests
                </a>
            </div>

            <div class="nav-section-title">Administration</div>

            <div class="nav-section">
                <a href="{{ route('admin.users.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-users mr-3 w-5"></i>Users
                </a>
            </div>

            <div class="nav-section">
                <a href="{{ route('admin.reports.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.reports.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-chart-bar mr-3 w-5"></i>Reports
                </a>
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
    <div class="flex-shrink-0 p-4 border-t border-gray-100 dark:border-gray-800">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all duration-300 w-full text-left font-medium" aria-label="Log out">
            <i class="fas fa-sign-out-alt mr-3 w-5" aria-hidden="true"></i>Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</aside>
