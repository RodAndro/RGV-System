<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Detail - RGV Multi-Tech Services</title>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Booking Detail Report'])

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Reference Number</div>
            <div class="info-value">{{ $booking->reference_number }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">
                <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Customer Name</div>
            <div class="info-value">{{ $booking->full_name }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value">{{ $booking->email }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Contact Number</div>
            <div class="info-value">{{ $booking->contact_number }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Purpose Category</div>
            <div class="info-value">{{ ucfirst(str_replace('-', ' ', $booking->purpose_category)) }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Preferred Date</div>
            <div class="info-value">{{ $booking->preferred_date->format('F d, Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Preferred Time</div>
            <div class="info-value">{{ $booking->preferred_time }}</div>
        </div>
    </div>

    <div class="section-title">Address</div>
    <div class="info-value">{{ $booking->address }}</div>

    <div class="section-title">Reason</div>
    <div class="info-value">{{ $booking->reason }}</div>

    @if($booking->employee)
        <div class="section-title">Assigned Employee</div>
        <div class="info-value">{{ $booking->employee?->name ?? 'Unassigned' }}</div>
    @endif

    @if($booking->remarks)
        <div class="section-title">Remarks</div>
        <div class="remarks">{{ $booking->remarks }}</div>
    @endif

    <div class="section-title">Timeline</div>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Created At</div>
            <div class="info-value">{{ $booking->created_at->format('F d, Y - g:i A') }}</div>
        </div>
        @if($booking->approved_at)
            <div class="info-item">
                <div class="info-label">Approved At</div>
                <div class="info-value">{{ $booking->approved_at->format('F d, Y - g:i A') }}</div>
            </div>
        @endif
        @if($booking->completed_at)
            <div class="info-item">
                <div class="info-label">Completed At</div>
                <div class="info-value">{{ $booking->completed_at->format('F d, Y - g:i A') }}</div>
            </div>
        @endif
        @if($booking->cancelled_at)
            <div class="info-item">
                <div class="info-label">Cancelled At</div>
                <div class="info-value">{{ $booking->cancelled_at->format('F d, Y - g:i A') }}</div>
            </div>
        @endif
    </div>

    <div class="signature-section">
        <div class="section-title">Digital Signature</div>
        <p style="font-size: 10px; color: #888; margin-bottom: 20px;">This document serves as an official record. Retain a signed copy for your files.</p>
        <div style="display: flex; gap: 30px;">
            <div style="flex: 1;">
                <div class="signature-line"></div>
                <p class="signature-label">Authorized Signature</p>
            </div>
            <div style="flex: 1;">
                <div class="signature-line"></div>
                <p class="signature-label">Printed Name</p>
            </div>
            <div style="flex: 1;">
                <div class="signature-line"></div>
                <p class="signature-label">Date</p>
            </div>
        </div>
    </div>
</body>
</html>
