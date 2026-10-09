<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RGV Multi-Tech Services - Customer Booking and Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
        }

        @keyframes project-slide-forward {
            from { opacity: 0.55; transform: translateX(18px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes project-slide-backward {
            from { opacity: 0.55; transform: translateX(-18px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .project-slide-forward { animation: project-slide-forward 350ms ease-out; }
        .project-slide-backward { animation: project-slide-backward 350ms ease-out; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">
    <!-- Navigation -->
    <nav class="bg-[#0f6f9d] border-b border-[#0a5c83] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-10 h-10 rounded-xl object-cover shadow-lg shadow-[#2563eb]/20">
                        <span class="font-bold text-xl text-white ml-3">RGV Multi-Tech Services</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('booking.form') }}" class="bg-white text-[#0f6f9d] px-4 py-2 rounded-lg hover:bg-[#eaf6fb] transition-colors">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                        </a>
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

    <!-- Hero Section -->
    <section class="bg-[#edf4fb] border-b border-[#d8e6f3] py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-10 lg:mb-0">
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-600 mb-4">Reliable technical support</p>
                    <h1 class="text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 mb-5">
                        Professional Multi-Tech Services for Your Business
                    </h1>
                    <p class="text-lg leading-8 mb-8 text-slate-600 max-w-xl">
                        RGV Multi-Tech Services provides comprehensive technical solutions with a focus on quality, efficiency, and customer satisfaction.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('booking.form') }}" class="bg-[#0f6f9d] text-white px-7 py-3 rounded-lg font-semibold hover:bg-[#0a5c83] transition-colors text-center shadow-sm">
                            <i class="fas fa-calendar-check mr-2"></i>Book Now
                        </a>
                        <a href="#services" class="border border-slate-300 text-slate-700 px-7 py-3 rounded-lg font-semibold hover:border-[#2563eb] hover:text-[#2563eb] transition-colors text-center">
                            <i class="fas fa-info-circle mr-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm">
                        <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-36 h-36 mx-auto mb-5 rounded-full object-cover shadow-sm">
                        <p class="text-xl font-semibold text-slate-900">Expert Technical Services</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-slate-900 mb-3">Our Services</h2>
                <p class="text-gray-600 max-w-3xl mx-auto">RGV Multi-Tech Services offers quality mechanical installations, aircon servicing, kitchen equipment solutions, and general contracting for your projects.</p>
            </div>
            <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div class="bg-white rounded-xl p-6 hover:border-blue-200 transition border border-slate-200">
                    <div class="bg-[#eff6ff] w-12 h-12 rounded-lg flex items-center justify-center mb-5">
                        <i class="fas fa-screwdriver-wrench text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-slate-900">Mechanical Installations</h3>
                    <p class="text-gray-600">Quality mechanical installation, maintenance, and support for commercial and project sites.</p>
                </div>
                <div class="bg-white rounded-xl p-6 hover:border-blue-200 transition border border-slate-200">
                    <div class="bg-[#eff6ff] w-12 h-12 rounded-lg flex items-center justify-center mb-5">
                        <i class="fas fa-wind text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-slate-900">Aircon Servicing</h3>
                    <p class="text-gray-600">Reliable air-conditioning installation, cleaning, repair, and preventive servicing.</p>
                </div>
                <div class="bg-white rounded-xl p-6 hover:border-blue-200 transition border border-slate-200">
                    <div class="bg-[#eff6ff] w-12 h-12 rounded-lg flex items-center justify-center mb-5">
                        <i class="fas fa-utensils text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-slate-900">Kitchen Equipment</h3>
                    <p class="text-gray-600">Installation and servicing for dependable commercial kitchen equipment and systems.</p>
                </div>
                <div class="bg-white rounded-xl p-6 hover:border-blue-200 transition border border-slate-200">
                    <div class="bg-[#eff6ff] w-12 h-12 rounded-lg flex items-center justify-center mb-5">
                        <i class="fas fa-building text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-slate-900">General Contractor</h3>
                    <p class="text-gray-600">Coordinated technical and construction services to help deliver projects from start to finish.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Highlights Section -->
    <section class="py-12 lg:py-14 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Selected work</p>
                    <h2 class="text-3xl font-bold text-slate-900">Project Highlights</h2>
                </div>
                <p class="text-gray-600 max-w-xl mx-auto mt-3">A look at the technical work and services delivered by RGV Multi-Tech Services.</p>
            </div>
            @php
                $projectImages = ['11', '111', '1', '2', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
            @endphp
            <div class="relative max-w-5xl mx-auto px-8 md:px-0">
                <button type="button" onclick="moveProject(-1)" aria-label="Previous project" class="absolute left-1 md:left-[-56px] top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-lg border border-slate-300 bg-white text-slate-600 shadow-sm hover:border-[#0f6f9d] hover:text-[#0f6f9d] transition-colors">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                </button>
                <button type="button" onclick="moveProject(1)" aria-label="Next project" class="absolute right-1 md:right-[-56px] top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-lg border border-slate-300 bg-white text-slate-600 shadow-sm hover:border-[#0f6f9d] hover:text-[#0f6f9d] transition-colors">
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
                <div id="project-stage" class="grid grid-cols-1 md:grid-cols-[0.8fr_1.25fr_0.8fr] items-center gap-3">
                <figure id="project-previous" onclick="moveProject(-1)" onkeydown="if (event.key === 'Enter' || event.key === ' ') moveProject(-1)" role="button" tabindex="0" class="hidden md:block cursor-pointer overflow-hidden rounded-xl bg-white border border-slate-200 opacity-60 blur-[1px] scale-90 transition-all duration-500 hover:opacity-80">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img class="h-full w-full object-cover" alt="Previous project highlight">
                    </div>
                </figure>
                <figure id="project-current" class="overflow-hidden rounded-xl bg-white border border-slate-200 shadow-lg shadow-slate-200/70 scale-100 transition-all duration-500">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img class="h-full w-full object-cover" alt="Current project highlight">
                    </div>
                </figure>
                <figure id="project-next" onclick="moveProject(1)" onkeydown="if (event.key === 'Enter' || event.key === ' ') moveProject(1)" role="button" tabindex="0" class="hidden md:block cursor-pointer overflow-hidden rounded-xl bg-white border border-slate-200 opacity-60 blur-[1px] scale-90 transition-all duration-500 hover:opacity-80">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img class="h-full w-full object-cover" alt="Next project highlight">
                    </div>
                </figure>
                </div>
            </div>
            <p id="project-position" class="text-center text-xs text-slate-500 mt-3" aria-live="polite"></p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-slate-900 mb-3">Contact Us</h2>
                <p class="text-gray-600">Get in touch with us for any inquiries</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-[#eff6ff] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Location</h3>
                    <p class="text-gray-600">0512 Barra Macabalan, Cagayan de Oro, Philippines, 9000</p>
                </div>
                <div class="text-center">
                    <div class="bg-[#eff6ff] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Phone</h3>
                    <p><a href="tel:09197503377" class="text-gray-600 hover:text-[#2563eb]">0919 750 3377</a></p>
                </div>
                <div class="text-center">
                    <div class="bg-[#eff6ff] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-[#2563eb] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Email</h3>
                    <p><a href="mailto:rgv_services@yahoo.com" class="text-gray-600 hover:text-[#2563eb]">rgv_services@yahoo.com</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#0f6f9d] border-t border-[#0a5c83] text-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('images/RGV-Logo.png') }}" alt="RGV Multi-Tech Services logo" class="w-10 h-10 rounded-lg object-cover mr-3">
                        <span class="font-bold text-xl">RGV Multi-Tech</span>
                    </div>
                    <p class="text-white/80">Your trusted partner for all technical services and solutions.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-white/80 hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('booking.form') }}" class="text-white/80 hover:text-white transition">Book Appointment</a></li>
                        <li><a href="#services" class="text-white/80 hover:text-white transition">Services</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-white/80 hover:text-white transition">Equipment Repair</a></li>
                        <li><a href="#" class="text-white/80 hover:text-white transition">Tool Rentals</a></li>
                        <li><a href="#" class="text-white/80 hover:text-white transition">Consultation</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact Us</h4>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/profile.php?id=100041825276461" target="_blank" rel="noopener noreferrer" aria-label="Follow us on Facebook" class="flex h-8 w-8 items-center justify-center text-white/80 transition hover:text-white"><i class="fab fa-facebook fa-fw text-xl leading-none"></i></a>
                        <a href="tel:09197503377" aria-label="Call 0919 750 3377" class="flex h-8 w-8 items-center justify-center text-white/80 transition hover:text-white"><i class="fas fa-phone fa-fw text-xl leading-none"></i></a>
                        <a href="mailto:rgv_services@yahoo.com" aria-label="Email rgv_services@yahoo.com" class="flex h-8 w-8 items-center justify-center text-white/80 transition hover:text-white"><i class="fas fa-envelope fa-fw text-xl leading-none"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/20 mt-8 pt-8 text-center text-white/80">
                <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
        const projectImages = @json($projectImages);
        const projectAssetBase = @json(asset('project-highlights'));
        let projectIndex = 0;

        function updateProjectCard(id, imageName, label) {
            const card = document.getElementById(id);
            card.querySelector('img').src = `${projectAssetBase}/${imageName}.jpg`;
            card.querySelector('img').alt = `${label} project highlight`;
        }

        function renderProjects(direction = 0) {
            const total = projectImages.length;
            const previous = projectImages[(projectIndex - 1 + total) % total];
            const current = projectImages[projectIndex];
            const next = projectImages[(projectIndex + 1) % total];

            updateProjectCard('project-previous', previous, 'Previous');
            updateProjectCard('project-current', current, 'Current');
            updateProjectCard('project-next', next, 'Next');
            document.getElementById('project-position').textContent = `${projectIndex + 1} of ${total}`;

            const stage = document.getElementById('project-stage');
            stage.classList.remove('project-slide-forward', 'project-slide-backward');
            if (direction !== 0) {
                void stage.offsetWidth;
                stage.classList.add(direction > 0 ? 'project-slide-forward' : 'project-slide-backward');
            }
        }

        function moveProject(direction) {
            projectIndex = (projectIndex + direction + projectImages.length) % projectImages.length;
            renderProjects(direction);
        }

        renderProjects();
    </script>
</body>
</html>
