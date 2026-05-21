<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs - RGV Multi-Tech Services</title>
    @include('pdf.partials.styles')
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; word-wrap: break-word; }
        th { background-color: #1e40af; color: white; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { border: 1px solid #e5e7eb; padding: 6px; font-size: 11px; overflow-wrap: break-word; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .col-date { width: 20%; }
        .col-event { width: 18%; }
        .col-user { width: 20%; }
        .col-ip { width: 18%; }
        .col-checksum { width: 12%; }
        .col-subject { width: 12%; }
        .valid { color: #166534; }
        .invalid { color: #dc2626; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Audit Logs'])

    <table>
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-event">Event</th>
                <th class="col-user">User</th>
                <th class="col-ip">IP Address</th>
                <th class="col-subject">Subject</th>
                <th class="col-checksum">Checksum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ $log->event }}</td>
                    <td>{{ $log->user?->email ?? 'System' }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td>{{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '—' }}</td>
                    <td>
                        @if(method_exists($log, 'isChecksumValid'))
                            <span class="{{ $log->isChecksumValid() ? 'valid' : 'invalid' }}">
                                {{ $log->isChecksumValid() ? 'Valid' : 'Invalid' }}
                            </span>
                        @else
                            <span class="valid">Valid</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-row">
        <p>Total Entries: {{ $logs->count() }}</p>
    </div>
</body>
</html>
