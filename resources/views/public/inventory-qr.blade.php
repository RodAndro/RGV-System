<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $inventory->name }} - RGV Multi-Tech Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
        }
        .card-mantis {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .badge-mantis-success {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
        }
        .badge-mantis-warning {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }
        .badge-mantis-danger {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-[#eff6ff]">
    <nav class="bg-[#0f6f9d] shadow-lg sticky top-0 z-50 border-b border-[#0a5c83]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-10 h-10 rounded-xl object-cover shadow-lg shadow-[#2563eb]/20">
                        <span class="font-bold text-xl text-white ml-3">RGV Multi-Tech Services</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-white hover:text-[#d6f4ff] font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="{{ route('booking.form') }}" class="text-white hover:text-[#d6f4ff] font-medium transition-colors">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-10">
        <div class="card-mantis p-6 md:p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Item Code</p>
                    <p class="font-mono text-2xl font-bold text-[#2563eb]">{{ $inventory->item_code }}</p>
                </div>
                <span class="badge-mantis-{{ $inventory->status == 'available' ? 'success' : ($inventory->status == 'borrowed' ? 'warning' : ($inventory->status == 'maintenance' ? 'warning' : 'danger')) }}">
                    {{ ucfirst($inventory->status) }}
                </span>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $inventory->name }}</h1>

            @if($inventory->description)
                <p class="text-gray-600 mb-6">{{ $inventory->description }}</p>
            @endif

            <div class="grid md:grid-cols-2 gap-6 mt-6">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500 mb-1">Category</p>
                    <p class="font-semibold text-gray-800">{{ $inventory->category->name }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500 mb-1">Quantity Available</p>
                    <p class="font-semibold text-gray-800">{{ $inventory->quantity }} {{ $inventory->unit }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500 mb-1">Condition</p>
                    <p class="font-semibold text-gray-800">{{ ucfirst($inventory->condition) }}</p>
                </div>
                @if($inventory->location)
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500 mb-1">Location</p>
                        <p class="font-semibold text-gray-800">{{ $inventory->location }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">Scanned from item QR code • Verified {{ now()->format('M d, Y') }}</p>
            </div>
        </div>
    </main>
</body>
</html>
