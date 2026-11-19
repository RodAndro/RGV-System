@extends('layouts.admin')

@section('title', 'Backup Settings')

@section('content')
<div class="p-6 space-y-6">
    <section class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Backup Settings</h1>
            <a href="{{ route('admin.backups.index') }}" class="text-sm text-[#1e40af] hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>Back to Backups
            </a>
        </div>

        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Current Schedule</h3>
            <div class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                @foreach($schedule as $item)
                    <div class="flex justify-between items-center px-4 py-3">
                        <span class="text-sm text-gray-700"><i class="fas fa-clock text-gray-400 mr-2"></i>{{ $item['task'] }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $item['schedule'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-2">Schedule is configured in the application code. Contact a developer to modify timing.</p>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Notification Settings</h3>
            <form method="POST" action="{{ route('admin.backups.settings.update') }}" class="space-y-4 max-w-lg">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notification Email</label>
                    <input type="email" name="notification_email" value="{{ old('notification_email', $notificationEmail) }}"
                        class="mt-1 block w-full rounded border-gray-300"
                        placeholder="admin@example.com">
                    <p class="text-xs text-gray-400 mt-1">Email address to receive backup success/failure notifications. Leave blank to disable.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Admin Update Email</label>
                    <input type="email" name="admin_notification_email" value="{{ old('admin_notification_email', $adminNotificationEmail) }}"
                        class="mt-1 block w-full rounded border-gray-300"
                        placeholder="admin@gmail.com">
                    <p class="text-xs text-gray-400 mt-1">Email address that receives notifications when bookings, inventory, or borrow requests are updated. Falls back to the Notification Email above if left blank.</p>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="backup_to_s3" value="1" {{ $backupToS3 ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Backup to S3 cloud storage</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1 ml-6">Requires AWS credentials to be configured in the environment.</p>
                </div>

                <button type="submit" class="rounded bg-[#2563eb] px-6 py-2 font-semibold text-white">Save Settings</button>
            </form>
        </div>
    </section>
</div>
@endsection
