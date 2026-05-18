<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory Item - RGV Multi-Tech Services</title>
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
                        <h1 class="text-2xl font-bold text-gray-800">Edit Inventory Item</h1>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <div class="card-mantis p-8">
                    @if(session('success'))
                        <div class="bg-gradient-to-r from-[#f0f9ef] to-[#e0f3df] border border-[#74c365] text-[#468a3f] px-4 py-3 rounded-xl mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.inventories.update', $inventory) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Item Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Code *</label>
                                <input type="text" name="item_code" value="{{ old('item_code', $inventory->item_code) }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                                <input type="text" name="name" value="{{ old('name', $inventory->name) }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <select name="category_id" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $inventory->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Supplier -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                <select name="supplier_id"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="">Select supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $inventory->supplier_id == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                <input type="number" name="quantity" value="{{ old('quantity', $inventory->quantity) }}" required min="0"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Unit -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                                <select name="unit" id="unitSelect" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="pcs" {{ $inventory->unit == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="kg" {{ $inventory->unit == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                    <option value="lbs" {{ $inventory->unit == 'lbs' ? 'selected' : '' }}>Pounds (lbs)</option>
                                    <option value="meters" {{ $inventory->unit == 'meters' ? 'selected' : '' }}>Meters</option>
                                    <option value="liters" {{ $inventory->unit == 'liters' ? 'selected' : '' }}>Liters</option>
                                    <option value="boxes" {{ $inventory->unit == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                    <option value="sets" {{ $inventory->unit == 'sets' ? 'selected' : '' }}>Sets</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Unit is automatically suggested based on the selected category</p>
                            </div>

                            <!-- Unit Cost -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                                <input type="number" name="unit_cost" value="{{ old('unit_cost', $inventory->unit_cost) }}" min="0" step="0.01"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Low Stock Threshold -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Low Stock Threshold *</label>
                                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $inventory->low_stock_threshold) }}" required min="0"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="available" {{ $inventory->status == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="borrowed" {{ $inventory->status == 'borrowed' ? 'selected' : '' }}>Borrowed</option>
                                    <option value="maintenance" {{ $inventory->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="damaged" {{ $inventory->status == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                </select>
                            </div>

                            <!-- Condition -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition *</label>
                                <select name="condition" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="new" {{ $inventory->condition == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="good" {{ $inventory->condition == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ $inventory->condition == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ $inventory->condition == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                            </div>

                            <!-- Date Added -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Added *</label>
                                <input type="date" name="date_added" value="{{ old('date_added', $inventory->date_added->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <input type="text" name="location" value="{{ old('location', $inventory->location) }}"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="3"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">{{ old('description', $inventory->description) }}</textarea>
                            </div>

                            <!-- Image -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Image</label>
                                @if($inventory->image_path)
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-500 mb-2">Current Image:</p>
                                        <img src="{{ asset('storage/' . $inventory->image_path) }}" class="w-32 h-32 object-cover rounded-xl">
                                    </div>
                                @endif
                                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-[#74c365] transition bg-gray-50">
                                    <input type="file" name="image" accept=".jpg,.jpeg,.png"
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#f0f9ef] file:text-[#74c365] hover:file:bg-[#e0f3df]">
                                    <p class="text-xs text-gray-500 mt-2">Accepted formats: JPG, PNG (Max 5MB)</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('admin.inventories.index') }}" class="px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium">
                                Cancel
                            </a>
                            <button type="submit" class="btn-mantis">
                                <i class="fas fa-save mr-2"></i>Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Category to Units mapping
        const categoryUnits = @json($categoryUnits);
        
        // Unit display mappings for better readability
        const unitLabels = {
            'pcs': 'Pieces (pcs)',
            'kg': 'Kilograms (kg)',
            'lbs': 'Pounds (lbs)',
            'meters': 'Meters',
            'liters': 'Liters',
            'boxes': 'Boxes',
            'sets': 'Sets',
            'licenses': 'Licenses',
            'subscriptions': 'Subscriptions',
            'reams': 'Reams',
            'packets': 'Packets',
            'bottles': 'Bottles',
            'containers': 'Containers',
        };

        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const chevron = document.getElementById(dropdownId.replace('-dropdown', '-chevron'));
            
            dropdown.classList.toggle('open');
            chevron.classList.toggle('rotate');
        }

        function updateUnitOptions() {
            const categorySelect = document.querySelector('select[name="category_id"]');
            const unitSelect = document.getElementById('unitSelect');
            const selectedCategoryId = categorySelect.value;

            if (!selectedCategoryId || !categoryUnits[selectedCategoryId]) {
                // Show all units if no category selected
                const allUnits = ['pcs', 'kg', 'lbs', 'meters', 'liters', 'boxes', 'sets', 'licenses', 'subscriptions', 'reams', 'packets', 'bottles', 'containers'];
                populateUnitSelect(allUnits);
                return;
            }

            // Get units for selected category
            const unitsForCategory = categoryUnits[selectedCategoryId];
            populateUnitSelect(unitsForCategory);
        }

        function populateUnitSelect(units) {
            const unitSelect = document.getElementById('unitSelect');
            const currentValue = unitSelect.value;
            
            // Clear current options
            unitSelect.innerHTML = '';

            // Add new options
            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit;
                option.textContent = unitLabels[unit] || unit;
                option.selected = (currentValue === unit); // Restore selection if still available
                unitSelect.appendChild(option);
            });
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

            // Initialize unit options and set up category change listener
            const categorySelect = document.querySelector('select[name="category_id"]');
            if (categorySelect) {
                updateUnitOptions(); // Initialize with current category
                categorySelect.addEventListener('change', updateUnitOptions);
            }
        });
    </script>
</body>
</html>
