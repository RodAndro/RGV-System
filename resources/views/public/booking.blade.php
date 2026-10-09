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
        .booking-review-panel {
            width: min(100%, 42rem);
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
            border: 1px solid #dbe4ea;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 45, 65, 0.28);
        }
        .booking-review-field {
            min-width: 0;
            padding: 12px 14px;
            border-left: 3px solid #0f6f9d;
            border-radius: 8px;
            background: #f6fafc;
        }
        .booking-review-field dt {
            margin-bottom: 4px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }
        .booking-review-field dd {
            color: #172b3a;
            font-size: 14px;
            line-height: 1.5;
        }
        .booking-review-cancel {
            min-height: 44px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 18px;
            color: #334155;
            font-weight: 600;
            transition: background-color 150ms ease, border-color 150ms ease;
        }
        .booking-review-cancel:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }
        .booking-review-submit {
            min-height: 44px;
            border: 1px solid #0a5c83;
            border-radius: 8px;
            background: #0f6f9d;
            padding: 0 20px;
            color: #fff;
            font-weight: 600;
            transition: background-color 150ms ease, transform 150ms ease;
        }
        .booking-review-submit:hover {
            background: #0a5c83;
        }
        .booking-review-submit:active {
            transform: translateY(1px);
        }
        .booking-review-cancel:focus-visible,
        .booking-review-submit:focus-visible,
        .booking-review-close:focus-visible {
            outline: 3px solid #93c5fd;
            outline-offset: 2px;
        }
        @media (max-width: 640px) {
            .booking-review-panel {
                max-height: calc(100dvh - 1.5rem);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-[#eff6ff]">
    <!-- Navigation -->
    <nav class="bg-[#0f6f9d] shadow-lg sticky top-0 z-50 border-b border-[#0a5c83]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-10 h-10 rounded-xl object-cover shadow-lg shadow-[#2563eb]/20">
                            <span class="font-bold text-xl text-white ml-3">RGV Multi-Tech Services</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-white hover:text-[#d6f4ff] font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    @guest
                        <a href="{{ route('login') }}" class="text-white hover:text-[#d6f4ff] font-medium transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-[#d6f4ff] font-medium transition-colors">
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
                    <div class="bg-gradient-to-r from-[#eff6ff] to-[#dbeafe] border border-[#2563eb] text-[#1e40af] px-4 py-3 rounded-xl mb-6">
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

                <form id="booking-form" action="{{ route('booking.store') }}" method="POST" onsubmit="return openBookingReview(event)">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                                placeholder="Enter your full name">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                                placeholder="Enter your email">
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                                placeholder="Enter your contact number">
                        </div>

                        <!-- Purpose Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Purpose Category *</label>
                            <select name="purpose_category" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
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
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                                placeholder="Enter your complete address">{{ old('address') }}</textarea>
                        </div>

                        <!-- Preferred Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Date *</label>
                            <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                        </div>

                        <!-- Preferred Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time *</label>
                            <input type="time" name="preferred_time" value="{{ old('preferred_time') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50">
                            <p class="text-xs text-gray-500 mt-1">Office Hours: 8:00 AM - 5:00 PM</p>
                        </div>

                        <!-- Reason -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason / Description *</label>
                            <textarea name="reason" rows="4" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
                                placeholder="Please describe your request in detail">{{ old('reason') }}</textarea>
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
                            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all bg-gray-50"
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

    <div id="booking-review-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-5" role="dialog" aria-modal="true" aria-labelledby="booking-review-title" aria-describedby="booking-review-description" onclick="if (event.target === this) closeBookingReview()">
        <div class="booking-review-panel flex flex-col">
            <div class="flex items-start gap-4 bg-[#0f6f9d] px-5 py-5 text-white sm:px-7">
                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-lg bg-white/15 text-lg" aria-hidden="true">
                    <i class="fas fa-clipboard-check"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="booking-review-title" class="text-xl font-bold">Review Booking Request</h2>
                    <p id="booking-review-description" class="mt-1 text-sm text-sky-100">Please verify the information before submitting.</p>
                </div>
                <button type="button" class="booking-review-close flex h-9 w-9 flex-none items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white" aria-label="Close review" onclick="closeBookingReview()">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <dl class="grid flex-1 grid-cols-1 gap-3 overflow-y-auto px-5 py-5 sm:grid-cols-2 sm:px-7">
                <div class="booking-review-field"><dt>Full Name</dt><dd id="review-full-name" class="break-words"></dd></div>
                <div class="booking-review-field"><dt>Email Address</dt><dd id="review-email" class="break-words"></dd></div>
                <div class="booking-review-field"><dt>Contact Number</dt><dd id="review-contact-number" class="break-words"></dd></div>
                <div class="booking-review-field"><dt>Purpose Category</dt><dd id="review-purpose-category" class="break-words"></dd></div>
                <div class="booking-review-field sm:col-span-2"><dt>Address</dt><dd id="review-address" class="whitespace-pre-wrap break-words"></dd></div>
                <div class="booking-review-field"><dt>Preferred Date</dt><dd id="review-preferred-date" class="break-words"></dd></div>
                <div class="booking-review-field"><dt>Preferred Time</dt><dd id="review-preferred-time" class="break-words"></dd></div>
                <div class="booking-review-field sm:col-span-2"><dt>Reason / Description</dt><dd id="review-reason" class="whitespace-pre-wrap break-words"></dd></div>
            </dl>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-7">
                <button id="booking-review-cancel" type="button" onclick="closeBookingReview()" class="booking-review-cancel">Cancel</button>
                <button type="button" onclick="confirmBookingSubmission()" class="booking-review-submit">Submit Booking Request</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-[#0f6f9d] border-t border-[#0a5c83] text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
        </div>
    </footer>
    <script>
        let bookingReviewConfirmed = false;

        function formatPreferredTime(value) {
            const match = /^(\d{1,2}):(\d{2})$/.exec(value);
            if (!match) return value;

            const hours = Number(match[1]);
            const meridiem = hours >= 12 ? 'PM' : 'AM';
            return `${hours % 12 || 12}:${match[2]} ${meridiem}`;
        }

        function openBookingReview(event) {
            if (bookingReviewConfirmed) {
                bookingReviewConfirmed = false;
                return true;
            }

            event.preventDefault();

            const form = event.currentTarget;
            const values = {
                'review-full-name': form.elements.full_name.value,
                'review-email': form.elements.email.value,
                'review-contact-number': form.elements.contact_number.value,
                'review-purpose-category': form.elements.purpose_category.selectedOptions[0].textContent,
                'review-address': form.elements.address.value,
                'review-preferred-date': form.elements.preferred_date.value,
                'review-preferred-time': formatPreferredTime(form.elements.preferred_time.value),
                'review-reason': form.elements.reason.value,
            };

            Object.entries(values).forEach(([id, value]) => {
                document.getElementById(id).textContent = value.trim() || '—';
            });

            const modal = document.getElementById('booking-review-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('booking-review-cancel').focus();
            return false;
        }

        function closeBookingReview() {
            const modal = document.getElementById('booking-review-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.querySelector('#booking-form button[type="submit"]').focus();
        }

        function confirmBookingSubmission() {
            bookingReviewConfirmed = true;
            document.getElementById('booking-form').requestSubmit();
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !document.getElementById('booking-review-modal').classList.contains('hidden')) {
                closeBookingReview();
            }
        });
    </script>
</body>
</html>
