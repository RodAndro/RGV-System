<aside class="w-72 bg-[#0f6f9d] border-r border-[#0a5c83] h-full shadow-xl flex flex-col transition-colors duration-300">
    <div class="px-4 pt-4 pb-2 flex-1 overflow-y-auto">
        <div class="flex items-center mb-4">
            <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-10 h-10 rounded-lg object-cover shadow-lg shadow-blue-500/20 flex-shrink-0">
            <span class="font-bold text-base text-white ml-2 transition-colors">RGV Multi-Tech Services</span>
        </div>
        <nav aria-label="Admin Navigation" class="space-y-0.5" x-data="{
            hasNewWorkRequest: false,
            hasNewBorrowRequest: false,
            init() {
                window.addEventListener('rgv-notifications-updated', (event) => {
                    const updates = event.detail.request_updates || {};
                    this.hasNewWorkRequest = Boolean(updates.work_request);
                    this.hasNewBorrowRequest = Boolean(updates.borrow_request);
                });
            }
        }">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active-mantis' : '' }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                <i class="fas fa-tachometer-alt mr-3 w-5" aria-hidden="true"></i>Dashboard
            </a>

            <div class="nav-section-title" style="color: #ffffff !important;">Management</div>

            <div class="nav-section">
                <a href="{{ route('admin.bookings.index') }}" class="sidebar-link-mantis {{ request()->routeIs('admin.bookings.*') ? 'sidebar-link-active-mantis' : '' }}">
                    <i class="fas fa-calendar-alt mr-3 w-5"></i>Work Request
                    <span x-cloak x-show="hasNewWorkRequest" class="ml-auto h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white/30" role="status" aria-label="Unseen work request updates"></span>
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
                    <span x-cloak x-show="hasNewBorrowRequest" class="ml-auto h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white/30" role="status" aria-label="Unseen borrow request updates"></span>
                </a>
            </div>

            <div class="nav-section-title" style="color: #ffffff !important;">Administration</div>

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

            <div class="nav-section-title" style="color: #ffffff !important;">System</div>

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
        <a href="#" onclick="event.preventDefault(); if (confirm('Are you sure you want to log out?')) document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all duration-300 w-full text-left font-medium" aria-label="Log out">
            <i class="fas fa-sign-out-alt mr-3 w-5 text-white" aria-hidden="true"></i>Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
</aside>
