@extends('layouts.admin')

@section('title', 'Import / Export')

@section('content')
<div class="p-6 space-y-6">

    <section class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">Import / Export</h1>

        <form id="inventory-import-form" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Inventory CSV/XLSX</label>
                <input type="file" name="file" id="inventory-file" class="mt-1 block w-full rounded border-gray-300" required accept=".csv,.txt,.xlsx,.xls">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duplicates</label>
                <select name="duplicate_strategy" class="mt-1 block w-full rounded border-gray-300">
                    <option value="skip">Skip</option>
                    <option value="update">Update</option>
                </select>
            </div>
            <button type="button" onclick="previewImport('inventory')" class="rounded bg-gray-600 px-4 py-2 font-semibold text-white text-sm">
                <i class="fas fa-eye mr-1"></i>Preview
            </button>
            <button type="button" onclick="submitImport('inventory')" class="rounded bg-[#2563eb] px-4 py-2 font-semibold text-white text-sm">
                Queue Import
            </button>
        </form>
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Bulk User Creation</h2>
        <form id="users-import-form" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-3 items-end">
            @csrf
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Users CSV/XLSX</label>
                <input type="file" name="file" id="users-file" class="mt-1 block w-full rounded border-gray-300" required accept=".csv,.txt,.xlsx,.xls">
            </div>
            <button type="button" onclick="previewImport('users')" class="rounded bg-gray-600 px-4 py-2 font-semibold text-white text-sm">
                <i class="fas fa-eye mr-1"></i>Preview
            </button>
            <button type="button" onclick="submitImport('users')" class="rounded bg-[#2563eb] px-4 py-2 font-semibold text-white text-sm">
                Queue User Import
            </button>
        </form>
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Exports</h2>
        <div class="flex flex-wrap gap-3">
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.inventory.export', ['format' => 'xlsx']) }}">Inventory XLSX</a>
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.inventory.export', ['format' => 'csv']) }}">Inventory CSV</a>
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.inventory.export', ['format' => 'pdf']) }}">Inventory PDF</a>
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.bookings.export', ['format' => 'xlsx']) }}">Bookings XLSX</a>
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.bookings.export', ['format' => 'csv']) }}">Bookings CSV</a>
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('admin.import-export.users.export', ['format' => 'xlsx']) }}">Users Redacted XLSX</a>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Logs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th>ID</th><th>Type</th><th>Status</th><th>Progress</th><th>Success</th><th>Failed</th><th></th></tr></thead>
                    <tbody>
                        @foreach($imports as $import)
                            <tr class="border-t" x-data="{
                                progress: {{ $import->progress() }},
                                status: '{{ $import->status }}',
                                errorPath: {{ json_encode($import->error_report_path) }}
                            }" x-init="if (['queued','processing'].includes(status)) {
                                setInterval(async () => {
                                    try {
                                        const res = await fetch('{{ route('admin.import-export.imports.status', $import) }}');
                                        const data = await res.json();
                                        progress = data.progress;
                                        status = data.status;
                                        errorPath = data.error_report_path;
                                    } catch(e) {}
                                }, 3000);
                            }">
                                <td class="py-2">#{{ $import->id }}</td>
                                <td>{{ $import->type }}</td>
                                <td>
                                    <span x-show="status === 'queued'" class="badge-mantis-info">Queued</span>
                                    <span x-show="status === 'processing'" class="badge-mantis-warning">Processing</span>
                                    <span x-show="status === 'completed'" class="badge-mantis-success">Completed</span>
                                    <span x-show="status === 'failed'" class="badge-mantis-danger">Failed</span>
                                </td>
                                <td>
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-[#2563eb] h-2 rounded-full transition-all" :style="'width:' + progress + '%'"></div>
                                    </div>
                                    <span class="text-xs text-gray-500" x-text="progress + '%'"></span>
                                </td>
                                <td>{{ $import->successful_rows }}</td>
                                <td>{{ $import->failed_rows }}</td>
                                <td>
                                    @if($import->failed_rows > 0 && $import->error_report_path)
                                        <a href="{{ route('admin.import-export.imports.errors', $import) }}"
                                           class="text-red-600 hover:underline text-xs">
                                            <i class="fas fa-download mr-1"></i>Errors ({{ $import->failed_rows }})
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $imports->links() }}
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Export Logs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="text-left text-gray-500"><th>ID</th><th>Type</th><th>Status</th><th>Rows</th><th></th></tr></thead>
                    <tbody>
                        @foreach($exports as $export)
                            <tr class="border-t">
                                <td class="py-2">#{{ $export->id }}</td>
                                <td>{{ $export->type }} {{ strtoupper($export->format) }}</td>
                                <td>{{ $export->status }}</td>
                                <td>{{ $export->record_count }}</td>
                                <td>
                                    @if($export->file_path)
                                        <a class="text-[#1e40af]" href="{{ route('admin.import-export.exports.download', $export) }}">Download</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $exports->links() }}
        </div>
    </section>
