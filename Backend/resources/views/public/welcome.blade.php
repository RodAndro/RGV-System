<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RGV Multi-Tech Services provides mechanical installation, air-conditioning, kitchen equipment, and general contracting services in Cagayan de Oro.">
    <title>RGV Multi-Tech Services | Technical Services in Cagayan de Oro</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css'])
    <style>
        :root { --rgv-blue: #0f6f9d; --rgv-blue-dark: #0a5c83; --rgv-sky: #eaf5fb; }
        html { scroll-behavior: smooth; }
        body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
        .project-slide-forward { animation: project-slide-forward 350ms ease-out; }
        .project-slide-backward { animation: project-slide-backward 350ms ease-out; }
        @keyframes project-slide-forward { from { opacity: .6; transform: translateX(18px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes project-slide-backward { from { opacity: .6; transform: translateX(-18px); } to { opacity: 1; transform: translateX(0); } }
        .service-card { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 18px 35px rgba(20, 54, 77, .1); border-color: #b8ddec; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">
    <nav class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="RGV Multi-Tech Services home">
                <img src="{{ asset('images/RGV-Logo.png') }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-slate-200">
                <span class="truncate text-sm font-extrabold leading-tight text-slate-900 sm:text-base">RGV <span class="font-semibold text-slate-600">Multi-Tech Services</span></span>
            </a>
            <div class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex">
                <a href="#services" class="transition hover:text-[#0f6f9d]">Services</a>
                <a href="#projects" class="transition hover:text-[#0f6f9d]">Project highlights</a>
                <a href="#contact" class="transition hover:text-[#0f6f9d]">Contact</a>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" aria-label="Log in" class="inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0f6f9d]"><i class="fa-solid fa-arrow-right-to-bracket sm:hidden" aria-hidden="true"></i><span class="hidden sm:inline">Log in</span></a>
                @else
                    <a href="{{ route('dashboard') }}" aria-label="Dashboard" class="inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0f6f9d]"><i class="fa-solid fa-gauge-high sm:hidden" aria-hidden="true"></i><span class="hidden sm:inline">Dashboard</span></a>
                @endguest
                <a href="{{ route('booking.form') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#0f6f9d] px-3.5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0a5c83] sm:px-5">
                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>Book a service</span>
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="overflow-hidden bg-[#f1f7fb]">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[.93fr_1.07fr] lg:gap-14 lg:px-8 lg:py-20">
                <div class="max-w-2xl">
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#c9e3ef] bg-white/80 px-3.5 py-2 text-[11px] font-extrabold uppercase tracking-[.16em] text-[#0f6f9d] sm:text-xs">
                        <span class="h-2 w-2 rounded-full bg-[#0f6f9d]"></span> Cagayan de Oro · Multi-tech service team
                    </p>
                    <h1 class="max-w-xl text-4xl font-black leading-[1.08] tracking-tight text-slate-950 sm:text-5xl lg:text-[3.65rem]">
                        Reliable technical service, from planning to completion.
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                        RGV brings mechanical installation, air-conditioning, kitchen equipment, and general contracting support together for your next project.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('booking.form') }}" class="inline-flex items-center justify-center gap-2.5 rounded-lg bg-[#0f6f9d] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#0f6f9d]/20 transition hover:-translate-y-0.5 hover:bg-[#0a5c83]">
                            Request an appointment <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                        </a>
                        <a href="#services" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition hover:border-[#0f6f9d] hover:text-[#0f6f9d]">
                            Explore our services
                        </a>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-slate-600">
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-[#0f6f9d]" aria-hidden="true"></i> Clear project communication</span>
                        <span class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-[#0f6f9d]" aria-hidden="true"></i> Appointment-based service</span>
                    </div>
                </div>
                <div class="relative mx-auto w-full max-w-2xl lg:ml-auto">
                    <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-[#cde8f4] blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-[1.6rem] bg-slate-200 shadow-2xl shadow-slate-900/15 ring-1 ring-white">
                        <img src="{{ asset('project-highlights/11.jpg') }}" alt="RGV Multi-Tech Services team at a commercial project" class="h-[340px] w-full object-cover sm:h-[440px] lg:h-[500px]">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-slate-950/5"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[.16em] text-sky-100">Work that keeps operations moving</p>
                                    <p class="mt-2 text-xl font-bold text-white sm:text-2xl">Technical solutions for real spaces</p>
                                </div>
                                <a href="#projects" aria-label="View project highlights" title="View project highlights" class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white sm:inline-flex"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-5 px-4 py-6 sm:px-6 md:grid-cols-3 md:gap-8 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-lg text-[#0f6f9d]"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>
                    <div><p class="font-bold text-slate-900">Hands-on expertise</p><p class="mt-0.5 text-sm text-slate-500">Support for technical installations and service</p></div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-lg text-[#0f6f9d]"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>
                    <div><p class="font-bold text-slate-900">Simple appointment requests</p><p class="mt-0.5 text-sm text-slate-500">Choose a date and time that works for you</p></div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-lg text-[#0f6f9d]"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                    <div><p class="font-bold text-slate-900">Based in Cagayan de Oro</p><p class="mt-0.5 text-sm text-slate-500">0512 Barra Macabalan, Cagayan de Oro</p></div>
                </div>
            </div>
        </section>

        <section id="services" class="scroll-mt-24 bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-9 flex flex-col justify-between gap-4 sm:mb-12 sm:flex-row sm:items-end">
                    <div class="max-w-2xl">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-[.18em] text-[#0f6f9d]">What we do</p>
                        <h2 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Technical services under one roof.</h2>
                    </div>
                    <p class="max-w-lg text-sm leading-6 text-slate-600 sm:text-base">From equipment support to project coordination, our team helps you move from a service need to a clear next step.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('booking.form') }}" class="service-card group rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-[#eaf5fb] text-xl text-[#0f6f9d]"><i class="fa-solid fa-gears" aria-hidden="true"></i></span>
                        <h3 class="text-lg font-extrabold text-slate-900">Mechanical Installations</h3>
                        <p class="mt-2 min-h-[72px] text-sm leading-6 text-slate-600">Installation, maintenance, and support for commercial and project sites.</p>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#0f6f9d]">Request service <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                    </a>
                    <a href="{{ route('booking.form') }}" class="service-card group rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-[#eaf5fb] text-xl text-[#0f6f9d]"><i class="fa-solid fa-wind" aria-hidden="true"></i></span>
                        <h3 class="text-lg font-extrabold text-slate-900">Aircon Servicing</h3>
                        <p class="mt-2 min-h-[72px] text-sm leading-6 text-slate-600">Air-conditioning installation, cleaning, repair, and preventive servicing.</p>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#0f6f9d]">Request service <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                    </a>
                    <a href="{{ route('booking.form') }}" class="service-card group rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-[#eaf5fb] text-xl text-[#0f6f9d]"><i class="fa-solid fa-utensils" aria-hidden="true"></i></span>
                        <h3 class="text-lg font-extrabold text-slate-900">Kitchen Equipment</h3>
                        <p class="mt-2 min-h-[72px] text-sm leading-6 text-slate-600">Installation and servicing for commercial kitchen equipment and systems.</p>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#0f6f9d]">Request service <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                    </a>
                    <a href="{{ route('booking.form') }}" class="service-card group rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-[#eaf5fb] text-xl text-[#0f6f9d]"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                        <h3 class="text-lg font-extrabold text-slate-900">General Contractor</h3>
                        <p class="mt-2 min-h-[72px] text-sm leading-6 text-slate-600">Coordinated technical and construction services for projects from start to finish.</p>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#0f6f9d]">Request service <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1" aria-hidden="true"></i></span>
                    </a>
                </div>
            </div>
        </section>

        <section id="projects" class="scroll-mt-24 border-y border-slate-200 bg-[#f5f8fb] py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-9 flex flex-col justify-between gap-5 sm:mb-12 sm:flex-row sm:items-end">
                    <div>
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-[.18em] text-[#0f6f9d]">Project highlights</p>
                        <h2 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">A closer look at our work.</h2>
                    </div>
                    <p class="max-w-lg text-sm leading-6 text-slate-600 sm:text-base">Explore selected installations and project moments from the RGV team.</p>
                </div>
                @php
                    $projectImages = ['11', '111', '1', '2', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
                @endphp
                <div class="relative mx-auto max-w-6xl px-7 sm:px-10">
                    <button type="button" onclick="moveProject(-1)" aria-label="Previous project" class="absolute left-0 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-md transition hover:border-[#0f6f9d] hover:text-[#0f6f9d] sm:-left-1">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" onclick="moveProject(1)" aria-label="Next project" class="absolute right-0 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-md transition hover:border-[#0f6f9d] hover:text-[#0f6f9d] sm:-right-1">
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                    <div id="project-stage" class="grid grid-cols-1 items-center gap-4 md:grid-cols-[.8fr_1.25fr_.8fr]">
                        <figure id="project-previous" onclick="moveProject(-1)" onkeydown="if (event.key === 'Enter' || event.key === ' ') moveProject(-1)" role="button" tabindex="0" class="hidden cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white opacity-60 transition-all duration-500 hover:opacity-90 md:block">
                            <div class="aspect-[4/3] overflow-hidden bg-slate-200"><img class="h-full w-full object-cover" alt="Previous project highlight"></div>
                            <figcaption class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">Previous project</figcaption>
                        </figure>
                        <figure id="project-current" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 transition-all duration-500">
                            <div class="aspect-[4/3] overflow-hidden bg-slate-200"><img class="h-full w-full object-cover" alt="Current project highlight"></div>
                            <figcaption class="flex items-center justify-end px-4 py-3.5"><span class="text-xs font-semibold text-[#0f6f9d]">RGV Multi-Tech</span></figcaption>
                        </figure>
                        <figure id="project-next" onclick="moveProject(1)" onkeydown="if (event.key === 'Enter' || event.key === ' ') moveProject(1)" role="button" tabindex="0" class="hidden cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white opacity-60 transition-all duration-500 hover:opacity-90 md:block">
                            <div class="aspect-[4/3] overflow-hidden bg-slate-200"><img class="h-full w-full object-cover" alt="Next project highlight"></div>
                            <figcaption class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">Next project</figcaption>
                        </figure>
                    </div>
                </div>
                <p id="project-position" class="mt-4 text-center text-xs font-semibold text-slate-500" aria-live="polite"></p>
            </div>
        </section>

        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[.85fr_1.15fr] lg:items-center lg:px-8">
                <div>
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[.18em] text-[#0f6f9d]">Our approach</p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl">Good work starts with a clear conversation.</h2>
                    <p class="mt-4 max-w-xl leading-7 text-slate-600">We listen to what your site needs, help clarify the next steps, and keep communication open as your request moves forward.</p>
                    <a href="{{ route('booking.form') }}" class="mt-6 inline-flex items-center gap-2 font-bold text-[#0f6f9d] hover:text-[#0a5c83]">Talk with our team <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-[#f8fbfd] p-5">
                        <span class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#0f6f9d] shadow-sm"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                        <h3 class="font-extrabold text-slate-900">Responsive service</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Know where your request stands and what comes next.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[#f8fbfd] p-5">
                        <span class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#0f6f9d] shadow-sm"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span>
                        <h3 class="font-extrabold text-slate-900">Solid workmanship</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Careful technical work across the services we provide.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[#f8fbfd] p-5">
                        <span class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#0f6f9d] shadow-sm"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
                        <h3 class="font-extrabold text-slate-900">Upfront details</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Share the scope and details before the visit is arranged.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#0d3c57] py-16 text-white sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-10 max-w-2xl text-center">
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[.18em] text-sky-200">How it works</p>
                    <h2 class="text-3xl font-black tracking-tight sm:text-4xl">A simple path to your service visit.</h2>
                    <p class="mt-4 leading-7 text-sky-100/80">Send us the details online. We’ll review your request and keep your booking status available to track.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/15 bg-white/[.06] p-6">
                        <span class="text-sm font-black tracking-widest text-sky-200">01</span>
                        <h3 class="mt-4 text-lg font-bold">Tell us what you need</h3>
                        <p class="mt-2 text-sm leading-6 text-sky-100/75">Choose a service category and share the important details.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/[.06] p-6">
                        <span class="text-sm font-black tracking-widest text-sky-200">02</span>
                        <h3 class="mt-4 text-lg font-bold">Pick a preferred time</h3>
                        <p class="mt-2 text-sm leading-6 text-sky-100/75">Request a visit during the available appointment hours.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/[.06] p-6">
                        <span class="text-sm font-black tracking-widest text-sky-200">03</span>
                        <h3 class="mt-4 text-lg font-bold">Track your request</h3>
                        <p class="mt-2 text-sm leading-6 text-sky-100/75">Use your booking reference to see status and appointment details.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#eaf5fb] py-12 sm:py-16">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-extrabold uppercase tracking-[.18em] text-[#0f6f9d]">Need a technical service?</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Start with an appointment request.</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Share your details and preferred schedule. Our team will review your request.</p>
                </div>
                <a href="{{ route('booking.form') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#0f6f9d] px-6 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-[#0a5c83]">Book an appointment <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
            </div>
        </section>

        <section id="contact" class="scroll-mt-24 bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-9 max-w-xl">
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[.18em] text-[#0f6f9d]">Contact us</p>
                    <h2 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Let’s talk about your project.</h2>
                    <p class="mt-3 leading-7 text-slate-600">Have a question before booking? Reach the RGV Multi-Tech Services team.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <a href="https://maps.google.com/?q=0512+Barra+Macabalan+Cagayan+de+Oro" target="_blank" rel="noopener noreferrer" class="flex gap-4 rounded-2xl border border-slate-200 p-5 transition hover:border-[#b8ddec] hover:bg-[#f8fbfd]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-[#0f6f9d]"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                        <span><span class="block font-bold text-slate-900">Visit us</span><span class="mt-1 block text-sm leading-6 text-slate-600">0512 Barra Macabalan, Cagayan de Oro, Philippines, 9000</span></span>
                    </a>
                    <a href="tel:09197503377" class="flex gap-4 rounded-2xl border border-slate-200 p-5 transition hover:border-[#b8ddec] hover:bg-[#f8fbfd]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-[#0f6f9d]"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                        <span><span class="block font-bold text-slate-900">Call us</span><span class="mt-1 block text-sm text-slate-600">0919 750 3377</span></span>
                    </a>
                    <a href="mailto:rgv_services@yahoo.com" class="flex gap-4 rounded-2xl border border-slate-200 p-5 transition hover:border-[#b8ddec] hover:bg-[#f8fbfd]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf5fb] text-[#0f6f9d]"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
                        <span><span class="block font-bold text-slate-900">Email us</span><span class="mt-1 block text-sm text-slate-600">rgv_services@yahoo.com</span></span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#0d3c57] text-white">
        <div class="mx-auto grid max-w-7xl gap-9 px-4 py-10 sm:px-6 md:grid-cols-[1.4fr_.8fr_.8fr] lg:px-8">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/RGV-Logo.png') }}" alt="" class="h-11 w-11 rounded-full bg-white object-cover">
                    <span class="text-lg font-extrabold">RGV Multi-Tech Services</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-sky-100/75">Your partner for mechanical, air-conditioning, kitchen equipment, and general contracting services.</p>
            </div>
            <div>
                <h3 class="font-bold">Explore</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-sky-100/75">
                    <li><a href="#services" class="transition hover:text-white">Services</a></li>
                    <li><a href="#projects" class="transition hover:text-white">Project highlights</a></li>
                    <li><a href="{{ route('booking.form') }}" class="transition hover:text-white">Book an appointment</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold">Connect</h3>
                <div class="mt-4 flex gap-3">
                    <a href="https://www.facebook.com/profile.php?id=100041825276461" target="_blank" rel="noopener noreferrer" aria-label="Follow RGV on Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-sky-100 transition hover:bg-white/10 hover:text-white"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="tel:09197503377" aria-label="Call RGV Multi-Tech Services" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-sky-100 transition hover:bg-white/10 hover:text-white"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>
                    <a href="mailto:rgv_services@yahoo.com" aria-label="Email RGV Multi-Tech Services" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-sky-100 transition hover:bg-white/10 hover:text-white"><i class="fa-regular fa-envelope" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-white/15 px-4 py-4 text-center text-xs text-sky-100/70">&copy; 2026 RGV Multi-Tech Services. All rights reserved.</div>
    </footer>

    <script>
        const projectImages = @json($projectImages);
        const projectAssetBase = @json(asset('project-highlights'));
        let projectIndex = 0;

        function updateProjectCard(id, imageName, label) {
            const card = document.getElementById(id);
            const image = card.querySelector('img');
            image.src = `${projectAssetBase}/${imageName}.jpg`;
            image.alt = `${label} project highlight`;
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
    <x-toast-container />
</body>
</html>
