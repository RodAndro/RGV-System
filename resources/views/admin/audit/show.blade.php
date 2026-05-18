@extends('layouts.admin')

@section('title', 'Audit Diff')

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-2xl font-semibold text-gray-900">Audit Diff #{{ $auditLog->id }}</h1>
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold mb-3">Old Values</h2>
            <pre class="text-xs whitespace-pre-wrap">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </section>
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold mb-3">New Values</h2>
            <pre class="text-xs whitespace-pre-wrap">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </section>
    </div>
</div>
@endsection