</div>

<!-- Preview Modal -->
<x-modal name="import-preview" maxWidth="3xl">
    <div class="p-6" x-data="{ preview: null, error: null }" x-on:import-preview-result.window="preview = $event.detail.preview; error = $event.detail.error">
        <h3 class="text-lg font-bold text-gray-900 mb-3">Import Preview</h3>

        <div x-show="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
            <i class="fas fa-exclamation-circle mr-2"></i><span x-text="error"></span>
        </div>

        <template x-if="preview">
            <div>
                <div class="flex gap-4 mb-3 text-sm">
                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i><span x-text="preview.valid"></span> valid</span>
                    <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i><span x-text="preview.invalid"></span> invalid</span>
                    <span class="text-gray-500">of <span x-text="preview.total"></span> rows shown</span>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left text-gray-500 bg-gray-50">
                                <th class="py-1 px-2">Row</th>
                                <template x-for="h in preview.headers" :key="h">
                                    <th class="py-1 px-2" x-text="h"></th>
                                </template>
                                <th class="py-1 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="r in preview.valid_rows" :key="'v' + r.row">
                                <tr class="border-t bg-green-50">
                                    <td class="py-1 px-2 text-green-600 font-medium" x-text="r.row"></td>
                                    <template x-for="h in preview.headers" :key="h">
                                        <td class="py-1 px-2" x-text="r.data[h] || ''"></td>
                                    </template>
                                    <td class="py-1 px-2 text-green-600"><i class="fas fa-check"></i></td>
                                </tr>
                            </template>
                            <template x-for="r in preview.invalid_rows" :key="'i' + r.row">
                                <tr class="border-t bg-red-50">
                                    <td class="py-1 px-2 text-red-600 font-medium" x-text="r.row"></td>
                                    <td class="py-1 px-2 text-red-600" colspan="100" x-text="r.errors.join(', ')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div class="flex justify-end mt-4">
            <button type="button" @click="$dispatch('close-modal', 'import-preview')"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                Close
            </button>
        </div>
    </div>
</x-modal>

@push('scripts')
<script>
    function previewImport(type) {
        const form = document.getElementById(type + '-import-form');
        const fileInput = document.getElementById(type + '-file');
        const file = fileInput.files[0];

        if (!file) {
            alert('Please select a file first.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        formData.append('_token', document.querySelector('meta[name=csrf-token]').content);

        const strategy = form.querySelector('[name="duplicate_strategy"]');
        if (strategy) {
            formData.append('duplicate_strategy', strategy.value);
        }

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'import-preview' }));

        fetch('{{ route('admin.import-export.preview') }}', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            window.dispatchEvent(new CustomEvent('import-preview-result', {
                detail: data.error
                    ? { preview: null, error: data.error }
                    : { preview: data, error: null }
            }));
        })
        .catch(err => {
            window.dispatchEvent(new CustomEvent('import-preview-result', {
                detail: { preview: null, error: 'Failed to preview file: ' + err.message }
            }));
        });
    }

    function submitImport(type) {
        const fileInput = document.getElementById(type + '-file');

        if (!fileInput.files[0]) {
            alert('Please select a file first.');
            return;
        }

        const url = type === 'inventory'
            ? '{{ route('admin.import-export.inventory.import') }}'
            : '{{ route('admin.import-export.users.import') }}';

        const form = document.getElementById(type + '-import-form');
        const formData = new FormData(form);

        formData.set('_token', document.querySelector('meta[name=csrf-token]').content);

        fetch(url, { method: 'POST', body: formData })
            .then(() => window.location.reload());
    }
</script>
@endpush
</div>
@endsection
