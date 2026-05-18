<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory Item - RGV Multi-Tech Services</title>
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
            border-bottom: 2px solid #f0f9ef;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #64748b;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
            color: #74c365;
        }
        .badge-mantis-success {
            background: linear-gradient(135deg, #f0f9ef 0%, #e0f3df 100%);
            color: #468a3f;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-mantis-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-mantis-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-mantis-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-mantis {
            background: linear-gradient(135deg, #74c365 0%, #5dad4f 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(116, 195, 101, 0.3);
        }
        .btn-mantis:hover {
            background: linear-gradient(135deg, #5dad4f 0%, #468a3f 100%);
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
                        <a href="{{ route('admin.bookings.index') }}" class="sidebar-link-mantis">
                            <i class="fas fa-calendar-alt mr-3 w-5"></i>Bookings
                        </a>
                    </div>

                    <div class="nav-section">
                        <div class="sidebar-link-active-mantis">
                            <i class="fas fa-boxes mr-3 w-5"></i>Inventory
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
                        <h1 class="text-2xl font-bold text-gray-800">Add New Inventory Item</h1>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <!-- Form Section -->
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-plus-circle"></i>Item Information</h2>
                </div>
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

                    <form action="{{ route('admin.inventories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Item Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Code *</label>
                                <input type="text" name="item_code" value="{{ old('item_code') }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="e.g., INV-001">
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Enter item name">
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <select name="category_id" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                <input type="number" name="quantity" value="{{ old('quantity', 0) }}" required min="0"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Enter quantity">
                            </div>

                            <!-- Unit -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                                <select name="unit" id="unitSelect" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="">Select unit</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="kg">Kilograms (kg)</option>
                                    <option value="lbs">Pounds (lbs)</option>
                                    <option value="meters">Meters</option>
                                    <option value="liters">Liters</option>
                                    <option value="boxes">Boxes</option>
                                    <option value="sets">Sets</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Unit is automatically suggested based on the selected category</p>
                            </div>

                            <!-- Unit Cost -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                                <input type="number" name="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Enter unit cost">
                            </div>

                            <!-- Low Stock Threshold -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Low Stock Threshold *</label>
                                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" required min="0"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Alert when quantity below this">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="available">Available</option>
                                    <option value="borrowed">Borrowed</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>

                            <!-- Condition -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition *</label>
                                <select name="condition" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                    <option value="new">New</option>
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                </select>
                            </div>

                            <!-- Date Added -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date Added *</label>
                                <input type="date" name="date_added" value="{{ old('date_added', date('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <input type="text" name="location" value="{{ old('location') }}"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Storage location">
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea name="description" rows="4"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                    placeholder="Enter item description">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <!-- Additional Information -->
                        <div class="section-header">
                            <h2 class="section-title"><i class="fas fa-image"></i>Item Image</h2>
                        </div>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-[#74c365] transition bg-gray-50 mb-6">
                            <input type="file" name="image" accept=".jpg,.jpeg,.png"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#f0f9ef] file:text-[#74c365] hover:file:bg-[#e0f3df]">
                            <p class="text-xs text-gray-500 mt-2">Accepted formats: JPG, PNG (Max 5MB)</p>
                        </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('admin.inventories.index') }}" class="px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium">
                                Cancel
                            </a>
                            <button type="submit" class="btn-mantis">
                                <i class="fas fa-save mr-2"></i>Save Item
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
            
            // Clear current options except the first one
            unitSelect.innerHTML = '<option value="">Select unit</option>';

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
