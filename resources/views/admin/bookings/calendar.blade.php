@extends('layouts.admin')

@section('title', 'Booking Calendar - Admin Dashboard')

@section('header', 'Booking Calendar')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
@endpush

@section('content')
<div class="p-4 md:p-8">
    <div class="card-mantis p-6">
        <div id="calendar"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var bookings = @json($bookings->map(function($booking) {
            return [
                'title' => $booking->full_name,
                'start' => $booking->preferred_date->format('Y-m-d') + 'T' . $booking->preferred_time,
                'backgroundColor' => $booking->status === 'approved' ? '#2563eb' : ($booking->status === 'pending' ? '#fbbf24' : '#1d4ed8'),
                'borderColor' => $booking->status === 'approved' ? '#2563eb' : ($booking->status === 'pending' ? '#fbbf24' : '#1d4ed8'),
                'url' => '/admin/bookings/' + $booking->id,
                'extendedProps' => { 'status': $booking->status, 'reference': $booking->reference_number }
            ];
        }));

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
            events: bookings,
            eventClick: function(info) { info.jsEvent.preventDefault(); if (info.event.url) window.open(info.event.url, '_blank'); },
            eventDidMount: function(info) {
                var tooltip = document.createElement('div');
                tooltip.innerHTML = '<div class="bg-gradient-to-br from-[#1e40af] to-[#2563eb] text-white px-4 py-3 rounded-xl shadow-lg shadow-blue-500/30 text-sm"><p class="font-semibold">' + info.event.title + '</p><p class="text-white/80">' + info.event.start.toLocaleString() + '</p><p class="text-white/80">Ref: ' + info.event.extendedProps.reference + '</p><p class="text-white/80">Status: ' + info.event.extendedProps.status + '</p></div>';
                tooltip.className = 'custom-tooltip';
                document.body.appendChild(tooltip);
                info.el.addEventListener('mouseenter', function(e) { tooltip.style.display = 'block'; tooltip.style.position = 'absolute'; tooltip.style.left = e.pageX + 'px'; tooltip.style.top = e.pageY + 'px'; tooltip.style.zIndex = '1000'; });
                info.el.addEventListener('mouseleave', function() { tooltip.style.display = 'none'; });
            },
            height: 'auto',
        });
        calendar.render();
    });
</script>
@endpush
