<x-mail::message>
# Backup Completed Successfully

Your application backup has been created successfully.

**Details:**
- **Disk:** {{ $disk }}
- **Size:** {{ $size }}
- **Completed:** {{ $completedAt }}
- **Checksum (SHA-256):** {{ substr($checksum ?? '—', 0, 16) }}...

The backup is stored securely and will be retained according to the configured retention policy.

<x-mail::button url="{{ url('/admin/backups') }}">
View Backup History
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
