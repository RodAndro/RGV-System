<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Booking - RGV Multi-Tech Services</title>
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
            transition: all 0.3s ease;
        }
        .card-mantis:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 32px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }
        .status-approved {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
        }
        .status-rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
        .status-completed {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
        }
        .status-cancelled {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-[#eff6ff]">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-xl shadow-lg sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-xl flex items-center justify-center shadow-lg shadow-[#2563eb]/30">
                                <i class="fas fa-cogs text-white text-xl"></i>
                            </div>
                            <span class="font-bold text-xl text-gray-800 ml-3">RGV Multi-Tech Services</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-[#2563eb] font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="{{ route('booking.form') }}" class="text-gray-700 hover:text-[#2563eb] font-medium transition-colors">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Tracking Section -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Track Your Booking</h1>
                <p class="text-gray-600">Reference Number: {{ $booking->reference_number }}</p>
            </div>

            <!-- Booking Details Card -->
            <div class="card-mantis p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Booking Details</h2>
                    <span class="badge-mantis-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'approved' ? 'success' : ($booking->status == 'rejected' ? 'danger' : ($booking->status == 'completed' ? 'success' : 'warning'))) }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Full Name</p>
                        <p class="font-semibold text-gray-800">{{ $booking->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Email</p>
                        <p class="font-semibold text-gray-800">{{ $booking->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Contact Number</p>
                        <p class="font-semibold text-gray-800">{{ $booking->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Purpose Category</p>
                        <p class="font-semibold text-gray-800">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Preferred Date</p>
                        <p class="font-semibold text-gray-800">{{ $booking->preferred_date->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Preferred Time</p>
                        <p class="font-semibold text-gray-800">{{ $booking->preferred_time }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Address</p>
                        <p class="font-semibold text-gray-800">{{ $booking->address }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Reason</p>
                        <p class="font-semibold text-gray-800">{{ $booking->reason }}</p>
                    </div>
                </div>

                @if($booking->remarks)
                    <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500 mb-1">Remarks</p>
                        <p class="text-gray-800">{{ $booking->remarks }}</p>
                    </div>
                @endif

                @if($booking->attachment_path)
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 mb-2">Attachment</p>
                        <a href="{{ asset('storage/' . $booking->attachment_path) }}" target="blank"
                            class="inline-flex items-center text-[#2563eb] hover:text-[#1d4ed8] font-medium">
                            <i class="fas fa-file-alt mr-2"></i>View Attachment
                        </a>
                    </div>
                @endif
            </div>

            <!-- Status Timeline -->
            <div class="card-mantis p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Booking Timeline</h2>
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    
                    <div class="relative pl-10 pb-8">
                        <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                        <div>
                            <p class="font-semibold text-gray-800">Booking Submitted</p>
                            <p class="text-sm text-gray-500">{{ $booking->created_at->format('F d, Y - g:i A') }}</p>
                        </div>
                    </div>

                    @if($booking->approved_at)
                        <div class="relative pl-10 pb-8">
                            <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                            <div>
                                <p class="font-semibold text-gray-800">Booking Approved</p>
                                <p class="text-sm text-gray-500">{{ $booking->approved_at->format('F d, Y - g:i A') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($booking->rejected_at)
                        <div class="relative pl-10 pb-8">
                            <div class="absolute left-2 w-5 h-5 bg-red-500 rounded-full border-4 border-white shadow-lg shadow-red-500/30"></div>
                            <div>
                                <p class="font-semibold text-gray-800">Booking Rejected</p>
                                <p class="text-sm text-gray-500">{{ $booking->rejected_at->format('F d, Y - g:i A') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($booking->completed_at)
                        <div class="relative pl-10 pb-8">
                            <div class="absolute left-2 w-5 h-5 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full border-4 border-white shadow-lg shadow-[#2563eb]/30"></div>
                            <div>
                                <p class="font-semibold text-gray-800">Booking Completed</p>
                                <p class="text-sm text-gray-500">{{ $booking->completed_at->format('F d, Y - g:i A') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($booking->cancelled_at)
                        <div class="relative pl-10">
                            <div class="absolute left-2 w-5 h-5 bg-gray-500 rounded-full border-4 border-white shadow-lg shadow-gray-500/30"></div>
                            <div>
                                <p class="font-semibold text-gray-800">Booking Cancelled</p>
                                <p class="text-sm text-gray-500">{{ $booking->cancelled_at->format('F d, Y - g:i A') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($booking->status == 'approved' || $booking->status == 'completed')
                <div class="bg-gradient-to-br from-[#eff6ff] to-[#dbeafe] border border-[#2563eb] rounded-2xl p-8 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#2563eb] to-[#1d4ed8] rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-[#2563eb]/30">
                        <i class="fas fa-check-circle text-white text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Your Booking is Confirmed!</h2>
                    <p class="text-gray-600 mb-4">Please arrive on time for your scheduled appointment.</p>
                    <a href="{{ route('booking.form') }}" class="btn-mantis">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Another Appointment
                    </a>
                </div>
            @elseif($booking->status == 'rejected')
                <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
                    <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-500/30">
                        <i class="fas fa-times-circle text-white text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Booking Rejected</h2>
                    <p class="text-gray-600 mb-4">Please contact us for more information or submit a new booking request.</p>
                    <a href="{{ route('booking.form') }}" class="btn-mantis">
                        <i class="fas fa-calendar-plus mr-2"></i>Submit New Booking
                    </a>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-8 text-center">
                    <div class="w-20 h-20 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-yellow-500/30">
                        <i class="fas fa-clock text-white text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Booking Pending Review</h2>
                    <p class="text-gray-600 mb-4">Your booking is currently being reviewed. We will notify you once it's approved.</p>
                    <a href="{{ route('home') }}" class="btn-mantis">
                        <i class="fas fa-home mr-2"></i>Back to Home
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-[#1e40af] to-[#2563eb] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
