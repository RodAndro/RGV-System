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
            --mantis: #74c365;
            --mantis-dark: #468a3f;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center shadow-lg shadow-[#74c365]/30">
                            <i class="fas fa-cogs text-white text-lg"></i>
                        </div>
                        <span class="font-bold text-xl text-gray-800 ml-3">RGV Multi-Tech Services</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('booking.form') }}" class="bg-gradient-to-r from-[#74c365] to-[#5dad4f] text-white px-4 py-2 rounded-xl hover:from-[#5dad4f] hover:to-[#468a3f] transition-all shadow-lg shadow-[#74c365]/30">
                            <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                        </a>
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

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-[#74c365] to-[#5dad4f] text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-10 lg:mb-0">
                    <h1 class="text-4xl lg:text-5xl font-bold mb-6">
                        Professional Multi-Tech Services for Your Business
                    </h1>
                    <p class="text-xl mb-8 text-white/90">
                        RGV Multi-Tech Services provides comprehensive technical solutions with a focus on quality, efficiency, and customer satisfaction.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('booking.form') }}" class="bg-white text-[#74c365] px-8 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-all text-center shadow-lg">
                            <i class="fas fa-calendar-check mr-2"></i>Book Now
                        </a>
                        <a href="#services" class="border-2 border-white text-white px-8 py-3 rounded-xl font-semibold hover:bg-white hover:text-[#74c365] transition-all text-center">
                            <i class="fas fa-info-circle mr-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 text-center">
                        <i class="fas fa-tools text-9xl mb-4"></i>
                        <p class="text-2xl font-semibold">Expert Technical Services</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">We offer a wide range of technical services to meet your business needs</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-wrench text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Equipment Repair</h3>
                    <p class="text-gray-600">Professional repair and maintenance for all types of technical equipment</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-tools text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Tool Rentals</h3>
                    <p class="text-gray-600">High-quality tools available for rent at competitive rates</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-cog text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Technical Consultation</h3>
                    <p class="text-gray-600">Expert advice and consultation for your technical projects</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">What Our Clients Say</h2>
                <p class="text-gray-600">Trusted by businesses across the region</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl p-6 shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-[#74c365]/30">JD</div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-800">Pablito Acma</p>
                            <p class="text-sm text-gray-600">Business Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Excellent service and very professional team. Highly recommended for all technical needs."</p>
                    <div class="mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-[#74c365]/30">JS</div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-800">Rod Andro Cutillar</p>
                            <p class="text-sm text-gray-600">Project Manager</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Fast response time and quality work. They've been our go-to for technical services."</p>
                    <div class="mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-[#74c365]/30">MJ</div>
                        <div class="ml-4">
                            <p class="font-semibold text-gray-800">Ronald Mark Dalajota</p>
                            <p class="text-sm text-gray-600">Contractor</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Great inventory system and easy booking process. Makes our work much more efficient."</p>
                    <div class="mt-4 text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Contact Us</h2>
                <p class="text-gray-600">Get in touch with us for any inquiries</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Location</h3>
                    <p class="text-gray-600">123 Tech Street, Business District, Philippines</p>
                </div>
                <div class="text-center">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Phone</h3>
                    <p class="text-gray-600">+63 912 345 6789</p>
                </div>
                <div class="text-center">
                    <div class="bg-[#f0f9ef] w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-envelope text-[#74c365] text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Email</h3>
                    <p class="text-gray-600">info@rgvtech.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-[#74c365] to-[#5dad4f] rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-cogs text-white text-lg"></i>
                        </div>
                        <span class="font-bold text-xl">RGV Multi-Tech</span>
                    </div>
                    <p class="text-gray-400">Your trusted partner for all technical services and solutions.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('booking.form') }}" class="text-gray-400 hover:text-white transition">Book Appointment</a></li>
                        <li><a href="#services" class="text-gray-400 hover:text-white transition">Services</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Equipment Repair</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Tool Rentals</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Consultation</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 RGV Multi-Tech Services. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
