import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

// Shared dropdown function for sidebar navigation
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const chevron = document.getElementById(dropdownId.replace('-dropdown', '-chevron'));

    if (dropdown && chevron) {
        dropdown.classList.toggle('open');
        chevron.classList.toggle('rotate');
    }
}

// Auto-open relevant dropdown based on current page
function autoOpenDropdown() {
    const currentPath = window.location.pathname;

    if (currentPath.includes('bookings')) {
        const dropdown = document.getElementById('bookings-dropdown');
        const chevron = document.getElementById('bookings-chevron');
        if (dropdown) dropdown.classList.add('open');
        if (chevron) chevron.classList.add('rotate');
    } else if (currentPath.includes('inventory')) {
        const dropdown = document.getElementById('inventory-dropdown');
        const chevron = document.getElementById('inventory-chevron');
        if (dropdown) dropdown.classList.add('open');
        if (chevron) chevron.classList.add('rotate');
    } else if (currentPath.includes('reports')) {
        const dropdown = document.getElementById('reports-dropdown');
        const chevron = document.getElementById('reports-chevron');
        if (dropdown) dropdown.classList.add('open');
        if (chevron) chevron.classList.add('rotate');
    } else if (currentPath.includes('users')) {
        const dropdown = document.getElementById('users-dropdown');
        const chevron = document.getElementById('users-chevron');
        if (dropdown) dropdown.classList.add('open');
        if (chevron) chevron.classList.add('rotate');
    } else if (currentPath.includes('borrow-requests')) {
        const dropdown = document.getElementById('borrow-dropdown');
        const chevron = document.getElementById('borrow-chevron');
        if (dropdown) dropdown.classList.add('open');
        if (chevron) chevron.classList.add('rotate');
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    autoOpenDropdown();
});

// Make functions globally available
window.toggleDropdown = toggleDropdown;
