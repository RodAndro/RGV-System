<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book Appointment - RGV Multi-Tech Services</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --primary: #0f6f9d;
            --primary-dark: #0a5c83;
            --primary-blue: #2563eb;
        }
        html { scroll-behavior: smooth; }
        body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
        .card-mantis {
            background: white;
            border-radius: 18px;
            box-shadow: 0 10px 28px -18px rgba(15, 45, 65, 0.32);
            border: 1px solid #dce6ec;
            transition: box-shadow 0.25s ease;
        }
        .card-mantis:hover {
            box-shadow: 0 16px 34px -20px rgba(15, 45, 65, 0.42);
        }
        .btn-mantis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            background: #0f6f9d;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(15, 111, 157, 0.25);
            border: none;
            cursor: pointer;
        }
        .btn-mantis:hover:not(:disabled) {
            background: #0a5c83;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(10, 92, 131, 0.32);
        }
        .btn-mantis:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .btn-mantis-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            background: white;
            color: #0f6f9d;
            border: 1.5px solid #0f6f9d;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-mantis-outline:hover {
            background: #eff6ff;
            color: #0a5c83;
            border-color: #0a5c83;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .status-approved {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .status-cancelled {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .booking-review-panel {
            width: min(100%, 42rem);
            max-height: calc(100dvh - 2rem);
            overflow: hidden;
            border: 1px solid #dbe4ea;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 16px 36px rgba(15, 45, 65, 0.22);
        }
        .booking-review-table th,
        .booking-review-table td {
            padding-bottom: 0.45rem;
            padding-top: 0.45rem;
        }
        .booking-review-close:focus-visible {
            outline: 3px solid #93c5fd;
            outline-offset: 2px;
        }
        .booking-hero-photo img { transition: transform .6s ease; }
        .booking-hero-photo:hover img { transform: scale(1.03); }
        .fade-in {
            animation: fadeIn 0.25s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#f5f8fb] text-slate-800 antialiased">
    <nav class="sticky top-0 z-50 flex-shrink-0 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('images/RGV-Logo.png') }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-slate-200">
                <span class="truncate text-sm font-extrabold leading-tight text-slate-900 sm:text-base">RGV <span class="font-semibold text-slate-600">Multi-Tech Services</span></span>
            </a>
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 transition hover:text-[#0f6f9d]"><i class="fas fa-arrow-left mr-1.5 text-xs"></i><span>Home</span></a>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col justify-start gap-6 px-4 py-6 sm:px-6 sm:py-9 lg:px-8">

        <section class="grid overflow-hidden rounded-[1.4rem] border border-[#dce7ed] bg-white shadow-[0_16px_42px_-30px_rgba(15,45,65,.45)] lg:grid-cols-[1fr_.9fr]">
            <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10">
                <p class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-[#c9e3ef] bg-[#f2f9fc] px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-[.15em] text-[#0f6f9d]">
                    <span class="h-2 w-2 rounded-full bg-[#0f6f9d]"></span> RGV Multi-Tech Services
                </p>
                <h1 class="max-w-xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl lg:text-[2.65rem]">Book a service appointment with our team.</h1>
                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">Tell us what you need, choose a preferred visit time, and we’ll review your request. You can return here any time to track its status.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @if($trackedBooking)
                        <a href="#track-section" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0f6f9d] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0a5c83]"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Track a booking</a>
                    @else
                        <a href="#booking-form-view" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0f6f9d] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0a5c83]"><i class="fas fa-calendar-check" aria-hidden="true"></i> Start a request</a>
                    @endif
                    <span class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-xs font-semibold text-slate-600"><i class="far fa-clock text-[#0f6f9d]" aria-hidden="true"></i> Office hours: 8:00 AM–5:00 PM</span>
                </div>
                <div class="mt-7 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-3">
                    <div><span class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">01</span><span class="mt-1 block text-sm font-bold text-slate-800">Share the details</span></div>
                    <div><span class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">02</span><span class="mt-1 block text-sm font-bold text-slate-800">We review your request</span></div>
                    <div><span class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">03</span><span class="mt-1 block text-sm font-bold text-slate-800">Track with your reference</span></div>
                </div>
            </div>
            <div class="booking-hero-photo relative min-h-[230px] overflow-hidden bg-slate-200 sm:min-h-[300px] lg:min-h-[420px]">
                <img src="{{ asset('project-highlights/12.jpg') }}" alt="Rooftop mechanical installation project" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/5 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5 text-white sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-sky-100">Technical support for your site</p>
                    <p class="mt-2 text-xl font-extrabold sm:text-2xl">Mechanical · Aircon · Kitchen Equipment</p>
                </div>
            </div>
        </section>

        <!-- 1. Track Your Booking Section (Positioned at the Top) -->
        <div id="track-section" class="card-mantis flex-shrink-0 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-[#0f6f9d]">
                        <i class="fas fa-search-location text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Already requested a visit?</h2>
                        <p class="text-xs text-gray-500">Enter your reference to view the latest appointment status and details.</p>
                    </div>
                </div>
            </div>

            <form id="track-form" action="{{ route('booking.search') }}" method="POST" onsubmit="return handleTrackBooking(event)">
                @csrf
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <input type="text" id="track_reference_number" name="reference_number" required
                            value="{{ request('reference', old('reference_number', $trackedBooking->reference_number ?? '')) }}"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 uppercase placeholder:normal-case font-mono font-medium text-gray-800 tracking-wide text-sm"
                            placeholder="Enter your reference number (e.g., BK-XXXXX)"
                            autocomplete="off"
                            oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <button type="submit" id="track-submit-btn" class="btn-mantis px-6 py-2.5 whitespace-nowrap">
                        <span id="track-btn-spinner" class="hidden mr-2">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        <span id="track-btn-icon" class="mr-2">
                            <i class="fas fa-search"></i>
                        </span>
                        <span id="track-btn-text">Track</span>
                    </button>
                </div>
            </form>

            <!-- Track Error Message Alert -->
            <div id="track-error-container" class="{{ !empty($trackingError) ? 'flex' : 'hidden' }} mt-3 p-3 items-center justify-between bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl fade-in" role="alert">
                <div class="flex items-center gap-2.5">
                    <i class="fas fa-exclamation-circle text-red-500 text-base flex-shrink-0"></i>
                    <span id="track-error-message" class="font-medium">{{ $trackingError ?? '' }}</span>
                </div>
                <button type="button" onclick="dismissTrackError()" class="text-red-400 hover:text-red-700 ml-2" aria-label="Dismiss error">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- 2. Dynamic Booking Section: Tracked Booking Details View (Shown when booking is tracked) -->
        <div id="booking-details-view" class="{{ $trackedBooking ? '' : 'hidden' }} card-mantis p-6 sm:p-8 border border-sky-100 fade-in">
            <!-- Details Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-5 mb-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0f6f9d]/10 text-[#0f6f9d] flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Booking Details</h2>
                        <p class="text-xs text-gray-500">Live appointment details retrieved from database</p>
                    </div>
                    <span id="detail-reference-badge" class="ml-2 px-2.5 py-1 rounded-md bg-gray-100 font-mono text-xs font-bold text-gray-800 border border-gray-200 hidden sm:inline"></span>
                </div>
                <button type="button" onclick="showBookingForm()" class="btn-mantis-outline">
                    <i class="fas fa-arrow-left mr-1.5"></i>
                    <span>Back to Booking Form</span>
                </button>
            </div>

            <!-- Status Banner -->
            <div id="booking-status-banner" class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3 border text-sm">
                <div class="flex items-center gap-3 min-w-0">
                    <span id="booking-status-banner-icon" class="text-xl flex-shrink-0"></span>
                    <div>
                        <h3 id="booking-status-banner-title" class="font-bold text-sm sm:text-base"></h3>
                        <p id="booking-status-banner-desc" class="text-xs opacity-90 mt-0.5"></p>
                    </div>
                </div>
                <div id="detail-status-badge-container" class="flex-shrink-0">
                    <span id="detail-status-badge" class="status-badge status-pending">Pending</span>
                </div>
            </div>

            <!-- Booking Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Reference Number -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100 flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                            <i class="fas fa-barcode text-gray-400 mr-1.5"></i>Reference Number
                        </p>
                        <p id="detail-reference-number" class="font-mono text-base font-bold text-gray-900 tracking-wide">
                            {{ $trackedBooking->reference_number ?? '' }}
                        </p>
                    </div>
                    <button type="button" id="copy-ref-btn" onclick="copyReferenceNumber()" class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-white text-gray-600 hover:text-[#0f6f9d] transition shadow-2xs flex items-center gap-1" title="Copy Reference Number">
                        <i class="far fa-copy"></i>
                        <span id="copy-ref-text">Copy</span>
                    </button>
                </div>

                <!-- Current Status -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-info-circle text-gray-400 mr-1.5"></i>Current Status
                    </p>
                    <p id="detail-status-text" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking ? ucfirst($trackedBooking->status) : '' }}
                    </p>
                </div>

                <!-- Full Name -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-user text-gray-400 mr-1.5"></i>Full Name
                    </p>
                    <p id="detail-full-name" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking->full_name ?? '' }}
                    </p>
                </div>

                <!-- Email Address -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-envelope text-gray-400 mr-1.5"></i>Email Address
                    </p>
                    <p id="detail-email" class="font-semibold text-gray-800 text-sm break-words">
                        {{ $trackedBooking->email ?? '' }}
                    </p>
                </div>

                <!-- Contact Number -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-phone text-gray-400 mr-1.5"></i>Contact Number
                    </p>
                    <p id="detail-contact-number" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking->contact_number ?? '' }}
                    </p>
                </div>

                <!-- Purpose Category -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-tag text-gray-400 mr-1.5"></i>Purpose Category
                    </p>
                    <p id="detail-purpose-category" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking ? ucfirst(str_replace(['-', '_'], ' ', $trackedBooking->purpose_category)) : '' }}
                    </p>
                </div>

                <!-- Preferred Date -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-calendar-day text-gray-400 mr-1.5"></i>Preferred Date
                    </p>
                    <p id="detail-preferred-date" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking && $trackedBooking->preferred_date ? $trackedBooking->preferred_date->format('F d, Y') : '' }}
                    </p>
                </div>

                <!-- Preferred Time -->
                <div class="bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-clock text-gray-400 mr-1.5"></i>Preferred Time
                    </p>
                    <p id="detail-preferred-time" class="font-semibold text-gray-800 text-sm">
                        {{ $trackedBooking ? ($trackedBooking->preferred_time_formatted ?? $trackedBooking->preferred_time) : '' }}
                    </p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2 bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-1.5"></i>Address
                    </p>
                    <p id="detail-address" class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">
                        {{ $trackedBooking->address ?? '' }}
                    </p>
                </div>

                <!-- Reason / Description -->
                <div class="md:col-span-2 bg-gray-50/90 rounded-xl p-3.5 border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1 flex items-center">
                        <i class="fas fa-align-left text-gray-400 mr-1.5"></i>Reason / Description
                    </p>
                    <p id="detail-reason" class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">
                        {{ $trackedBooking->reason ?? '' }}
                    </p>
                </div>
            </div>

            <!-- Remarks (if present) -->
            <div id="detail-remarks-container" class="{{ !empty($trackedBooking->remarks) ? '' : 'hidden' }} mt-4 p-3 rounded-xl bg-blue-50/80 border border-blue-100 text-sm">
                <span class="font-semibold text-[#0f6f9d] mr-1"><i class="fas fa-comment-dots mr-1"></i>Staff Remarks:</span>
                <span id="detail-remarks" class="text-gray-800">{{ $trackedBooking->remarks ?? '' }}</span>
            </div>

            <!-- Timeline Row -->
            <div id="detail-timeline-section" class="mt-5 pt-4 border-t border-gray-100 text-xs text-gray-600 flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="font-semibold text-gray-700 flex items-center gap-1"><i class="fas fa-history text-gray-400"></i> Timeline:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#0f6f9d]"></span>
                    <span>Submitted: <strong id="timeline-submitted-at">{{ $trackedBooking && $trackedBooking->created_at ? $trackedBooking->created_at->format('M d, Y - g:i A') : '—' }}</strong></span>
                </span>
                <span id="timeline-approved-item" class="{{ !empty($trackedBooking->approved_at) ? 'inline-flex' : 'hidden' }} items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Approved: <strong id="timeline-approved-at">{{ $trackedBooking && $trackedBooking->approved_at ? $trackedBooking->approved_at->format('M d, Y - g:i A') : '' }}</strong></span>
                </span>
                <span id="timeline-rejected-item" class="{{ !empty($trackedBooking->rejected_at) ? 'inline-flex' : 'hidden' }} items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span>Rejected: <strong id="timeline-rejected-at">{{ $trackedBooking && $trackedBooking->rejected_at ? $trackedBooking->rejected_at->format('M d, Y - g:i A') : '' }}</strong></span>
                </span>
                <span id="timeline-completed-item" class="{{ !empty($trackedBooking->completed_at) ? 'inline-flex' : 'hidden' }} items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Completed: <strong id="timeline-completed-at">{{ $trackedBooking && $trackedBooking->completed_at ? $trackedBooking->completed_at->format('M d, Y - g:i A') : '' }}</strong></span>
                </span>
                <span id="timeline-cancelled-item" class="{{ !empty($trackedBooking->cancelled_at) ? 'inline-flex' : 'hidden' }} items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                    <span>Cancelled: <strong id="timeline-cancelled-at">{{ $trackedBooking && $trackedBooking->cancelled_at ? $trackedBooking->cancelled_at->format('M d, Y - g:i A') : '' }}</strong></span>
                </span>
            </div>
        </div>

        <!-- 3. Dynamic Booking Section: Book an Appointment Submission Form View -->
        <div id="booking-form-view" class="{{ $trackedBooking ? 'hidden' : '' }} card-mantis p-6 sm:p-8 fade-in">
            <!-- Form Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-5 border-b border-gray-100">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900">Request an appointment</h2>
                    <p class="text-sm text-gray-600 mt-0.5">Tell us about the service you need and your preferred visit time.</p>
                </div>
                <div class="mt-2 sm:mt-0 text-xs font-medium text-[#0f6f9d] bg-sky-50 px-3 py-1.5 rounded-lg border border-sky-100 flex items-center gap-1.5">
                    <i class="fas fa-clock"></i> Office Hours: 8:00 AM - 5:00 PM
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="booking-form" action="{{ route('booking.store') }}" method="POST" onsubmit="return openBookingReview(event)">
                @csrf
                <!-- 2-Column Responsive Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <!-- Column 1: Personal & Service Information -->
                    <div class="space-y-4">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm"
                                placeholder="Enter your full name">
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm"
                                placeholder="Enter your email">
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number *</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm"
                                placeholder="Enter your contact number">
                        </div>

                        <!-- Purpose Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Purpose Category *</label>
                            <select name="purpose_category" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm">
                                <option value="">Select category</option>
                                <option value="equipment-repair" {{ old('purpose_category') == 'equipment-repair' ? 'selected' : '' }}>Equipment Repair</option>
                                <option value="tool-rental" {{ old('purpose_category') == 'tool-rental' ? 'selected' : '' }}>Tool Rental</option>
                                <option value="consultation" {{ old('purpose_category') == 'consultation' ? 'selected' : '' }}>Technical Consultation</option>
                                <option value="maintenance" {{ old('purpose_category') == 'maintenance' ? 'selected' : '' }}>Maintenance Service</option>
                                <option value="other" {{ old('purpose_category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Column 2: Schedule & Request Details -->
                    <div class="space-y-4">
                        <!-- Preferred Date & Time Inline -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Date *</label>
                                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm">
                            </div>
                            <div class="relative" id="time-picker-wrapper">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                                    <span>Preferred Time *</span>
                                    <span class="text-xs text-[#0f6f9d] font-normal">8:00 AM - 5:00 PM</span>
                                </label>

                                <button type="button" id="time-picker-btn" onclick="toggleTimeDropdown()"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm flex items-center justify-between text-left">
                                    @php
                                        $timeSlots = [
                                            '08:00' => '8:00 AM',
                                            '08:30' => '8:30 AM',
                                            '09:00' => '9:00 AM',
                                            '09:30' => '9:30 AM',
                                            '10:00' => '10:00 AM',
                                            '10:30' => '10:30 AM',
                                            '11:00' => '11:00 AM',
                                            '11:30' => '11:30 AM',
                                            '12:00' => '12:00 PM',
                                            '12:30' => '12:30 PM',
                                            '13:00' => '1:00 PM',
                                            '13:30' => '1:30 PM',
                                            '14:00' => '2:00 PM',
                                            '14:30' => '2:30 PM',
                                            '15:00' => '3:00 PM',
                                            '15:30' => '3:30 PM',
                                            '16:00' => '4:00 PM',
                                            '16:30' => '4:30 PM',
                                            '17:00' => '5:00 PM',
                                        ];
                                        $selectedTimeVal = old('preferred_time', '');
                                        $selectedTimeText = $selectedTimeVal && isset($timeSlots[$selectedTimeVal]) ? $timeSlots[$selectedTimeVal] : '';
                                    @endphp
                                    <span id="time-picker-label" class="{{ $selectedTimeText ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                        {{ $selectedTimeText ?: 'Select appointment time' }}
                                    </span>
                                    <i id="time-picker-arrow" class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"></i>
                                </button>

                                <input type="hidden" name="preferred_time" id="preferred_time" value="{{ $selectedTimeVal }}" required>

                                <!-- Custom Compact Time Menu (Capped Size & Clean 2 Columns) -->
                                <div id="time-picker-menu" class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden fade-in w-full">
                                    <div class="p-2 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between text-xs text-gray-600">
                                        <span class="font-semibold flex items-center gap-1.5"><i class="fas fa-clock text-[#0f6f9d]"></i> Available Slots</span>
                                    </div>
                                    <div class="p-2 max-h-52 overflow-y-auto grid grid-cols-2 gap-1.5">
                                        @foreach($timeSlots as $slotValue => $slotLabel)
                                            <button type="button"
                                                onclick="selectTimeSlot('{{ $slotValue }}', '{{ $slotLabel }}')"
                                                data-slot="{{ $slotValue }}"
                                                class="time-option-btn text-xs py-2 px-2.5 rounded-lg text-center font-medium transition-all border {{ $selectedTimeVal == $slotValue ? 'bg-[#0f6f9d] text-white border-[#0f6f9d] shadow-xs' : 'text-gray-700 bg-white border-gray-100 hover:bg-sky-50 hover:text-[#0f6f9d] hover:border-sky-200' }}">
                                                {{ $slotLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Address *</label>
                            <textarea name="address" rows="2" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm"
                                placeholder="Enter your complete address">{{ old('address') }}</textarea>
                        </div>

                        <!-- Reason / Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason / Description *</label>
                            <textarea name="reason" rows="2" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0f6f9d] focus:border-[#0f6f9d] transition-all bg-gray-50 text-sm"
                                placeholder="Please describe your request in detail">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Footer Bar -->
                <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="text-xs text-gray-500 flex items-center">
                        <i class="fas fa-shield-alt text-[#0f6f9d] mr-1.5 text-sm"></i> Your information is kept confidential and will only be used for this service.
                    </span>
                    <button type="submit"
                        class="w-full sm:w-auto btn-mantis px-8 py-3 text-sm font-semibold">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Booking Request
                    </button>
                </div>
            </form>
        </div>

    </main>

    <!-- Review Booking Modal -->
    <div id="booking-review-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-3 sm:p-5" role="dialog" aria-modal="true" aria-labelledby="booking-review-title" aria-describedby="booking-review-description" onclick="if (event.target === this) closeBookingReview()">
        <div class="booking-review-panel flex flex-col">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 sm:px-6">
                <div>
                    <h2 id="booking-review-title" class="text-lg font-semibold text-gray-800">Review Booking Request</h2>
                    <p id="booking-review-description" class="text-xs text-gray-500 mt-0.5">Please verify the information before submitting.</p>
                </div>
                <button type="button" class="booking-review-close flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Close review" onclick="closeBookingReview()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto text-sm">
                <table class="booking-review-table w-full table-fixed border-collapse">
                    <thead class="bg-gradient-to-r from-[#eff6ff] to-white">
                        <tr>
                            <th scope="col" class="w-[36%] border-b border-r border-gray-200 px-4 py-2.5 text-left text-xs font-semibold text-gray-700">Request detail</th>
                            <th scope="col" class="border-b border-gray-200 px-4 py-2.5 text-left text-xs font-semibold text-gray-700">Provided information</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Full Name</th>
                            <td id="review-full-name" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Email Address</th>
                            <td id="review-email" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Contact Number</th>
                            <td id="review-contact-number" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Purpose Category</th>
                            <td id="review-purpose-category" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Address</th>
                            <td id="review-address" class="whitespace-pre-wrap break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Preferred Date</th>
                            <td id="review-preferred-date" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-b border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Preferred Time</th>
                            <td id="review-preferred-time" class="break-words border-b border-gray-100 px-4 py-2 text-gray-800"></td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <th scope="row" class="border-r border-gray-100 px-4 py-2 text-left text-xs font-medium text-gray-600">Reason / Description</th>
                            <td id="review-reason" class="whitespace-pre-wrap break-words px-4 py-2 text-gray-800"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
                <button id="booking-review-cancel" type="button" onclick="closeBookingReview()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="confirmBookingSubmission()" class="btn-mantis px-4 py-2 text-xs font-semibold">Submit Booking Request</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto flex-shrink-0 border-t border-white/10 bg-[#0d3c57] py-5 text-center text-xs text-sky-100/75 sm:text-sm">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
            <div class="flex items-center gap-5">
                <a href="tel:09197503377" class="transition hover:text-white"><i class="fas fa-phone mr-1.5" aria-hidden="true"></i>0919 750 3377</a>
                <a href="mailto:rgv_services@yahoo.com" class="transition hover:text-white"><i class="far fa-envelope mr-1.5" aria-hidden="true"></i>Email us</a>
            </div>
        </div>
    </footer>

    <!-- Interactive Tracking & Form Switch Scripts -->
    <script>
        let bookingReviewConfirmed = false;

        const statusConfig = {
            pending: {
                label: 'Pending',
                badgeClass: 'status-pending',
                icon: '<i class="fas fa-clock text-amber-500"></i>',
                bannerBg: 'bg-amber-50 border-amber-200 text-amber-900',
                bannerTitle: 'Pending Staff Review',
                bannerDesc: 'Your request has been received and is currently being reviewed by our team.'
            },
            approved: {
                label: 'Approved',
                badgeClass: 'status-approved',
                icon: '<i class="fas fa-check-circle text-blue-600"></i>',
                bannerBg: 'bg-blue-50 border-blue-200 text-blue-900',
                bannerTitle: 'Booking Confirmed & Approved',
                bannerDesc: 'Your appointment is confirmed! Please arrive at our office or designated location on your scheduled date and time.'
            },
            completed: {
                label: 'Completed',
                badgeClass: 'status-completed',
                icon: '<i class="fas fa-flag-checkered text-emerald-600"></i>',
                bannerBg: 'bg-emerald-50 border-emerald-200 text-emerald-900',
                bannerTitle: 'Appointment Completed',
                bannerDesc: 'This service appointment has been marked as successfully completed. Thank you for choosing RGV Multi-Tech Services!'
            },
            rejected: {
                label: 'Rejected',
                badgeClass: 'status-rejected',
                icon: '<i class="fas fa-times-circle text-red-600"></i>',
                bannerBg: 'bg-red-50 border-red-200 text-red-900',
                bannerTitle: 'Booking Request Declined',
                bannerDesc: 'Unfortunately, we are unable to accommodate this appointment request. Please contact us or submit a new request.'
            },
            cancelled: {
                label: 'Cancelled',
                badgeClass: 'status-cancelled',
                icon: '<i class="fas fa-ban text-gray-500"></i>',
                bannerBg: 'bg-gray-100 border-gray-300 text-gray-800',
                bannerTitle: 'Booking Cancelled',
                bannerDesc: 'This appointment booking was cancelled.'
            }
        };

        function updateStatusUI(statusKey) {
            const normalizedStatus = (statusKey || 'pending').toLowerCase();
            const config = statusConfig[normalizedStatus] || statusConfig.pending;

            const badge = document.getElementById('detail-status-badge');
            if (badge) {
                badge.className = `status-badge ${config.badgeClass}`;
                badge.innerHTML = `${config.icon} <span class="ml-1.5">${config.label}</span>`;
            }

            const statusText = document.getElementById('detail-status-text');
            if (statusText) {
                statusText.textContent = config.label;
            }

            const banner = document.getElementById('booking-status-banner');
            const bannerIcon = document.getElementById('booking-status-banner-icon');
            const bannerTitle = document.getElementById('booking-status-banner-title');
            const bannerDesc = document.getElementById('booking-status-banner-desc');

            if (banner) {
                banner.className = `mb-5 p-4 rounded-xl flex items-center justify-between gap-3 border text-sm ${config.bannerBg}`;
                bannerIcon.innerHTML = config.icon;
                bannerTitle.textContent = config.bannerTitle;
                bannerDesc.textContent = config.bannerDesc;
            }
        }

        async function handleTrackBooking(event) {
            if (event) {
                event.preventDefault();
            }

            const input = document.getElementById('track_reference_number');
            const ref = (input.value || '').trim().toUpperCase();

            if (!ref) {
                showTrackError('Please enter a booking reference number.');
                input.focus();
                return false;
            }

            hideTrackError();

            const submitBtn = document.getElementById('track-submit-btn');
            const spinner = document.getElementById('track-btn-spinner');
            const btnIcon = document.getElementById('track-btn-icon');
            const btnText = document.getElementById('track-btn-text');

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            btnIcon.classList.add('hidden');
            btnText.textContent = 'Tracking...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('{{ route("booking.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ reference_number: ref })
                });

                const data = await response.json();

                if (response.ok && data.success && data.booking) {
                    displayTrackedBooking(data.booking);
                    hideTrackError();

                    const url = new URL(window.location.href);
                    url.searchParams.set('reference', data.booking.reference_number);
                    window.history.pushState({ reference: data.booking.reference_number }, '', url.toString());
                } else {
                    const message = data.message || `No booking found with reference number "${ref}". Please check your reference number and try again.`;
                    showTrackError(message);
                    showBookingForm(false);
                }
            } catch (err) {
                console.error('Tracking fetch error:', err);
                showTrackError('An error occurred while tracking the booking. Please try again.');
                showBookingForm(false);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnIcon.classList.remove('hidden');
                btnText.textContent = 'Track';
            }

            return false;
        }

        function displayTrackedBooking(booking) {
            document.getElementById('detail-reference-number').textContent = booking.reference_number || '—';
            const badgeRef = document.getElementById('detail-reference-badge');
            if (badgeRef) {
                badgeRef.textContent = booking.reference_number || '';
            }

            document.getElementById('detail-full-name').textContent = booking.full_name || '—';
            document.getElementById('detail-email').textContent = booking.email || '—';
            document.getElementById('detail-contact-number').textContent = booking.contact_number || '—';
            document.getElementById('detail-purpose-category').textContent = booking.purpose_category_label || booking.purpose_category || '—';
            document.getElementById('detail-preferred-date').textContent = booking.preferred_date_formatted || booking.preferred_date || '—';
            document.getElementById('detail-preferred-time').textContent = booking.preferred_time || '—';
            document.getElementById('detail-address').textContent = booking.address || '—';
            document.getElementById('detail-reason').textContent = booking.reason || '—';

            updateStatusUI(booking.status);

            const remarksContainer = document.getElementById('detail-remarks-container');
            const remarksText = document.getElementById('detail-remarks');
            if (booking.remarks && booking.remarks.trim()) {
                remarksText.textContent = booking.remarks;
                remarksContainer.classList.remove('hidden');
            } else {
                remarksContainer.classList.add('hidden');
            }

            document.getElementById('timeline-submitted-at').textContent = booking.created_at_formatted || '—';
            toggleTimelineItem('timeline-approved-item', 'timeline-approved-at', booking.approved_at_formatted);
            toggleTimelineItem('timeline-rejected-item', 'timeline-rejected-at', booking.rejected_at_formatted);
            toggleTimelineItem('timeline-completed-item', 'timeline-completed-at', booking.completed_at_formatted);
            toggleTimelineItem('timeline-cancelled-item', 'timeline-cancelled-at', booking.cancelled_at_formatted);

            document.getElementById('booking-form-view').classList.add('hidden');
            document.getElementById('booking-details-view').classList.remove('hidden');
        }

        function toggleTimelineItem(containerId, textId, timestamp) {
            const container = document.getElementById(containerId);
            const textElem = document.getElementById(textId);
            if (container && textElem) {
                if (timestamp) {
                    textElem.textContent = timestamp;
                    container.classList.remove('hidden');
                    container.classList.add('inline-flex');
                } else {
                    container.classList.add('hidden');
                    container.classList.remove('inline-flex');
                }
            }
        }

        function showBookingForm(clearSearch = true) {
            document.getElementById('booking-details-view').classList.add('hidden');
            document.getElementById('booking-form-view').classList.remove('hidden');

            if (clearSearch) {
                document.getElementById('track_reference_number').value = '';
                hideTrackError();

                const url = new URL(window.location.href);
                url.searchParams.delete('reference');
                window.history.pushState({}, '', url.pathname);
            }
        }

        function showTrackError(message) {
            const container = document.getElementById('track-error-container');
            const messageEl = document.getElementById('track-error-message');
            messageEl.textContent = message;
            container.classList.remove('hidden');
            container.classList.add('flex');
        }

        function hideTrackError() {
            const container = document.getElementById('track-error-container');
            container.classList.add('hidden');
            container.classList.remove('flex');
        }

        function dismissTrackError() {
            hideTrackError();
        }

        function copyReferenceNumber() {
            const ref = document.getElementById('detail-reference-number').textContent.trim();
            if (!ref || ref === '—') return;

            navigator.clipboard.writeText(ref).then(() => {
                const textSpan = document.getElementById('copy-ref-text');
                const prevText = textSpan.textContent;
                textSpan.textContent = 'Copied!';
                setTimeout(() => {
                    textSpan.textContent = prevText;
                }, 2000);
            }).catch(() => {
                const tempInput = document.createElement('input');
                tempInput.value = ref;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            });
        }

        window.addEventListener('popstate', (e) => {
            const params = new URLSearchParams(window.location.search);
            const ref = params.get('reference');
            if (ref) {
                document.getElementById('track_reference_number').value = ref;
                handleTrackBooking();
            } else {
                showBookingForm(false);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            @if(!empty($trackedBooking))
                updateStatusUI("{{ $trackedBooking->status }}");
            @endif
        });

        function toggleTimeDropdown() {
            const menu = document.getElementById('time-picker-menu');
            const arrow = document.getElementById('time-picker-arrow');
            if (!menu) return;
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                if (arrow) arrow.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }

        function closeTimeDropdown() {
            const menu = document.getElementById('time-picker-menu');
            const arrow = document.getElementById('time-picker-arrow');
            if (menu) menu.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-180');
        }

        function selectTimeSlot(val, label) {
            document.getElementById('preferred_time').value = val;
            const display = document.getElementById('time-picker-label');
            if (display) {
                display.textContent = label;
                display.classList.remove('text-gray-500');
                display.classList.add('text-gray-900', 'font-medium');
            }

            const btn = document.getElementById('time-picker-btn');
            if (btn) {
                btn.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            }

            document.querySelectorAll('.time-option-btn').forEach(b => {
                if (b.getAttribute('data-slot') === val) {
                    b.className = 'time-option-btn text-xs py-2 px-2.5 rounded-lg text-center font-medium transition-all border bg-[#0f6f9d] text-white border-[#0f6f9d] shadow-xs';
                } else {
                    b.className = 'time-option-btn text-xs py-2 px-2.5 rounded-lg text-center font-medium transition-all border text-gray-700 bg-white border-gray-100 hover:bg-sky-50 hover:text-[#0f6f9d] hover:border-sky-200';
                }
            });

            closeTimeDropdown();
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('time-picker-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeTimeDropdown();
            }
        });

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
            const timeVal = form.elements.preferred_time?.value;
            if (!timeVal) {
                const triggerBtn = document.getElementById('time-picker-btn');
                if (triggerBtn) {
                    triggerBtn.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                    toggleTimeDropdown();
                    triggerBtn.focus();
                }
                return false;
            }

            const values = {
                'review-full-name': form.elements.full_name.value,
                'review-email': form.elements.email.value,
                'review-contact-number': form.elements.contact_number.value,
                'review-purpose-category': form.elements.purpose_category.selectedOptions[0]?.textContent || '—',
                'review-address': form.elements.address.value,
                'review-preferred-date': form.elements.preferred_date.value,
                'review-preferred-time': document.getElementById('time-picker-label')?.textContent?.trim() || formatPreferredTime(timeVal),
                'review-reason': form.elements.reason.value,
            };

            Object.entries(values).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value.trim() || '—';
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
            document.querySelector('#booking-form button[type="submit"]')?.focus();
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
    <x-toast-container />
</body>
</html>
