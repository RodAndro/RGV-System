<x-mail::message>
# ⚠️ Backup Failed — Action Required

A scheduled backup has failed and requires your attention.

**Details:**
- **Disk:** {{ $disk }}
- **Failed at:** {{ $failedAt }}

**Error:**
```
{{ $error }}
```

Please investigate the issue and ensure backups are running correctly.

<x-mail::button url="{{ url('/admin/backups') }}">
View Backup Status
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
