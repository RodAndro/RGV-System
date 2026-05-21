<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bookings Report - RGV Multi-Tech Services</title>
    @include('pdf.partials.styles')
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; word-wrap: break-word; }
        th { background-color: #1e40af; color: white; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { border: 1px solid #e5e7eb; padding: 6px; font-size: 11px; overflow-wrap: break-word; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .col-ref { width: 12%; }
        .col-name { width: 16%; }
        .col-email { width: 16%; }
        .col-contact { width: 12%; }
        .col-date { width: 9%; }
        .col-time { width: 8%; }
        .col-category { width: 10%; }
        .col-status { width: 9%; }
        .col-assigned { width: 8%; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Bookings Report'])

    <table>
        <thead>
            <tr>
                <th class="col-ref">Reference</th>
                <th class="col-name">Customer</th>
                <th class="col-email">Email</th>
                <th class="col-contact">Contact</th>
                <th class="col-date">Date</th>
                <th class="col-time">Time</th>
                <th class="col-category">Category</th>
                <th class="col-status">Status</th>
                <th class="col-assigned">Assigned</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->reference_number }}</td>
                    <td>{{ $booking->full_name }}</td>
                    <td>{{ $booking->email }}</td>
                    <td>{{ $booking->contact_number }}</td>
                    <td>{{ $booking->preferred_date->format('M d, Y') }}</td>
                    <td>{{ $booking->preferred_time }}</td>
                    <td>{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</td>
                    <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                    <td>{{ $booking->employee ? $booking->employee->name : 'Unassigned' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-row">
        <p>Total Bookings: {{ $bookings->count() }}</p>
    </div>
</body>
</html>
