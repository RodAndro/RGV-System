<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Notification Preferences
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <p class="text-sm text-gray-600 mb-6">Choose how you'd like to receive notifications for each type.</p>

                <form method="POST" action="{{ route('profile.notification-preferences.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        @foreach($preferences as $type => $pref)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $type)) }}</p>
                                    <p class="text-xs text-gray-500">
                                        @switch($type)
                                            @case('booking') Work request updates @break
                                            @case('borrow_request') Borrow request status changes @break
                                            @case('inventory') Stock alerts and inventory changes @break
                                            @case('system') System maintenance and backups @break
                                            @case('user') Account status and changes @break
                                        @endswitch
                                    </p>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <input type="hidden" name="preferences[{{ $loop->index }}][type]" value="{{ $type }}">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="preferences[{{ $loop->index }}][email_enabled]" value="1" {{ $pref->email_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                        <span class="text-sm text-gray-600">Email</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="preferences[{{ $loop->index }}][in_app_enabled]" value="1" {{ $pref->in_app_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                        <span class="text-sm text-gray-600">In-App</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>Save Preferences</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-[#2563eb] underline">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
