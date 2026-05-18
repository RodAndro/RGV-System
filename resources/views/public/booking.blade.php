<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - RGV Multi-Tech Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --mantis: #74c365;
            --mantis-dark: #468a3f;
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
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-[#f0f9ef]">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-xl shadow-lg sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center shadow-lg shadow-[#74c365]/30">
                                <i class="fas fa-cogs text-white text-xl"></i>
                            </div>
                            <span class="font-bold text-xl text-gray-800 ml-3">RGV Multi-Tech Services</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-[#74c365] font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#74c365] font-medium transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-[#74c365] font-medium transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Booking Form Section -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Book an Appointment</h1>
                <p class="text-gray-600">Fill out the form below to schedule your appointment with us</p>
            </div>

            <div class="card-mantis p-8">
                @if(session('success'))
                    <div class="bg-gradient-to-r from-[#f0f9ef] to-[#e0f3df] border border-[#74c365] text-[#468a3f] px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        {{ session('error') }}
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

                <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                placeholder="Enter your full name">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                placeholder="Enter your email">
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                placeholder="Enter your contact number">
                        </div>

                        <!-- Purpose Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Purpose Category *</label>
                            <select name="purpose_category" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                                <option value="">Select category</option>
                                <option value="equipment-repair" {{ old('purpose_category') == 'equipment-repair' ? 'selected' : '' }}>Equipment Repair</option>
                                <option value="tool-rental" {{ old('purpose_category') == 'tool-rental' ? 'selected' : '' }}>Tool Rental</option>
                                <option value="consultation" {{ old('purpose_category') == 'consultation' ? 'selected' : '' }}>Technical Consultation</option>
                                <option value="maintenance" {{ old('purpose_category') == 'maintenance' ? 'selected' : '' }}>Maintenance Service</option>
                                <option value="other" {{ old('purpose_category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="address" rows="2" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                placeholder="Enter your complete address">{{ old('address') }}</textarea>
                        </div>

                        <!-- Preferred Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Date *</label>
                            <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                        </div>

                        <!-- Preferred Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time *</label>
                            <input type="time" name="preferred_time" value="{{ old('preferred_time') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50">
                            <p class="text-xs text-gray-500 mt-1">Office Hours: 8:00 AM - 5:00 PM</p>
                        </div>

                        <!-- Reason -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason / Description *</label>
                            <textarea name="reason" rows="4" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                                placeholder="Please describe your request in detail">{{ old('reason') }}</textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Attachment (Optional)</label>
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-[#74c365] transition bg-gray-50">
                                <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#f0f9ef] file:text-[#74c365] hover:file:bg-[#e0f3df]">
                                <p class="text-xs text-gray-500 mt-2">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit"
                            class="w-full btn-mantis flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Booking Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- Track Booking -->
            <div class="mt-12 card-mantis p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Track Your Booking</h2>
                <form action="{{ route('booking.search') }}" method="POST">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-4">
                        <input type="text" name="reference_number" required
                            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#74c365] focus:border-[#74c365] transition-all bg-gray-50"
                            placeholder="Enter your reference number (e.g., BK-XXXXX)">
                        <button type="submit"
                            class="btn-mantis">
                            <i class="fas fa-search mr-2"></i>Track
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-[#468a3f] to-[#74c365] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
