<x-mail::message>
# {{ $title }}

A new report has been generated for your review.

**Type:** {{ ucwords(str_replace('_', ' ', $type)) }}
**Date:** {{ $reportDate }}

**Summary:**
{{ $summary }}

<x-mail::button url="{{ url('/admin/reports') }}">
View Reports
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
